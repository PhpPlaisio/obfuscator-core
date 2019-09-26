<?php
declare(strict_types=1);

namespace SetBased\Abc\Command;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use SetBased\Exception\RuntimeException;
use SetBased\Stratum\Middle\Helper\RowSetHelper;
use SetBased\Stratum\MySql\StaticDataLayer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class for creating parameters for reference obfuscator.
 */
class ReferenceObfuscatorCommand extends Command
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The configuration parameters.
   *
   * @var array<string,mixed>
   */
  private $config;

  /**
   * The name of the configuration file.
   *
   * @var string
   */
  private $configFileName;

  /**
   * Number of bytes of MySQL integer types.
   *
   * @var array
   */
  private $integerTypeSizes = ['tinyint'   => 1,
                               'smallint'  => 2,
                               'mediumint' => 3,
                               'int'       => 4,
                               'bigint'    => 8];

  /**
   * The PSR-3 logger.
   *
   * @var LoggerInterface
   */
  private $logger;

  /**
   * Metadata of all tables with auto increment columns.
   *
   * @var array[]
   */
  private $tables;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Compares obfuscator metadata for sorting.
   *
   * @param array $a The first metadata.
   * @param array $b The second metadata.
   *
   * @return int
   */
  public static function compare(array $a, array $b): int
  {
    if (strtolower($a['label'])==strtolower($b['label']))
    {
      return 0;
    }

    return (strtolower($a['label'])>strtolower($b['label'])) ? 1 : -1;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function configure()
  {
    $this->setName('dev:reference-obfuscator-generator')
         ->setDescription('Generates the keys and mask for the Reference Obfuscator')
         ->addArgument('config.json', InputArgument::REQUIRED, 'The configuration file');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->configFileName = $input->getArgument('config.json');
    $this->readConfigFile($this->configFileName);

    $this->extractDatabaseIds();
    $this->generateConstants();
    $this->writeConstant();

    return 0;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Initializes the PSR-3 logger.
   *
   * @param InputInterface  $input  An InputInterface instance.
   * @param OutputInterface $output An OutputInterface instance.
   *
   * @throws \Exception
   */
  protected function initialize(InputInterface $input, OutputInterface $output)
  {
    $verbosityLevelMap = [LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL];
    $this->logger      = new ConsoleLogger($output, $verbosityLevelMap);

    // Create style for database objects.
    $style = new OutputFormatterStyle(null, null, ['bold']);
    $output->getFormatter()->setStyle('dbo', $style);

    // Create style for file and directory names.
    $style = new OutputFormatterStyle(null, null, ['bold']);
    $output->getFormatter()->setStyle('fso', $style);

    // Create style for SQL statements.
    $style = new OutputFormatterStyle('magenta', null, ['bold']);
    $output->getFormatter()->setStyle('sql', $style);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Reads configuration parameters from the configuration file.
   *
   * @param string $configFilename The name of the configuration file.
   */
  protected function readConfigFile(string $configFilename): void
  {
    $content      = file_get_contents($configFilename);
    $this->config = json_decode($content, true);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Writes a file in two phase to the filesystem.
   *
   * First write the data to a temporary file (in the same directory) and than renames the temporary file. If the file
   * already exists and its content is equal to the data that must be written no action  is taken. This has the
   * following advantages:
   * * In case of some write error (e.g. disk full) the original file is kept in tact and no file with partially data
   * is written.
   * * Renaming a file is atomic. So, running processes will never read a partially written data.
   *
   * @param string $filename The name of the file were the data must be stored.
   * @param string $data     The data that must be written.
   */
  protected function writeTwoPhases(string $filename, string $data): void
  {
    $write_flag = true;
    if (file_exists($filename))
    {
      $oldData = file_get_contents($filename);
      if ($data==$oldData) $write_flag = false;
    }

    if ($write_flag)
    {
      $tmpFilename = $filename.'.tmp';
      file_put_contents($tmpFilename, $data);
      rename($tmpFilename, $filename);

      $this->logger->notice(sprintf('Wrote <fso>%s</fso>', OutputFormatter::escape($filename)));
    }
    else
    {
      $this->logger->notice(sprintf('File <fso>%s</fso> is up to date', OutputFormatter::escape($filename)));
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts metadata about tables with autoincrement columns.
   */
  private function extractDatabaseIds(): void
  {
    $query = "
select table_name
,      column_name
,      data_type
from   information_schema.columns
where table_schema = database()
and   extra        = 'auto_increment'
order by table_name";

    StaticDataLayer::connect($this->getConfig('database/host_name'),
                             $this->getConfig('database/user_name'),
                             $this->getConfig('database/password'),
                             $this->getConfig('database/database_name'));
    $tables = StaticDataLayer::executeRows($query);
    StaticDataLayer::disconnect();

    // Remove the table to be ignored.
    $ignore = $this->getConfig('ignore', false);
    foreach ($tables as $table)
    {
      if (!in_array($table['table_name'], $ignore))
      {
        $this->tables[] = $table;
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Searches for 3 lines in the source code with reference obfuscator parameters. The lines are:
   * <ul>
   * <li> The first line of the doc block with the annotation '@setbased.abc.obfuscator'.
   * <li> The last line of this doc block.
   * <li> The last line of array declarations directly after the doc block.
   * </ul>
   * If one of these line can not be found the line number will be set to null.
   *
   * @param string $source The source code of the PHP file.
   *
   * @return array With the 3 line numbers as described.
   */
  private function extractLines(string $source): array
  {
    $tokens = token_get_all($source);

    $line1 = null;
    $line2 = null;
    $line3 = null;

    // Find annotation @setbased.abc.obfuscator
    $step = 1;
    foreach ($tokens as $key => $token)
    {
      switch ($step)
      {
        case 1:
          // Step 1: Find doc comment with annotation.
          if (is_array($token) && $token[0]==T_DOC_COMMENT)
          {
            if (strpos($token[1], '@setbased.abc.obfuscator')!==false)
            {
              $line1 = $token[2];
              $step  = 2;
            }
          }
          break;

        case 2:
          // Step 2: Find end of doc block.
          if (is_array($token))
          {
            if ($token[0]==T_WHITESPACE)
            {
              $line2 = $token[2];
            }
            else
            {
              $step = 3;
            }
          }
          break;

        case 3:
          // Step 4: Find end of array declaration.
          if (is_string($token))
          {
            if ($token==']' && $tokens[$key + 1]==';')
            {
              if ($tokens[$key + 2][0]==T_WHITESPACE)
              {
                $line3 = $tokens[$key + 2][2];
                $step  = 4;
              }
            }
          }
          break;

        case 4:
          // Leave loop.
          break;
      }
    }

    return [$line1, $line2, $line3];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Creates array declaration ($length, $key, $mask) for each database ID.
   */
  private function generateConstants(): void
  {
    // Get constants already defined.
    $defined = $this->getConfig('constants', true);

    // Get class for deriving the label from table metadata.
    $mangler = $this->getConfig('mangler', true);

    $this->removeObsoleteConstants();

    foreach ($this->tables as $table)
    {
      if (!isset($defined[$table['table_name']]))
      {
        // Key and mask is not yet defined for $label. Generate key and mask.
        $this->logger->notice(sprintf('Generating key and mask for table <dbo>%s</dbo>', $table['table_name']));

        $size = $this->integerTypeSizes[$table['data_type']];
        $key  = rand(1, pow(2, 16) - 1);
        $mask = rand(pow(2, 8 * $size - 1), pow(2, 8 * $size) - 1);

        $label = $mangler::getLabel($table);
        $other = $this->getTableByLabel($label);
        if ($other)
        {
          throw new RuntimeException("Tables '%s' and '%s' have the same label '%s'.",
                                     $table['table_name'],
                                     $other,
                                     $label);
        }

        $this->config['constants'][$table['table_name']] = ['label' => $label,
                                                            'size'  => $size,
                                                            'key'   => $key,
                                                            'mask'  => $mask];
      }
    }

    // Save the configuration file.
    $this->rewriteConfig();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Gets variable from config by path.
   *
   * @param string $path      The forward slash separated path of the variable.
   * @param bool   $mandatory If set the variable is mandatory and when the variable is not set an exception will be
   *                          thrown.
   *
   * @return mixed
   */
  private function getConfig(string $path, bool $mandatory = true)
  {
    $ret  = null;
    $keys = explode('/', $path);

    $config = $this->config;
    foreach ($keys as $key)
    {
      if (!isset($config[$key]))
      {
        // If the config variable is mandatory throw a runtime exception.
        if ($mandatory)
        {
          throw new RuntimeException("Variable '%s' not set in configuration file '%s'", $path, $this->configFileName);
        }

        // Otherwise, leave the loop.
        $ret = null;
        break;
      }
      else
      {
        $config = $config[$key];
        $ret    = $config;
      }
    }

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Searches for a table name based on a label.
   *
   * @param string $label The label to search for.
   *
   * @return string|null The table name of the table with  the label, null if no table with the label exists.
   */
  private function getTableByLabel(string $label): ?string
  {
    foreach ($this->config['constants'] as $tableName => $constant)
    {
      if ($constant['label']==$label)
      {
        return $tableName;
      }
    }

    return null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns PHP snippet with an array declaration for reference obfuscator.
   *
   * @param int $indent The number of space indenting the array declaration.
   *
   * @return string[]
   */
  private function makeVariableStatements(int $indent): array
  {
    // Sort constants by label.
    uasort($this->config['constants'], __CLASS__.'::compare');

    $variable = "[\n";
    foreach ($this->getConfig('constants') as $value)
    {
      $variable .= sprintf("  %s'%s' => [%s, %s, %s],\n",
                           str_repeat(' ', $indent),
                           $value['label'],
                           $value['size'],
                           $value['key'],
                           $value['mask']);
    }
    $variable .= sprintf('%s]', str_repeat(' ', $indent));

    $constants   = [];
    $constants[] = sprintf('%s%s = %s;',
                           str_repeat(' ', $indent),
                           $this->getConfig('variable'),
                           $variable);

    return $constants;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Removes obsolete constants from the configuration.
   */
  private function removeObsoleteConstants(): void
  {
    $constants = $this->getConfig('constants', true);
    foreach ($constants as $tableName => $const)
    {
      $check = RowSetHelper::searchInRowSet($this->tables, 'table_name', $tableName);
      if ($check===null)
      {
        unset($this->config['constants'][$tableName]);
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Saves the configuration data to the configuration file.
   */
  private function rewriteConfig(): void
  {
    // Sort array with labels, keys and masks by label.
    ksort($this->config['constants']);

    $this->writeTwoPhases($this->configFileName, json_encode($this->config, JSON_PRETTY_PRINT));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Insert new and replace old (if any) array declaration for reference obfuscator in a PHP source file.
   */
  private function writeConstant(): void
  {
    $source = file_get_contents($this->getConfig('file'));
    $lines  = explode("\n", $source);

    // Search for the lines where to insert and replace constant declaration statements.
    $lineNumbers = $this->extractLines($source);
    if (!isset($lineNumbers[0]))
    {
      throw new RuntimeException("Annotation not found in '%s'", $this->getConfig('file'));
    }

    // Generate the variable statements.
    $indent    = strlen($lines[$lineNumbers[0] - 1]) - strlen(ltrim($lines[$lineNumbers[0] - 1]));
    $constants = $this->makeVariableStatements($indent);

    // Insert new and replace old (if any) constant declaration statements.
    $tmp1  = array_splice($lines, 0, $lineNumbers[1]);
    $tmp2  = array_splice($lines, (isset($lineNumbers[2])) ? $lineNumbers[2] - $lineNumbers[1] : 0);
    $lines = array_merge($tmp1, $constants, $tmp2);

    // Save the file.
    $this->writeTwoPhases($this->getConfig('file'), implode("\n", $lines));
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

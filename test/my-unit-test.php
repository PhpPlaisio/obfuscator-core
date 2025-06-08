<?php
declare(strict_types=1);

/**
 * This test cause PHPUnit to bark about PHP deprecation in PHP 8.4.
 */

use Plaisio\Obfuscator\Exception\DecodeException;
use Plaisio\Obfuscator\ReferenceObfuscatorFactory;
use SetBased\ErrorHandler\ErrorHandler;

require_once(__DIR__.'/../vendor/autoload.php');

$errorHandler = new ErrorHandler();
$errorHandler->registerErrorHandler();

$mask                               = mt_rand(2147483647, 4294967295);
$key                                = mt_rand(0, 65535);
ReferenceObfuscatorFactory::$labels = ['abc' => [4, $key, $mask]];

try
{
  $obfuscator = ReferenceObfuscatorFactory::create('abc');
  $obfuscator->decode('123abcg');
}
catch (DecodeException)
{
  return 0;
}

return -1;

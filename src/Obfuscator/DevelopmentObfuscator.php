<?php

namespace SetBased\Abc\Obfuscator;

/**
 * An Obfuscator for development environments only.
 *
 * This Obfuscator just prepends the label of a database ID to the database ID. This allows for easy inspecting database
 * IDs in URLs and HTML code and detecting programming errors where a database ID is obfuscated and de-obfuscated with
 * different labels.
 */
class DevelopmentObfuscator implements Obfuscator
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The alias for table from which IDs originates.
   *
   * @var string
   */
  private $alias;

  //--------------------------------------------------------------------------------------------------------------------

  /**
   * Object constructor.
   *
   * @param string $alias The alias for table from which the ID originates.
   */
  public function __construct(string $alias)
  {
    $this->alias = $alias;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   *
   * @param string|null $code The obfuscated database ID.
   *
   * Throws an exception if the database ID is obfuscated with different label.
   */
  public function decode(?string $code): ?int
  {
    return DevelopmentObfuscatorFactory::decode($code, $this->alias);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public function encode(?int $id): ?string
  {
    return DevelopmentObfuscatorFactory::encode($id, $this->alias);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

<?php
declare(strict_types=1);

namespace Plaisio\Obfuscator;

/**
 * An implementation of Obfuscator that does not obfuscate database IDs.
 */
class IdentityObfuscator implements Obfuscator
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public function decode(?string $code): ?int
  {
    return IdentityObfuscatorFactory::decode($code, '');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public function encode(?int $id): ?string
  {
    return IdentityObfuscatorFactory::encode($id, '');
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

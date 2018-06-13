<?php

namespace SetBased\Abc\Obfuscator;

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
    return ($code===null || $code==='') ? null : (int)$code;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public function encode(?int $id): ?string
  {
    return ($id===null) ? null : (string)$id;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

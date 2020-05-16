<?php
declare(strict_types=1);

namespace Plaisio\Obfuscator;

use Plaisio\Obfuscator\Exception\CoreDecodeException;

/**
 * A factory for obfuscators that do not obfuscate at all.
 */
class IdentityObfuscatorFactory implements ObfuscatorFactory
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   *
   * @return IdentityObfuscator
   */
  public static function create(string $alias): Obfuscator
  {
    return new IdentityObfuscator();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public static function decode(?string $code, string $alias): ?int
  {
    if ($code===null || $code==='') return null;

    if (preg_match('/^\d+$/', $code)!=1)
    {
      throw new CoreDecodeException('Not a valid obfuscated database ID: %s', $code);
    }

    return (int)$code;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public static function encode(?int $id, string $alias): ?string
  {
    return ($id===null) ? null : (string)$id;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

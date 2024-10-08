<?php
declare(strict_types=1);

namespace Plaisio\Obfuscator;

use Plaisio\Obfuscator\Exception\CoreDecodeException;
use SetBased\Exception\LogicException;

/**
 * Factory for obfuscators for development only.
 */
class DevelopmentObfuscatorFactory implements ObfuscatorFactory
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   *
   * @return DevelopmentObfuscator
   */
  public static function create(string $alias): Obfuscator
  {
    return new DevelopmentObfuscator($alias);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public static function decode(?string $code, string $alias): ?int
  {
    if ($code===null || $code==='')
    {
      return null;
    }

    if (!str_starts_with($code, $alias))
    {
      throw new LogicException(sprintf("Labels '%s' and '%s' don't match.", substr($code, 0, strlen($alias)), $alias));
    }

    $id = substr($code, strlen($alias) + 1);

    if (preg_match('/^\d+$/', $id)!=1)
    {
      throw new CoreDecodeException('Not a valid obfuscated database ID: %s', $code);
    }

    return (int)$id;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public static function encode(?int $id, string $alias): ?string
  {
    if ($id===null)
    {
      return null;
    }

    return $alias.'_'.$id;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

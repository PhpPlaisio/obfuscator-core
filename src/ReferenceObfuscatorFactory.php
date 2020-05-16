<?php
declare(strict_types=1);

namespace Plaisio\Obfuscator;

use SetBased\Exception\LogicException;

/**
 * A factory for obfuscators using a reference implementation for obfuscating database ID.
 */
class ReferenceObfuscatorFactory implements ObfuscatorFactory
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * A lookup table from label to [length, key, bit mask].
   *
   * @var array[]
   *
   * @plaisio.obfuscator.labels
   */
  public static $labels = [];

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   *
   * @return ReferenceObfuscator
   */
  public static function create(string $alias): Obfuscator
  {
    if (!isset(self::$labels[$alias]))
    {
      throw new LogicException("Unknown label '%s'", $alias);
    }

    return new ReferenceObfuscator(self::$labels[$alias][0],
                                   self::$labels[$alias][1],
                                   self::$labels[$alias][2]);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public static function decode(?string $code, string $alias): ?int
  {
    if (!isset(self::$labels[$alias]))
    {
      throw new LogicException("Unknown label '%s'", $alias);
    }

    return ReferenceObfuscator::decrypt($code,
                                        self::$labels[$alias][0],
                                        self::$labels[$alias][1],
                                        self::$labels[$alias][2]);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public static function encode(?int $id, string $alias): ?string
  {
    if (!isset(self::$labels[$alias]))
    {
      throw new LogicException("Unknown label '%s'", $alias);
    }

    return ReferenceObfuscator::encrypt($id,
                                        self::$labels[$alias][0],
                                        self::$labels[$alias][1],
                                        self::$labels[$alias][2]);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

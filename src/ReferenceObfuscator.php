<?php
declare(strict_types=1);

namespace Plaisio\Obfuscator;

use Plaisio\Obfuscator\Exception\CoreDecodeException;

/**
 * Class for obfuscator database ID using two very simple encryption techniques: a (very weak) encryption method and a
 * bit mask with the same length as the database ID.
 */
class ReferenceObfuscator implements Obfuscator
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Some magic constants.
   */
  const int C1 = 52845;
  const int C2 = 22719;

  /**
   * The bit mask to be applied on a database ID. The length (in bytes) of this bit mask must be equal to the maximum
   * length (in bytes) of the database ID.
   *
   * @var int
   */
  private int $bitMask;

  /**
   * The key in the encryption algorithm. Must be a number between 0 and 65535.
   *
   * @var int
   */
  private int $key;

  /**
   * The maximum length (in bytes) of the database ID.
   *
   * @var int
   */
  private int $length;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param int $key     The key used by the encryption algorithm. Must be a number between 0 and 65535.
   * @param int $length  The maximum length (in bytes) of the database ID.
   * @param int $bitMask The bit mask to be applied on a database ID. The length (in bytes) of this bit mask must be
   *                     equal to the maximum length (in bytes) of the database ID.
   */
  public function __construct(int $length, int $key, int $bitMask)
  {
    $this->length  = $length;
    $this->key     = $key;
    $this->bitMask = $bitMask;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * De-obfuscates an obfuscated database ID.
   *
   * @param string|null $code   The obfuscated database ID.
   * @param int         $length The length (in bytes) of the (original) database ID.
   * @param int         $key    The encryption key. Must be a number between 0 and 65535.
   * @param int         $mask   The bit mask. The length (in bytes) of this bit mask must be equal to the maximum length
   *                            (in bytes) of the database ID.
   *
   * @return int|null
   */
  public static function decrypt(?string $code, int $length, int $key, int $mask): ?int
  {
    if ($code===null || $code==='')
    {
      return null;
    }

    try
    {
      $val = hexdec($code);
    }
    catch (\Throwable $exception)
    {
      throw new CoreDecodeException([$exception], 'Not a valid obfuscated database ID: %s', $code);
    }

    $result = 0;
    $k      = 1;
    for ($i = 0; $i<$length; $i++)
    {
      $remainder = $val % 256;
      $part      = $remainder ^ ($key >> 8);
      $result    = $result + $k * $part;
      $k         = $k << 8;
      $key       = (($remainder + $key) * self::C1 + self::C2) % 65536;
      $val       = $val >> 8;
    }

    return $result ^ $mask;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Obfuscates a database ID.
   *
   * @param int|null $id     The database ID.
   * @param int      $length The length (in bytes) of the (original) database ID.
   * @param int      $key    The encryption key. Must be a number between 0 and 65535.
   * @param int      $mask   The bit mask. The length (in bytes) of this bit mask must be equal to the maximum length
   *                         (in bytes) of the database ID.
   *
   * @return null|string
   */
  public static function encrypt(?int $id, int $length, int $key, int $mask): ?string
  {
    if ($id===null)
    {
      return null;
    }

    $val    = $id ^ $mask;
    $result = 0;
    $k      = 1;
    for ($i = 0; $i<$length; $i++)
    {
      $remainder = $val % 256;
      $part      = $remainder ^ ($key >> 8);
      $result    = $result + $k * $part;
      $k         = $k << 8;
      $key       = (($part + $key) * self::C1 + self::C2) % 65536;
      $val       = $val >> 8;
    }

    return dechex($result);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public function decode(?string $code): ?int
  {
    return self::decrypt($code, $this->length, $this->key, $this->bitMask);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public function encode(?int $id): ?string
  {
    return self::encrypt($id, $this->length, $this->key, $this->bitMask);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

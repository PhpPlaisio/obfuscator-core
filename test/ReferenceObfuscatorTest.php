<?php
declare(strict_types=1);

namespace Plaisio\Obfuscator\Test;

use PHPUnit\Framework\TestCase;
use Plaisio\Obfuscator\Exception\DecodeException;
use Plaisio\Obfuscator\ReferenceObfuscatorFactory;
use SetBased\ErrorHandler\ErrorHandler;

/**
 * Test cases for ReferenceObfuscator.
 */
class ReferenceObfuscatorTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test all database IDs are encoded en decoded correctly.
   *
   * Remove the leading underscore to enable this test.
   *  * Takes about 7h51 on an Intel i5-3570K 3.40GHz processor with PHP 5.4.16.
   *  * Takes about 0h53 on AMD Ryzen 7 3.8 GHz processor with PHP 8.1.14 (2023-02-04).
   */
  public function _testObfuscateDeObfuscateAll(): void
  {
    $obfuscator = ReferenceObfuscatorFactory::create('abc');

    for ($value = 1; $value<4294967295; ++$value)
    {
      $code = $obfuscator->encode($value);
      $tmp  = $obfuscator->decode($code);

      self::assertEquals($value, $tmp);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public function setup(): void
  {
    mt_srand(crc32(microtime()));

    $mask                               = mt_rand(2147483647, 4294967295);
    $key                                = mt_rand(0, 65535);
    ReferenceObfuscatorFactory::$labels = ['abc' => [4, $key, $mask]];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test null and empty string are decoded to null.
   */
  public function testDeObfuscate1(): void
  {
    $obfuscator = ReferenceObfuscatorFactory::create('abc');

    $codes = ['', null];
    foreach ($codes as $code)
    {
      $id = $obfuscator->decode($code);
      self::assertNull($id);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test null is encoded to null.
   */
  public function testObfuscate1(): void
  {
    $obfuscator = ReferenceObfuscatorFactory::create('abc');

    $code = $obfuscator->encode(null);
    self::assertNull($code);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test ID 0 is encoded en decoded correctly.
   */
  public function testObfuscate2(): void
  {
    $obfuscator = ReferenceObfuscatorFactory::create('abc');

    $code = $obfuscator->encode(0);
    $tmp  = $obfuscator->decode($code);

    self::assertEquals(0, $tmp);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test some random chosen databases IDs are encoded en decoded correctly.
   */
  public function testObfuscateDeObfuscate1(): void
  {
    $obfuscator = ReferenceObfuscatorFactory::create('abc');

    for ($value = 1; $value<100000; ++$value)
    {
      $code = $obfuscator->encode($value);
      $tmp  = $obfuscator->decode($code);

      self::assertEquals($value, $tmp);
    }

    for ($value = 100000; $value<=2147483647; $value += mt_rand(1, 1000000))
    {
      $code = $obfuscator->encode($value);
      $tmp  = $obfuscator->decode($code);

      self::assertEquals($value, $tmp);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

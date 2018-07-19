<?php
declare(strict_types=1);

namespace SetBased\Abc\Test\Obfuscator;

use PHPUnit\Framework\TestCase;
use SetBased\Abc\Obfuscator\ReferenceObfuscatorFactory;

/**
 * Test cases for ReferenceObfuscatorFactory.
 */
class ReferenceObfuscatorFactoryTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test all database IDs are encoded en decoded correctly.
   *
   * Remove the leading underscore to enable this test. Takes about 7.85 hours on a Intel i5-3570K @ 3.40GHz processor
   * with PHP 5.4.16.
   */
  public function _testObfuscateDeObfuscateAll()
  {
    for ($value = 1; $value<4294967295; ++$value)
    {
      $code = ReferenceObfuscatorFactory::encode($value, 'abc');
      $tmp  = ReferenceObfuscatorFactory::decode($code, 'abc');

      self::assertEquals($value, $tmp);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public function setup()
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
  public function testDeObfuscate1()
  {
    $codes = ['', null];
    foreach ($codes as $code)
    {
      $id = ReferenceObfuscatorFactory::decode($code, 'abc');
      self::assertNull($id);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test null is encoded to null.
   */
  public function testObfuscate1()
  {
    $code = ReferenceObfuscatorFactory::encode(null, 'abc');
    self::assertNull($code);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test ID 0 is encoded en decoded correctly.
   */
  public function testObfuscate2()
  {
    $code = ReferenceObfuscatorFactory::encode(0, 'abc');
    $tmp  = ReferenceObfuscatorFactory::decode($code, 'abc');

    self::assertEquals($tmp, 0);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test some random chosen databases IDs are encoded en decoded correctly.
   */
  public function testObfuscateDeObfuscate1()
  {
    for ($value = 1; $value<100000; ++$value)
    {
      $code = ReferenceObfuscatorFactory::encode($value, 'abc');
      $tmp  = ReferenceObfuscatorFactory::decode($code, 'abc');

      self::assertEquals($value, $tmp);
    }

    for ($value = 100000; $value<=2147483647; $value += mt_rand(1, 1000000))
    {
      $code = ReferenceObfuscatorFactory::encode($value, 'abc');
      $tmp  = ReferenceObfuscatorFactory::decode($code, 'abc');

      self::assertEquals($value, $tmp);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

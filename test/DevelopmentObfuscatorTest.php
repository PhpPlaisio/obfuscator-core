<?php
declare(strict_types=1);

namespace Plaisio\Obfuscator\Test;

use PHPUnit\Framework\TestCase;
use Plaisio\Obfuscator\DevelopmentObfuscatorFactory;
use Plaisio\Obfuscator\Exception\DecodeException;
use SetBased\Exception\LogicException;

/**
 * Test cases for DevelopmentObfuscator.
 */
class DevelopmentObfuscatorTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public function setup(): void
  {
    mt_srand(crc32(microtime()));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test null and empty string are decoded to null.
   */
  public function testDeObfuscate1(): void
  {
    $obfuscator = DevelopmentObfuscatorFactory::getObfuscator('abc');

    $codes = ['', null];
    foreach ($codes as $code)
    {
      $id = $obfuscator->decode($code);
      self::assertNull($id);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test a non integer ID.
   */
  public function testDeObfuscateNonInt1(): void
  {
    $this->expectException(DecodeException::class);
    DevelopmentObfuscatorFactory::decode('abc_abc', 'abc');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test null is encoded to null.
   */
  public function testObfuscate1(): void
  {
    $obfuscator = DevelopmentObfuscatorFactory::getObfuscator('abc');

    $code = $obfuscator->encode(null);
    self::assertNull($code);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test ID 0 is encoded en decoded correctly.
   */
  public function testObfuscate2(): void
  {
    $obfuscator = DevelopmentObfuscatorFactory::getObfuscator('abc');

    $code = $obfuscator->encode(0);
    $tmp  = $obfuscator->decode($code);

    self::assertEquals($tmp, 0);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test some random chosen databases IDs are encoded en decoded correctly.
   */
  public function testObfuscateDeObfuscate1(): void
  {
    $obfuscator = DevelopmentObfuscatorFactory::getObfuscator('abc');

    for ($value = 1; $value<100000; ++$value)
    {
      $code = $obfuscator->encode($value);
      $tmp  = $obfuscator->decode($code);

      self::assertEquals($value, $tmp);
    }

    for ($value = 100000; $value<=2147483647; $value += mt_rand(1, 10000000))
    {
      $code = $obfuscator->encode($value);
      $tmp  = $obfuscator->decode($code);

      self::assertEquals($value, $tmp);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test an ID encoded and decoded ID with different labels throws an exception.
   */
  public function testObfuscateDeObfuscate2(): void
  {
    $id = mt_rand(0, 4294967295);

    $code = DevelopmentObfuscatorFactory::encode($id, 'abc');
    $this->expectException(LogicException::class);
    DevelopmentObfuscatorFactory::decode($code, 'cba');
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

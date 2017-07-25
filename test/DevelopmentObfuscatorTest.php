<?php
//----------------------------------------------------------------------------------------------------------------------
namespace SetBased\Abc\Obfusctaor\Test;

use PHPUnit\Framework\TestCase;
use SetBased\Abc\Obfuscator\DevelopmentObfuscatorFactory;
use SetBased\Exception\LogicException;

/**
 * Test case for DevelopmentObfuscator.
 */
class DevelopmentObfuscatorTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function setup()
  {
    mt_srand(crc32(microtime()));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test none database ID are decoded to null.
   */
  public function testObfuscate1()
  {
    $obfuscator = DevelopmentObfuscatorFactory::getObfuscator('abc');

    $values = ['', null, false]; //, true, array('hello'=> 'world') );
    foreach ($values as $value)
    {
      $code = $obfuscator->encode($value);

      self::assertNull($code);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test ID 0 is encoded en decoded correctly.
   */
  public function testObfuscate2()
  {
    $obfuscator = DevelopmentObfuscatorFactory::getObfuscator('abc');

    $values = [0, '0'];
    foreach ($values as $value)
    {
      $code = $obfuscator->encode($value);
      $tmp  = $obfuscator->decode($code);

      self::assertEquals($tmp, 0);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test some random chosen databases IDs are encoded en decoded correctly.
   */
  public function testObfuscateDeObfuscate1()
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
   * Test a ID encoded and decoded ID with different labels throws an exception.
   *
   * @expectedException LogicException
   */
  public function testObfuscateDeObfuscate2()
  {
    $id = mt_rand(0, 4294967295);

    $code = DevelopmentObfuscatorFactory::encode($id, 'abc');
    DevelopmentObfuscatorFactory::decode($code, 'cba');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test a non integer ID.
   *
   * @expectedException LogicException
   */
  public function testObfuscateNonInt1()
  {
    DevelopmentObfuscatorFactory::encode('id', 'abc');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test a non integer ID.
   *
   * @expectedException LogicException
   */
  public function testObfuscateNonInt2()
  {
    DevelopmentObfuscatorFactory::encode(new LogicException(), 'abc');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test a non integer ID.
   *
   * @expectedException LogicException
   */
  public function testDeObfuscateNonInt1()
  {
    DevelopmentObfuscatorFactory::decode('abc_abc', 'abc');
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
 
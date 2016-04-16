<?php
//----------------------------------------------------------------------------------------------------------------------
use SetBased\Abc\Obfuscator\ReferenceObfuscatorFactory;

//----------------------------------------------------------------------------------------------------------------------
class ReferenceObfuscatorTest extends PHPUnit_Framework_TestCase
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
    $obfuscator = ReferenceObfuscatorFactory::getObfuscator('abc');

    for ($value = 1; $value<4294967295; ++$value)
    {
      $code = $obfuscator->encode($value);
      $tmp  = $obfuscator->decode($code);

      $this->assertEquals($value, $tmp);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  public function setup()
  {
    mt_srand(crc32(microtime()));

    $mask                               = mt_rand(2147483647, 4294967295);
    $key                                = mt_rand(0, 65535);
    ReferenceObfuscatorFactory::$labels = ['abc' => [4, $key, $mask]];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test none database ID are decoded to null.
   */
  public function testObfuscate1()
  {
    $obfuscator = ReferenceObfuscatorFactory::getObfuscator('abc');

    $values = ['', null, false]; //, true, array('hello'=> 'world') );
    foreach ($values as $value)
    {
      $code = $obfuscator->encode($value);

      $this->assertNull($code);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test ID 0 is encoded en decoded correctly.
   */
  public function testObfuscate2()
  {
    $obfuscator = ReferenceObfuscatorFactory::getObfuscator('abc');

    $values = [0, '0'];
    foreach ($values as $value)
    {
      $code = $obfuscator->encode($value);
      $tmp  = $obfuscator->decode($code);

      $this->assertEquals($tmp, 0);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test some random chosen databases IDs are encoded en decoded correctly.
   */
  public function testObfuscateDeObfuscate1()
  {
    $obfuscator = ReferenceObfuscatorFactory::getObfuscator('abc');

    for ($value = 1; $value<100000; ++$value)
    {
      $code = $obfuscator->encode($value);
      $tmp  = $obfuscator->decode($code);

      $this->assertEquals($value, $tmp);
    }

    for ($value = 100000; $value<=2147483647; $value += mt_rand(1, 1000000))
    {
      $code = $obfuscator->encode($value);
      $tmp  = $obfuscator->decode($code);

      $this->assertEquals($value, $tmp);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
 
<?php
//----------------------------------------------------------------------------------------------------------------------
use SetBased\Abc\Obfuscator\IdentityObfuscatorFactory;

//----------------------------------------------------------------------------------------------------------------------
class IdentityObfuscatorTest extends PHPUnit_Framework_TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  public function setup()
  {
    mt_srand(crc32(microtime()));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test encode does nothing.
   */
  public function testIdentity1()
  {
    $obfuscator = IdentityObfuscatorFactory::getObfuscator('abc');
    $id         = mt_rand(1, 2147483647);
    $code       = $obfuscator->encode($id);

    $this->assertEquals($id, $code);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test decode does nothing.
   */
  public function testIdentity2()
  {
    $obfuscator = IdentityObfuscatorFactory::getObfuscator('abc');
    $code       = (string)mt_rand(1, 2147483647);
    $id         = $obfuscator->decode($code);

    $this->assertEquals($code, $id);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test ID 0 is encoded en decoded correctly.
   */
  public function testObfuscateDeObfuscate1()
  {
    $obfuscator = IdentityObfuscatorFactory::getObfuscator('abc');
    $values     = [0, '0'];

    foreach ($values as $value)
    {
      $code = $obfuscator->encode($value);
      $tmp  = $obfuscator->decode($code);

      $this->assertEquals($tmp, 0);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
 
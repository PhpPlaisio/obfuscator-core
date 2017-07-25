<?php
//----------------------------------------------------------------------------------------------------------------------
namespace SetBased\Abc\Obfuscator;

use SetBased\Exception\LogicException;

//----------------------------------------------------------------------------------------------------------------------
/**
 * Factory for obfuscators for development only.
 */
class DevelopmentObfuscatorFactory implements ObfuscatorFactory
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public static function decode($code, $alias)
  {
    if ($code===null || $code===false || $code==='') return null;

    if (substr($code, 0, strlen($alias))!=$alias)
    {
      throw new LogicException(sprintf("Labels '%s' and '%s' don't match.", substr($code, 0, strlen($alias)), $alias));
    }

    $id = substr($code, strlen($alias) + 1);

    if (preg_match('/^\d+$/', $id)!=1)
    {
      throw new LogicException("Integer expected, got '%s'", (string)$id);
    }

    return $id;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public static function encode($id, $alias)
  {
    if ($id===null || $id===false || $id==='') return null;

    if (preg_match('/^\d+$/', $id)!=1)
    {
      throw new LogicException("Integer expected, got '%s'", (string)$id);
    }

    return $alias.'_'.$id;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   *
   * @return DevelopmentObfuscator
   */
  public static function getObfuscator($alias)
  {
    return new DevelopmentObfuscator($alias);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

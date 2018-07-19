<?php
declare(strict_types=1);

namespace SetBased\Abc\Obfuscator\Mangler;

use SetBased\Exception\RuntimeException;

/**
 * Class for deriving labels from tables following the SetBased's coding standards for databases.
 */
class SetBasedReferenceObfuscatorMangler implements ReferenceObfuscatorMangler
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the label of a table based on the metadata of a table following the SetBased's coding standards for
   * databases. The alias (or label) of a table are the first three characters of its columns (that are not foreign
   * keys).
   *
   * @param array $table The metadata of the table. The array must have the following keys:
   *                     <ul>
   *                     <li> table_name   The name of the table.
   *                     <li> column_name  The name of the autoincrement column.
   *                     <li> column_type  The data type of the autoincrement column.
   *                     </ul>
   *
   * @return string
   */
  public static function getLabel(array $table): string
  {
    $id = substr($table['column_name'], -strlen('_id'));
    if ($id!='_id')
    {
      throw new RuntimeException("Trailing '_id' not found in column '%s' of table '%s'.",
                                 $table['column_name'],
                                 $table['table_name']);
    }

    return substr($table['column_name'], 0, -strlen('_id'));
  }

  //-------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

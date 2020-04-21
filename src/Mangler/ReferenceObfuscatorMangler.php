<?php
declare(strict_types=1);

namespace Plaisio\Obfuscator\Mangler;

/**
 * Interface for deriving labels from table metadata.
 */
interface ReferenceObfuscatorMangler
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the label of a table based on the metadata of the table.
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
  public static function getLabel(array $table): string;

  //-------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------


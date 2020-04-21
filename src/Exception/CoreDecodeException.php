<?php
declare(strict_types=1);

namespace Plaisio\Obfuscator\Exception;

use SetBased\Exception\FormattedException;

/**
 * @inheritDoc
 */
class CoreDecodeException extends DecodeException
{
  use FormattedException;
}

//----------------------------------------------------------------------------------------------------------------------

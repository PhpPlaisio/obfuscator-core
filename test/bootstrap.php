<?php
declare(strict_types=1);

use SetBased\ErrorHandler\ErrorHandler;

date_default_timezone_set('Europe/Amsterdam');

require_once(__DIR__.'/../vendor/autoload.php');

$errorHandler = new ErrorHandler();
$errorHandler->registerErrorHandler();

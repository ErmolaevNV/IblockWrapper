<?php
require_once (__DIR__ . '/bootstrap.php');
initBitrixCore();
\tests\migration\MigrateController::Up();
endBitirx();
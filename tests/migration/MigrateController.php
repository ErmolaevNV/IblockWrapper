<?php
namespace tests\migration;

class MigrateController
{
    public static function Up() {
        \tests\migration\IblockType::up();
        \tests\migration\Iblock::up();
    }

    public static function Down() {
        \tests\migration\IblockType::down();
        \tests\migration\Iblock::down();
    }
}
<?php
namespace tests\migration;

class MigrateController
{
    public static function Up() {
        \tests\migration\IblockType::up();
        \tests\migration\Iblock::up();
        \tests\migration\Elements::up();
    }

    public static function Down() {
        \tests\migration\Elements::down();
        \tests\migration\Iblock::down();
        \tests\migration\IblockType::down();
    }
}
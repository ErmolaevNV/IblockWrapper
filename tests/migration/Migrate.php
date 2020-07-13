<?php


namespace tests\migration;


use Bitrix\Main\Loader;
use Sprint\Migration\HelperManager;

abstract class Migrate
{
    protected $helper;

    protected function __construct() {
        Loader::includeModule('iblock');
        Loader::includeModule('sprint.migration');
        $this->helper = HelperManager::getInstance();
    }

    public static function up() {
        $obj = new static();
        $obj->add();
    }

    public static function down() {
        $obj = new static();
        $obj->delete();
    }

    protected function add() {}

    protected function delete() {}
}
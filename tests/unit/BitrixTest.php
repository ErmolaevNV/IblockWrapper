<?php

use Bitrix\Main\Loader;
use PHPUnit\Framework\TestCase;

class BitrixTest extends TestCase
{
    protected $classOB;

    protected function setUp() : void
    {
        parent::setUp();

        initBitrixCore();

    }

    public function testModuleInstalled()
    {
        $this->assertTrue(Loader::includeModule("iblock"));
    }


    protected function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub

    }
}
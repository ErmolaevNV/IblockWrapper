<?php


namespace tests;


use PHPUnit\Framework\TestCase;

class TestCaseBitrix extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        initBitrixCore();
    }
}

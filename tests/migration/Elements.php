<?php


namespace tests\migration;


class Elements extends Migrate
{
    public const ELEMENT_1 = 'ELEMENT_1';
    public const ELEMENT_2 = 'ELEMENT_2';

    protected $iblockId;

    protected function __construct() {
        parent::__construct();
        $this->iblockId = $this->helper->Iblock()->getIblockId(Iblock::CODE);
    }

    protected function add() {
        $properties = $this->helper->Iblock()->getPropertyEnums(['CODE' => 'LIST']);

        $this->helper->Iblock()->addElement($this->iblockId, [
            'NAME' => 'test_1',
            'IBLOCK_TYPE_ID' => IblockType::ID,
            'PROPERTY_VALUES' => [
                Iblock::PROP_CODE_LIST => [$properties[0]['ID'], $properties[1]['ID']],
                Iblock::PROP_CODE_NAME => 'Hello, World!'
            ],
            'CODE' => self::ELEMENT_1
        ]);

        $this->helper->Iblock()->addElement($this->iblockId, [
            'NAME' => 'test_2',
            'IBLOCK_TYPE_ID' => IblockType::ID,
            'PROPERTY_VALUES' => [
                Iblock::PROP_CODE_LIST => [$properties[0]['ID']]
            ],
            'CODE' => self::ELEMENT_2
        ]);
    }

    protected function delete() {
        $this->helper->Iblock()->deleteElementIfExists($this->iblockId, self::ELEMENT_1);
        $this->helper->Iblock()->deleteElementIfExists($this->iblockId, self::ELEMENT_2);
    }
}
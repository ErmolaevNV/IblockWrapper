<?php

namespace tests\migration;

use Bitrix\Main\Loader;

class IblockType extends Migrate
{
    public const NAME = 'IBLOCK_WRAPPER_TEST_NAME';
    public const ID = 'BitrixWrapperTestIBType';

    protected function add() {
        $iblockType = new \CIBlockType();
        $iblockType->Add(
            [
                'NAME' => self::NAME,
                'ID' => self::ID,
                'LANG' =>
                    [
                        'ru' =>
                        [
                            'NAME' => self::NAME,
                            'SECTION_NAME' => '',
                            'ELEMENT_NAME' => self::NAME,
                        ]
                    ]
            ]
        );
    }

    protected function delete() {
        \CIBlockType::Delete(self::ID);
    }
}
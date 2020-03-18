<?php


namespace ErmolaevNV;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CIBlock;
use CIBlockElement;
use CIBlockProperty;
use CIBlockSection;
use Psr\SimpleCache\InvalidArgumentException;

abstract class Iblock
{
    private static $cacheIblockIdPostfix = '_IBLOCK_ID';

    protected const CODE = '';

    public static function getCode(): string {
        return static::CODE;
    }

    /**
     * Получить ID Инфоблока
     *
     * @return mixed
     */
    public static function getId() {
        try {
            Loader::includeModule('iblock');
        } catch (\Exception $e) {
            return false;
        }
        $cache = new SimpleCacheBitrix();

        $id = $cache->get(self::getCode() . self::$cacheIblockIdPostfix);
        if (!$id) {
            $id = (int) IblockTable::getList(['filter'=>['CODE'=> self::getCode()]])->fetch()['ID'];

            try {
                $cache->set(self::getCode() . self::$cacheIblockIdPostfix, $id);
            } catch (InvalidArgumentException $e) {
                echo 'Не удалось записать в кэш'; // TODO: Должно падать в Лог!
            }
        }

        return $id;
    }

    /**
     * Получить список элементов
     *
     * @param array $arOrder
     * @param array $arFilter
     * @param bool|array $arGroupBy
     * @param bool|array $arNavStartParams
     * @param array $arSelectFields
     *
     * @return array|null
     * @throws LoaderException
     */
    public static function getElementsList(
        $arOrder = ['SORT' => 'ASC'],
        $arFilter = [],
        $arGroupBy = false,
        $arNavStartParams = false,
        $arSelectFields = []
    ): ?array {
        $dbRes = self::getElementsListRaw($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);

        if ($dbRes instanceof \CIBlockResult) {
            $list = [];
            while ($item = $dbRes->GetNext()) {
                $list[] = $item;
            }
            return $list;
        }
        return null;
    }

    /**
     * Получить список элементов в сыром виде
     *
     * @param array $arOrder
     * @param array $arFilter
     * @param bool $arGroupBy
     * @param bool $arNavStartParams
     * @param array $arSelectFields
     *
     * @return \CIBlockResult|int
     *
     * @throws LoaderException
     */
    public static function getElementsListRaw(
        $arOrder = ['SORT' => 'ASC'],
        $arFilter = [],
        $arGroupBy = false,
        $arNavStartParams = false,
        $arSelectFields = []
    ) {
        Loader::includeModule('iblock');

        $arFilter['IBLOCK_ID'] = self::getId();

        return CIBlockElement::GetList(
            $arOrder,
            $arFilter,
            $arGroupBy,
            $arNavStartParams,
            $arSelectFields
        );
    }

    public static function getTreeList($arFilter = [], $arSelect = []) {
        Loader::includeModule('iblock');
        $arFilter['IBLOCK_ID'] = self::getId();
        return CIBlockSection::GetTreeList($arFilter, $arSelect);
    }

    public static function getSectionListRaw(
        $arOrder = ['SORT' => 'ASC'],
        $arFilter = [],
        $bIncCnt = false,
        $arSelect = [],
        $arNavStartParams = false
    ) {
        Loader::includeModule('iblock');

        $arFilter['IBLOCK_ID'] = self::getId();

        return CIBlockSection::GetList(
            $arOrder,
            $arFilter,
            $bIncCnt,
            $arSelect,
            $arNavStartParams
        );
    }

    /**
     * Get section list
     *
     * @param array $arOrder
     * @param array $arFilter
     * @param bool $bIncCnt
     * @param array $arSelect
     * @param bool $arNavStartParams
     *
     * @return array
     */
    public static function getSectionList(
        $arOrder = ['SORT' => 'ASC'],
        $arFilter = [],
        $bIncCnt = false,
        $arSelect = [],
        $arNavStartParams = false
    ): array {
        $list = [];

        $dbRes = self::getSectionListRaw(
            $arOrder,
            $arFilter,
            $bIncCnt,
            $arSelect,
            $arNavStartParams
        );

        while ($item = $dbRes->GetNext()) {
            $list[] = $item;
        }
        return $list;
    }

    public static function getSectionById($id): array {
        return CIBlockSection::GetByID($id)->GetNext();
    }

    /**
     * Добавить раздел
     *
     * @param array $fields
     *
     * @return bool|int
     *
     * @throws LoaderException
     */
    public static function addSection($fields = []) {
        $default = [
            'ACTIVE' => 'Y',
            'IBLOCK_SECTION_ID' => false,
            'NAME' => 'section',
            'CODE' => '',
            'SORT' => 100,
            'PICTURE' => false,
            'DESCRIPTION' => '',
            'DESCRIPTION_TYPE' => 'text',
        ];

        $fields = array_replace_recursive($default, $fields);
        $fields['IBLOCK_ID'] = self::getId();

        $ib = new CIBlockSection;
        $id = $ib->Add($fields);

        if ($id) {
            return $id;
        }
        return false;
    }

    /**
     * Добавить элемент
     *
     * @param array $fields
     * @param array $props
     *
     * @return int|bool
     */
    public static function addElement($fields = [], $props = []) {
        $default = [
            'NAME' => 'element',
            'IBLOCK_SECTION_ID' => false,
            'ACTIVE' => 'Y',
            'PREVIEW_TEXT' => '',
            'DETAIL_TEXT' => '',
        ];

        $fields = array_replace_recursive($default, $fields);
        $fields['IBLOCK_ID'] = self::getId();

        if (!empty($props)) {
            $fields['PROPERTY_VALUES'] = $props;
        }

        $ib = new CIBlockElement;
        $id = $ib->Add($fields);

        if ($id) {
            return $id;
        }
        return false;
    }

    /**
     * Получить список свойств используя arFilter и arOrder
     *
     * @param array $arOrder
     * @param array $arFilter
     *
     * @return array
     */
    public static function getProperty($arOrder = [], $arFilter = []): array {
        $arFilter['IBLOCK_ID'] = self::getId();
        $resdb  = CIBlockProperty::GetList($arOrder, $arFilter);
        $result = [];
        while ($res = $resdb->GetNext()) {
            $result[] = $res;
        }
        return $result;
    }

    public static function getIblockType() {
    }

    /**
     * Создание класса для Инфоблока по коду
     *
     * @param $code
     *
     * @return Iblock
     */
    public static function iblockFactory($code) {
        $class = "\\PESKOT\\Iblock\\" . $code;
        return class_exists($class) ? new $class() : null;
    }

    public static function getIblock() {
        return CIBlock::GetByID(self::getId())->GetNext();
    }
}

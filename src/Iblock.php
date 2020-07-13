<?php


namespace ErmolaevNV;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Web\DOM\DomException;
use CIBlock;
use CIBlockElement;
use CIBlockProperty;
use CIBlockSection;
use ErmolaevNV\Traits\GetCode;
use Psr\SimpleCache\InvalidArgumentException;

abstract class Iblock
{
    use GetCode;

    private static $cacheIblockIdPostfix = '_IBLOCK_ID';

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
                AddMessage2Log(__METHOD__ . ": Failed to write to the cache ({$e->getMessage()})");
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
     *
     * @throws DomException
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

        if(!$id) {
            throw new DomException('Failed to add item:' . $ib->LAST_ERROR);
        }
        return $id;
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

    public static function getIblock() {
        return CIBlock::GetByID(self::getId())->GetNext();
    }

    /**
     * Возвращает список свойств инфоблока
     *
     * @param bool $flag Если флаг true, то названиям свойств будет добавлена приставка PROPERTY_
     *
     * @return array
     */
    public static function getPropertyCodeList($flag = false) {
        $result = [];
        $constList = (new \ReflectionClass(static::class))->getConstants();
        if (!empty($constList)) {
            foreach($constList as $k => $v){
                if(preg_match("/^PROPERTY_.*/", $k)){
                    $result[] = $flag ? 'PROPERTY_'.$v : $v;
                }
            }
        }
        return $result;
    }

    /**
     * Получить элемент инфоблока по ID
     *
     * @param $id
     *
     * @return array
     *
     * @throws DomException
     * @throws LoaderException
     */
    public static function getById($id) {
        $e = self::getElementsList(
            [],
            ['ID' => $id],
            false,
            false,
            array_merge(['*'], self::getPropertyCodeList(true)));
        if (empty($e)) {
            throw new DomException("Element with ID:$id doesn't exist");
        }
        return $e[0];
    }

    /**
     * Получить список элементов
     *
     * @param $arParams
     *
     * @return array|null
     *
     * @throws LoaderException
     */
    public static function getList($arParams): ?array {
        $dbRes = self::getElementsListRaw(
            $arParams['order'] ?? [],
            $arParams['filter'] ?? [],
            $arParams['groupBy'] ?? [],
            $arParams['navStartParams'] ?? [],
            $arParams['selectFields'] ?? []
        );

        if ($dbRes instanceof \CIBlockResult) {
            $list = [];
            while ($item = $dbRes->GetNext()) {
                $list[] = $item;
            }
            return $list;
        }
        return null;
    }
}
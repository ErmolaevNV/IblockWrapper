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

    private static $id;

    private $cacheIblockIdPostfix;
    private $cache;

    public function __construct($config = []) {
        Loader::includeModule('iblock');

        $this->cacheIblockIdPostfix = $config['cache_id_postfix'] ?? '_IBLOCK_ID';

        $this->cache = new SimpleCacheBitrix();
    }

    /**
     * Получить ID Инфоблока
     *
     * @return mixed
     */
    public function getId() : int {
        if (self::$id === null) {
            self::$id = (int) $this->cache->get($this->getCode() . $this->cacheIblockIdPostfix);
            if (!self::$id) {
                self::$id = (int) IblockTable::getList(['filter' => ['CODE' => $this->getCode()]])->fetch()['ID'];
                try {
                    $this->cache->set($this->getCode() . $this->cacheIblockIdPostfix, self::$id);
                } catch (InvalidArgumentException $e) {
                    AddMessage2Log(__METHOD__ . ": Failed to write to the cache ({$e->getMessage()})");
                }
            }
        }
        return self::$id;
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
    public function getElementsList(
        $arOrder = ['SORT' => 'ASC'],
        $arFilter = [],
        $arGroupBy = false,
        $arNavStartParams = false,
        $arSelectFields = []
    ): ?array {
        $dbRes = $this->getElementsListRaw($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);

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
    public function getElementsListRaw(
        $arOrder = ['SORT' => 'ASC'],
        $arFilter = [],
        $arGroupBy = false,
        $arNavStartParams = false,
        $arSelectFields = []
    ) {
        $arFilter['IBLOCK_ID'] = $this->getId();

        return CIBlockElement::GetList(
            $arOrder,
            $arFilter,
            $arGroupBy,
            $arNavStartParams,
            $arSelectFields
        );
    }

    public function getTreeList($arFilter = [], $arSelect = []) {
        Loader::includeModule('iblock');
        $arFilter['IBLOCK_ID'] = $this->getId();
        return CIBlockSection::GetTreeList($arFilter, $arSelect);
    }

    public function getSectionListRaw(
        $arOrder = ['SORT' => 'ASC'],
        $arFilter = [],
        $bIncCnt = false,
        $arSelect = [],
        $arNavStartParams = false
    ) {
        Loader::includeModule('iblock');

        $arFilter['IBLOCK_ID'] = $this->getId();

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
    public function getSectionList(
        $arOrder = ['SORT' => 'ASC'],
        $arFilter = [],
        $bIncCnt = false,
        $arSelect = [],
        $arNavStartParams = false
    ): array {
        $list = [];

        $dbRes = $this->getSectionListRaw(
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

    public function getSectionById($id): array {
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
    public function addSection($fields = []) {
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
        $fields['IBLOCK_ID'] = $this->getId();

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
    public function addElement($fields = [], $props = []) {
        $default = [
            'NAME' => 'element',
            'IBLOCK_SECTION_ID' => false,
            'ACTIVE' => 'Y',
            'PREVIEW_TEXT' => '',
            'DETAIL_TEXT' => '',
        ];

        $fields = array_replace_recursive($default, $fields);
        $fields['IBLOCK_ID'] = $this->getId();

        if (!empty($props)) {
            $fields['PROPERTY_VALUES'] = $props;
        }

        $ib = new CIBlockElement;
        $id = $ib->Add($fields);

        if (!$id) {
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
    public function getProperty($arOrder = [], $arFilter = []): array {
        $arFilter['IBLOCK_ID'] = $this->getId();
        $resdb  = CIBlockProperty::GetList($arOrder, $arFilter);
        $result = [];
        while ($res = $resdb->GetNext()) {
            $result[] = $res;
        }
        return $result;
    }

    public function getIblockType() {
        throw new \DomainException('Iblock type is not defined');
    }

    public function getIblock() {
        return CIBlock::GetByID($this->getId())->GetNext();
    }

    /**
     * Возвращает список свойств инфоблока
     *
     * @param bool $flag Если флаг true, то названиям свойств будет добавлена приставка PROPERTY_
     *
     * @return array
     */
    public function getPropertyCodeList($flag = false) {
        $result    = [];
        $constList = (new \ReflectionClass(static::class))->getConstants();
        if (!empty($constList)) {
            foreach ($constList as $k => $v) {
                if (preg_match("/^PROPERTY_.*/", $k)) {
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
    public function getById($id) {
        $e = $this->getElementsList(
            [],
            ['ID' => $id],
            false,
            false,
            array_merge(['*'], $this->getPropertyCodeList(true))
        );
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
    public function getList($arParams = []): ?array {
        $dbRes = $this->getElementsListRaw(
            $arParams['order'] ?? [],
            $arParams['filter'] ?? [],
            $arParams['groupBy'] ?? false,
            $arParams['navStartParams'] ?? false,
            $arParams['selectFields'] ?? array_merge(['*'], $this->getPropertyCodeList(true))
        );

        if ($dbRes instanceof \CIBlockResult) {
            $list = [];
            while ($item = $dbRes->GetNext()) {
                if (isset($list[$item['ID']])) {
                    foreach ($item as $name => $value) {
                        if (stripos($name, 'PROPERTY') !== false) {
                            if (!is_array($list[$item['ID']][$name])) {
                                if ($list[$item['ID']][$name] !== $value) {
                                    $list[$item['ID']][$name] = [$list[$item['ID']][$name], $value];
                                }
                            } elseif (!in_array($value, $list[$item['ID']][$name], true)) {
                                $list[$item['ID']][$name][] = $value;
                            }
                        }
                    }
                } else {
                    $list[$item['ID']] = $item;
                }
            }
            return array_values($list);
        }
        return null;
    }
}

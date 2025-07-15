<?php

namespace Aspro\Max\Itemaction;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use CMax as Solution;
use CMaxCache as SolutionCache;

Loc::loadMessages(__FILE__);

class Compare extends Base
{
    use Trait\Site;
    use Trait\Element;

    protected static $siteId = '';
    protected static $bModified = true;
    protected static $iblocks = [];

    public static function getIblocks(): array
    {
        if (!static::$iblocks) {
            $catalogIblockId = Option::get(
                Solution::moduleID,
                'CATALOG_IBLOCK_ID',
                SolutionCache::$arIBlocks[static::getSiteId()]['aspro_'.Solution::solutionName.'_catalog']['aspro_'.Solution::solutionName.'_catalog'][0],
                static::getSiteId()
            );
            if ($catalogIblockId) {
                static::$iblocks[] = $catalogIblockId;
            }
        }

        return static::$iblocks;
    }

    public static function setIblocks(array $iblocks)
    {
        static::$bModified = true;
        static::$iblocks = [];

        foreach ($iblocks as $iblockId) {
            $iblockId = intval($iblockId);
            if ($iblockId > 0) {
                static::$iblocks[] = $iblockId;
            }
        }
    }

    public static function getItems(): array
    {
        static $result;

        if (static::$bModified) {
            $result = [];

            if (
                $_SESSION['CATALOG_COMPARE_LIST']
                && is_array($_SESSION['CATALOG_COMPARE_LIST'])
            ) {
                foreach (static::getIblocks() as $iblockId) {
					if (
						is_array($_SESSION['CATALOG_COMPARE_LIST'][$iblockId]) &&
						is_array($_SESSION['CATALOG_COMPARE_LIST'][$iblockId]['ITEMS']) &&
						$_SESSION['CATALOG_COMPARE_LIST'][$iblockId]['ITEMS']
					) {
						foreach (array_keys($_SESSION['CATALOG_COMPARE_LIST'][$iblockId]['ITEMS']) as $itemId) {
							$result[$itemId] = $itemId;
						}
					}
				}
            }

            static::$bModified = false;
        }

        return $result;
    }

    public static function addItem(int $id)
    {
        if ($id <= 0) {
            throw new SystemException(Loc::getMessage('ITEMACTION_COMPARE_ERROR_ITEM_ID'));
        }

        $arItem = static::getElement($id);
        if (!$arItem) {
            throw new SystemException(Loc::getMessage('ITEMACTION_COMPARE_ERROR_ITEM'));
        }

        $iblockId = $arItem['IBLOCK_ID'];

        static::modifyItem($id, $iblockId, $arItem);

        if (!is_array($_SESSION['CATALOG_COMPARE_LIST'])) {
            $_SESSION['CATALOG_COMPARE_LIST'] = [];
        }

        if (!is_array($_SESSION['CATALOG_COMPARE_LIST'][$iblockId])) {
            $_SESSION['CATALOG_COMPARE_LIST'][$iblockId] = [];
        }

        if (!is_array($_SESSION['CATALOG_COMPARE_LIST'][$iblockId]['ITEMS'])) {
            $_SESSION['CATALOG_COMPARE_LIST'][$iblockId]['ITEMS'] = [];
        }

        $_SESSION['CATALOG_COMPARE_LIST'][$iblockId]['ITEMS'][$id] = $arItem;

        print_r($_SESSION['CATALOG_COMPARE_LIST']);

        static::$bModified = true;
    }

    public static function removeItem(int $id)
    {
        if ($id <= 0) {
            throw new SystemException(Loc::getMessage('ITEMACTION_COMPARE_ERROR_ITEM_ID'));
        }

        $arItem = static::getElement($id);
        if (!$arItem) {
            throw new SystemException(Loc::getMessage('ITEMACTION_COMPARE_ERROR_ITEM'));
        }

        $iblockId = $arItem['IBLOCK_ID'];
        static::modifyItem($id, $iblockId, $arItem);

        if (!is_array($_SESSION['CATALOG_COMPARE_LIST'])) {
            $_SESSION['CATALOG_COMPARE_LIST'] = [];
        }

        if (!is_array($_SESSION['CATALOG_COMPARE_LIST'][$iblockId])) {
            $_SESSION['CATALOG_COMPARE_LIST'][$iblockId] = [
                'ITEMS' => [],
            ];
        }

        if (!is_array($_SESSION['CATALOG_COMPARE_LIST'][$iblockId]['ITEMS'])) {
            $_SESSION['CATALOG_COMPARE_LIST'][$iblockId]['ITEMS'] = [];
        }

        unset($_SESSION['CATALOG_COMPARE_LIST'][$iblockId]['ITEMS'][$id]);

        static::$bModified = true;
    }

    public static function getTitle(): string
    {
        return Loc::getMessage('ITEMACTION_COMPARE_TITLE');
    }
}

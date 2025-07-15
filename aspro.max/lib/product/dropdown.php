<?php

namespace Aspro\Max\Product;

use \Bitrix\Main\Error,
    \Aspro\Max\ItemAction\Favorite,
    \Aspro\Max\ItemAction\Compare,
    \Bitrix\Main\Loader,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Localization\Loc,
    CMax as Solution,
    CMaxCache as SolutionCache,
    \Aspro\Functions\CAsproMax as SolutionFunctions;

class Dropdown {
    protected static $iblocks = [];

    public static function getItems($type = 'favorite'): ?array
    {
        $arResult = $arProducts = [];
        $linkToAll = $titleToAll = '';

        if($type === 'compare'){
            $arProducts = Compare::getItems();
            $linkToAll = str_replace('#SITE_DIR#', SITE_DIR, Option::get(Solution::moduleID, 'COMPARE_PAGE_URL', SITE_DIR . "catalog/compare.php", SITE_ID));
            $titleToAll = Loc::getMessage('COMPARE_TO_ALL');
        } else {
            $arProducts = Favorite::getItems();
            $linkToAll = str_replace('#SITE_DIR#', SITE_DIR, Option::get(Solution::moduleID, 'FAVORITE_PAGE_URL', SITE_DIR . "personal/favorite/", SITE_ID));
            $titleToAll = Loc::getMessage('FAVORITE_TO_ALL');
        }

        $arProductsInfo = static::getItemsInfo($arProducts);
        $htmlProducts = '';

        if(!empty($arProductsInfo)){
            ob_start();
            SolutionFunctions::showBlockHtml([
                'FILE' => 'dropdown_products.php',
                'PARAMS' => [
                    'ITEMS' => $arProductsInfo,
                    'TYPE' => $type,
                    'PATH_TO_ALL' => $linkToAll,
                    'TITLE_TO_ALL' => $titleToAll,
                ],
            ]);
            $htmlProducts = trim(ob_get_clean());
        }

        $arResult['html'] = $htmlProducts;

        return $arResult;
    }

    public static function getItemsInfo( array $arItems = []) :array
    {
        Loader::includeModule("iblock");
        Loader::includeModule("fileman");

        $arCatalogItems = static::getCatalogItems($arItems);
        $arSkuItems = static::getSkuItems($arItems);
        $arElements = $arSkuItems + $arCatalogItems;

        $arResultItems = [];
        foreach ($arItems as $keyProduct) {
            $arNewElement = $arElements[$keyProduct];
            $arNewElement['IMAGE'] = [];
            if ($arNewElement['IMAGE_ID']) {
                $arNewElement['IMAGE'] = \CFile::ResizeImageGet($arNewElement['IMAGE_ID'], ['width' => 70, 'height' => 70], BX_RESIZE_IMAGE_PROPORTIONAL, true, []);
            }
            $jsonData = json_encode([
                'ID' => (int)$arNewElement['ID'],
                'IBLOCK_ID' => (int)$arNewElement['IBLOCK_ID']
            ]);
            $arNewElement['JSON_DATA'] = htmlspecialchars($jsonData);
            $arResultItems[$keyProduct] = $arNewElement;
        }

        return $arResultItems;
    }

    public static function getCatalogItems( array $arItems = []) :array {
        $arElements = [];
        $catalogIblockId = static::getCatalogIblock();

        if(!empty($arItems) && $catalogIblockId){

            $arSort = [];
            $arFilter = ['IBLOCK_ID' => $catalogIblockId, "ID" => $arItems];
            $arSelect = array('ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'IBLOCK_ID');

            $res = \CIBlockElement::getList($arSort, $arFilter, false, ['nTopCount' => 1000], $arSelect);
            while ($row = $res->GetNext()) {
                $row['IMAGE_ID'] = $row['PREVIEW_PICTURE'] ?: $row['DETAIL_PICTURE'];
                $arElements[$row['ID']] = $row;
            }
        }

        return $arElements;
    }

    public static function getSkuItems( array $arItems = []) :array {
        $arElements = [];
        $linkPropCode = 'CML2_LINK';
        $catalogSkuIblockId = static::getCatalogSkuIblock();

        if(!empty($arItems)){
            $arSort = [];
            $arFilter = ['IBLOCK_ID' => $catalogSkuIblockId, "ID" => $arItems];
            $arSelect = array('ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'IBLOCK_ID', 'PROPERTY_'.$linkPropCode.'.PREVIEW_PICTURE', 'PROPERTY_'.$linkPropCode.'.DETAIL_PICTURE');

            $res = \CIBlockElement::getList($arSort, $arFilter, false, ['nTopCount' => 1000], $arSelect);
            while ($row = $res->GetNext()) {
                $imageId = $row['PREVIEW_PICTURE'] ?: $row['DETAIL_PICTURE'];
                if(!$imageId){
                    $imageId = $row["PROPERTY_".$linkPropCode."_PREVIEW_PICTURE"] ?: $row["PROPERTY_".$linkPropCode."_DETAIL_PICTURE"];
                }
                $row['IMAGE_ID'] = $imageId;
                $arElements[$row['ID']] = $row;
            }
        }

        return $arElements;
    }

    public static function getIblocks() :array {
		if (!static::$iblocks) {
			$catalogIblockId = static::getCatalogIblock();
			if ($catalogIblockId) {
				static::$iblocks[] = $catalogIblockId;
			}
            $catalogSkuIblockId = static::getCatalogSkuIblock();
			if ($catalogSkuIblockId) {
				static::$iblocks[] = $catalogSkuIblockId;
			}
		}

		return static::$iblocks;
	}

    public static function getCatalogIblock() {
        static $catalogIblockId;

        if(!isset($catalogIblockId)){
            $catalogIblockId = Option::get(
                Solution::moduleID,
                'CATALOG_IBLOCK_ID',
                SolutionCache::$arIBlocks[SITE_ID]['aspro_'.Solution::solutionName.'_catalog']['aspro_'.Solution::solutionName.'_catalog'][0],
                SITE_ID
            );
        }

        return $catalogIblockId;
    }

    public static function getCatalogSkuIblock() {
        static $catalogSkuIblockId;

        if(!isset($catalogSkuIblockId)){
            $catalogSkuIblockId = Option::get(
				Solution::moduleID,
				'CATALOG_SKU_IBLOCK_ID',
				SolutionCache::$arIBlocks[SITE_ID]['aspro_'.Solution::solutionName.'_catalog']['aspro_'.Solution::solutionName.'_sku'][0],
				SITE_ID
			);
        }

        return $catalogSkuIblockId;
    }
}

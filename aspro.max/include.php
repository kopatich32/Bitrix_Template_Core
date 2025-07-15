<?php

CModule::AddAutoloadClasses(
    'aspro.max',
    [
        'CMaxCache' => 'classes/general/CMaxCache.php',
        'CMax' => 'classes/general/CMax.php',
        'CMaxTools' => 'classes/general/CMaxTools.php',
        'CMaxEvents' => 'classes/general/CMaxEvents.php',
        'CMaxRegionality' => 'classes/general/CMaxRegionality.php',
        'CMaxCondition' => 'classes/general/CMaxCondition.php',
        'CInstargramMax' => 'classes/general/CInstargramMax.php',
        'CVKMax' => 'classes/general/CVKMax.php',
        'Aspro\Solution\CAsproMarketingMax' => 'classes/general/CAsproMarketingMax.php',
        'Aspro\Functions\CAsproMaxSku' => 'lib/functions/CAsproMaxSku.php',
        'Aspro\Functions\CAsproMaxItem' => 'lib/functions/CAsproMaxItem.php',
        'Aspro\Functions\CAsproMax' => 'lib/functions/CAsproMax.php',
        'Aspro\Functions\CAsproMaxCustom' => 'lib/functions/CAsproMaxCustom.php',
        'Aspro\Functions\CAsproMaxCRM' => 'lib/functions/CAsproMaxCRM.php',
        'Aspro\Max\ShareBasketTable' => 'lib/sharebasket.php',
        'Aspro\Max\ShareBasketItemTable' => 'lib/sharebasketitem.php',
        'Aspro\Max\GS' => 'lib/gs.php',
        'Aspro\Max\Iconset' => 'lib/iconset.php',
        'Aspro\Max\SearchQuery' => 'lib/searchquery.php',
        'Aspro\Max\PhoneAuth' => 'lib/phoneauth.php',
        'Aspro\Max\PWA' => 'lib/pwa.php',
        'Aspro\Max\CrossSales' => 'lib/crosssales.php',
        'Aspro\Max\MarketingPopup' => 'lib/marketingpopup.php',
        'Aspro\Max\Preset' => 'lib/preset.php',
        'Aspro\Max\Property\ListStores' => 'lib/property/liststores.php',
        'Aspro\Max\Property\ListPrices' => 'lib/property/listprices.php',
        'Aspro\Max\Property\ListLocations' => 'lib/property/listlocations.php',
        'Aspro\Max\Property\CustomFilter' => 'lib/property/customfilter.php',
        'Aspro\Max\Property\CustomFilter\CondCtrl' => 'lib/property/customfilter/condctrl.php',
        'Aspro\Max\Property\Service' => 'lib/property/service.php',
        'Aspro\Max\Property\YaDirectQuery' => 'lib/property/yadirectquery.php',
        'Aspro\Max\Property\IBInherited' => 'lib/property/ibinherited.php',
        'Aspro\Max\Property\ListUsersGroups' => 'lib/property/listusersgroups.php',
        'Aspro\Max\Property\ListWebForms' => 'lib/property/listwebforms.php',
        'Aspro\Max\Property\RegionPhone' => 'lib/property/regionphone.php',
        'Aspro\Max\Property\ModalConditions' => 'lib/property/modalconditions.php',
        'Aspro\Max\Property\ModalConditions\CondModal' => 'lib/property/modalconditions/condmodal.php',
        'Aspro\Max\Property\ModalConditions\ConditionType' => 'lib/property/conditiontype.php',
        'Aspro\Max\Functions\Extensions' => 'lib/functions/Extensions.php',
        'Aspro\Max\Stores\Property' => 'lib/stores/property.php',
        'Aspro\Max\Stores\HelperHL' => 'lib/stores/helperhl.php',
        'Aspro\Max\Traits\Serialize' => 'lib/traits/serialize.php',
        'Aspro\Max\Traits\Menu' => 'lib/traits/menu.php',
        'Aspro\Max\Traits\Js' => 'lib/traits/js.php',
        'Aspro\Max\Traits\Admin' => 'lib/traits/admin.php',
        'Aspro\Max\Traits\Events\User' => 'lib/traits/events/user.php',
        'Aspro\Max\Product\Sku' => 'lib/product/sku.php',
        'Aspro\Max\Product\SkuTools' => 'lib/product/skutools.php',
    ]
);

/* test events */

/*AddEventHandler('aspro.max', 'OnAsproRegionalityAddSelectFieldsAndProps', 'OnAsproRegionalityAddSelectFieldsAndPropsHandler'); // regionality
function OnAsproRegionalityAddSelectFieldsAndPropsHandler(&$arSelect){
    if($arSelect)
    {
        // $arSelect[] = 'PROPERTY_TEST';
    }
}*/

/*AddEventHandler('aspro.max', 'OnAsproRegionalityGetElements', 'OnAsproRegionalityGetElementsHandler'); // regionality
function OnAsproRegionalityGetElementsHandler(&$arItems){
    if($arItems)
    {
        print_r($arItems);
        foreach($arItems as $key => $arItem)
        {
            $arItems[$key]['TEST'] = CUSTOM_VALUE;
        }
    }
}*/

// AddEventHandler('aspro.max', 'OnAsproShowPriceMatrix', array('\Aspro\Functions\CAsproMax', 'OnAsproShowPriceMatrixHandler'));
// function - CMax::showPriceMatrix

// AddEventHandler('aspro.max', 'OnAsproShowPriceRangeTop', array('\Aspro\Functions\CAsproMax', 'OnAsproShowPriceRangeTopHandler'));
// function - CMax::showPriceRangeTop

// AddEventHandler('aspro.max', 'OnAsproItemShowItemPrices', array('\Aspro\Functions\CAsproMax', 'OnAsproItemShowItemPricesHandler'));
// function - \Aspro\Functions\CAsproMaxItem::showItemPrices

// AddEventHandler('aspro.max', 'OnAsproSkuShowItemPrices', array('\Aspro\Functions\CAsproMax', 'OnAsproSkuShowItemPricesHandler'));
// function - \Aspro\Functions\CAsproMaxSku::showItemPrices

// AddEventHandler('aspro.max', 'OnAsproGetTotalQuantity', array('\Aspro\Functions\CAsproMax', 'OnAsproGetTotalQuantityHandler'));
// function - CMax::GetTotalCount

// AddEventHandler('aspro.max', 'OnAsproGetTotalQuantityBlock', array('\Aspro\Functions\CAsproMax', 'OnAsproGetTotalQuantityBlockHandler'));
// function - CMax::GetQuantityArray

// AddEventHandler('aspro.max', 'OnAsproGetBuyBlockElement', array('\Aspro\Functions\CAsproMax', 'OnAsproGetBuyBlockElementHandler'));
// function - CMax::GetAddToBasketArray

<?
use Bitrix\Main\Loader;

include_once('const.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

Loader::includeModule('sale');
Loader::includeModule('aspro.max');

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

if ('Y' === $request['CLEAR_ALL']) {
    Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('basket-allitems-block');

    $arBasketItems = CMax::getBasketItems(0, 'ID');

    $type = 'BASKET';
    if ($request['TYPE']) {
        switch ($request['TYPE']) {
            case 'all':
                $type = 'all';
                break;

            case 2:
                $type = 'DELAY';
                break;

            // case 3:
            //  $type = 'SUBSCRIBE'; // not used
            //  break;

            case 4:
                $type = 'NOT_AVAILABLE';
                break;  

            default:
                break;
        }
    }

    $arIDs2Delete = [];
    if ('all' === $type) {
        foreach ($arBasketItems as $key => $arItems) {
            if (
                'BASKET' === $key
                || 'NOT_AVAILABLE' === $key
                || 'SERVICES' === $key
                || 'DELAY' === $key // not used
                // || 'SUBSCRIBE' === $key // not used
            ) {
                if ('SERVICES' === $key) {
                    $arIDs2Delete = array_merge($arIDs2Delete, array_column($arItems, 'item_id'));
                }
                else {
                    $arIDs2Delete = array_merge($arIDs2Delete, $arItems);
                }
            }
        }
    }
    elseif ($type && $arBasketItems[$type]) {
        foreach ($arBasketItems[$type] as $id) {
            $id = intval($id);
            if ($id > 0) {
                $arIDs2Delete[] = $id;
            }
        }
    }

    $arIDs2Delete = CMax::checkUserCurrentBasketItems($arIDs2Delete);
    if ($arIDs2Delete) {
        foreach ($arIDs2Delete as $id) {
            CSaleBasket::Delete($id);
        }
    }

    Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('basket-allitems-block', '');
}
elseif ('Y' === $request['delete_top_item']) {
    $id = empty($request['delete_top_item_id']) ? 0 : $request['delete_top_item_id'];
        
    $arIDs2Delete = CMax::checkUserCurrentBasketItems($id);
    foreach ($arIDs2Delete as $id) {
        CSaleBasket::Delete($id);
    }
}

CMaxCache::ClearCacheByTag('sale_basket');
CMax::clearBasketCounters();

<?
use Bitrix\Main\Loader,
    CMax as Solution;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$bShowShareBasket = $shareBasketPageUrl = false;
if(Loader::includeModule(Solution::moduleID)){
    $bShowShareBasket = Solution::GetFrontParametrValue('SHOW_SHARE_BASKET', SITE_ID) === 'Y';
    $shareBasketPageUrl = Solution::GetFrontParametrValue('SHARE_BASKET_PAGE_URL', SITE_ID);
}

if(!$bShowShareBasket){
    if(Loader::includeModule('iblock')){
        // goto 404
        Bitrix\Iblock\Component\Tools::process404(
            '',
            true,
            true,
            true,
            $arParams['FILE_404']
        );

        return;
    }
    else{
        LocalRedirect(
            defined('SITE_DIR') ? SITE_DIR : '/',
            true,
            '302 Found'
        );

        die();
    }
}

$arDefaultUrlTemplates404 = array(
    'new'   => 'new/',
    'detail' => '#CODE#/',
    'list' => '',
);

$arDefaultVariableAliases404 = array(
    'detail' => array(
        'CODE' => 'CODE',
    ),
);

$arDefaultVariableAliases = array(
    'CODE' => 'CODE',
);

$arComponentVariables = array('CODE');

$SEF_FOLDER = '';
$arUrlTemplates = $arVariables = array();

if($arParams['SEF_MODE'] === 'Y'){
    $arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates(
        $arDefaultUrlTemplates404,
        $arParams['SEF_URL_TEMPLATES']
    );

    $arVariableAliases = CComponentEngine::MakeComponentVariableAliases(
        $arDefaultVariableAliases404,
        $arParams['VARIABLE_ALIASES']
    );

    $componentPage = CComponentEngine::ParseComponentPath(
        $arParams['SEF_FOLDER'],
        $arUrlTemplates,
        $arVariables
    );

    if(!strlen($componentPage)){
        $componentPage = 'list';
    }

    CComponentEngine::InitComponentVariables(
        $componentPage,
        $arComponentVariables,
        $arVariableAliases,
        $arVariables
    );

    $SEF_FOLDER = $arParams['SEF_FOLDER'];
}
else{

    $arVariableAliases = CComponentEngine::MakeComponentVariableAliases(
        $arDefaultVariableAliases,
        $arParams['VARIABLE_ALIASES']
    );

    CComponentEngine::InitComponentVariables(
        false,
        $arComponentVariables,
        $arVariableAliases,
        $arVariables
    );

    $arUrlTemplates = array(
        'detail' => '?'.$arVariableAliases['CODE'].'=#CODE#',
        'new' => '?'.$arVariableAliases['CODE'].'=new',
    );
    $SEF_FOLDER = $shareBasketPageUrl;

    if(strlen($arVariables['CODE'])){
        if($arVariables['CODE'] === 'new'){
          $componentPage = 'new';
        }
        else{
          $componentPage = 'detail';
        }
    }
    else{
        $componentPage = 'list';
    }
}

if(
    strpos($_SERVER['SCRIPT_NAME'], '/ajax/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/form/') !== false
){
    $componentPage = 'new';
}

if($componentPage === 'list'){
    LocalRedirect(
        defined('SITE_DIR') ? SITE_DIR : '/',
        true,
        '302 Found'
    );
    die();
}

$arParams['NEW_SET_PAGE_TITLE'] = isset($arParams['NEW_SET_PAGE_TITLE']) && $arParams['NEW_SET_PAGE_TITLE'] === 'N' ? 'N' : 'Y';

if(
    isset($arParams['NEW_SITE_ID']) &&
    strlen(trim($arParams['NEW_SITE_ID']))
){
    $arParams['NEW_SITE_ID'] = trim($arParams['NEW_SITE_ID']);
}
else{
    $arParams['NEW_SITE_ID'] = '';
}

if(
    isset($arParams['NEW_USER_ID']) &&
    intval(trim($arParams['NEW_USER_ID'])) > 0
){
    $arParams['NEW_USER_ID'] = intval(trim($arParams['NEW_USER_ID']));
}
else{
    $arParams['NEW_USER_ID'] = '';
}

$arParams['NEW_SHOW_SHARE_SOCIALS'] = isset($arParams['NEW_SHOW_SHARE_SOCIALS']) && $arParams['NEW_SHOW_SHARE_SOCIALS'] === 'N' ? 'N' : 'Y';
$arParams['NEW_SHOW_QRCODE'] = isset($arParams['NEW_SHOW_QRCODE']) && $arParams['NEW_SHOW_QRCODE'] === 'N' ? 'N' : 'Y';

$arParams['NEW_SHARE_SOCIALS'] = (isset($arParams['NEW_SHARE_SOCIALS']) && is_array($arParams['NEW_SHARE_SOCIALS']) && $arParams['NEW_SHARE_SOCIALS']) ? $arParams['NEW_SHARE_SOCIALS'] : array(
    'VKONTAKTE',
    'FACEBOOK',
    'ODNOKLASSNIKI',
    'TWITTER',
);

$arParams['NEW_USE_CUSTOM_MESSAGES'] = isset($arParams['NEW_USE_CUSTOM_MESSAGES']) && $arParams['NEW_USE_CUSTOM_MESSAGES'] === 'Y' ? 'Y' : 'N';
$arParams['NEW_MESS_TITLE'] = isset($arParams['NEW_MESS_TITLE']) ? $arParams['NEW_MESS_TITLE'] : '';
$arParams['NEW_MESS_URL_FIELD_TITLE'] = isset($arParams['NEW_MESS_URL_FIELD_TITLE']) ? $arParams['NEW_MESS_URL_FIELD_TITLE'] : '';
$arParams['NEW_MESS_URL_COPY_HINT'] = isset($arParams['NEW_MESS_URL_COPY_HINT']) ? $arParams['NEW_MESS_URL_COPY_HINT'] : '';
$arParams['NEW_MESS_URL_COPIED_HINT'] = isset($arParams['NEW_MESS_URL_COPIED_HINT']) ? $arParams['NEW_MESS_URL_COPIED_HINT'] : '';
$arParams['NEW_MESS_URL_COPY_ERROR_HINT'] = isset($arParams['NEW_MESS_URL_COPY_ERROR_HINT']) ? $arParams['NEW_MESS_URL_COPY_ERROR_HINT'] : '';
$arParams['NEW_MESS_SHARE_SOCIALS_TITLE'] = isset($arParams['NEW_MESS_SHARE_SOCIALS_TITLE']) ? $arParams['NEW_MESS_SHARE_SOCIALS_TITLE'] : '';
$arParams['NEW_MESS_QR_CODE_HINT'] = isset($arParams['NEW_MESS_QR_CODE_HINT']) ? $arParams['NEW_MESS_QR_CODE_HINT'] : '';

$arParams['DETAIL_ACTUAL'] = isset($arParams['DETAIL_ACTUAL']) && $arParams['DETAIL_ACTUAL'] === 'N' ? 'N' : 'Y';
$arParams['DETAIL_SHOW_VERSION_SWITCHER'] = isset($arParams['DETAIL_SHOW_VERSION_SWITCHER']) && $arParams['DETAIL_SHOW_VERSION_SWITCHER'] === 'N' ? 'N' : 'Y';
$arParams['DETAIL_USE_COMPARE'] = isset($arParams['DETAIL_USE_COMPARE']) && $arParams['DETAIL_USE_COMPARE'] === 'N' ? 'N' : 'Y';
$arParams['DETAIL_USE_DELAY'] = isset($arParams['DETAIL_USE_DELAY']) && $arParams['DETAIL_USE_DELAY'] === 'Y' ? 'Y' : 'N';
$arParams['DETAIL_USE_FAST_VIEW'] = isset($arParams['DETAIL_USE_FAST_VIEW']) && $arParams['DETAIL_USE_FAST_VIEW'] === 'Y' ? 'Y' : 'N';
$arParams['DETAIL_SHOW_ONE_CLICK_BUY'] = isset($arParams['DETAIL_SHOW_ONE_CLICK_BUY']) && $arParams['DETAIL_SHOW_ONE_CLICK_BUY'] === 'Y' ? 'Y' : 'N';
$arParams['DETAIL_SHOW_STICKERS'] = isset($arParams['DETAIL_SHOW_STICKERS']) && $arParams['DETAIL_SHOW_STICKERS'] === 'Y' ? 'Y' : 'N';

$arParams['FAV_ITEM'] = isset($arParams['FAV_ITEM']) ? trim($arParams['FAV_ITEM']) : 'FAVORIT_ITEM';
$arParams['FINAL_PRICE'] = isset($arParams['FINAL_PRICE']) ? trim($arParams['FINAL_PRICE']) : 'FINAL_PRICE';
$arParams['STIKERS_PROP'] = isset($arParams['STIKERS_PROP']) ? trim($arParams['STIKERS_PROP']) : 'HIT';
$arParams['SALE_STIKER'] = isset($arParams['SALE_STIKER']) ? trim($arParams['SALE_STIKER']) : 'SALE_TEXT';

$arParams['DETAIL_SHOW_AMOUNT'] = isset($arParams['DETAIL_SHOW_AMOUNT']) && $arParams['DETAIL_SHOW_AMOUNT'] === 'N' ? 'N' : 'Y';
$arParams['DETAIL_SHOW_OLD_PRICE'] = isset($arParams['DETAIL_SHOW_OLD_PRICE']) && $arParams['DETAIL_SHOW_OLD_PRICE'] === 'N' ? 'N' : 'Y';
$arParams['DETAIL_SHOW_DISCOUNT_PERCENT'] = isset($arParams['DETAIL_SHOW_DISCOUNT_PERCENT']) && $arParams['DETAIL_SHOW_DISCOUNT_PERCENT'] === 'N' ? 'N' : 'Y';
$arParams['DETAIL_SHOW_DISCOUNT_PERCENT_NUMBER'] = isset($arParams['DETAIL_SHOW_DISCOUNT_PERCENT_NUMBER']) && $arParams['DETAIL_SHOW_DISCOUNT_PERCENT_NUMBER'] === 'N' ? 'N' : 'Y';

$arParams['DETAIL_PRODUCT_PROPERTIES'] = (isset($arParams['DETAIL_PRODUCT_PROPERTIES']) && is_array($arParams['DETAIL_PRODUCT_PROPERTIES']) && $arParams['DETAIL_PRODUCT_PROPERTIES']) ? $arParams['DETAIL_PRODUCT_PROPERTIES'] : array(
    'CML2_ARTICLE',
);

$arParams['DETAIL_USE_CUSTOM_MESSAGES'] = isset($arParams['DETAIL_USE_CUSTOM_MESSAGES']) && $arParams['DETAIL_USE_CUSTOM_MESSAGES'] === 'Y' ? 'Y' : 'N';
$arParams['DETAIL_MESS_TITLE'] = isset($arParams['DETAIL_MESS_TITLE']) ? $arParams['DETAIL_MESS_TITLE'] : '';
$arParams['DETAIL_MESS_SHOW_ACTUAL_PRICES'] = isset($arParams['DETAIL_MESS_SHOW_ACTUAL_PRICES']) ? $arParams['DETAIL_MESS_SHOW_ACTUAL_PRICES'] : '';
$arParams['DETAIL_MESS_TOTAL_PRICE'] = isset($arParams['DETAIL_MESS_TOTAL_PRICE']) ? $arParams['DETAIL_MESS_TOTAL_PRICE'] : '';
$arParams['DETAIL_MESS_ADD_TO_BASKET'] = isset($arParams['DETAIL_MESS_ADD_TO_BASKET']) ? $arParams['DETAIL_MESS_ADD_TO_BASKET'] : '';
$arParams['DETAIL_MESS_REPLACE_BASKET'] = isset($arParams['DETAIL_MESS_REPLACE_BASKET']) ? $arParams['DETAIL_MESS_REPLACE_BASKET'] : '';
$arParams['DETAIL_MESS_PRODUCT_ECONOMY'] = isset($arParams['DETAIL_MESS_PRODUCT_ECONOMY']) ? $arParams['DETAIL_MESS_PRODUCT_ECONOMY'] : '';
$arParams['DETAIL_MESS_PRODUCT_NOT_EXISTS'] = isset($arParams['DETAIL_MESS_PRODUCT_NOT_EXISTS']) ? $arParams['DETAIL_MESS_PRODUCT_NOT_EXISTS'] : '';

$arResult = array(
    'FOLDER'        => $SEF_FOLDER,
    'URL_TEMPLATES' => $arUrlTemplates,
    'VARIABLES'  => $arVariables,
    'ALIASES'      => $arVariableAliases,
);

$this->IncludeComponentTemplate($componentPage);

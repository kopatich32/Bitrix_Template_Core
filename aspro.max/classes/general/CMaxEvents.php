<?php
if (!defined('ASPRO_MAX_MODULE_ID')) {
    define('ASPRO_MAX_MODULE_ID', 'aspro.max');
}

use Aspro\Functions\CAsproMax as SolutionFunctions;
use Aspro\Max\Captcha;
use Aspro\Max\CRM;
use Aspro\Max\Functions\Extensions;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Sale\Internals\OrderPropsTable;
use CMax as Solution;
use CMaxCache as Cache;
use CMaxRegionality as SolutionRegionality;

Loc::loadMessages(__FILE__);

class CMaxEvents
{
    use Aspro\Max\Traits\Events\User;

    public const moduleID = ASPRO_MAX_MODULE_ID;
    public const partnerName = 'aspro';
    public const solutionName = 'max';
    public const wizardID = 'aspro:max';

    public static function BeforeSendEvent(Event $event)
    {
        if (isset($_REQUEST['ONE_CLICK_BUY']) && method_exists('\Bitrix\Sale\Compatible\EventCompatibility', 'setDisableMailSend')) {
            Bitrix\Sale\Compatible\EventCompatibility::setDisableMailSend(true);
            if (method_exists('\Bitrix\Sale\Notify', 'setNotifyDisable')) {
                Bitrix\Sale\Notify::setNotifyDisable(true);
            }
        }
    }

    public static function OnBeforeEventAddHandler(&$event, &$lid, &$arFields, &$message_id)
    {
        if (Bitrix\Main\Loader::includeModule(self::moduleID)) {
            if ($arCurrentRegion = SolutionRegionality::getCurrentRegion()) {
                $arFields['REGION_ID'] = $arCurrentRegion['ID'];
                $arFields['REGION_MAIN_DOMAIN'] = $arCurrentRegion['PROPERTY_MAIN_DOMAIN_VALUE'];
                $arFields['REGION_MAIN_DOMAIN_RAW'] = (CMain::IsHTTPS() ? 'https://' : 'http://').$arCurrentRegion['PROPERTY_MAIN_DOMAIN_VALUE'];
                $arFields['REGION_ADDRESS'] = $arCurrentRegion['PROPERTY_ADDRESS_VALUE']['TEXT'] ?? '';
                $arFields['REGION_EMAIL'] = implode(', ', $arCurrentRegion['PROPERTY_EMAIL_VALUE']);
                $arFields['REGION_PHONE'] = implode(', ', $arCurrentRegion['PHONES']);

                $arTagSeoMarks = [];
                foreach ($arCurrentRegion as $key => $value) {
                    if (strpos($key, 'PROPERTY_REGION_TAG') !== false && strpos($key, '_VALUE_ID') === false) {
                        $tag_name = str_replace(['PROPERTY_', '_VALUE'], '', $key);
                        $arTagSeoMarks['#'.$tag_name.'#'] = $key;
                    }
                }

                if ($arTagSeoMarks) {
                    SolutionRegionality::addSeoMarks($arTagSeoMarks);
                }

                foreach (SolutionRegionality::$arSeoMarks as $mark => $field) {
                    $mark = str_replace('#', '', $mark);
                    if (is_array($arCurrentRegion[$field])) {
                        $arFields[$mark] = $arCurrentRegion[$field]['TEXT'] ?? '';
                    } else {
                        $arFields[$mark] = $arCurrentRegion[$field];
                    }
                }
            }
        }
    }

    public static function OnBeforeAction(Event $event)
    {
        $controller = $event->getParameter('controller');
        $controllerName = get_class($controller);

        $action = $event->getParameter('action');
        $actionName = $action->getName();

        if (
            $controllerName === 'Bitrix\Main\Controller\LoadExt'
            && $actionName === 'getextensions'
            && Solution::isSelfSiteTemplate(SITE_TEMPLATE_PATH)
        ) {
            Extensions::register();
        }

        $event->addResult(new EventResult(EventResult::SUCCESS));
    }

    public static function fixRegionMailFields(&$arFields, $regionID = null)
    {
        $arCurrentRegion = [];
    }

    public static function OnFindSocialservicesUserHandler($arFields)
    {
        // check for user with email
        if ($arFields['EMAIL']) {
            $arUser = CUser::GetList($by = 'ID', $ord = 'ASC', ['EMAIL' => $arFields['EMAIL'], 'ACTIVE' => 'Y'], ['NAV_PARAMS' => ['nTopCount' => '1']])->fetch();
            if ($arUser) {
                if ($arFields['PERSONAL_PHOTO']) {
                    /*if(!$arUser['PERSONAL_PHOTO'])
                    {
                        $arUpdateFields = Array(
                            'PERSONAL_PHOTO' => $arFields['PERSONAL_PHOTO'],
                        );
                        $user->Update($arUser['ID'], $arUpdateFields);
                    }
                    else
                    {*/
                    $code = 'UF_'.strtoupper($arFields['EXTERNAL_AUTH_ID']);
                    $arUserFieldUserImg = CUserTypeEntity::GetList([], ['ENTITY_ID' => 'USER', 'FIELD_NAME' => $code])->Fetch();
                    if (!$arUserFieldUserImg) {
                        $arFieldsUser = [
                            'FIELD_NAME' => $code,
                            'USER_TYPE_ID' => 'file',
                            'XML_ID' => $code,
                            'SORT' => 100,
                            'MULTIPLE' => 'N',
                            'MANDATORY' => 'N',
                            'SHOW_FILTER' => 'N',
                            'SHOW_IN_LIST' => 'Y',
                            'EDIT_IN_LIST' => 'Y',
                            'IS_SEARCHABLE' => 'N',
                            'SETTINGS' => [
                                'DISPLAY' => 'LIST',
                                'LIST_HEIGHT' => 5,
                            ],
                        ];
                        $arLangs = [
                            'EDIT_FORM_LABEL' => [
                                'ru' => $code,
                                'en' => $code,
                            ],
                            'LIST_COLUMN_LABEL' => [
                                'ru' => $code,
                                'en' => $code,
                            ],
                        ];

                        $ob = new CUserTypeEntity();
                        $FIELD_ID = $ob->Add(array_merge($arFieldsUser, ['ENTITY_ID' => 'USER'], $arLangs));
                    }
                    $user = new CUser();
                    $arUpdateFields = [
                        $code => $arFields['PERSONAL_PHOTO'],
                    ];
                    $user->Update($arUser['ID'], $arUpdateFields);
                    // }
                }

                return $arUser['ID'];
            }
        }

        return false;
    }

    public static function OnAfterSocServUserAddHandler($arFields)
    {
        if ($arFields['EMAIL']) {
            global $USER;
            $userEmail = $USER->GetEmail();
            $email = (is_null($userEmail) ? $arFields['EMAIL'] : $userEmail);
            // $resUser = CUser::GetList(($by="ID"), ($order="asc"), array("=EMAIL" => $arFields["EMAIL"]), array("FIELDS" => array("ID")));
            $resUser = CUser::GetList($by = 'ID', $order = 'asc', ['=EMAIL' => $email], ['FIELDS' => ['ID']]);
            $arUserAlreadyExist = $resUser->Fetch();

            if ($arUserAlreadyExist['ID']) {
                Bitrix\Main\Loader::includeModule('socialservices');
                global $USER;
                if ($resUser->SelectedRowsCount() > 1) {
                    CSocServAuthDB::Update($arFields['ID'], ['USER_ID' => $arUserAlreadyExist['ID'], 'CAN_DELETE' => 'Y']);
                    CUser::Delete($arFields['USER_ID']);
                    $USER->Authorize($arUserAlreadyExist['ID']);
                } else {
                    $def_group = COption::GetOptionString('main', 'new_user_registration_def_group', '');
                    if ($def_group != '') {
                        $GROUP_ID = explode(',', $def_group);
                        $arPolicy = $USER->GetGroupPolicy($GROUP_ID);
                    } else {
                        $arPolicy = $USER->GetGroupPolicy([]);
                    }
                    $password_min_length = (int) $arPolicy['PASSWORD_LENGTH'];
                    if ($password_min_length <= 0) {
                        $password_min_length = 6;
                    }
                    $password_chars = [
                        'abcdefghijklnmopqrstuvwxyz',
                        'ABCDEFGHIJKLNMOPQRSTUVWXYZ',
                        '0123456789',
                    ];
                    if ($arPolicy['PASSWORD_PUNCTUATION'] === 'Y') {
                        $password_chars[] = ",.<>/?;:'\"[]{}\|`~!@#\$%^&*()-_+=";
                    }
                    $NEW_PASSWORD = $NEW_PASSWORD_CONFIRM = randString($password_min_length + 2, $password_chars);

                    $user = new CUser();
                    $arFieldsUser = [
                        'NAME' => $arFields['NAME'],
                        'LAST_NAME' => $arFields['LAST_NAME'],
                        'EMAIL' => $arFields['EMAIL'],
                        'LOGIN' => $arFields['EMAIL'],
                        'GROUP_ID' => $GROUP_ID,
                        'PASSWORD' => $NEW_PASSWORD,
                        'CONFIRM_PASSWORD' => $NEW_PASSWORD_CONFIRM,
                    ];
                    unset($arFields['LOGIN']);
                    unset($arFields['PASSWORD']);
                    unset($arFields['EXTERNAL_AUTH_ID']);
                    unset($arFields['XML_ID']);
                    $arAddFields = [];
                    $arAddFields = array_merge($arFieldsUser, $arFields);
                    if (isset($arAddFields['PERSONAL_PHOTO']) && $arAddFields['PERSONAL_PHOTO']) {
                        $arPic = CFile::MakeFileArray($arFields['PERSONAL_PHOTO']);
                        $arAddFields['PERSONAL_PHOTO'] = $arPic;
                    }

                    // if($arUserAlreadyExist["ID"]!=$arFields["USER_ID"]){
                    $ID = $user->Add($arAddFields);
                    // $ID = $user->Add($arFieldsUser);
                    CSocServAuthDB::Update($arFields['ID'], ['USER_ID' => $ID, 'CAN_DELETE' => 'Y']);
                    CUser::Delete($arFields['USER_ID']);
                    $USER->Authorize($ID);
                    // }
                }
            }
        }
    }

    public static function OnSaleComponentOrderProperties(&$arUserResult, $arRequest, $arParams, $arResult)
    {
        if ($arUserResult['ORDER_PROP']) {
            $arPhoneProp = CSaleOrderProps::GetList(
                ['SORT' => 'ASC'],
                [
                    'PERSON_TYPE_ID' => $arUserResult['PERSON_TYPE_ID'],
                    'IS_PHONE' => 'Y',
                ],
                false,
                false,
                []
            )->fetch(); // get phone prop
            if ($arPhoneProp && $arParams['USE_PHONE_NORMALIZATION'] != 'N') {
                global $USER;
                if ($arUserResult['ORDER_PROP'][$arPhoneProp['ID']]) {
                    if ($_REQUEST['order']['ORDER_PROP_'.$arPhoneProp['ID']] && $_REQUEST['order']['profile_change'] != 'Y') {
                        $arUserResult['ORDER_PROP'][$arPhoneProp['ID']] = $_REQUEST['order']['ORDER_PROP_'.$arPhoneProp['ID']];
                    } else {
                        if ($arUserResult['PROFILE_ID']) { // get phone from user profile
                            $arUserPropValue = CSaleOrderUserPropsValue::GetList(
                                ['ID' => 'ASC'],
                                ['USER_PROPS_ID' => $arUserResult['PROFILE_ID'], 'ORDER_PROPS_ID' => $arPhoneProp['ID']]
                            )->fetch();
                            if ($arUserPropValue['VALUE']) {
                                $arUserResult['ORDER_PROP'][$arPhoneProp['ID']] = $arUserPropValue['VALUE'];
                            }
                        } elseif ($USER->isAuthorized()) { // get phone from user field
                            $rsUser = CUser::GetByID($USER->GetID());
                            if ($arUser = $rsUser->Fetch()) {
                                if (!empty($arUser['PERSONAL_PHONE'])) {
                                    $value = $arUser['PERSONAL_PHONE'];
                                } elseif (!empty($arUser['PERSONAL_MOBILE'])) {
                                    $value = $arUser['PERSONAL_MOBILE'];
                                }
                            }
                            if ($value) {
                                $arUserResult['ORDER_PROP'][$arPhoneProp['ID']] = $value;
                            }
                        }
                        if ($arUserResult['ORDER_PROP'][$arPhoneProp['ID']]) { // add + mark for correct mask
                            $mask = Option::get('aspro.max', 'PHONE_MASK', '+7 (999) 999-99-99');
                            if (strpos($arUserResult['ORDER_PROP'][$arPhoneProp['ID']], '+') === false && strpos($mask, '+') !== false) {
                                $arUserResult['ORDER_PROP'][$arPhoneProp['ID']] = '+'.$arUserResult['ORDER_PROP'][$arPhoneProp['ID']];
                            }
                        }
                    }
                }
            }
        }
    }

    public static function OnSaleComponentOrderOneStepProcess(&$arResult, $arUserResult, $arParams)
    {
        if (isset($_SESSION['ASPRO_MAX_USE_MODIFIER']) || isset($_COOKIE['ASPRO_MAX_USE_MODIFIER'])) {
            $bServicesInOrder = false;
            $link_services = [];
            foreach ($arResult['JS_DATA']['GRID']['ROWS'] as $key => $arItem) {
                /* fill buy services array */
                if ($arItem['data']['PROPS']) {
                    $arPropsByCode = array_column($arItem['data']['PROPS'], null, 'CODE');
                    $isServices = isset($arPropsByCode['ASPRO_BUY_PRODUCT_ID']) && $arPropsByCode['ASPRO_BUY_PRODUCT_ID']['VALUE'] > 0;
                    $services_info = [];
                    if ($isServices) {
                        $services_info['BASKET_ID'] = $arItem['data']['ID'];
                        $services_info['PRODUCT_ID'] = $arItem['data']['PRODUCT_ID'];
                        $services_info['NAME'] = $arItem['data']['NAME'];
                        $services_info['QUANTITY'] = $arItem['data']['QUANTITY'];
                        $services_info['MEASURE_TEXT'] = $arItem['data']['MEASURE_TEXT'];
                        $services_info['PRICE'] = $arItem['data']['PRICE'];
                        $services_info['PRICE_FORMATED'] = $arItem['data']['PRICE_FORMATED'];
                        $services_info['BASE_PRICE_FORMATED'] = $arItem['data']['BASE_PRICE_FORMATED'];
                        $services_info['SUM_FORMATED'] = $arItem['data']['SUM'];
                        $services_info['SUM_BASE_FORMATED'] = $arItem['data']['SUM_BASE_FORMATED'];
                        $services_info['NEED_SHOW_OLD_SUM'] = $arItem['data']['DISCOUNT_PRICE'] > 0 ? 'Y' : 'N';
                        $services_info['CURRENCY'] = $arItem['data']['CURRENCY'];
                        $link_services[$arPropsByCode['ASPRO_BUY_PRODUCT_ID']['VALUE']][$arItem['data']['PRODUCT_ID']] = $services_info;
                        $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']['IS_SERVICES'] = 'Y';
                        $bServicesInOrder = true;
                    } else {
                        $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']['IS_SERVICES'] = 'N';
                    }
                }
            }

            if ($link_services) {
                $arResult['JS_DATA']['SERVICES_ITEMS'] = [
                    'COUNT' => 0,
                    'SUMM' => 0,
                    'SUMM_FORMATTED' => 0,
                    'CURRENCY' => '',
                ];
            }

            foreach ($arResult['JS_DATA']['GRID']['ROWS'] as $key => $arItem) {
                /* fill link services add to cart */
                if (is_array($link_services) && count($link_services) > 0) {
                    if (isset($link_services[$arItem['data']['PRODUCT_ID']])) {
                        $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']['LINK_SERVICES'] = $link_services[$arItem['data']['PRODUCT_ID']];

                        $arResult['JS_DATA']['SERVICES_ITEMS']['COUNT'] += count($link_services[$arItem['data']['PRODUCT_ID']]);
                        $arResult['JS_DATA']['SERVICES_ITEMS']['SUMM'] += array_reduce($link_services[$arItem['data']['PRODUCT_ID']], function ($carry, $item) {
                            return $carry + ($item['QUANTITY'] * $item['PRICE']);
                        }, 0);
                        $arResult['JS_DATA']['SERVICES_ITEMS']['CURRENCY'] = $arItem['data']['CURRENCY'];
                    }
                }
            }

            if ($arResult['JS_DATA']['SERVICES_ITEMS']['SUMM']) {
                $arResult['JS_DATA']['SERVICES_ITEMS']['SUMM_FORMATTED'] = CCurrencyLang::CurrencyFormat($arResult['JS_DATA']['SERVICES_ITEMS']['SUMM'], $arResult['JS_DATA']['SERVICES_ITEMS']['CURRENCY'], true);

                $arResult['JS_DATA']['TOTAL']['ITEMS_COUNT'] = $arResult['JS_DATA']['TOTAL']['BASKET_POSITIONS'] - $arResult['JS_DATA']['SERVICES_ITEMS']['COUNT'];
                $arResult['JS_DATA']['TOTAL']['ITEMS_SUMM'] = CCurrencyLang::CurrencyFormat($arResult['JS_DATA']['TOTAL']['ORDER_PRICE'] - $arResult['JS_DATA']['SERVICES_ITEMS']['SUMM'], $arResult['JS_DATA']['SERVICES_ITEMS']['CURRENCY'], true);
            }

            $arResult['JS_DATA']['SERVICES_IN_ORDER'] = $bServicesInOrder;
        }
    }

    public static function OnSaleComponentOrderOneStepComplete($ID, $arOrder, $arParams)
    {
        $arOrderProps = [];
        $resOrder = CSaleOrderPropsValue::GetList([], ['ORDER_ID' => $ID]);
        while ($item = $resOrder->fetch()) {
            $arOrderProps[$item['CODE']] = $item;
        }
        $arPhoneProp = CSaleOrderProps::GetList(
            ['SORT' => 'ASC'],
            [
                'PERSON_TYPE_ID' => $arOrder['PERSON_TYPE_ID'],
                'IS_PHONE' => 'Y',
            ],
            false,
            false,
            []
        )->fetch(); // get phone prop
        if ($arPhoneProp && $arParams['USE_PHONE_NORMALIZATION'] != 'N') {
            if ($arOrderProps[$arPhoneProp['CODE']]) {
                if ($arOrderProps[$arPhoneProp['CODE']]['VALUE']) {
                    if ($_REQUEST['ORDER_PROP_'.$arOrderProps[$arPhoneProp['CODE']]['ORDER_PROPS_ID']]) {
                        CSaleOrderPropsValue::Update($arOrderProps[$arPhoneProp['CODE']]['ID'], ['VALUE' => $_REQUEST['ORDER_PROP_'.$arOrderProps[$arPhoneProp['CODE']]['ORDER_PROPS_ID']]]); // set phone order prop
                        $arUserProps = CSaleOrderUserProps::GetList(
                            ['DATE_UPDATE' => 'DESC'],
                            ['USER_ID' => $arOrder['USER_ID'], 'PERSON_TYPE_ID' => $arOrder['PERSON_TYPE_ID']]
                        )->fetch(); // get user profile info

                        if ($arUserProps) {
                            $arUserPropValue = CSaleOrderUserPropsValue::GetList(
                                ['ID' => 'ASC'],
                                ['USER_PROPS_ID' => $arUserProps['ID'], 'ORDER_PROPS_ID' => $arOrderProps[$arPhoneProp['CODE']]['ORDER_PROPS_ID']]
                            )->fetch(); // get phone from user prop
                            if ($arUserPropValue['VALUE']) {
                                CSaleOrderUserPropsValue::Update($arUserPropValue['ID'], ['VALUE' => $_REQUEST['ORDER_PROP_'.$arOrderProps[$arPhoneProp['CODE']]['ORDER_PROPS_ID']]]); // set phone in user profile
                            }
                        }
                    }
                }
            }
        }

        $siteId = $arOrder['LID'] ?: SITE_ID;
        $acloud = CRM\Acloud\Connection::getInstance($siteId);
        if ($acloud->orders_autosend) {
            try {
                CRM\Helper::sendOrder($ID, $acloud);
            } catch (Exception $e) {
            }
        }
    }

    public static function correctInstall()
    {
        if (COption::GetOptionString(self::moduleID, 'WIZARD_DEMO_INSTALLED') == 'Y') {
            if (CModule::IncludeModule('main')) {
                require_once $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/classes/general/wizard.php';
                @set_time_limit(0);
                /* if(!CWizardUtil::DeleteWizard(self::wizardID)){if(!DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/".self::partnerName."/".self::solutionName."/")){self::removeDirectory($_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/".self::partnerName."/".self::solutionName."/");}} */
                UnRegisterModuleDependences('main', 'OnBeforeProlog', self::moduleID, get_class(), 'correctInstall');
                COption::SetOptionString(self::moduleID, 'WIZARD_DEMO_INSTALLED', 'N');
            }
        }
    }

    public static function OnBeforeUserUpdateHandler(&$arFields)
    {

        $bTmpUser = false;
        $bAdminSection = (defined('ADMIN_SECTION') && ADMIN_SECTION === true);

        if (strlen($arFields['NAME'])) {
            $arFields['NAME'] = trim($arFields['NAME']);
        }

        $siteID = SITE_ID;

        if ($bAdminSection) {
            // include CMainPage
            require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/mainpage.php';
            // get site_id by host
            $CMainPage = new CMainPage();
            $siteID = $CMainPage->GetSiteByHost();
            if (!$siteID) {
                $siteID = 's1';
            }

            $sOneFIO = COption::GetOptionString(ASPRO_MAX_MODULE_ID, 'PERSONAL_ONEFIO', 'N', $siteID);
            $sChangeLogin = COption::GetOptionString(ASPRO_MAX_MODULE_ID, 'LOGIN_EQUAL_EMAIL', 'N', $siteID);
        } else {
            $arFrontParametrs = Solution::GetFrontParametrsValues($siteID);
            $sOneFIO = $arFrontParametrs['PERSONAL_ONEFIO'];
            $sChangeLogin = $arFrontParametrs['LOGIN_EQUAL_EMAIL'];
        }

        if (isset($arFields['NAME'])) {
            $arFields['NAME'] = trim($arFields['NAME']);
        }
        if (isset($arFields['LAST_NAME'])) {
            $arFields['LAST_NAME'] = trim($arFields['LAST_NAME']);
        }
        if (isset($arFields['SECOND_NAME'])) {
            $arFields['SECOND_NAME'] = trim($arFields['SECOND_NAME']);
        }

        if (strlen($arFields['NAME']) && !strlen($arFields['LAST_NAME']) && !strlen($arFields['SECOND_NAME'])) {
            if ($sOneFIO !== 'N') {
                $arName = explode(' ', $arFields['NAME']);

                if ($arName) {
                    $arFields['NAME'] = '';
                    $arFields['SECOND_NAME'] = '';
                    foreach ($arName as $i => $name) {
                        if (!$i) {
                            $arFields['LAST_NAME'] = $name;
                        } else {
                            if (!strlen($arFields['NAME'])) {
                                $arFields['NAME'] = $name;
                            } elseif (!strlen($arFields['SECOND_NAME'])) {
                                $arFields['SECOND_NAME'] = $name;
                            }
                        }
                    }
                }
            }
        }
        if ($_REQUEST['confirmorder'] == 'Y' && !strlen($arFields['SECOND_NAME']) && $_REQUEST['ORDER_PROP_1']) {
            $arNames = explode(' ', $_REQUEST['ORDER_PROP_1']);
            if ($arNames[2]) {
                $arFields['SECOND_NAME'] = $arNames[2];
            }
        }

        if (isset($_REQUEST['soa-action']) && $_REQUEST['soa-action'] == 'saveOrderAjax') { // set correct phone in user field
            $arPhoneProp = CSaleOrderProps::GetList(
                ['SORT' => 'ASC'],
                [
                    'PERSON_TYPE_ID' => $_REQUEST['PERSON_TYPE'],
                    'IS_PHONE' => 'Y',
                ],
                false,
                false,
                []
            )->fetch();
            if ($arPhoneProp) {
                if ($_REQUEST['ORDER_PROP_'.$arPhoneProp['ID']]) {
                    $arFields['PERSONAL_PHONE'] = $_REQUEST['ORDER_PROP_'.$arPhoneProp['ID']];
                }
            }
        }

        if (strlen($arFields['EMAIL'])) {
            if ($sChangeLogin != 'N') {
                $bEmailAlredyExists = false;
                $bMultipleLogin = false;

                if (Option::get('main', 'new_user_email_uniq_check', 'N') === 'Y' && $arFields['EMAIL']) {
                    $bEmailAlredyExists = CUser::GetList(
                        $by = 'ID',
                        $order = 'ASC',
                        ['=EMAIL' => $arFields['EMAIL'], '!ID' => $arFields['ID']]
                    )->SelectedRowsCount() > 0;

                    if (!$bEmailAlredyExists) {
                        $bEmailAlredyExists = CUser::GetList(
                            $by = 'ID',
                            $order = 'ASC',
                            ['LOGIN_EQUAL' => $arFields['EMAIL'], '!ID' => $arFields['ID']]
                        )->SelectedRowsCount() > 0;
                    }
                } else {
                    $bMultipleLogin = CUser::GetList(
                        $by = 'ID',
                        $order = 'ASC',
                        ['LOGIN_EQUAL' => $arFields['EMAIL'], '!ID' => $arFields['ID']]
                    )->SelectedRowsCount() > 0;
                }

                if ($bEmailAlredyExists) {
                    global $APPLICATION;
                    $APPLICATION->throwException(Loc::getMessage('EMAIL_IS_ALREADY_EXISTS', ['#EMAIL#' => $arFields['EMAIL']]));

                    return false;
                } else {
                    // !admin
                    if (!isset($GLOBALS['USER']) || !is_object($GLOBALS['USER'])) {
                        $bTmpUser = true;
                        $GLOBALS['USER'] = new CUser();
                    }

                    $shouldSetLoginAsEmail = !$GLOBALS['USER']->IsAdmin();
                    if ($bAdminSection) {
                        $arUserGroups = !empty($arFields['ID']) ? CUser::GetUserGroup($arFields['ID']) : array_column($arFields['GROUP_ID'], 'GROUP_ID');

                        $isAdmin = in_array(1, $arUserGroups);
                        $shouldSetLoginAsEmail = !$isAdmin;
                    }

                    // Set login if allowed and there is no conflict
                    if ($shouldSetLoginAsEmail && !$bMultipleLogin) {
                        $arFields['LOGIN'] = $arFields['EMAIL'];
                    }
                }
            } else {
                if (!$arFields['LOGIN'] || $arFields['LOGIN'] == 1) {
                    $newLogin = $arFields['EMAIL'];
                    $pos = strpos($newLogin, '@');
                    if ($pos !== false) {
                        $newLogin = substr($newLogin, 0, $pos);
                    }

                    if (strlen($newLogin) > 47) {
                        $newLogin = substr($newLogin, 0, 47);
                    }

                    if (strlen($newLogin) < 3) {
                        $newLogin .= '_';
                    }

                    if (strlen($newLogin) < 3) {
                        $newLogin .= '_';
                    }
                    $arFields['LOGIN'] = $newLogin;
                }
            }
        }

        if ($bTmpUser) {
            unset($GLOBALS['USER']);
        }

        return $arFields;
    }

    public static function InsertCounters(&$html)
    {
    }

    public static function clearBasketCacheHandler($orderID, $arFields, $arParams = [])
    {
        Cache::ClearCacheByTag('sale_basket');
        unset($_SESSION['ASPRO_BASKET_COUNTERS']);
        if (isset($arFields) && $arFields) {
            if (isset($arFields['ID']) && $arFields['ID']) {
                Bitrix\Main\Loader::includeModule('sale');
                global $USER;
                $USER_ID = ($USER_ID = $USER->GetID()) ? $USER_ID : 0;
                $arUser = $arUser = Cache::CUser_GetList(['SORT' => 'ASC', 'CACHE' => ['MULTI' => 'N', 'TAG' => Cache::GetUserCacheTag($USER_ID)]], ['ID' => $USER_ID], ['FIELDS' => ['ID', 'PERSONAL_PHONE']]);
                if (!$arUser['PERSONAL_PHONE']) {
                    $rsOrder = CSaleOrderPropsValue::GetList([], ['ORDER_ID' => $arFields['ID']]);
                    $arOrderProps = [];
                    while ($item = $rsOrder->Fetch()) {
                        $arOrderProps[$item['CODE']] = $item;
                    }
                    if (isset($arOrderProps['PHONE']) && $arOrderProps['PHONE'] && (isset($arOrderProps['PHONE']['VALUE']) && $arOrderProps['PHONE']['VALUE'])) {
                        $user = new CUser();
                        $fields = [
                            'PERSONAL_PHONE' => $arOrderProps['PHONE']['VALUE'],
                        ];
                        $user->Update($arUser['ID'], $fields);
                    }
                }
            }
        }
    }

    public static function DoIBlockAfterSave($arg1, $arg2 = false)
    {
        $ELEMENT_ID = false;
        $IBLOCK_ID = false;
        $OFFERS_IBLOCK_ID = false;
        $OFFERS_PROPERTY_ID = false;
        if (CModule::IncludeModule('currency')) {
            $strDefaultCurrency = CCurrency::GetBaseCurrency();
        }

        // Check for catalog event
        if (is_array($arg2) && $arg2['PRODUCT_ID'] > 0) {
            // Get iblock element
            $rsPriceElement = CIBlockElement::GetList(
                [],
                [
                    'ID' => $arg2['PRODUCT_ID'],
                ],
                false,
                false,
                ['ID', 'IBLOCK_ID']
            );
            if ($arPriceElement = $rsPriceElement->Fetch()) {
                $arCatalog = CCatalog::GetByID($arPriceElement['IBLOCK_ID']);
                if (is_array($arCatalog)) {
                    // Check if it is offers iblock
                    if ($arCatalog['OFFERS'] == 'Y') {
                        // Find product element
                        $rsElement = CIBlockElement::GetProperty(
                            $arPriceElement['IBLOCK_ID'],
                            $arPriceElement['ID'],
                            'sort',
                            'asc',
                            ['ID' => $arCatalog['SKU_PROPERTY_ID']]
                        );
                        $arElement = $rsElement->Fetch();
                        if ($arElement && $arElement['VALUE'] > 0) {
                            $ELEMENT_ID = $arElement['VALUE'];
                            $IBLOCK_ID = $arCatalog['PRODUCT_IBLOCK_ID'];
                            $OFFERS_IBLOCK_ID = $arCatalog['IBLOCK_ID'];
                            $OFFERS_PROPERTY_ID = $arCatalog['SKU_PROPERTY_ID'];
                        }
                    }
                    // or iblock which has offers
                    elseif ($arCatalog['OFFERS_IBLOCK_ID'] > 0) {
                        $ELEMENT_ID = $arPriceElement['ID'];
                        $IBLOCK_ID = $arPriceElement['IBLOCK_ID'];
                        $OFFERS_IBLOCK_ID = $arCatalog['OFFERS_IBLOCK_ID'];
                        $OFFERS_PROPERTY_ID = $arCatalog['OFFERS_PROPERTY_ID'];
                    }
                    // or it's regular catalog
                    else {
                        $ELEMENT_ID = $arPriceElement['ID'];
                        $IBLOCK_ID = $arPriceElement['IBLOCK_ID'];
                        $OFFERS_IBLOCK_ID = false;
                        $OFFERS_PROPERTY_ID = false;
                    }
                }
            }
        }
        // Check for iblock event
        elseif (is_array($arg1) && $arg1['ID'] > 0 && $arg1['IBLOCK_ID'] > 0) {
            $IBLOCK_ID = $arg1['IBLOCK_ID'];

            // Check if iblock has offers
            $arOffers = CIBlockPriceTools::GetOffersIBlock($arg1['IBLOCK_ID']);
            if (is_array($arOffers)) {
                $ELEMENT_ID = $arg1['ID'];
                $OFFERS_IBLOCK_ID = $arOffers['OFFERS_IBLOCK_ID'];
                $OFFERS_PROPERTY_ID = $arOffers['OFFERS_PROPERTY_ID'];
            } else {
                if (Aspro\Max\SearchQuery::isLandingSearchIblock($IBLOCK_ID)) {
                    $arLandingSearchMetaHash =
                    $arLandingSearchMetaData =
                    $arLandingSearchQuery = [];
                    $urlCondition = $queryReplacement = $queryExample = '';

                    $dbRes = CIBlockElement::GetProperty(
                        $IBLOCK_ID,
                        $arg1['ID'],
                        ['id' => 'asc'],
                        ['CODE' => 'QUERY']
                    );
                    while ($arSeoSearchElementQuery = $dbRes->Fetch()) {
                        if (strlen($query = trim($arSeoSearchElementQuery['VALUE']))) {
                            list($query, $hash, $arData) = Aspro\Max\SearchQuery::getSentenceMeta($query);
                            $arLandingSearchQuery[] = $query;
                            $arLandingSearchMetaHash[] = $hash;
                            $arLandingSearchMetaData[] = serialize($arData);
                        }
                    }

                    // get value of property QUERY_REPLACEMENT
                    $dbRes = CIBlockElement::GetProperty(
                        $IBLOCK_ID,
                        $arg1['ID'],
                        ['id' => 'asc'],
                        ['CODE' => 'QUERY_REPLACEMENT']
                    );
                    $arPropertyQueryReplacement = $dbRes->Fetch();
                    $queryReplacement = trim($arPropertyQueryReplacement['VALUE']);

                    if ($arLandingSearchQuery) {
                        if (strlen($queryExample = Aspro\Max\SearchQuery::getSentenceExampleQuery(reset($arLandingSearchQuery), LANG))) {
                            // check value of property URL_CONDITION
                            $dbRes = CIBlockElement::GetProperty(
                                $IBLOCK_ID,
                                $arg1['ID'],
                                ['id' => 'asc'],
                                ['CODE' => 'URL_CONDITION']
                            );
                            if ($arPropertyUrlCondition = $dbRes->Fetch()) {
                                $urlCondition = ltrim(trim($arPropertyUrlCondition['VALUE']), '/');
                            }
                        }
                    }

                    $arUpdateFields = [
                        'QUERY' => $arLandingSearchQuery,
                        'META_HASH' => $arLandingSearchMetaHash,
                        'META_DATA' => $arLandingSearchMetaData,
                        'URL_CONDITION' => strlen($urlCondition) ? '/'.$urlCondition : '',
                        'QUERY_REPLACEMENT' => $queryReplacement,
                    ];

                    // clear multiple properties values for correct values order
                    CIBlockElement::SetPropertyValuesEx(
                        $arg1['ID'],
                        $IBLOCK_ID,
                        [
                            'QUERY' => false,
                            'META_HASH' => false,
                            'META_DATA' => false,
                        ]
                    );

                    // update values
                    CIBlockElement::SetPropertyValuesEx(
                        $arg1['ID'],
                        $IBLOCK_ID,
                        $arUpdateFields
                    );

                    if (Cache::$arIBlocksInfo[$IBLOCK_ID]) {
                        $arSitesLids = Cache::$arIBlocksInfo[$IBLOCK_ID]['LID'];

                        // search and remove urlrewrite item
                        $searchRule = 'ls='.$arg1['ID'];
                        $searchCondition = strlen($urlCondition) ? '#^/'.$urlCondition.'[^/]*$#' : false;
                        foreach ($arSitesLids as $siteId) {
                            if ($arUrlRewrites = Bitrix\Main\UrlRewriter::getList($siteId, ['ID' => ''])) {
                                foreach ($arUrlRewrites as $arUrlRewrite) {
                                    if ($arUrlRewrite['RULE'] && strpos($arUrlRewrite['RULE'], $searchRule) !== false) {
                                        Bitrix\Main\UrlRewriter::delete($siteId, ['CONDITION' => $arUrlRewrite['CONDITION']]);
                                    }

                                    if ($searchCondition && $arUrlRewrite['CONDITION'] === $searchCondition) {
                                        Bitrix\Main\UrlRewriter::delete($siteId, ['CONDITION' => $arUrlRewrite['CONDITION']]);
                                    }
                                }
                            }
                        }

                        // add new urlrewrite condition item
                        if (strlen($urlCondition)) {
                            $cntActive = CIBlockElement::GetList(
                                [],
                                [
                                    'ID' => $arg1['ID'],
                                    'ACTIVE' => 'Y',
                                ],
                                []
                            );

                            if ($cntActive) {
                                static $arCacheSites;
                                if (!isset($arCacheSites)) {
                                    $arCacheSites = [];
                                }

                                foreach ($arSitesLids as $siteId) {
                                    $arSite = $arCacheSites[$siteId];
                                    if (!isset($arSite)) {
                                        $dbSite = CSite::GetByID($siteId);
                                        $arCacheSites[$siteId] = $arSite = $dbSite->Fetch();
                                    }

                                    if ($arSite) {
                                        $siteDir = $arSite['DIR'];

                                        // catalog page
                                        $catalogPage = trim(Solution::GetFrontParametrValue('CATALOG_PAGE_URL', $siteId, false));
                                        if (!strlen($catalogPage)) {
                                            // catalog iblock id
                                            if (defined('URLREWRITE_SEARCH_LANDING_CONDITION_CATALOG_IBLOCK_ID_'.$siteId)) {
                                                $catalogIblockId = constant('URLREWRITE_SEARCH_LANDING_CONDITION_CATALOG_IBLOCK_ID_'.$siteId);
                                            }
                                            if (!$catalogIblockId) {
                                                $catalogIblockId = Option::get(
                                                    self::moduleID,
                                                    'CATALOG_IBLOCK_ID',
                                                    Cache::$arIBlocks[$siteId]['aspro_max_catalog']['aspro_max_catalog'][0],
                                                    $siteId
                                                );
                                            }
                                            if ($catalogIblockId && isset(Cache::$arIBlocksInfo[$catalogIblockId])) {
                                                $catalogPage = Cache::$arIBlocksInfo[$catalogIblockId]['LIST_PAGE_URL'];
                                            }
                                        }

                                        // catalog page script
                                        $catalogScriptConst = 'ASPRO_CATALOG_SCRIPT_'.$siteId;
                                        $catalogScript = defined($catalogScriptConst) && strlen(constant($catalogScriptConst)) ? constant($catalogScriptConst) : 'index.php';

                                        // catalog full url
                                        $pathFile = str_replace(['#SITE_DIR#', $catalogScript], [$siteDir, ''], $catalogPage).$catalogScript;
                                        Bitrix\Main\UrlRewriter::add(
                                            $siteId,
                                            [
                                                'CONDITION' => '#^/'.$urlCondition.'[^/]*$#',
                                                'ID' => '',
                                                'PATH' => $pathFile,
                                                'RULE' => 'ls='.$arg1['ID'],
                                            ]
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($ELEMENT_ID) {
            static $arPropCache = [];
            static $arPropArray = [];

            if (!array_key_exists($IBLOCK_ID, $arPropCache)) {
                // Check for MINIMAL_PRICE property
                $rsProperty = CIBlockProperty::GetByID('MINIMUM_PRICE', $IBLOCK_ID);
                $arProperty = $rsProperty->Fetch();
                if ($arProperty) {
                    $arPropCache[$IBLOCK_ID] = $arProperty['ID'];
                    $arPropArray['MINIMUM_PRICE'] = $arProperty['ID'];
                } else {
                    $arPropCache[$IBLOCK_ID] = false;
                }
                $rsProperty = CIBlockProperty::GetByID('IN_STOCK', $IBLOCK_ID);
                $arProperty = $rsProperty->Fetch();
                if ($arProperty) {
                    $arPropCache[$IBLOCK_ID] = $arProperty['ID'];
                    $arPropArray['IN_STOCK'] = $arProperty['ID'];
                } else {
                    if (!$arPropCache[$IBLOCK_ID]) {
                        $arPropCache[$IBLOCK_ID] = false;
                    }
                }
            }

            if ($arPropCache[$IBLOCK_ID]) {
                // Compose elements filter
                if ($OFFERS_IBLOCK_ID) {
                    $rsOffers = CIBlockElement::GetList(
                        [],
                        [
                            'IBLOCK_ID' => $OFFERS_IBLOCK_ID,
                            'PROPERTY_'.$OFFERS_PROPERTY_ID => $ELEMENT_ID,
                            'ACTIVE' => 'Y',
                        ],
                        false,
                        false,
                        ['ID']
                    );
                    while ($arOffer = $rsOffers->Fetch()) {
                        $arProductID[] = $arOffer['ID'];
                    }

                    if (!is_array($arProductID)) {
                        $arProductID = [$ELEMENT_ID];
                    }
                } else {
                    $arProductID = [$ELEMENT_ID];
                }

                if ($arPropArray['MINIMUM_PRICE']) {
                    $minPrice = false;
                    $maxPrice = false;
                    // Get prices
                    $rsPrices = CPrice::GetList(
                        [],
                        [
                            'PRODUCT_ID' => $arProductID,
                        ]
                    );
                    while ($arPrice = $rsPrices->Fetch()) {
                        if (CModule::IncludeModule('currency') && $strDefaultCurrency != $arPrice['CURRENCY']) {
                            $arPrice['PRICE'] = CCurrencyRates::ConvertCurrency($arPrice['PRICE'], $arPrice['CURRENCY'], $strDefaultCurrency);
                        }

                        $PRICE = $arPrice['PRICE'];

                        if ($minPrice === false || $minPrice > $PRICE) {
                            $minPrice = $PRICE;
                        }

                        if ($maxPrice === false || $maxPrice < $PRICE) {
                            $maxPrice = $PRICE;
                        }
                    }

                    // Save found minimal price into property
                    if ($minPrice !== false) {
                        CIBlockElement::SetPropertyValuesEx(
                            $ELEMENT_ID,
                            $IBLOCK_ID,
                            [
                                'MINIMUM_PRICE' => $minPrice,
                                'MAXIMUM_PRICE' => $maxPrice,
                            ]
                        );
                    }
                }
                if ($arPropArray['IN_STOCK']) {
                    $quantity = 0;
                    $rsQuantity = CCatalogProduct::GetList(
                        ['QUANTITY' => 'DESC'],
                        ['ID' => $arProductID],
                        false,
                        false,
                        ['QUANTITY']
                    );
                    while ($arQuantity = $rsQuantity->Fetch()) {
                        if ($arQuantity['QUANTITY'] > 0) {
                            $quantity += $arQuantity['QUANTITY'];
                        }
                    }
                    if ($quantity > 0) {
                        $rsPropStock = CIBlockPropertyEnum::GetList(['DEF' => 'DESC', 'SORT' => 'ASC'], ['IBLOCK_ID' => $IBLOCK_ID, 'CODE' => 'IN_STOCK']);
                        if ($arPropStock = $rsPropStock->Fetch()) {
                            $idProp = $arPropStock['ID'];
                        }

                        CIBlockElement::SetPropertyValuesEx(
                            $ELEMENT_ID,
                            $IBLOCK_ID,
                            [
                                'IN_STOCK' => $idProp,
                            ]
                        );
                    } else {
                        CIBlockElement::SetPropertyValuesEx(
                            $ELEMENT_ID,
                            $IBLOCK_ID,
                            [
                                'IN_STOCK' => '',
                            ]
                        );
                    }
                    if (class_exists('\Bitrix\Iblock\PropertyIndex\Manager')) {
                        Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($IBLOCK_ID, $ELEMENT_ID);
                    }
                }
            }
        }
    }

    public static function DoIBlockElementAfterDelete($arFields)
    {
        $IBLOCK_ID = $arFields['IBLOCK_ID'];

        if (Aspro\Max\SearchQuery::isLandingSearchIblock($IBLOCK_ID)) {
            $ID = $arFields['ID'];

            if (Cache::$arIBlocksInfo[$IBLOCK_ID]) {
                $arSitesLids = Cache::$arIBlocksInfo[$IBLOCK_ID]['LID'];

                // search and remove urlrewrite item
                $searchRule = 'ls='.$ID;
                foreach ($arSitesLids as $siteId) {
                    if ($arUrlRewrites = Bitrix\Main\UrlRewriter::getList($siteId, ['ID' => ''])) {
                        foreach ($arUrlRewrites as $arUrlRewrite) {
                            if ($arUrlRewrite['RULE'] && strpos($arUrlRewrite['RULE'], $searchRule) !== false) {
                                Bitrix\Main\UrlRewriter::delete($siteId, ['CONDITION' => $arUrlRewrite['CONDITION']]);
                            }
                        }
                    }
                }
            }
        }
    }

    protected static $handlerDisallow = 0;

    public static function disableHandler()
    {
        --self::$handlerDisallow;
    }

    public static function enableHandler()
    {
        ++self::$handlerDisallow;
    }

    public static function isEnabledHandler()
    {
        return self::$handlerDisallow >= 0;
    }

    public static function setStoreProductHandler($ID, $arFields)
    {
        static $stores_quantity_product, $updateFromCatalog;
        $arProduct = CCatalogStoreProduct::GetList([], ['ID' => $ID], false, false, ['PRODUCT_ID'])->Fetch();
        if ($arProduct['PRODUCT_ID'] && Option::get(self::moduleID, 'EVENT_SYNC', 'N') == 'Y') {
            if (isset($arFields['AMOUNT']) && $arFields['AMOUNT']) {
                $stores_quantity_product += $arFields['AMOUNT'];
            }

            if ($updateFromCatalog !== null) {
                /* set flag */
                self::disableHandler();
            }

            CCatalogProduct::Update($arProduct['PRODUCT_ID'], ['QUANTITY' => $stores_quantity_product]);

            if ($updateFromCatalog !== null) {
                /* unset flag */
                self::enableHandler();
            }
        }
    }

    public static function setStockProduct($event)
    {
        $ID = $event->getParameter('id');

        /* check flag */
        if (!self::isEnabledHandler()) {
            return;
        }

        /* set flag */
        self::disableHandler();

        // Get iblock element
        $rsPriceElement = CIBlockElement::GetList(
            [],
            [
                'ID' => $ID,
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID']
        );

        if ($arPriceElement = $rsPriceElement->Fetch()) {
            $arCatalog = CCatalog::GetByID($arPriceElement['IBLOCK_ID']);
            if (is_array($arCatalog)) {
                // Check if it is offers iblock
                if ($arCatalog['OFFERS'] == 'Y') {
                    // Find product element
                    $rsElement = CIBlockElement::GetProperty(
                        $arPriceElement['IBLOCK_ID'],
                        $arPriceElement['ID'],
                        'sort',
                        'asc',
                        ['ID' => $arCatalog['SKU_PROPERTY_ID']]
                    );
                    $arElement = $rsElement->Fetch();
                    if ($arElement && $arElement['VALUE'] > 0) {
                        $ELEMENT_ID = $arElement['VALUE'];
                        $IBLOCK_ID = $arCatalog['PRODUCT_IBLOCK_ID'];
                        $OFFERS_IBLOCK_ID = $arCatalog['IBLOCK_ID'];
                        $OFFERS_PROPERTY_ID = $arCatalog['SKU_PROPERTY_ID'];
                    }
                }
                // or iblock which has offers
                elseif ($arCatalog['OFFERS_IBLOCK_ID'] > 0) {
                    $ELEMENT_ID = $arPriceElement['ID'];
                    $IBLOCK_ID = $arPriceElement['IBLOCK_ID'];
                    $OFFERS_IBLOCK_ID = $arCatalog['OFFERS_IBLOCK_ID'];
                    $OFFERS_PROPERTY_ID = $arCatalog['OFFERS_PROPERTY_ID'];
                }
                // or it's regular catalog
                else {
                    $ELEMENT_ID = $arPriceElement['ID'];
                    $IBLOCK_ID = $arPriceElement['IBLOCK_ID'];
                    $OFFERS_IBLOCK_ID = false;
                    $OFFERS_PROPERTY_ID = false;
                }
            }
        }
        if ($ELEMENT_ID) {
            static $arPropCache = [];
            static $arPropArray = [];

            if (!array_key_exists($IBLOCK_ID, $arPropCache)) {
                // Check for IN_STOCK property
                $rsProperty = CIBlockProperty::GetByID('IN_STOCK', $IBLOCK_ID);
                $arProperty = $rsProperty->Fetch();
                if ($arProperty) {
                    $arPropCache[$IBLOCK_ID] = $arProperty['ID'];
                    $arPropArray['IN_STOCK'] = $arProperty['ID'];
                } else {
                    if (!$arPropCache[$IBLOCK_ID]) {
                        $arPropCache[$IBLOCK_ID] = false;
                    }
                }
            }
            if ($arPropCache[$IBLOCK_ID]) {
                // Compose elements filter
                $arProductID = [];
                if ($OFFERS_IBLOCK_ID) {
                    $rsOffers = CIBlockElement::GetList(
                        [],
                        [
                            'IBLOCK_ID' => $OFFERS_IBLOCK_ID,
                            'PROPERTY_'.$OFFERS_PROPERTY_ID => $ELEMENT_ID,
                            'ACTIVE' => 'Y',
                        ],
                        false,
                        false,
                        ['ID']
                    );
                    while ($arOffer = $rsOffers->Fetch()) {
                        $arProductID[] = $arOffer['ID'];
                    }

                    if (!$arProductID) {
                        $arProductID = [$ELEMENT_ID];
                    }
                } else {
                    $arProductID = [$ELEMENT_ID];
                }

                if ($arPropArray['IN_STOCK']) {
                    /* sync quantity product by stores start */
                    if ($arProductID /* && \Bitrix\Main\Config\Option::get('catalog', 'default_use_store_control', 'N') == 'N' */ && ($_SESSION['CUSTOM_UPDATE'] == 'Y' || Option::get(self::moduleID, 'EVENT_SYNC', 'N') == 'Y')) {
                        static $bStores;
                        if (class_exists('CCatalogStore')) {
                            if (!$bStores) {
                                $dbRes = CCatalogStore::GetList([], [], false, false, []);
                                if ($c = $dbRes->SelectedRowsCount()) {
                                    $bStores = true;
                                }
                            }
                        }
                        if ($bStores) {
                            static $updateFromCatalog;
                            $updateFromCatalog = true;

                            foreach ($arProductID as $id) {
                                $quantity_stores = 0;
                                $rsStore = CCatalogStore::GetList([], ['PRODUCT_ID' => $id], false, false, ['ID', 'PRODUCT_AMOUNT']);
                                while ($arStore = $rsStore->Fetch()) {
                                    $quantity_stores += $arStore['PRODUCT_AMOUNT'];
                                }
                                CCatalogProduct::Update($id, ['QUANTITY' => $quantity_stores]);
                            }
                        }
                    }
                    /* sync quantity product by stores end */

                    $quantity = 0;
                    $rsQuantity = CCatalogProduct::GetList(
                        ['QUANTITY' => 'DESC'],
                        ['ID' => $arProductID],
                        false,
                        false,
                        ['QUANTITY']
                    );
                    while ($arQuantity = $rsQuantity->Fetch()) {
                        if ($arQuantity['QUANTITY'] > 0) {
                            $quantity += $arQuantity['QUANTITY'];
                        }
                    }
                    if ($quantity > 0) {
                        $rsPropStock = CIBlockPropertyEnum::GetList(['DEF' => 'DESC', 'SORT' => 'ASC'], ['IBLOCK_ID' => $IBLOCK_ID, 'CODE' => 'IN_STOCK']);
                        if ($arPropStock = $rsPropStock->Fetch()) {
                            $idProp = $arPropStock['ID'];
                        }

                        CIBlockElement::SetPropertyValuesEx(
                            $ELEMENT_ID,
                            $IBLOCK_ID,
                            [
                                'IN_STOCK' => $idProp,
                            ]
                        );
                    } else {
                        CIBlockElement::SetPropertyValuesEx(
                            $ELEMENT_ID,
                            $IBLOCK_ID,
                            [
                                'IN_STOCK' => '',
                            ]
                        );
                    }
                    if (class_exists('\Bitrix\Iblock\PropertyIndex\Manager')) {
                        Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($IBLOCK_ID, $ELEMENT_ID);
                    }
                }
            }
        }

        /* unset flag */
        self::enableHandler();
    }

    public static function CurrencyFormatHandler($price, $currency)
    {
        if (!defined('ADMIN_SECTION') && !CSite::inDir(SITE_DIR.'personal/orders')) {
            $arCurFormat = CCurrencyLang::GetFormatDescription($currency);

            $intDecimals = $arCurFormat['DECIMALS'];
            if (CCurrencyLang::isAllowUseHideZero() && $arCurFormat['HIDE_ZERO'] == 'Y') {
                if (round($price, $arCurFormat['DECIMALS']) == round($price, 0)) {
                    $intDecimals = 0;
                }
            }
            $price = number_format($price, $intDecimals, $arCurFormat['DEC_POINT'], $arCurFormat['THOUSANDS_SEP']);
            if ($arCurFormat['THOUSANDS_VARIANT'] == CCurrencyLang::SEP_NBSPACE) {
                $price = str_replace(' ', '&nbsp;', $price);
            }
            $arFormatString = explode('#', $arCurFormat['FORMAT_STRING']);
            $arFormatString[1] = '<span class=\'price_currency\'>'.$arFormatString[1].'</span>';
            $arCurFormat['FORMAT_STRING'] = '#'.$arFormatString[1];

            return preg_replace('/(^|[^&])#/', '${1}<span class=\'price_value\'>'.$price.'</span>', $arCurFormat['FORMAT_STRING']);
        }
    }

    public static function OnBeforeChangeFileHandler($path, $content)
    {
        return true;
    }

    public static function OnChangeFileHandler($path, $site)
    {
        return true;
    }

    public static function OnAfterUpdateSitemapHandler()
    {
        $arId = func_get_arg(0);
        $arFields = func_get_arg(2);

        if (
            $arId
            && $arFields
            && is_array($arId)
            && is_array($arFields)
            && ($SITEMAP_ID = intval($arId['ID'])) > 0
            && $arFields['DATE_RUN']
        ) {
            $dbSitemap = Bitrix\Seo\SitemapTable::getById($SITEMAP_ID);
            if ($arSitemap = $dbSitemap->fetch()) {
                $arSitemap['SETTINGS'] = Solution::unserialize($arSitemap['SETTINGS']);
            }

            $dbSitemap = Bitrix\Seo\SitemapTable::getById($SITEMAP_ID);
            if ($arSitemap = $dbSitemap->fetch()) {
                $arSitemap['SETTINGS'] = Solution::unserialize($arSitemap['SETTINGS']);

                $arLandingSearchIblocksIds = [];
                if (Cache::$arIBlocks && Cache::$arIBlocks[$arSitemap['SITE_ID']] && Cache::$arIBlocks[$arSitemap['SITE_ID']]['aspro_max_catalog']['aspro_max_search']) {
                    $arLandingSearchIblocksIds = Cache::$arIBlocks[$arSitemap['SITE_ID']]['aspro_max_catalog']['aspro_max_search'];
                }

                if ($arLandingSearchIblocksIds && $arSitemap['SETTINGS']['IBLOCK_ACTIVE'] && $arSitemap['SETTINGS']['IBLOCK_ELEMENT']) {
                    $siteDocRoot = Bitrix\Main\SiteTable::getDocumentRoot($arSitemap['SITE_ID']);
                    $regionSitemapPath = $siteDocRoot.'/aspro_regions/sitemap';
                    if (!is_dir($regionSitemapPath)) {
                        @mkdir($regionSitemapPath, BX_DIR_PERMISSIONS, true);
                    }

                    $bUseRegionality = Option::get(self::moduleID, 'USE_REGIONALITY', 'N', $arSitemap['SITE_ID']) === 'Y';
                    $bUseOneDomain = Option::get(self::moduleID, 'REGIONALITY_TYPE', 'ONE_DOMAIN', $arSitemap['SITE_ID']) !== 'SUBDOMAIN';

                    $arRegions = [];
                    $dbRes = CIBlockElement::GetList(
                        [],
                        [
                            'ACTIVE' => 'Y',
                            'LID' => $arSitemap['SITE_ID'],
                            'IBLOCK_CODE' => 'aspro_max_regions',
                        ],
                        false,
                        false,
                        [
                            'ID',
                            'IBLOCK_ID',
                            'PROPERTY_MAIN_DOMAIN',
                        ]
                    );
                    while ($arRegion = $dbRes->Fetch()) {
                        $arRegions[] = $arRegion;
                    }

                    foreach ($arSitemap['SETTINGS']['IBLOCK_ELEMENT'] as $iblockId => $val) {
                        if (in_array($iblockId, $arLandingSearchIblocksIds) && $val === 'Y' && $arSitemap['SETTINGS']['IBLOCK_ACTIVE'][$iblockId] === 'Y') {
                            $arLandingSearchIBlock = Cache::$arIBlocksInfo[$iblockId];
                            if ($fileName = str_replace(
                                ['#IBLOCK_ID#', '#IBLOCK_CODE#', '#IBLOCK_XML_ID#'],
                                [$iblockId, $arLandingSearchIBlock['CODE'], $arLandingSearchIBlock['XML_ID']],
                                $arSitemap['SETTINGS']['FILENAME_IBLOCK']
                            )) {
                                $fileName = $siteDocRoot.'/'.trim($fileName, '/');
                                if (file_exists($fileName)) {
                                    $content = @file_get_contents($fileName);

                                    // get landings items
                                    $arLandings = $arLandingsIDs = [];
                                    if ($content && preg_match_all('/<url>\s*<loc>(([^<]*)=(\d*))<\/loc>\s*<lastmod>([^<]*)<\/lastmod>\s*<\/url>/i', $content, $arLandingsMatches)) {
                                        $arLandingsIDs = $arLandingsMatches[3];
                                        $arLandings = Cache::CIBLockElement_GetList(
                                            [
                                                'ID' => 'ASC',
                                                'CACHE' => [
                                                    'MULTI' => 'N',
                                                    'TAG' => Cache::GetIBlockCacheTag($iblockId),
                                                    'GROUP' => ['ID'],
                                                ],
                                            ],
                                            [
                                                'ID' => $arLandingsIDs,
                                                'ACTIVE' => 'Y',
                                            ],
                                            false,
                                            false,
                                            [
                                                'ID',
                                                'IBLOCK_ID',
                                                'NAME',
                                                'DETAIL_PAGE_URL',
                                                'PROPERTY_IS_INDEX',
                                                'PROPERTY_URL_CONDITION',
                                                'PROPERTY_REDIRECT_URL',
                                                'PROPERTY_QUERY',
                                                'PROPERTY_LINK_REGION',
                                            ]
                                        );

                                        // get enum id of property IS_INDEX with XML_ID = Y
                                        $arEnumID_IS_INDEX = CIBlockPropertyEnum::GetList(
                                            [],
                                            [
                                                'IBLOCK_ID' => $iblockId,
                                                'CODE' => 'IS_INDEX',
                                                'XML_ID' => 'Y',
                                            ]
                                        )->GetNext();

                                        $arRegionFiles = [$fileName];
                                        if ($bUseRegionality && !$bUseOneDomain) {
                                            foreach ($arRegions as $arRegion) {
                                                $arRegionFiles[$arRegion['ID']] = $regionSitemapPath.'/'.basename($fileName, '.xml').'_'.$arRegion['PROPERTY_MAIN_DOMAIN_VALUE'].'.xml';
                                            }
                                        }
                                    }

                                    foreach ($arRegionFiles as $regionId => $fileNameTo) {
                                        $newContent = $content;

                                        foreach ($arLandingsMatches[0] as $i => $match) {
                                            $LID = $arLandingsMatches[3][$i];
                                            if ($arLandings[$LID]) {
                                                $arLandings[$LID]['PROPERTY_LINK_REGION_VALUE'] = (array) $arLandings[$LID]['PROPERTY_LINK_REGION_VALUE'];
                                                if (!$arEnumID_IS_INDEX || ($arEnumID_IS_INDEX && $arLandings[$LID]['PROPERTY_IS_INDEX_ENUM_ID'] == $arEnumID_IS_INDEX['ID'])) {
                                                    if (
                                                        !$bUseRegionality
                                                        || !$regionId
                                                        || !$arLandings[$LID]['PROPERTY_LINK_REGION_VALUE']
                                                        || (
                                                            !$bUseOneDomain && in_array($regionId, $arLandings[$LID]['PROPERTY_LINK_REGION_VALUE'])
                                                        )
                                                    ) {
                                                        $catalogDir = preg_replace('/[\?].*/', '', $arLandings[$LID]['DETAIL_PAGE_URL']);
                                                        $url = Aspro\Max\SearchQuery::getLandingUrl(
                                                            $catalogDir,
                                                            $arLandings[$LID]['PROPERTY_URL_CONDITION_VALUE'],
                                                            $arLandings[$LID]['PROPERTY_REDIRECT_URL_VALUE'],
                                                            $arLandings[$LID]['PROPERTY_QUERY_VALUE'],
                                                            $arLandings[$LID]['ID']
                                                        );

                                                        $url = str_replace('&', '&amp;', $url);

                                                        if (strpos($url, 'http') === false) {
                                                            $url = (CMain::isHTTPS() ? 'https://' : 'http://').str_replace('//', '/', $arSitemap['SETTINGS']['DOMAIN'].$url);
                                                        }
                                                        $newContent = str_replace($arLandingsMatches[1][$i], $url, $newContent);

                                                        continue;
                                                    }
                                                }
                                            }

                                            // delete if not IS_INDEX
                                            $newContent = str_replace($match, '', $newContent);
                                        }
                                        @file_put_contents($fileNameTo, $newContent);
                                    }
                                    unset($newContent);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function replaceMarks($text, $arMarks)
    {
        global $arRegion;
        foreach ($arMarks as $mark => $field) {
            if (strpos($text, $mark) !== false) {
                if ($arRegion) {
                    if (is_array($arRegion[$field])) {
                        $text = str_replace([$mark, str_replace('#REGION_TAG_', '#REGION_STRIP_TAG_', $mark)], [$arRegion[$field]['TEXT'] ?? '', strip_tags($arRegion[$field]['TEXT'] ?? '')], $text);
                    } else {
                        $text = str_replace([$mark, str_replace('#REGION_TAG_', '#REGION_STRIP_TAG_', $mark)], [$arRegion[$field], strip_tags($arRegion[$field])], $text);
                    }
                } else {
                    $text = str_replace([$mark, str_replace('#REGION_TAG_', '#REGION_STRIP_TAG_', $mark)], '', $text);
                }
            }
        }

        return $text;
    }

    public static function onAfterAjaxResponseHandler()
    {
        if (defined('ADMIN_SECTION')) {
            return;
        }

        if (!defined('_USE_AJAX_HANDLER_MAX_')) {
            return;
        }

        global $arRegion, $APPLICATION;
        $arRegion = SolutionRegionality::getCurrentRegion();

        Solution::setRegionSeoMarks();

        /* add|remove top banner in section */
        $banner = trim($APPLICATION->GetViewContent('section_bnr_h1_content'));

        if (strlen($banner) > 0) {?>
            <script>
                $(<?= CUtil::PhpToJSObject($banner); ?>).insertAfter($('.top-block-wrapper .page-top > div:first-of-type'))
                $('.wrapper1').addClass($('.js-banner').data('class'));
            </script>
        <?} else {?>
            <script>
                if(window.jQuery){
                    $('.wrapper1').removeClass('has-secion-banner');
                    $('.top-block-wrapper .section-banner-top').remove();
                }
            </script>
        <?}

        ?>
        <script>
            setTimeout(function(){
                if(typeof window.InitStickySideBar === "function"){
                    InitStickySideBar();
                }
            }, 100)
        </script>
        <?php
        // update breadcrumbs
        $APPLICATION->arAdditionalChain = array_map(
            function ($arElement) {
                $arElement['TITLE'] = self::replaceMarks($arElement['TITLE'], SolutionRegionality::$arSeoMarks);

                return $arElement;
            }, $APPLICATION->arAdditionalChain);

        // update h1
        $APPLICATION->sDocTitle = self::replaceMarks($APPLICATION->sDocTitle, SolutionRegionality::$arSeoMarks);

        // update meta title
        $APPLICATION->SetPageProperty('title', self::replaceMarks($APPLICATION->getProperty('title'), SolutionRegionality::$arSeoMarks));
    }

    public static function OnEndBufferContentHandler(&$content)
    {
        $bCompSaleOrderAjaxPost = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['soa-action']);
        if (!defined('ADMIN_SECTION') && !defined('WIZARD_SITE_ID') && !defined('CUSTOM_CONTENT') || $bCompSaleOrderAjaxPost) {
            global $SECTION_BNR_CONTENT, $arRegion, $APPLICATION, $bShowSimple;
            $bIndexBot = Solution::checkIndexBot(); // is indexed yandex/google bot
            Solution::setRegionSeoMarks();

            // if((strpos($APPLICATION->GetCurPage(), 'ajax') === false && strpos($APPLICATION->GetCurPage(), 'bitrix') === false))
            // {
            foreach (SolutionRegionality::$arSeoMarks as $mark => $field) {
                if (strpos($content, $mark) !== false) {
                    if ($arRegion) {
                        if (is_array($arRegion[$field])) {
                            $value = $bCompSaleOrderAjaxPost ? trim(Bitrix\Main\Web\Json::encode($arRegion[$field]['TEXT'] ?? ''), '"') : ($arRegion[$field]['TEXT'] ?? '');
                            $content = str_replace([$mark, str_replace('#REGION_TAG_', '#REGION_STRIP_TAG_', $mark)], [$value, strip_tags($value)], $content);
                        } else {
                            $value = $bCompSaleOrderAjaxPost ? trim(Bitrix\Main\Web\Json::encode($arRegion[$field]), '"') : $arRegion[$field];
                            $content = str_replace([$mark, str_replace('#REGION_TAG_', '#REGION_STRIP_TAG_', $mark)], [$value, strip_tags($value)], $content);
                        }
                    } else {
                        $content = str_replace([$mark, str_replace('#REGION_TAG_', '#REGION_STRIP_TAG_', $mark)], '', $content);
                    }
                }
            }
            // }

            Aspro\Max\SearchQuery::replaceUrls($content);

            // replace canonical|next|prev to <head>
            if (preg_match_all('/<\s*link\s+[^\>]*rel\s*=\s*[\'"](canonical|next|prev)[\'"][^\>]*>/i'.BX_UTF_PCRE_MODIFIER, $content, $arMatches)) {
                $arLinks = array_map(
                    function ($match) {
                        if (preg_match('/href\s*=\s*[\'"]([^\'"]*)[\'"]/i'.BX_UTF_PCRE_MODIFIER, $match, $arMatch)) {
                            return preg_replace('/href\s*=\s*[\'"]([^\'"]*)[\'"]/i'.BX_UTF_PCRE_MODIFIER, 'href="'.preg_replace('/(http[s]*:\/\/|^)([^\/]*[\/]?)(.*)/i'.BX_UTF_PCRE_MODIFIER, (CMain::IsHTTPS() ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].'/${3}', $arMatch[1]).'"', $match);
                        }

                        return $match;
                    },
                    array_values($arMatches[0])
                );

                // ignone dulicate canonical
                $bFindCanonical = false;
                foreach ($arLinks as $keyLink => $link) {
                    if (preg_match('/<\s*link\s+[^\>]*rel\s*=\s*[\'"](canonical)[\'"][^\>]*>/i'.BX_UTF_PCRE_MODIFIER, $link, $arMatchCanonical)) {
                        if ($bFindCanonical) {
                            unset($arLinks[$keyLink]);
                        } else {
                            $bFindCanonical = true;
                        }
                    }
                }

                $links = implode(
                    '',
                    $arLinks
                );

                $content = preg_replace(
                    [
                        '/<\s*link\s+[^\>]*rel\s*=\s*[\'"](canonical|next|prev)[\'"][^\>]*>/i'.BX_UTF_PCRE_MODIFIER,
                        '/<\s*head(\s+[^\>]*|)>/i'.BX_UTF_PCRE_MODIFIER,
                    ],
                    [
                        '',
                        '${0}'.$links,
                    ],
                    $content
                );
            }

            // lazyload
            if (isset($GLOBALS['_USE_LAZY_LOAD_MAX_']) && $GLOBALS['_USE_LAZY_LOAD_MAX_'] && !Solution::checkMask(Option::get('aspro.max', 'LAZY_LOAD_EXCEPTIONS', ''))) {
                if (strpos($_SERVER['REQUEST_URI'], '/bitrix/components/') === false && strpos($_SERVER['REQUEST_URI'], '/bitrix/tools/') === false && strpos($_SERVER['REQUEST_URI'], '/bitrix/admin/') === false) {
                    // add lazyload attribyte for each <img> that does not contain data-src
                    $tmpContent = preg_replace('/<img ((?![^>]*(\bdata-src\b|\bloading\b))[^>]*>)/i'.BX_UTF_PCRE_MODIFIER, '<img data-lazyload ${1}', $content);
                    if (isset($tmpContent) && strpos($_SERVER['REQUEST_URI'], '/bitrix/components/') === false) {
                        $content = $tmpContent;
                        $content = preg_replace('/(<img data-lazyload [^>]*)src=/i'.BX_UTF_PCRE_MODIFIER, '${1}src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src=', $content);
                    }

                    $arTags = [
                        'div',
                        'a',
                        'li',
                        'span',
                        'tr',
                        'td',
                    ];
                    $sTags = implode('|', $arTags);
                    $bgPatterns = [
                        '/<('.$sTags.')((?![^>]*\bdata-bg\b)[^>]*background\-image\:\s*url\s*\()/i'.BX_UTF_PCRE_MODIFIER,
                        '/<('.$sTags.')((?![^>]*\bdata-bg\b)[^>]*background\:.*?url\s*\([^>]*>)/i'.BX_UTF_PCRE_MODIFIER,
                    ];
                    $tmpContent = preg_replace($bgPattern, '${1}background-image:url(data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==)${4} data-bg="${3}"', $content);
                    $tmpContent = preg_replace($bgPatterns, '<${1} data-lazyload ${2}', $content);
                    if (isset($tmpContent)) {
                        $content = $tmpContent;
                        $content = preg_replace('/(<('.$sTags.') data-lazyload [^>]*data-src=(["\']{1})([^\"\']*)["\']{1})([^>]*)/i'.BX_UTF_PCRE_MODIFIER, '${1} data-bg=${3}${4}${3} ${5}', $content);
                    }

                    if (isset($tmpContent)) {
                        $content = preg_replace('/(<\w+ data-lazyload [^>]*?)class=([\'"])(?![^>]*\blazy\b)/is'.BX_UTF_PCRE_MODIFIER, '${1}class=${2}lazy ', $content);
                        $content = preg_replace('/<\w+ data-lazyload (?![^>]*\bclass\s*=\s*[\'\"]\b)(?![^>]*\blazy\b)/is'.BX_UTF_PCRE_MODIFIER, '${0}class="lazy " ', $content);
                    }
                }
            }

            if ($bIndexBot) {
                $pattern = '/<iframe.*?<\/iframe>/is';
                $content = preg_replace($pattern, '', $content);

                $pattern = '/<script.*?<\/script>/is';
                $content = preg_replace($pattern, '', $content);
            }

            if (isset($GLOBALS['_USE_GAPS_REPLACE_MAX_']) && $GLOBALS['_USE_GAPS_REPLACE_MAX_'] && (!defined('IGNORE_EOL_OPT') || IGNORE_EOL_OPT === false)) {
                $pattern = '/\n(\s*?)\n/is';
                $content = preg_replace($pattern, PHP_EOL, $content);
            }

            $content = str_replace('
<!DOCTYPE html>', '<!DOCTYPE html>', $content);

            // replace text/javascript for html5 validation w3c
            $content = str_replace(' type="text/javascript"', '', $content);
            $content = str_replace(' type=\'text/javascript\'', '', $content);
            $content = str_replace(' type="text/css"', '', $content);
            $content = str_replace(' type=\'text/css\'', '', $content);
            $content = str_replace(' charset="utf-8"', '', $content);
            $content = str_replace(' data-charset="utf-8"', ' charset="utf-8"', $content);

            if ($SECTION_BNR_CONTENT) {
                $start = strpos($content, '<!--title_content-->');
                if ($start > 0) {
                    $end = strpos($content, '<!--end-title_content-->');

                    if (($end > 0) && ($end > $start)) {
                        if (defined('BX_UTF') && BX_UTF === true && (!Solution::checkVersionModule('20.100.0', 'main'))) {
                            $content = Solution::utf8_substr_replace($content, '', $start, $end - $start);
                        } else {
                            $content = substr_replace($content, '', $start, $end - $start);
                        }
                    }
                }
                $content = str_replace('body class="', 'body class="with_banners ', $content);
            }

            if ($bShowSimple) {
                $content = str_replace('body class="', 'body class="simple_basket_mode ', $content);
            }

            // replace captcha html stucture
            Captcha::getInstance()->onEndBufferContent($content);
        }
    }

    public static function OnPageStartHandler()
    {
        if (defined('ADMIN_SECTION')) {
            return;
        }

        // decode auth request from utf-8 to site charset, the request was sended with xmlhttprequest
        if (
            (!defined('BX_UTF') || BX_UTF !== true)
            && isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
            && isset($_REQUEST['AUTH_FORM'])
            && $_REQUEST['AUTH_FORM'] != ''
            && isset($_REQUEST['TYPE'])
            && $_REQUEST['TYPE'] === 'AUTH'
        ) {
            $_REQUEST = Bitrix\Main\Text\Encoding::convertEncoding($_REQUEST, 'UTF-8', SITE_CHARSET);
        }

        // current region
        global $arRegion;
        if (!$arRegion) {
            $arRegion = SolutionRegionality::getCurrentRegion();
        }

        Aspro\Max\SearchQuery::onLandingSearchPageStart();

        // add captcha assets & verify response
        Captcha::getInstance()->onPageStart();
    }

    public static function OnSearchGetURL($arFields)
    {
        if ($arFields['MODULE_ID'] === 'iblock') {
            if (($iblockId = intval($arFields['PARAM2'])) > 0) {
                if (($id = intval($arFields['ITEM_ID'])) > 0) {
                    if (strpos($arFields['URL'], '#YEAR#') !== false) {
                        $arElement = Cache::CIBlockElement_GetList(
                            [
                                'CACHE' => [
                                    'TAG' => Cache::GetIBlockCacheTag($iblockId),
                                    'MULTI' => 'N',
                                ],
                            ],
                            ['ID' => $id],
                            false,
                            false,
                            [
                                'ID',
                                'ACTIVE_FROM',
                            ]
                        );

                        if ($arElement['ACTIVE_FROM']) {
                            if ($arDateTime = ParseDateTime($arElement['ACTIVE_FROM'], FORMAT_DATETIME)) {
                                return str_replace('#YEAR#', $arDateTime['YYYY'], $arFields['URL']);
                            }
                        }
                    } elseif (Aspro\Max\SearchQuery::isLandingSearchIblock($iblockId)) {
                        $dbRes = CIBlockElement::GetProperty($iblockId, $id, ['sort' => 'asc'], ['CODE' => 'IS_SEARCH_TITLE']);
                        if ($arValue = $dbRes->Fetch()) {
                            if ($arValue['VALUE_XML_ID'] === Aspro\Max\SearchQuery::IS_SEARCH_TITLE_BY_NAME_XML_ID) {
                                $arElement = Cache::CIBlockElement_GetList(
                                    [
                                        'CACHE' => [
                                            'TAG' => Cache::GetIBlockCacheTag($iblockId),
                                            'MULTI' => 'N',
                                        ],
                                    ],
                                    ['ID' => $id],
                                    false,
                                    false,
                                    [
                                        'ID',
                                        'IBLOCK_ID',
                                        'PROPERTY_URL_CONDITION',
                                        'PROPERTY_REDIRECT_URL',
                                        'PROPERTY_QUERY',
                                    ]
                                );

                                $catalogDir = preg_replace('/[\?].*/', '', $arFields['URL']);

                                return $url = Aspro\Max\SearchQuery::getLandingUrl(
                                    $catalogDir,
                                    $arElement['PROPERTY_URL_CONDITION_VALUE'],
                                    $arElement['PROPERTY_REDIRECT_URL_VALUE'],
                                    $arElement['PROPERTY_QUERY_VALUE'],
                                    $arElement['ID']
                                );
                            }
                        }

                        return false;
                    }
                }
            }
        }

        return $arFields['URL'];
    }

    public static function OnBeforeBasketUpdateHandler($ID, &$arFields)
    {
        global $arRegion;
        if (!defined('ADMIN_SECTION') && $arRegion) {
            // get PRODUCT_ID
            $arFilter = [
                'FUSER_ID' => CSaleBasket::GetBasketUserID(),
                'LID' => SITE_ID,
                'ORDER_ID' => 'NULL',
                'ID' => $ID,
            ];
            $arBasketItem = CSaleBasket::GetList(
                ['ID' => 'ASC'],
                $arFilter,
                false,
                false,
                ['ID', 'PRODUCT_ID', 'QUANTITY', 'LID']
            )->Fetch();

            // get store amount
            if ($arRegion['LIST_STORES'] && reset($arRegion['LIST_STORES']) != 'component') {
                // get catalog PRODUCT
                $arProduct = CCatalogProduct::GetByID($arBasketItem['PRODUCT_ID']);

                // check CAN_BUY quantity
                if ($arProduct['QUANTITY_TRACE'] == 'Y' && $arProduct['CAN_BUY_ZERO'] != 'Y') {
                    $quantity_stores = 0;
                    $arSelect = ['ID', 'PRODUCT_AMOUNT'];
                    $arFilter = [
                        'ID' => $arRegion['LIST_STORES'],
                        'PRODUCT_ID' => $arBasketItem['PRODUCT_ID'],
                    ];
                    $rsStore = CCatalogStore::GetList(
                        [],
                        $arFilter,
                        false,
                        false,
                        $arSelect
                    );
                    while ($arStore = $rsStore->Fetch()) {
                        $quantity_stores += $arStore['PRODUCT_AMOUNT'];
                    }

                    if ($quantity_stores <= 0) {
                        $arFields['CAN_BUY'] = 'N';
                    } elseif ($arFields['QUANTITY']) {
                        if ($arFields['QUANTITY'] > $quantity_stores) {
                            $arFields['QUANTITY'] = $quantity_stores;
                        }
                    } elseif ($arBasketItem['QUANTITY'] > $quantity_stores) {
                        $arFields['QUANTITY'] = $quantity_stores;
                    }
                }
            }
        }
    }

    public static function OnGetOptimalPriceHandler($intProductID, $quantity = 1, $arUserGroups = [], $renewal = 'N', $priceList = [], $siteID = false, $arDiscountCoupons = false)
    {
        $siteID = $siteID ? $siteID : SITE_ID;

        return Aspro\Max\Product\CCatalog::OnGetOptimalPriceRegion($intProductID, $quantity, $arUserGroups, $renewal, $priceList, $siteID, $arDiscountCoupons);
    }

    public static function OnRegionUpdateHandler($arFields)
    {
        $arIBlock = CIBlock::GetList([], ['ID' => $arFields['IBLOCK_ID']])->Fetch();
        if (isset(Cache::$arIBlocks[$arIBlock['LID']]['aspro_max_regionality']['aspro_max_regions'][0]) && Cache::$arIBlocks[$arIBlock['LID']]['aspro_max_regionality']['aspro_max_regions'][0]) {
            $iRegionIBlockID = Cache::$arIBlocks[$arIBlock['LID']]['aspro_max_regionality']['aspro_max_regions'][0];
        } else {
            return;
        }
        if ($iRegionIBlockID == $arFields['IBLOCK_ID']) {
            $arSite = CSite::GetList($by = 'sort', $sort = 'asc', ['ACTIVE' => 'Y', 'ID' => $arIBlock['LID']])->Fetch();
            $arSite['DIR'] = str_replace('//', '/', '/'.$arSite['DIR']);
            if (!strlen($arSite['DOC_ROOT'])) {
                $arSite['DOC_ROOT'] = $_SERVER['DOCUMENT_ROOT'];
            }
            $arSite['DOC_ROOT'] = str_replace('//', '/', $arSite['DOC_ROOT'].'/');
            $siteDir = str_replace('//', '/', $arSite['DOC_ROOT'].$arSite['DIR']);

            $arProperty = CIBlockElement::GetProperty($arFields['IBLOCK_ID'], $arFields['ID'], 'sort', 'asc', ['CODE' => 'MAIN_DOMAIN'])->Fetch();
            $xml_file = (isset($arFields['SITE_MAP']) && $arFields['SITE_MAP'] ? $arFields['SITE_MAP'] : 'sitemap.xml');
            if ($arProperty['VALUE']) {
                if (file_exists($siteDir.'robots.txt')) {
                    CopyDirFiles($siteDir.'robots.txt', $siteDir.'aspro_regions/robots/robots_'.$arProperty['VALUE'].'.txt', true, true);

                    $arFile = file($siteDir.'aspro_regions/robots/robots_'.$arProperty['VALUE'].'.txt');
                    foreach ($arFile as $key => $str) {
                        if (strpos($str, 'Host') !== false) {
                            $arFile[$key] = 'Host: '.(CMain::isHTTPS() ? 'https://' : 'http://').$arProperty['VALUE']."\r\n";
                        }
                        if (strpos($str, 'Sitemap') !== false) {
                            $arFile[$key] = 'Sitemap: '.(CMain::isHTTPS() ? 'https://' : 'http://').$arProperty['VALUE'].'/'.$xml_file."\r\n";
                        }
                    }

                    $strr = implode('', (array) $arFile);
                    file_put_contents($siteDir.'aspro_regions/robots/robots_'.$arProperty['VALUE'].'.txt', $strr);
                }
            }
        }
    }

    public static function onBeforeResultAddHandler($WEB_FORM_ID, &$arFields, &$arrVALUES)
    {
        if (!defined('ADMIN_SECTION') && isset($_REQUEST['aspro_max_form_validate'])) {
            global $APPLICATION;
            $arTheme = Solution::GetFrontParametrsValues(SITE_ID);

            if ($arTheme['HIDDEN_CAPTCHA'] == 'Y' && $arrVALUES['nspm'] && !isset($arrVALUES['captcha_sid'])) {
                $APPLICATION->ThrowException(Loc::getMessage('ERROR_FORM_CAPTCHA'));
            }

            if ($arTheme['SHOW_LICENCE'] == 'Y' && ((!isset($arrVALUES['licenses_popup']) || !$arrVALUES['licenses_popup']) && (!isset($arrVALUES['licenses_inline']) || !$arrVALUES['licenses_inline']))) {
                $APPLICATION->ThrowException(Loc::getMessage('ERROR_FORM_LICENSE'));
            }
        }
    }

    public static function OnSaleComponentOrderPropertiesHandler(&$arFields)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (
                isset($arFields['PERSON_TYPE_ID'])
                && isset($arFields['PERSON_TYPE_OLD'])
            ) {
                if (
                    $arFields['PROFILE_CHANGE'] === 'Y'
                    || (
                        $arFields['PERSON_TYPE_ID']
                        && $arFields['PERSON_TYPE_OLD']
                        && (
                            $arFields['PERSON_TYPE_ID'] != $arFields['PERSON_TYPE_OLD']
                        )
                    )
                ) {
                    $arLocationProps = $arZipProps = [];

                    $dbRes = CSaleOrderProps::GetList(
                        ['SORT' => 'ASC'],
                        [
                            'PERSON_TYPE_ID' => [$arFields['PERSON_TYPE_ID'], $arFields['PERSON_TYPE_OLD']],
                            'TYPE' => 'LOCATION',
                            'IS_LOCATION' => 'Y',
                            'ACTIVE' => 'Y',
                        ],
                        false,
                        false,
                        [
                            'ID',
                            'PERSON_TYPE_ID',
                        ]
                    );
                    while ($arLocationProp = $dbRes->Fetch()) {
                        $arLocationProps[$arLocationProp['PERSON_TYPE_ID']] = $arLocationProp['ID'];
                    }

                    if ($arLocationProps) {
                        $arFields['ORDER_PROP'][$arLocationProps[$arFields['PERSON_TYPE_ID']]] = $_POST['order']['ORDER_PROP_'.$arLocationProps[$arFields['PERSON_TYPE_OLD']]];
                    }

                    $dbRes = CSaleOrderProps::GetList(
                        ['SORT' => 'ASC'],
                        [
                            'PERSON_TYPE_ID' => [$arFields['PERSON_TYPE_ID'], $arFields['PERSON_TYPE_OLD']],
                            'CODE' => 'ZIP',
                            'ACTIVE' => 'Y',
                        ],
                        false,
                        false,
                        [
                            'ID',
                            'PERSON_TYPE_ID',
                        ]
                    );
                    while ($arZipProp = $dbRes->Fetch()) {
                        $arZipProps[$arZipProp['PERSON_TYPE_ID']] = $arZipProp['ID'];
                    }

                    if ($arZipProps) {
                        $arFields['ORDER_PROP'][$arZipProps[$arFields['PERSON_TYPE_ID']]] = $_POST['order']['ORDER_PROP_'.$arZipProps[$arFields['PERSON_TYPE_OLD']]];
                    }
                }
            }
        } else {
            if (SolutionRegionality::checkUseRegionality()) {
                // seleted location in current region or main location in current region or real location by ip
                $arLocation = SolutionRegionality::getCurrentLocation() ?: SolutionRegionality::getLocationByIP(SolutionRegionality::getIP());
                if ($arLocation) {
                    $locationId = $arLocation['ID'];

                    // set location & ZIP
                    // from current region
                    // or from real region that defined by ip
                    if ($locationId) {
                        $res = OrderPropsTable::getList([
                            'order' => ['SORT' => 'ASC'],
                            'filter' => [
                                'PERSON_TYPE_ID' => $arFields['PERSON_TYPE_ID'],
                                'TYPE' => 'LOCATION',
                                'IS_LOCATION' => 'Y',
                                'ACTIVE' => 'Y',
                            ],
                            'select' => ['ID'],
                        ]);
                        if ($arLocationProp = $res->fetch()) {
                            $arFields['ORDER_PROP'][$arLocationProp['ID']] = CSaleLocation::getLocationCODEbyID($locationId);
                        }

                        $res = OrderPropsTable::getList([
                            'order' => ['SORT' => 'ASC'],
                            'filter' => [
                                'PERSON_TYPE_ID' => $arFields['PERSON_TYPE_ID'],
                                'CODE' => 'ZIP',
                                'ACTIVE' => 'Y',
                            ],
                            'select' => ['ID'],
                        ]);
                        if ($arLocationZipProp = $res->fetch()) {
                            $rsLocactionZip = CSaleLocation::GetLocationZIP($locationId);
                            $arLocationZip = $rsLocactionZip->Fetch();
                            if ($arLocationZip['ZIP']) {
                                $arFields['ORDER_PROP'][$arLocationZipProp['ID']] = $arLocationZip['ZIP'];
                            }
                        }
                    }
                }
            }
        }
    }

    public static function OnCatalogDeliveryComponentInitUserResult(&$arResult, &$arParams, $request)
    {
        if (
            !$arResult['LOCATION']
            || $arResult['LOCATION_SOURCE'] === 'geoIp'
        ) {
            // seleted location in current region or main location in current region or real location by ip
            $arLocation = SolutionRegionality::getCurrentLocation() ?: SolutionRegionality::getLocationByIP(SolutionRegionality::getIP());
            if ($arLocation) {
                $arResult['LOCATION'] = CAsproCatalogDeliveryMax::getLocationByCode($arLocation['CODE'], LANGUAGE_ID);
                $arResult['LOCATION_SOURCE'] = 'regionality';
            }
        }
    }

    public static function OnBeforeSubscriptionAddHandler(&$arFields)
    {
        if (!defined('ADMIN_SECTION')) {
            global $APPLICATION;
            $arTheme = Solution::GetFrontParametrsValues(SITE_ID);
            if ($arTheme['SHOW_LICENCE'] == 'Y' && (isset($_REQUEST['check_condition']) && $_REQUEST['check_condition'] == 'YES') && !isset($_REQUEST['licenses_subscribe'])) {
                $APPLICATION->ThrowException(Loc::getMessage('ERROR_FORM_LICENSE'));

                return false;
            }
        }
    }

    public static function onAfterResultAddHandler($WEB_FORM_ID, $RESULT_ID)
    {
        SolutionFunctions::sendResultToIBlock($WEB_FORM_ID, $RESULT_ID);

        $acloud = CRM\Acloud\Connection::getInstance(SITE_ID);
        if ($acloud->forms_autosend) {
            try {
                CRM\Helper::sendFormResult($WEB_FORM_ID, $RESULT_ID, $acloud);
            } catch (Exception $e) {
            }
        }

        if (CRM\Flowlu\Connection::getInstance(SITE_ID)->forms_autosend) {
            SolutionFunctions::sendLeadCrmFromForm($WEB_FORM_ID, $RESULT_ID, 'FLOWLU', SITE_ID, false, false);
        }

        if (CRM\Amocrm\Connection::getInstance(SITE_ID)->forms_autosend) {
            SolutionFunctions::sendLeadCrmFromForm($WEB_FORM_ID, $RESULT_ID, 'AMO_CRM', SITE_ID, false, false);
        }
    }

    public static function OnBeforeCommentAddHandler(&$arFields)
    {
        $application = Application::getInstance();
        $request = $application->getContext()->getRequest();

        if (isset($request['rating'])) {
            if ($request['rating']) {
                global $USER;

                $arFields['UF_ASPRO_COM_RATING'] = $request['rating'];

                $userID = $USER->GetID();
                if ($userID) {
                    $arFilter = ['USER_ID' => $userID];
                    if (strpos($request['XML_ID'], '%') !== false) {
                        $arFilter['%=BASKET_PRODUCT_XML_ID'] = str_replace('%', '#%', $request['XML_ID']);
                    } else {
                        $arFilter['BASKET_PRODUCT_ID'] = $request['ELEMENT_ID'];
                    }

                    $arFields['UF_ASPRO_COM_APPROVE'] = CSaleOrder::GetList([], $arFilter, false, false)->SelectedRowsCount() > 0;
                }
            } elseif (!$request['parentId']) {
                global $APPLICATION;

                $APPLICATION->throwException(Loc::getMessage('RATING_IS_REQUIRED'));

                return false;
            }
        }

        if (isset($arFields['AUTHOR_NAME'])) {
            $arFields['AUTHOR_NAME'] = strip_tags($arFields['AUTHOR_NAME']);
        }

        if (isset($arFields['POST_TEXT'])) {
            $arFields['POST_TEXT'] = strip_tags($arFields['POST_TEXT'], '<virtues><limitations><comment>');
        }
    }

    public static function OnCommentAddHandler($commentID, &$arFields)
    {
        if ($_FILES['comment_images']) {
            $maxSize = $_SESSION['BLOG_MAX_IMAGE_SIZE'] * 1024 * 1024;

            foreach ($_FILES['comment_images']['name'] as $key => $imgName) {
                if ($maxSize && $_FILES['comment_images']['size'][$key] > $maxSize) {
                    $notAdded[] = $imgName;
                    continue;
                }
                $fileArray = [
                    'name' => $imgName,
                    'size' => $_FILES['comment_images']['size'][$key],
                    'tmp_name' => $_FILES['comment_images']['tmp_name'][$key],
                    'type' => $_FILES['comment_images']['type'][$key],
                    'MODULE_ID' => 'blog',
                ];
                $fileId = CFile::SaveFile($fileArray, '/blog/comment/');
                if ($fileId) {
                    $filesToAttach[$key] = $fileId;
                }
            }

            unset($_FILES['comment_images']);
        }

        if ($filesToAttach) {
            foreach ($filesToAttach as $imageKey => $imageId) {
                CBlogImage::Add([
                    'FILE_ID' => $imageId,
                    'POST_ID' => $arFields['POST_ID'],
                    'BLOG_ID' => $arFields['BLOG_ID'],
                    'COMMENT_ID' => intval($commentID),
                    'IMAGE_SIZE' => $_FILES['comment_images']['size'][$imageKey],
                ]);
            }
        }

        if ($notAdded) {
            $_SESSION['NOT_ADDED_FILES']['ID'] = $commentID;
            $_SESSION['NOT_ADDED_FILES']['FILES'] = $notAdded;
        }

        Solution::updateExtendedReviewsProps($commentID);
    }

    public static function OnBeforeCommentUpdateHandler($id, &$arFields)
    {
        $application = Application::getInstance();
        $request = $application->getContext()->getRequest();

        if (isset($request['approve_comment_id']) || isset($request['unapprove_comment_id'])) {
            $bStatus = isset($request['approve_comment_id']);

            global $USER_FIELD_MANAGER;
            $USER_FIELD_MANAGER->Update('BLOG_COMMENT', $id, ['UF_ASPRO_COM_APPROVE' => $bStatus]);
        }

        if (isset($request['rating'])) {
            global $USER_FIELD_MANAGER;
            $USER_FIELD_MANAGER->Update('BLOG_COMMENT', $id, ['UF_ASPRO_COM_RATING' => $request['rating']]);
        }

        if (isset($arFields['POST_TEXT'])) {
            $arFields['POST_TEXT'] = strip_tags($arFields['POST_TEXT'], '<virtues><limitations><comment>');
        }
    }

    public static function OnCommentUpdateHandler($commentID, &$arFields)
    {
        $application = Application::getInstance();
        $request = $application->getContext()->getRequest()->toArray();

        if (isset($request['rating'])) {
            if ($request['deleted_images']) {
                $resImages = CBlogImage::GetList(
                    ['ID' => 'DESC'],
                    [
                        'COMMENT_ID' => $commentID,
                        '@FILE_ID' => $request['deleted_images'],
                    ],
                    false,
                    false
                );

                while ($arImage = $resImages->Fetch()) {
                    CFile::Delete($arImage['FILE_ID']);
                    CBlogImage::Delete($arImage['ID']);
                }
            }

            if (isset($_FILES['comment_images']['name'][0]) && $_FILES['comment_images']['name'][0]) {
                $maxSize = $_SESSION['BLOG_MAX_IMAGE_SIZE'] * 1024 * 1024;

                foreach ($_FILES['comment_images']['name'] as $key => $imgName) {
                    if ($maxSize && $_FILES['comment_images']['size'][$key] > $maxSize) {
                        continue;
                    }
                    $fileArray = [
                        'name' => $imgName,
                        'size' => $_FILES['comment_images']['size'][$key],
                        'tmp_name' => $_FILES['comment_images']['tmp_name'][$key],
                        'type' => $_FILES['comment_images']['type'][$key],
                        'MODULE_ID' => 'blog',
                    ];
                    $fileId = CFile::SaveFile($fileArray, '/blog/comment/');
                    if ($fileId) {
                        $filesToAttach[$key] = $fileId;
                    }
                }

                unset($_FILES['comment_images']);
            }

            if ($filesToAttach) {
                foreach ($filesToAttach as $imageKey => $imageId) {
                    CBlogImage::Add([
                        'FILE_ID' => $imageId,
                        'POST_ID' => $arFields['POST_ID'],
                        'BLOG_ID' => $arFields['BLOG_ID'],
                        'COMMENT_ID' => intval($commentID),
                        'IMAGE_SIZE' => $_FILES['comment_images']['size'][$imageKey],
                    ]);
                }
            }
        }

        Solution::updateExtendedReviewsProps($commentID);
    }

    public static function OnEpilogHandler()
    {
        global $bHeaderStickyMenu, $bHeaderStickyMenuSm;
        $asset = Asset::getInstance();
        if ($bHeaderStickyMenu) {
            $asset->addCss(SITE_TEMPLATE_PATH.'/css/header28.css');
        }

        if ($bHeaderStickyMenuSm) {
            $asset->addCss(SITE_TEMPLATE_PATH.'/css/header28.css');
        }
    }

    public static function OnCommentDeleteHandler($ID)
    {
        $resImages = CBlogImage::GetList(['ID' => 'DESC'], ['COMMENT_ID' => $ID]);
        while ($arImage = $resImages->Fetch()) {
            CFile::Delete($arImage['FILE_ID']);
        }

        Solution::updateExtendedReviewsProps($ID, 'delete');
    }

    public static function OnSaleComponentOrderJsDataHandler($arResult, $arParams)
    {
        /* filter region stores */
        global $arRegion;
        if ($arResult['JS_DATA'] && is_array($arResult['JS_DATA']) && $arResult['JS_DATA']['STORE_LIST']) {
            if (!$arRegion) {
                $arRegion = SolutionRegionality::getCurrentRegion();
            }
            if ($arRegion && ($arRegion['LIST_STORES'] && is_array($arRegion['LIST_STORES']) && reset($arRegion['LIST_STORES']) !== 'component')) {
                $arResult['JS_DATA']['STORE_LIST'] = array_filter($arResult['JS_DATA']['STORE_LIST'], function ($item) use ($arRegion) {
                    return in_array($item['ID'], $arRegion['LIST_STORES']);
                });
            }
        }

        /* user profiles */
        if ($arResult['JS_DATA']['USER_PROFILES']) {
            $arPersonType = reset(array_filter($arResult['PERSON_TYPE'], function ($item) {
                return $item['CHECKED'] === 'Y';
            }));

            $arPhoneProp = CSaleOrderProps::GetList(
                [
                    'SORT' => 'ASC',
                ],
                [
                    'USER_PROPS' => 'Y',
                    'IS_PHONE' => 'Y',
                    'PERSON_TYPE_ID' => $arPersonType['ID'],
                ]
            )->Fetch();

            $arUserProps = [];
            $rsUserProps = CSaleOrderUserPropsValue::GetList(
                [
                    'ID' => 'ASC',
                ],
                [
                    'USER_PROPS_ID' => array_keys($arResult['JS_DATA']['USER_PROFILES']),
                ],
                false,
                false,
                ['ID', 'USER_PROPS_ID', 'VALUE', 'NAME', 'CODE', 'PROP_IS_EMAIL']
            );
            while ($arUserProp = $rsUserProps->Fetch()) {
                if ($arUserProp['VALUE']) {
                    if ($arUserProp['PROP_IS_EMAIL'] === 'Y') {
                        $arUserProps[$arUserProp['USER_PROPS_ID']]['EMAIL'] = $arUserProp['VALUE'];
                    }
                    if ($arPhoneProp && $arPhoneProp['CODE'] === $arUserProp['CODE']) {
                        $arUserProps[$arUserProp['USER_PROPS_ID']]['PHONE'] = $arUserProp['VALUE'];
                    }
                }
            }

            if ($arUserProps) {
                $arResult['JS_DATA']['USER_PROFILES_PROPS'] = $arUserProps;
            }
        }
    }

    public static function OnAdminContextMenuShowHandler(&$items)
    {
        if (
            $_SERVER['REQUEST_METHOD'] === 'GET'
            && (
                $GLOBALS['APPLICATION']->GetCurPage() === '/bitrix/admin/sale_order_view.php'
                || $GLOBALS['APPLICATION']->GetCurPage() === '/bitrix/admin/sale_order_edit.php'
            )
            && isset($_REQUEST['ID'])
        ) {
            $orderId = intval($_REQUEST['ID']);
            if ($orderId > 0) {
                if (Bitrix\Main\Loader::includeModule('sale')) {
                    if ($order = Bitrix\Sale\Order::load($orderId)) {
                        $siteId = $order->getSiteId();
                        $personTypeId = $order->getPersonTypeId();
                        $arSendingResult = CRM\Helper::getSendingOrderResult($orderId, $siteId);

                        $arMenuItem = [
                            'TEXT' => $title = Loc::getMessage('CRM_SEND'),
                            'TITLE' => $title,
                            'MENU' => [],
                            'ASPRO_CRM' => 'Y',
                        ];

                        $arSubMenuItem = [];

                        $acloud = CRM\Acloud\Connection::getInstance($siteId);
                        if ($acloud->active) {
                            $leadId = intval(isset($arSendingResult['ACLOUD']) ? (is_array($arSendingResult['ACLOUD']) ? $arSendingResult['ACLOUD'][$acloud->domain] : $arSendingResult['ACLOUD']) : 0);
                            $bSended = $leadId > 0;
                            if ($bSended) {
                                $url = CRM\Acloud\Lead::getUrl($leadId);
                                $url = $url ? $acloud->domain.$url : $leadId;

                                $title = Loc::getMessage(
                                    'CRM_OPEN_LEAD',
                                    [
                                        '#CRM_DOMAIN#' => $acloud->domain,
                                        '#LEAD_ID#' => $leadId,
                                    ]
                                );

                                $arSubMenuItem = [
                                    'TEXT' => $title,
                                    'TITLE' => $title,
                                    'ONCLICK' => 'window.open(\''.$url.'\'); return false;',
                                ];
                            } else {
                                $matches = (array) $acloud->orders_matches[$personTypeId];

                                if ($matches) {
                                    $title = Loc::getMessage(
                                        'CRM_SEND_ORDER',
                                        [
                                            '#CRM_DOMAIN#' => $acloud->domain,
                                        ]
                                    );

                                    $arSubMenuItem = [
                                        'TEXT' => $title,
                                        'TITLE' => $title,
                                        'LINK' => 'javascript: BX.ajax({
                                            url: \'/bitrix/admin/aspro.max_crm_acloud.php?SendCrm=Y&ORDER_ID='.$orderId.'&SITE_ID='.$siteId.'&sessid='.bitrix_sessid().'\',
                                            method: \'POST\',
                                            dataType: \'json\',
                                            async: false,
                                            start: true,
                                            cache: false,
                                            onsuccess: function(data) {
                                                if (
                                                    typeof data === \'object\' &&
                                                    data &&
                                                    (
                                                        \'error\' in data ||
                                                        \'response\' in data
                                                    )
                                                ){
                                                    if(\'error\' in data){
                                                        alert(data.error);
                                                    }
                                                    else if(\'response\' in data){
                                                        location.reload();
                                                    }

                                                    return true;
                                                }
                                            },
                                            onfailure: function() {
                                                alert(\'error\');
                                            },
                                        });',
                                    ];
                                }
                            }

                            if ($arSubMenuItem) {
                                $bFinded = false;
                                foreach ($items as &$item) {
                                    if (
                                        is_array($item)
                                        && is_array($item['MENU'])
                                        && array_key_exists('ASPRO_CRM', $item)
                                    ) {
                                        $item['MENU'][] = $arSubMenuItem;
                                        $bFinded = true;

                                        break;
                                    }
                                }
                                unset($item);

                                if (!$bFinded) {
                                    $arMenuItem['MENU'][] = $arSubMenuItem;
                                    $items[] = $arMenuItem;
                                }
                            }
                        }
                    }
                }
            }
        } elseif (
            $_SERVER['REQUEST_METHOD'] === 'GET'
            && (
                $GLOBALS['APPLICATION']->GetCurPage() === '/bitrix/admin/form_result_edit.php'
                || $GLOBALS['APPLICATION']->GetCurPage() === '/bitrix/admin/form_result_view.php'
            )
            && isset($_REQUEST['RESULT_ID'])
            && isset($_REQUEST['WEB_FORM_ID'])
        ) {
            // only for second buttons row
            if (in_array('btn_new', array_column($items, 'ICON'))) {
                $formId = intval($_REQUEST['WEB_FORM_ID']);
                $resultId = intval($_REQUEST['RESULT_ID']);
                if (
                    $formId > 0
                    && $resultId > 0
                ) {
                    if (Bitrix\Main\Loader::includeModule('form')) {
                        CFormResult::GetDataByID($resultId, [], $arResultFields, $arAnswers);

                        if ($arResultFields) {
                            $arSites = CForm::GetSiteArray($formId);

                            if ($arSites) {
                                $arMenuItem = [
                                    'TEXT' => $title = Loc::getMessage('CRM_SEND'),
                                    'TITLE' => $title,
                                    'MENU' => [],
                                    'ASPRO_CRM' => 'Y',
                                ];

                                $arSubMenuItem = [];

                                foreach ($arSites as $siteId) {
                                    $arSendingResult = CRM\Helper::getSendingFormResult($resultId, $siteId);

                                    $acloud = CRM\Acloud\Connection::getInstance($siteId);
                                    if ($acloud->active) {
                                        $leadId = intval(isset($arSendingResult['ACLOUD']) ? (is_array($arSendingResult['ACLOUD']) ? $arSendingResult['ACLOUD'][$acloud->domain] : $arSendingResult['ACLOUD']) : 0);
                                        $bSended = $leadId > 0;
                                        if ($bSended) {
                                            $url = CRM\Acloud\Lead::getUrl($leadId);
                                            $url = $url ? $acloud->domain.$url : $leadId;

                                            $title = Loc::getMessage(
                                                'CRM_OPEN_LEAD',
                                                [
                                                    '#CRM_DOMAIN#' => $acloud->domain,
                                                    '#LEAD_ID#' => $leadId,
                                                ]
                                            );

                                            if (count($arSites) > 1) {
                                                $title .= ' ('.$siteId.')';
                                            }

                                            $arSubMenuItem = [
                                                'TEXT' => $title,
                                                'TITLE' => $title,
                                                'ONCLICK' => 'window.open(\''.$url.'\'); return false;',
                                            ];
                                        } else {
                                            $matches = (array) $acloud->forms_matches[$formId];

                                            if ($matches) {
                                                $title = Loc::getMessage(
                                                    'CRM_SEND_ORDER',
                                                    [
                                                        '#CRM_DOMAIN#' => $acloud->domain,
                                                    ]
                                                );

                                                if (count($arSites) > 1) {
                                                    $title .= ' ('.$siteId.')';
                                                }

                                                $arSubMenuItem = [
                                                    'TEXT' => $title,
                                                    'TITLE' => $title,
                                                    'LINK' => 'javascript: BX.ajax({
                                                        url: \'/bitrix/admin/aspro.max_crm_acloud.php?SendCrm=Y&FORM_ID='.$formId.'&RESULT_ID='.$resultId.'&SITE_ID='.$siteId.'&sessid='.bitrix_sessid().'\',
                                                        method: \'POST\',
                                                        dataType: \'json\',
                                                        async: false,
                                                        start: true,
                                                        cache: false,
                                                        onsuccess: function(data) {
                                                            if (
                                                                typeof data === \'object\' &&
                                                                data &&
                                                                (
                                                                    \'error\' in data ||
                                                                    \'response\' in data
                                                                )
                                                            ){
                                                                if(\'error\' in data){
                                                                    alert(data.error);
                                                                }
                                                                else if(\'response\' in data){
                                                                    location.reload();
                                                                }

                                                                return true;
                                                            }
                                                        },
                                                        onfailure: function() {
                                                            alert(\'error\');
                                                        },
                                                    });',
                                                ];
                                            }
                                        }

                                        if ($arSubMenuItem) {
                                            $bFinded = false;
                                            foreach ($items as &$item) {
                                                if (
                                                    is_array($item)
                                                    && is_array($item['MENU'])
                                                    && array_key_exists('ASPRO_CRM', $item)
                                                ) {
                                                    $item['MENU'][] = $arSubMenuItem;
                                                    $bFinded = true;

                                                    break;
                                                }
                                            }
                                            unset($item);

                                            if (!$bFinded) {
                                                $arMenuItem['MENU'][] = $arSubMenuItem;
                                                $items[] = $arMenuItem;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function OnAfterUserLoginHandler(&$items)
    {
        if ($items['USER_ID'] && !defined('ADMIN_SECTION')) {
            $_SESSION['ASPRO_MAX_SUCCESSFUL_AUTHORIZATION'] = 'Y';
        }
    }

    public static function onAsproParametersHandler(&$arParams)
    {
        if (method_exists('Aspro\Functions\CAsproMax', 'getCustomBlocks')) { // not use alias
            $arNewOptions = SolutionFunctions::getCustomBlocks();
            if ($arNewOptions) {
                $currentIndexType = Option::get(Solution::moduleID, 'INDEX_TYPE', 'index1');
                $indexOptions = $arParams['INDEX_PAGE']['OPTIONS']['INDEX_TYPE']['SUB_PARAMS'][$currentIndexType];

                $arParams['INDEX_PAGE']['OPTIONS']['INDEX_TYPE']['SUB_PARAMS'][$currentIndexType] = array_merge($indexOptions, $arNewOptions);
            }
        }
    }
}

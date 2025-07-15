<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

Option::set("sale", "SHOP_SITE_".WIZARD_SITE_ID, WIZARD_SITE_ID);

Option::set("main", "captcha_registration", "N");
Option::set("iblock", "use_htmledit", "Y");
Option::set("fileman", "propstypes", serialize(array("description"=>Loc::getMessage("MAIN_OPT_DESCRIPTION"), "keywords"=>Loc::getMessage("MAIN_OPT_KEYWORDS"), "title"=>Loc::getMessage("MAIN_OPT_TITLE"), "keywords_inner"=>Loc::getMessage("MAIN_OPT_KEYWORDS_INNER"), "viewed_show"=>GetMessage("MAIN_OPT_VIEWED_SHOW"), "HIDETITLE" => Loc::getMessage("MAIN_OPT_HIDETITLE"), "WIDE_PAGE" => Loc::getMessage("MAIN_OPT_FULLWIDTH"), "HIDE_LEFT_BLOCK" => Loc::getMessage("MAIN_OPT_MENU"), "BLOG_PAGE" => Loc::getMessage("BLOG_PAGE_TITLE"), "MENU_SHOW_SECTIONS" => Loc::getMessage("MENU_SHOW_SECTIONS_TITLE"), "MENU_SHOW_ELEMENTS" => Loc::getMessage("MENU_SHOW_ELEMENTS_TITLE"), "HIDE_LEFT_BLOCK_DETAIL" => Loc::getMessage("HIDE_LEFT_BLOCK_DETAIL_TITLE"), "HIDE_LEFT_BLOCK_LIST" => Loc::getMessage("HIDE_LEFT_BLOCK_LIST_TITLE"))), WIZARD_SITE_ID);
Option::set("search", "suggest_save_days", 250);
Option::set("search", "use_tf_cache", "Y");
Option::set("search", "use_word_distance", "Y");
Option::set("search", "use_social_rating", "Y");

// social auth services
if (Option::get("socialservices", "auth_services") == ""){
	$bRu = (LANGUAGE_ID == 'ru');
	$arServices = array(
		"VKontakte" => "Y",
		"MyMailRu" => "Y",
		"Twitter" => "Y",
		"Facebook" => "Y",
		"Livejournal" => "Y",
		"YandexOpenID" => ($bRu? "Y":"N"),
		"Rambler" => ($bRu? "Y":"N"),
		"MailRuOpenID" => ($bRu? "Y":"N"),
		"Liveinternet" => ($bRu? "Y":"N"),
		"Blogger" => "N",
		"OpenID" => "Y",
		"LiveID" => "N",
	);
	Option::set("socialservices", "auth_services", serialize($arServices));
}

Option::set("socialservices", "auth_services", serialize($arServices));
Option::set("aspro.max", "WIZARD_SITE_ID", WIZARD_SITE_ID, WIZARD_SITE_ID);
Option::set("aspro.max", "SITE_INSTALLED", "Y", WIZARD_SITE_ID);
Option::set("aspro.max", "USE_FILTERS", "Y", WIZARD_SITE_ID);

if(\Bitrix\Main\ModuleManager::isModuleInstalled('form'))
{
	Option::set("form", "SIMPLE", 'N');
}

// subscribe to products - set active notify for sites
$notifyOption = Option::get("sale", "subscribe_prod", "");
$arNotify = unserialize($notifyOption, ['allowed_classes' => false]);
if($arNotify){
	foreach($arNotify as $siteID => $notify){
		if($siteID == WIZARD_SITE_ID){
			$arNotify[$siteID]['use'] = 'Y';
			$arNotify[$siteID]['del_after'] = $arNotify[$siteID]['del_after'] > 0 ? $arNotify[$siteID]['del_after'] : 30;
		}
	}
	Option::set("sale", "subscribe_prod", serialize($arNotify));
}

// get DB charset
if (method_exists('\Bitrix\Main\Application', 'isUtfMode')) {
    $isUTF8 = \Bitrix\Main\Application::isUtfMode();
} else {
    $sql='SHOW VARIABLES LIKE "character_set_database";';
    if(method_exists('\Bitrix\Main\Application', 'getConnection')){
        $db=\Bitrix\Main\Application::getConnection();
        $arResult = $db->query($sql)->fetch();
        $isUTF8 = stripos($arResult['Value'], 'utf8') !== false;
    }elseif(defined("BX_USE_MYSQLI") && BX_USE_MYSQLI === true){
        if($result = @mysqli_query($sql)){
            $arResult = mysql_fetch_row($result);
            $isUTF8 = stripos($arResult[1], 'utf8') !== false;
        }
    }elseif($result = @mysql_query($sql)){
        $arResult = mysql_fetch_row($result);
        $isUTF8 = stripos($arResult[1], 'utf8') !== false;
    }
}

// new options
Option::set("aspro.max", "ORDER_BASKET_VIEW", "FLY2", WIZARD_SITE_ID);
Option::set("aspro.max", "INDEX_TYPE", "index1", WIZARD_SITE_ID);
Option::set("aspro.max", "STORES_SOURCE", "IBLOCK", WIZARD_SITE_ID);
Option::set("aspro.max", "TYPE_SKU", "TYPE_1", WIZARD_SITE_ID);
Option::set("aspro.max", "MAX_DEPTH_MENU", "3", WIZARD_SITE_ID);
Option::set("aspro.max", "FILTER_VIEW", "COMPACT", WIZARD_SITE_ID);
Option::set("aspro.max", "USE_FAST_VIEW_PAGE_DETAIL", "fast_view_1", WIZARD_SITE_ID);
Option::set("aspro.max", "SHOW_BASKET_ONADDTOCART", "Y", WIZARD_SITE_ID);
Option::set("aspro.max", "USE_PRODUCT_QUANTITY_LIST", "Y", WIZARD_SITE_ID);
Option::set("aspro.max", "USE_PRODUCT_QUANTITY_DETAIL", "Y", WIZARD_SITE_ID);
Option::set("aspro.max", "BUYNOPRICEGGOODS", "NOTHING", WIZARD_SITE_ID);
Option::set("aspro.max", "BUYMISSINGGOODS", "ADD", WIZARD_SITE_ID);
// Option::set("aspro.max", "EXPRESSION_ORDER_BUTTON", ($isUTF8 ? iconv('CP1251', 'UTF-8', 'Под заказ') : 'Под заказ'), WIZARD_SITE_ID);

$DefaultGroupID = 0;
$rsGroups = CGroup::GetList($by = "id", $order = "asc", array("ACTIVE" => "Y"));
while($arItem = $rsGroups->Fetch()){
	if($arItem["ANONYMOUS"] == "Y"){
		$DefaultGroupID = $arItem["ID"];
		break;
	}
}

if (!\Bitrix\Main\Loader::includeModule('form')) {
	$fpServices = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/aspro.max/install/wizards/aspro/max/site/services';
	$fpServiceUniversal = $fpServices . '/service_universal.php';
	$fpLang = $fpServices . '/form/lang/' . LANGUAGE_ID . '/forms.php';

	if (file_exists($fpServiceUniversal) && file_exists($fpLang)) {
		@include $fpServiceUniversal;
		@include $fpLang;

		if (isset($arServices) && isset($arServices['form']) && isset($arServices['form']['STAGES'])) {
			$arFormList = $arServices['form']['STAGES'];

			if (count($arFormList)) {
				$formSIDRegex = "/[$]FORM_SID\s*=\s*[\"'](\S*)[\"'];/s";
				$arFormListData = [];

				foreach ($arFormList as $file) {
					$fpForm = $fpServices . '/form/' . $file;

					if (file_exists($fpForm)) {
						$formContent = @file_get_contents($fpForm);
						preg_match($formSIDRegex, $formContent, $matches);

						if (isset($matches[1])) {
							$formSID = $matches[1];
							$optionName =  $formSID . '_FORM';

							Option::set('aspro.max',  $optionName, 'CRM', WIZARD_SITE_ID);
						}
						unset($matches);
					}
				}
			}
		}
	}
}

Option::set("aspro.max", "SHOW_QUANTITY_FOR_GROUPS", $DefaultGroupID, WIZARD_SITE_ID);
Option::set("aspro.max", "SHOW_QUANTITY_COUNT_FOR_GROUPS", $DefaultGroupID, WIZARD_SITE_ID);
// Option::set("aspro.max", "EXPRESSION_FOR_EXISTS", ($isUTF8 ? iconv('CP1251', 'UTF-8', 'Есть в наличии') : 'Есть в наличии'), WIZARD_SITE_ID);
// Option::set("aspro.max", "EXPRESSION_FOR_NOTEXISTS", ($isUTF8 ? iconv('CP1251', 'UTF-8', 'Нет в наличии') : 'Нет в наличии'), WIZARD_SITE_ID);
Option::set("aspro.max", "USE_WORD_EXPRESSION", "Y", WIZARD_SITE_ID);
Option::set("aspro.max", "MAX_AMOUNT", 10, WIZARD_SITE_ID);
Option::set("aspro.max", "MIN_AMOUNT", 2, WIZARD_SITE_ID);
Option::set("aspro.max", "USE_REGIONALITY", "N", WIZARD_SITE_ID);
Option::set("aspro.max", "REGIONALITY_TYPE", "ONE_DOMAIN", WIZARD_SITE_ID);
Option::set("aspro.max", "SHOW_REGION_CONTACT", "Y", WIZARD_SITE_ID);
Option::set("aspro.max", "PRINT_BUTTON", "N", WIZARD_SITE_ID);
Option::set("aspro.max", "SHOW_BASKET_ON_PAGES", "N", WIZARD_SITE_ID);
Option::set("aspro.max", "VIEWED_TEMPLATE", "HORIZONTAL", WIZARD_SITE_ID);
Option::set("aspro.max", "VIEWED_TYPE", "LOCAL", WIZARD_SITE_ID);
Option::set("aspro.max", "ADV_SIDE", "Y", WIZARD_SITE_ID);
// Option::set("aspro.max", "EXPRESSION_FOR_MIN", ($isUTF8 ? iconv('CP1251', 'UTF-8', 'Мало') : 'Мало'), WIZARD_SITE_ID);
// Option::set("aspro.max", "EXPRESSION_FOR_MID", ($isUTF8 ? iconv('CP1251', 'UTF-8', 'Достаточно') : 'Достаточно'), WIZARD_SITE_ID);
// Option::set("aspro.max", "EXPRESSION_FOR_MAX", ($isUTF8 ? iconv('CP1251', 'UTF-8', 'Много') : 'Много'), WIZARD_SITE_ID);
// Option::set("aspro.max", "MIN_ORDER_PRICE_TEXT", ($isUTF8 ? iconv('CP1251', 'UTF-8', '<b>Минимальная сумма заказа #PRICE#</b><br/>Пожалуйста, добавьте еще товаров в корзину') : '<b>Минимальная сумма заказа #PRICE#</b><br/>Пожалуйста, добавьте еще товаров в корзину'), WIZARD_SITE_ID);
Option::set("aspro.max", "SHOW_CALLBACK", "HEADER,FOOTER,SIDE,INNER_MENU", WIZARD_SITE_ID);
Option::set("aspro.max", "SHOW_QUESTION", "SIDE,INNER_MENU", WIZARD_SITE_ID);
Option::set("aspro.max", "SHOW_REVIEW", "SIDE,INNER_MENU", WIZARD_SITE_ID);

// enable composite
if(class_exists("CHTMLPagesCache")){
	if(method_exists("CHTMLPagesCache", "GetOptions")){
		if($arHTMLCacheOptions = CHTMLPagesCache::GetOptions()){
			if($arHTMLCacheOptions["COMPOSITE"] !== "Y"){
				$arDomains = array();

				$arSites = array();
				$dbRes = CSite::GetList($by="sort", $order="desc", array("ACTIVE" => "Y"));
				while($item = $dbRes->Fetch()){
					$arSites[$item["LID"]] = $item;
				}

				if($arSites){
					foreach($arSites as $arSite){
						if(strlen($serverName = trim($arSite["SERVER_NAME"], " \t\n\r"))){
							$arDomains[$serverName] = $serverName;
						}
						if(strlen($arSite["DOMAINS"])){
							foreach(explode("\n", $arSite["DOMAINS"]) as $domain){
								if(strlen($domain = trim($domain, " \t\n\r"))){
									$arDomains[$domain] = $domain;
								}
							}
						}
					}
				}

				if(!$arDomains){
					$arDomains[$_SERVER["SERVER_NAME"]] = $_SERVER["SERVER_NAME"];
				}

				if(!$arHTMLCacheOptions["GROUPS"]){
					$arHTMLCacheOptions["GROUPS"] = array();
				}
				$rsGroups = CGroup::GetList(($by="id"), ($order="asc"), array());
				while($arGroup = $rsGroups->Fetch()){
					if($arGroup["ID"] > 2){
						if(in_array($arGroup["STRING_ID"], array("RATING_VOTE_AUTHORITY", "RATING_VOTE")) && !in_array($arGroup["ID"], $arHTMLCacheOptions["GROUPS"])){
							$arHTMLCacheOptions["GROUPS"][] = $arGroup["ID"];
						}
					}
				}

				$arHTMLCacheOptions["COMPOSITE"] = "Y";
				$arHTMLCacheOptions["DOMAINS"] = array_merge((array)$arHTMLCacheOptions["DOMAINS"], (array)$arDomains);
				CHTMLPagesCache::SetEnabled(true);
				CHTMLPagesCache::SetOptions($arHTMLCacheOptions);
				bx_accelerator_reset();
			}
		}
	}
}
// add agent check license
if (\Bitrix\Main\Loader::includeModule('aspro.max')) {
	\Aspro\Max\Agents\Notifications\CheckInfoModule::add();
}

?>

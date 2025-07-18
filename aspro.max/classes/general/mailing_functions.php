<?
$compiler = \Bitrix\Main\Mail\EventMessageThemeCompiler::getInstance();
$site_id = $compiler->getSiteId();

if($site_id == "ru")
{
	// include CMainPage
	require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/mainpage.php");
	// get site_id by host
	$obMainPage = new CMainPage();
	$site_id = $obMainPage->GetSiteByHost();
	if(!$site_id || $site_id == "ru")
	    $site_id = "s1";
}

$moduleID = "aspro.max";
\Bitrix\Main\Loader::includeModule($moduleID);
$arColoredHead = array("TYPE_2", "TYPE_6", "TYPE_7", "TYPE_8");

// get site info
$arSite = \CSite::GetByID($site_id)->Fetch();
$arSite['DIR'] = str_replace('//', '/', '/'.$arSite['DIR']);
if(!strlen($arSite['DOC_ROOT']))
{
    $arSite['DOC_ROOT'] = Bitrix\Main\Application::getDocumentRoot();
}
$site_charset = $arSite['CHARSET'];
$arSite['DOC_ROOT'] = str_replace('//', '/', $arSite['DOC_ROOT'].'/');
$siteDir = str_replace('//', '/', $arSite['DOC_ROOT'].$arSite['DIR']);
$siteProtocol = (\CMain::isHttps() ? "https" : "http");
$siteAddress = $arSite['SERVER_NAME'];
$siteAddressFull = $siteProtocol."://".$arSite['SERVER_NAME'];

//get iblock linked to site
\Bitrix\Main\Loader::includeModule('iblock');
global $arIblocks;
$arIblocks = array();
$dbIblock = \CIBlock::GetList(array(), array("ACTIVE" => "Y", "LID" => $site_id));
while($arIblock = $dbIblock->Fetch()){
	$arIblocks[$arIblock["IBLOCK_TYPE_ID"]][$arIblock["CODE"]][] = $arIblock["ID"];
}

//get vars
$arLogo = \Bitrix\Main\Config\Option::get($moduleID, "LOGO_IMAGE", false, $site_id);
$arLogoEmail = \Bitrix\Main\Config\Option::get($moduleID, "LOGO_IMAGE_EMAIL", false, $site_id);

$color_theme = \Bitrix\Main\Config\Option::get($moduleID, "BASE_COLOR", 9, $site_id);
$custom_color_theme = \Bitrix\Main\Config\Option::get($moduleID, "BASE_COLOR_CUSTOM", false, $site_id);
$type_head = \Bitrix\Main\Config\Option::get($moduleID, "HEAD", "TYPE_1", $site_id);
$logo_color_bg = \Bitrix\Main\Config\Option::get($moduleID, "COLORED_LOGO", "", $site_id);
$order_email = \Bitrix\Main\Config\Option::get("sale", "order_email", "sale@".$siteAddress);

$isColoredHead = (in_array($type_head, $arColoredHead) ? true : false);
$type_color = ($isColoredHead ? "colored" : "main");
$phone_color = ($type_color == "colored" ? "#fff" : "#1d2029");
$arModuleOptions = \CMax::GetBackParametrsValues($site_id, false);
$arThemeValues = array();
$bg_color = $bg_color_logo = "";
$phone = '+0 000 000-00-00';
global $copyright;
$copyright = '&copy; '.$arSite["NAME"];
global $social;
$social = '';

$bg_image = $siteAddressFull.'/bitrix/templates/'.str_replace(".", "_", $moduleID).'/images/phone_mail.png';
$bg_phone_position = '0px 0px';
$phonePath = $siteDir.'/include/phone.php';
$copyrightPath = $siteDir.'/include/footer/copy/copyright.php';
$socialPath = $siteDir.'/include/footer/social.info.max.default.php';

$logo_src = $siteAddressFull;

$arLogo = $arLogoEmail == serialize(array()) || $arLogoEmail == false ? $arLogo : $arLogoEmail;

if($arLogo == serialize(array()) || $arLogo == false)
{
    $logo_src .= "/include/logo.png";
}
else
{
    $arLogoValue = \CMax::unserialize($arLogo);
    $logo_src .= \CFIle::GetPath(current($arLogoValue));
}

$iPhoneCount = (int)$arModuleOptions["HEADER_PHONES"];
if ($iPhoneCount) {
    $phone = '';
    for ($i = 0; $i < $iPhoneCount; ++$i) {
        $phone .= '<a rel="nofollow" href="'.$arModuleOptions['HEADER_PHONES_array_PHONE_HREF_'.$i].'" class="dark-color">'.$arModuleOptions['HEADER_PHONES_array_PHONE_VALUE_'.$i].'</a>';
    }
}
$bg_color = \CMax::$arParametrsList['MAIN']['OPTIONS']['BASE_COLOR']['LIST'][$color_theme]['COLOR'];

if($color_theme == "CUSTOM")
{
    $bg_color = "#".$custom_color_theme;
}
$theme_color =  $bg_color;

$bg_color_logo = $bg_color;
if($logo_color_bg != "Y")
    $bg_color_logo = "none;";

if(Bitrix\Main\IO\File::isFileExists($copyrightPath))
{
    $copyright = Bitrix\Main\IO\File::getFileContents($copyrightPath);
	// cut php
	$pattern = '/<\?(.*)\?>/is';
	preg_match($pattern, $copyright,$matches);
	if($matches[1])
	{
		$copyright = str_replace(array($matches[1], '<?', '?>'), "", $copyright);
	}
}
?>

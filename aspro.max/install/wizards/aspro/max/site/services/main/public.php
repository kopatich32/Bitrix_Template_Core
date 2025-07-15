<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!defined("WIZARD_SITE_ID")) return;
if(!defined("WIZARD_SITE_DIR")) return;
if(!defined("WIZARD_SITE_PATH")) return;

function ___writeToAreasFile($fn, $text){
	if(file_exists($fn) && !is_writable($abs_path) && defined("BX_FILE_PERMISSIONS")){
		@chmod($abs_path, BX_FILE_PERMISSIONS);
	}
	if(!$fd = @fopen($fn, "wb")){
		return false;
	}
	if(!$res = @fwrite($fd, $text)){
		@fclose($fd);
		return false;
	}
	@fclose($fd);
	if(defined("BX_FILE_PERMISSIONS"))
		@chmod($fn, BX_FILE_PERMISSIONS);
}

$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."/";
//$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"]."/local/templates/".WIZARD_TEMPLATE_ID."/";
$wizard =& $this->GetWizard();
use \Bitrix\Main\Config\Option;

if(Option::get("main", "upload_dir") == "")
	Option::set("main", "upload_dir", "upload");

if(WIZARD_INSTALL_DEMO_DATA){
	// copy files
	CopyDirFiles(
		str_replace("//", "/", WIZARD_ABSOLUTE_PATH."/site/public/ru/"),
		WIZARD_SITE_PATH,
		$rewrite = true,
		$recursive = true,
		$delete_after_copy = false,
		$exclude = "bitrix"
	);

	// favicon
	//@copy(WIZARD_THEME_ABSOLUTE_PATH."/favicon.ico", WIZARD_SITE_PATH."favicon.ico");

	// .htaccess
	WizardServices::PatchHtaccess(WIZARD_SITE_PATH);

	// replace macros SITE_DIR & SITE_ID
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("SITE_DIR" => WIZARD_SITE_DIR));
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("SITE_ID" => WIZARD_SITE_ID));

	// add to UrlRewrite
	$arUrlRewrite = array();
	if(file_exists(WIZARD_SITE_ROOT_PATH."/urlrewrite.php")){
		include(WIZARD_SITE_ROOT_PATH."/urlrewrite.php");
	}

	$arNewUrlRewrite = array(
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."bitrix/services/ymarket/([\\w\\d\\-]+)?(/)?(([\\w\\d\\-]+)(/)?)?#",
			"RULE" => "REQUEST_OBJECT=\$1&METHOD=\$4",
			"ID" => "",
			"PATH" => WIZARD_SITE_DIR."bitrix/services/ymarket/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."personal/history-of-orders/#",
			"RULE" => "",
			"ID" => "bitrix:sale.personal.order",
			"PATH" => WIZARD_SITE_DIR."personal/history-of-orders/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."contacts/stores/#",
			"RULE" => "",
			"ID" => "bitrix:catalog.store",
			"PATH" => WIZARD_SITE_DIR."contacts/stores/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."contacts/stores/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."contacts/stores/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."personal/order/#",
			"RULE" => "",
			"ID" => "bitrix:sale.personal.order",
			"PATH" => WIZARD_SITE_DIR."personal/order/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."blog/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."blog/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."auth/#",
			"RULE" => "",
			"ID" => "aspro:auth.max",
			"PATH" => WIZARD_SITE_DIR."auth/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."company/news/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."company/news/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."projects/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."projects/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."company/vacancy/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."company/vacancy/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."company/staff/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."company/staff/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."info/brands/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."info/brands/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."services/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."services/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."catalog/#",
			"RULE" => "",
			"ID" => "bitrix:catalog",
			"PATH" => WIZARD_SITE_DIR."catalog/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."landings/#",
			"RULE" => "",
			"ID" => "bitrix:catalog",
			"PATH" => WIZARD_SITE_DIR."landings/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."sale/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."sale/index.php",
		),
		array(
			'CONDITION' => "#^".WIZARD_SITE_DIR."sharebasket/#",
			'RULE' => "",
			'ID' => "aspro:basket.share.max",
			'PATH' => WIZARD_SITE_DIR."sharebasket/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."lookbooks/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."lookbooks/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."company/partners/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."company/partners/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."company/licenses/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."company/licenses/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."company/reviews/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."company/reviews/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."company/vacancy/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."company/vacancy/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."company/docs/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."company/docs/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."personal/#",
			"RULE" => "",
			"ID" => "bitrix:sale.personal.section",
			"PATH" => WIZARD_SITE_DIR."personal/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."gallery/#",
			"RULE" => "",
			"ID" => "bitrix:news",
			"PATH" => WIZARD_SITE_DIR."gallery/index.php",
		),
	);

	foreach($arNewUrlRewrite as $arUrl){
		if(!in_array($arUrl, $arUrlRewrite)){
			CUrlRewriter::Add($arUrl);
		}
	}
}

CheckDirPath(WIZARD_SITE_PATH."include/");

// site name
if($wizard->GetVar('siteName', true)){
	$siteName = htmlspecialcharsbx($wizard->GetVar("siteName"));
	$rsSites = CSite::GetList($by="sort", $order="desc", Array());
	if($rsSites ->SelectedRowsCount() === 1){
		COption::SetOptionString("main", "site_name", $siteName);
	}
	$obSite = new CSite;
	$arFields = array("NAME" => $siteName, "SITE_NAME" => $siteName);
	$siteRes = $obSite->Update(WIZARD_SITE_ID, $arFields);
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("SITE_NAME" => $siteName));
}
// copyright
___writeToAreasFile(WIZARD_SITE_PATH."include/footer/copy/copyright.php", "<?=date(\"Y\")?> &copy; ".$wizard->GetVar("siteCopy"));


$sitePhones = $wizard->GetVar("siteTelephone");
$arPhones = array();
if($sitePhones)
	$arPhones = explode(",", $sitePhones);
$sitePhoneOne = "";
$iCountPhones = 0;
if($arPhones)
{
	$sitePhoneOne = reset($arPhones);
	$iCountPhones = count($arPhones);
}

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."include/contacts-site-phone.php", Array("SITE_PHONE" => $sitePhones));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."include/contacts-site-phone-one.php", Array("SITE_PHONE" => $sitePhoneOne));

// phone
$phones = Option::get('aspro.max', "HEADER_PHONES", $iCountPhones, WIZARD_SITE_ID);
Option::set('aspro.max', "HEADER_PHONES", $iCountPhones, WIZARD_SITE_ID);
if($iCountPhones)
{
	foreach($arPhones as $key => $value)
	{
		Option::set('aspro.max', "HEADER_PHONES_array_PHONE_VALUE_".$key, $value, WIZARD_SITE_ID);
	}
}
else
{
	if($phones)
	{
		for($i = 0; $i <= $phones; ++$i)
		{
			Option::delete('aspro.max', array("name" => "HEADER_PHONES_array_PHONE_VALUE_".$i, "site_id" => WIZARD_SITE_ID));
		}
	}
}

// email
$siteEmail = $wizard->GetVar("siteEmail");
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."include/footer/site-email.php", Array("SITE_EMAIL" => $siteEmail));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."include/contacts-site-email.php", Array("SITE_EMAIL" => $siteEmail));

// address
$siteAddress = $wizard->GetVar("siteAddress");
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."include/top_page/site-address.php", Array("SITE_ADDRESS" => $siteAddress));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."include/contacts-site-address.php", Array("SITE_ADDRESS" => $siteAddress));

// schedule
$siteSchedule = $wizard->GetVar("siteSchedule");
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."include/contacts-site-schedule.php", Array("SITE_SCHEDULE" => $siteSchedule));

// meta
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.section.php", array("SITE_DESCRIPTION" => htmlspecialcharsbx($wizard->GetVar("siteMetaDescription"))));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.section.php", array("SITE_KEYWORDS" => htmlspecialcharsbx($wizard->GetVar("siteMetaKeywords"))));

// logo
if($wizard->GetVar('siteLogoSet', true)){
	$templateID = $wizard->GetVar("templateID");
	$themeVarName = $templateID."_themeID";
	$themeID = $wizard->GetVar($themeVarName);
	$siteLogo = $wizard->GetVar("siteLogo");
	$ff = CFile::GetByID($siteLogo);
	if($zr = $ff->Fetch()){
		$strOldFile = str_replace("//", "/", WIZARD_SITE_ROOT_PATH."/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$zr["SUBDIR"]."/".$zr["FILE_NAME"]);
		@copy($strOldFile, WIZARD_SITE_PATH."include/logo.png");
		//___writeToAreasFile(WIZARD_SITE_PATH."include/logo.php", '<a href="'.WIZARD_SITE_DIR.'"><img src="'.WIZARD_SITE_DIR.'include/logo.png"  /></a>');
		CFile::Delete($siteLogo);
	}
}

// socials
Option::set('aspro.max', "SOCIAL_VK", $wizard->GetVar("shopVk"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_FACEBOOK", $wizard->GetVar("shopFacebook"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_TWITTER", $wizard->GetVar("shopTwitter"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_YOUTUBE", $wizard->GetVar("shopYoutube"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_INSTAGRAM", $wizard->GetVar("shopInstagram"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_TELEGRAM", $wizard->GetVar("shopTelegram"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_ODNOKLASSNIKI", $wizard->GetVar("shopOdnoklassniki"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_GOOGLEPLUS", $wizard->GetVar("shopGooglePlus"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_MAIL", $wizard->GetVar("shopMailRu"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_VIBER", $wizard->GetVar("shopViber"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_WHATS", $wizard->GetVar("shopWhats"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_TIKTOK", $wizard->GetVar("shopTikTok"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_ZEN", $wizard->GetVar("shopZen"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_PINTEREST", $wizard->GetVar("shopPinterest"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_SNAPCHAT", $wizard->GetVar("shopSnapchat"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_LINKEDIN", $wizard->GetVar("shopLinkedin"), WIZARD_SITE_ID);
Option::set('aspro.max', "SOCIAL_RUTUBE", $wizard->GetVar("shopRutube"), WIZARD_SITE_ID);

// rewrite /index.php
if($wizard->GetVar('rewriteIndex', true)){
	CopyDirFiles(
		WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/_index.php",
		WIZARD_SITE_PATH."/index.php",
		$rewrite = true,
		$recursive = true,
		$delete_after_copy = false
	);
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", Array("SITE_DIR" => WIZARD_SITE_DIR));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/index.php", Array("SITE_DIR" => WIZARD_SITE_DIR));
}

DeleteDirFilesEx(WIZARD_SITE_PATH."/_index.php");

@unlink(WIZARD_SITE_PATH."/aspro_regions/readme.txt");
@unlink(WIZARD_SITE_PATH."/aspro_regions/robots/readme.txt");
@unlink(WIZARD_SITE_PATH."/aspro_regions/sitemap/readme.txt");
?>
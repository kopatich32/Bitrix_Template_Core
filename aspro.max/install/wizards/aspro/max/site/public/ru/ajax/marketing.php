<?define("STATISTIC_SKIP_ACTIVITY_CHECK", "true");?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>

<?if(\Bitrix\Main\Loader::includeModule('aspro.popup')){
	$APPLICATION->IncludeComponent(
		"aspro:marketing.popup", 
		"max", 
		array(
			'SHOW_FORM' => 'Y'
		),
		false, array('HIDE_ICONS' => 'Y')
	);
}?>

<a href="#" class="close jqmClose"><?=CMax::showIconSvg('', SITE_TEMPLATE_PATH.'/images/svg/Close.svg')?></a>

<!-- marketnig popups -->
<?
if(\Bitrix\Main\Loader::includeModule('aspro.popup')){
	$APPLICATION->IncludeComponent(
		"aspro:marketing.popup", 
		"max", 
		array(),
		false, array('HIDE_ICONS' => 'Y')
	);
}
?>
<!-- /marketnig popups -->
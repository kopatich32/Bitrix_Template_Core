<?
use Bitrix\Main\Localization\Loc,
	CMax as Solution;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

$this->setFrameMode(true);

if($arResult['SHOW_DEFAULT']){
	$title = Loc::getMessage('DA_T_TITLE');
	$link = 'https://aspro.ru';
	$attrTitle = Loc::getMessage('DA_T_ATTR_TITLE');
	$logo = Solution::showIconSvg('developer_logo', $templateFolder.'/images/svg/logo_'.$arResult['COLOR'] .'.svg', '', '', true, false);
}
else{
	$title = $arResult['DEVELOPER']['TITLE'] ?? '';
	$link = $arResult['DEVELOPER']['LINK'] ?? '';
	$attrTitle = $arResult['DEVELOPER']['ATTR_TITLE'] ?? '';
	$logo = $arResult['DEVELOPER']['LOGO'] ?? '';
}

$bSvgLogo = strpos($logo, '<svg ') !== false;
?>
<?if(strlen($link)):?>
	<a href="<?=$link?>" id="developer" class="developer" target="_blank"<?=(strlen($attrTitle) ? ' title="'.htmlspecialcharsbx($attrTitle).'"' : '')?>>
<?else:?>
	<div id="developer" class="developer"<?=(strlen($attrTitle) ? ' title="'.htmlspecialcharsbx($attrTitle).'"' : '')?>>
<?endif;?>

	<div class="developer__title"><?=$title?></div>
	<?if(strlen($logo)):?>
		<div class="developer__logo<?=($bSvgLogo ? ' developer__logo--svg' : '')?>"><?=$logo?></div>
	<?endif;?>

<?if(strlen($link)):?>
	</a>
<?else:?>
	</div>
<?endif;?>

<?if(!$arResult['IS_AJAX']):?>
	<script>
		BX.ready(function(){
			new JDeveloper(<?=CUtil::PhpToJSObject($arResult, false, true)?>);
		});
	</script>
<?endif;?>
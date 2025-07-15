<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();

//options from TSolution\Functions::showBlockHtml
$arOptions = $arConfig['PARAMS'] ?? [];
$arProp = $arConfig['ITEM'] ?? [];

// item class
$itemClassList = ['properties__item'];
if ($arOptions['IS_ITEM']) {
	$itemClassList[] = 'js-prop-replace';
} else {
	$itemClassList[] = 'js-prop';
}
if ($arOptions['ITEM_CLASSES']) {
	$itemClassList[] = $arOptions['ITEM_CLASSES'];
}

// title class
$titleClassList = ['properties__title properties__item--inline muted js-prop-title'];

// value class
$valueClassList = ['properties__value darken properties__item--inline js-prop-value'];

if ($arOptions['FONT_CLASSES']) {
	$titleClassList[] = $valueClassList[] = $arOptions['FONT_CLASSES'];
}

$itemClasses = TSolution\Utils::implodeClasses($itemClassList);
$titleClasses = TSolution\Utils::implodeClasses($titleClassList);
$valueClasses = TSolution\Utils::implodeClasses($valueClassList);
?>
<div class="<?=$itemClasses;?>">
	<div class="<?=$titleClasses;?>">
		<?=$arProp['NAME'] ?? '#PROP_TITLE#';?>
		<?if ($arOptions["SHOW_HINTS"] === 'Y' && $arProp['HINT']):?>
			<div class="hint hint--down">
				<span class="icon colored_theme_hover_bg"><i>?</i></span>
				<div class="tooltip"><?=$arProp["HINT"];?></div>
			</div>
		<?endif;?>
	</div>
	<div class="properties__hr properties__item--inline">&mdash;</div>
	<div class="<?=$valueClasses;?>"><?=$arProp['DISPLAY_VALUE'] ? implode(', ', (array)$arProp['DISPLAY_VALUE']) : '#PROP_VALUE#';?></div>
</div>
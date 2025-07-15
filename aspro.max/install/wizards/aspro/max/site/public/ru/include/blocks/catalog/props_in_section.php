<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<?//$arOptions from \Aspro\Functions\CAsproPremier::showBlockHtml?>
<?
$arOptions = $arConfig['PARAMS'];
$arItem = $arConfig['ITEM'];

$propIterator = 0;
$maxVisibleProps = $arOptions['VISIBLE_PROP_COUNT'] ?? PHP_INT_MAX;
?>

<?=($arConfig['TITLE_TOP'] ?? '');?>
<div class="properties js-offers-prop <?=$arOptions['WRAPPER_CLASSES'];?>">
	<?
	foreach ((array)$arItem['DISPLAY_PROPERTIES'] as $arProp) {
		if (empty($arProp['VALUE'])) continue;
		if ($propIterator >= $maxVisibleProps) break;

		TSolution\Functions::showBlockHtml([
			'FILE' => 'catalog/props/list.php',
			'ITEM' => $arProp,
			'PARAMS' => $arOptions + ['IS_ITEM' => true]
		]);

		$propIterator++;
	}

	foreach ((array)$arItem['OFFER_PROP'] as $arProp) {
		if (empty($arProp['VALUE'])) continue;

		if ($propIterator < $maxVisibleProps || $arOptions['VISIBLE_PROP_WITH_OFFER']) {
			TSolution\Functions::showBlockHtml([
				'FILE' => 'catalog/props/list.php',
				'ITEM' => $arProp,
				'PARAMS' => $arOptions
			]);
		}

		$propIterator++;
	}
	?>
</div>
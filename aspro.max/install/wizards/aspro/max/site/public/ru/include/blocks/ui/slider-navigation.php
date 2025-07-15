<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

// options from \Aspro\Functions\CAsproMax::showBlockHtml
$arOptions = $arConfig['PARAMS'];

$classList = ['slider-nav'];
if ($arOptions['CLASSES']) {
    $classList[] = $arOptions['CLASSES'];
}
?>
<div class="navigation hidden-xs">
    <button type="button" role="presentation" class="<?=TSolution\Utils::implodeClasses($classList);?> swiper-button-prev"></button>
    <button type="button" role="presentation" class="<?=TSolution\Utils::implodeClasses($classList);?> swiper-button-next"></button>
</div>

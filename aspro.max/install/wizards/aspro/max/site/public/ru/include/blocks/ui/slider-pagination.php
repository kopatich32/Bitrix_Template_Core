<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

// options from \Aspro\Functions\CAsproMax::showBlockHtml
$arOptions = $arConfig['PARAMS'];

$classList = ['swiper-pagination'];
if ($arOptions['CLASSES']) {
    $classList[] = $arOptions['CLASSES'];
}

$classList = TSolution\Utils::implodeClasses($classList);
?>
<div class="<?= $classList; ?>" style="<?= $arOptions['STYLE']; ?>"></div>

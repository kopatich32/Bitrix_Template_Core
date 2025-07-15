<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}?>

<?// options from \Aspro\Functions\CAsproMax::showBlockHtml?>
<?php
use CMax as Solution;

$arOptions = (array) $arConfig['PARAMS'];
$arItems = $arConfig['PARAMS']['ITEMS'];
$type = $arConfig['PARAMS']['TYPE'];
?>

<div class="tabs_content basket dropdown-product">
    <div class="basket_wrap">
        <div class="items_wrap scrollblock">

            <div class="items scrollbar dropdown-product__items">
                <div class="items">
                    <?php foreach ($arItems as $k => $arItem) { ?>
                        <div class="item dropdown-product__item">
                            <div class="flexbox flexbox--row flexbox--gap flexbox--gap-20" data-item="<?php echo $arItem['JSON_DATA']; ?>">
                                <div class="image">
                                    <div class="dropdown-product__item-image">
                                        <?php if ($arItem['DETAIL_PAGE_URL']) { ?><a href="<?php echo $arItem['DETAIL_PAGE_URL']; ?>" class="thumb flexbox"><?php } ?>
                                        <?php if ($arItem['IMAGE']['src']) { ?>
                                            <img src="<?php echo $arItem['IMAGE']['src']; ?>" alt="<?php echo $arItem['NAME']; ?>" title="<?php echo $arItem['NAME']; ?>" />
                                        <?php } else { ?>
                                            <img src="<?php echo SITE_TEMPLATE_PATH; ?>/images/svg/noimage_product.svg" alt="<?php echo $arItem['NAME']; ?>" title="<?php echo $arItem['NAME']; ?>" width="72" height="72" />
                                        <?php } ?>
                                        <?php if ($arItem['DETAIL_PAGE_URL']) { ?></a><?php } ?>
                                    </div>
                                </div>
                                <div class="body-info">
                                    <div class="description">
                                        <div class="dropdown-product__item-title lineclamp-3 ">
                                            <?php if ($arItem['DETAIL_PAGE_URL']) { ?><a class="dark_link" href="<?php echo $arItem['DETAIL_PAGE_URL']; ?>"><?php } ?><?php echo $arItem['NAME']; ?><?php if ($arItem['DETAIL_PAGE_URL']) { ?></a><?php } ?>
                                        </div>
                                    </div>
                                    <div class="remove-cell" title="<?php echo GetMessage('REMOVE_ITEM'); ?>" data-action="<?php echo $type; ?>"  data-item="<?=$arItem['ID'];?>" data-iblock="<?=$arItem['IBLOCK_ID'];?>">
                                        <span class="remove"><?=Solution::showIconSvg("remove colored_theme_hover_text", SITE_TEMPLATE_PATH.'/images/svg/catalog/cancelfilter.svg', '', '', true, false);?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="dropdown-product-foot">
            <div class="buttons">
                <div class="wrap_button basket_back">
                    <a href="<?php echo $arConfig['PARAMS']['PATH_TO_ALL']; ?>" class="btn btn-transparent-border-color round-ignore btn-lg noborder"><span><?php echo $arConfig['PARAMS']['TITLE_TO_ALL']; ?></span></a>
                </div>
            </div>
        </div>
    </div>
</div>

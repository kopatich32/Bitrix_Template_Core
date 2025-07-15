<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}
$this->setFrameMode(true);
$arParams['TITLE_BLOCK'] = strlen($arParams['TITLE_BLOCK']) ? $arParams['TITLE_BLOCK'] : GetMessage('CATALOG_VIEWED_TITLE');
global $bHeaderStickyMenu;
?>
<!-- noindex -->
<?php
if (strlen($arResult['ERROR'])) {
    ShowError($arResult['ERROR']);

    return;
}
if (!$arResult['ITEMS']) {
    return;
}
?>
<div class="viewed-wrapper swipeignore <?=$templateName;?>">
    <div class="font_lg viewed-title option-font-bold darken"><?=$arParams['TITLE_BLOCK'];?></div>
    <?php
    $arOptions = [
        // Disable preloading of all images
        'preloadImages' => false,
        // Enable lazy loading
        'lazy' => false,
        'keyboard' => true,
        'init' => false,
        'rewind' => true,

        'freeMode' => [
            'enabled' => true,
            'momentum' => true,
            'sticky' => true,
        ],
        'watchSlidesProgress' => true, // fix slide on click on slide link in mobile template
        'slidesPerView' => 'auto',
        'spaceBetween' => 10,
        // 'pagination' => false,
        'type' => 'banners_in_header',
        'breakpoints' => [
            '601' => [
                'slidesPerView' => 2,
                'freeMode' => false,
            ],
            '768' => [
                'slidesPerView' => 3,
                'freeMode' => false,
            ],
            '992' => [
                'slidesPerView' => ($bHeaderStickyMenu ? '3' : '4'),
                'freeMode' => false,
            ],
            '1200' => [
                'slidesPerView' => ($bHeaderStickyMenu ? '4' : '5'),
                'freeMode' => false,
            ],
        ],
    ];
    ?>
    <div class="swiper-nav-offset relative">
        <div class="block-items1 swiper slider-solution swipeignore loading_state block-items--margined appear-block" data-plugin-options='<?=Bitrix\Main\Web\Json::encode($arOptions);?>'>
            <div class="swiper-wrapper no-shrinked">
                <?foreach ($arResult['ITEMS'] as $key => $arItem):?>
                    <?php
                    if ($key > 7) {
                        continue;
                    }
                    $isItem = (isset($arItem['PRODUCT_ID']) ? true : false);
                    ?>
                    <div class="swiper-slide block-item bordered rounded3<?=$isItem ? ' box-shadow-sm' : '';?>">
                        <?if ($isItem):?>
                            <div data-id=<?=$arItem['PRODUCT_ID'];?> data-picture='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arItem['PICTURE']));?>' class="item_wrap item block-item__wrapper<?=$isItem ? ' has-item' : '';?>" id=<?=$this->GetEditAreaId($arItem['PRODUCT_ID']);?>>
                                <?php
                                $this->AddEditAction($arItem['PRODUCT_ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT'));
                                $this->AddDeleteAction($arItem['PRODUCT_ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')]);
                                ?>
                                <div class="block-item__inner flexbox flexbox--row flexbox--align-normal">
                                    <div class="block-item__image block-item__image--wh90 skeleton"></div>
                                    <div class="block-item__info item_info flex1 flexbox gap gap--12">
                                        <div class="skeleton skeleton-item--title"></div>
                                        <div class="skeleton skeleton-item--price"></div>
                                    </div>
                                </div>
                            </div>
                        <?else:?>
                            <div class="item_wrap item block-item__wrapper"></div>
                        <?endif;?>
                    </div>
                <?endforeach;?>
            </div>
        </div>
        <?php
        TSolution\Functions::showBlockHtml([
            'FILE' => 'ui/slider-pagination.php',
            'PARAMS' => [
                'CLASSES' => 'swiper-pagination--small swiper-pagination--static visible-xs',
            ],
        ]);

        TSolution\Functions::showBlockHtml([
            'FILE' => 'ui/slider-navigation.php',
            'PARAMS' => [
                'CLASSES' => 'swiper-button-lock',
            ],
        ]);
        ?>
    </div>
</div>
<?TSolution\Functions::showBonusComponentList($arResult);?>
<script type="text/javascript">
    BX.message({
        LAST_ACTIVE_FROM_VIEWED: '<?=$arResult['LAST_ACTIVE_FROM'];?>',
        SHOW_MEASURE_VIEWED: '<?=$arParams['SHOW_MEASURE'] !== 'N' ? 'true' : '';?>',
        SITE_TEMPLATE_PATH: '<?=SITE_TEMPLATE_PATH;?>',
        CATALOG_FROM_VIEWED: '<?=GetMessage('CATALOG_FROM');?>',
        SITE_ID: '<?=SITE_ID;?>',
        EMPTY_PRICE_TEXT_VIEWED: '<?=$arResult['TEXT_FOR_EMPTY_PRICE'];?>',
        USE_BONUS_VIEWED: '<?=TSolution\Functions::isBonusSystemOn() ? 'Y' : 'N';?>',
    })
    var lastViewedTime = BX.message('LAST_ACTIVE_FROM_VIEWED');
    var bShowMeasure = BX.message('SHOW_MEASURE_VIEWED');
    var $viewedSlider = $('.viewed-wrapper .block-item');
    var $viewedEmptyPriceText = BX.message('EMPTY_PRICE_TEXT_VIEWED');
    var bShowBonusViewed = BX.message('USE_BONUS_VIEWED');
    var sMissingGoodsPriceDisplay = '<?=$arResult['MISSING_GOODS_PRICE_DISPLAY'];?>';

    BX.Aspro.Loader.once({
        appear: ['.viewed_product_block'],
        add: {
            ext: ['swiper', 'skeleton'],
            js: '<?=$GLOBALS['APPLICATION']->oAsset->getFullAssetPath($this->__folder.'/template.js')?>',
        }
    }).then(() => {
        showViewedItems(lastViewedTime, bShowMeasure, $viewedSlider);
        typeof initSwiperSlider === "function" && initSwiperSlider();
    });
</script>
<!-- /noindex -->

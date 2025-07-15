<?
namespace Aspro\Functions;

use Bitrix\Main\Application;
use Bitrix\Main\Web\DOM\Document;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\DOM\CssParser;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Directory;
use \Bitrix\Catalog;
use \Bitrix\Currency;

Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');

if(!defined('FUNCTION_MODULE_ID'))
    define('FUNCTION_MODULE_ID', 'aspro.max');

if(!class_exists("CAsproMaxItem"))
{
    class CAsproMaxItem{

        public static function getCurrentPrice($price_field, $arPrice, $bFromPrice = true){
            $val = '';
            if($bFromPrice)
            {
                $format_value = \CCurrencyLang::CurrencyFormat($arPrice[$price_field], $arPrice['CURRENCY'], false);
                if(isset($arPrice["PRINT_".$price_field]))
                {
                    $specCurrency = false;
                    $specPrice = $arPrice["PRINT_".$price_field];
                    if(preg_match('/\s*&#\d+\s*/', $specPrice, $match)){
                        $specCurrency = $match[0];
                        $specPrice = preg_replace('/\s*&#\d+\s*/', '#CURRENCY#', $specPrice);
                    }

                    if(strpos($specPrice, $format_value) !== false):
                        $val = str_replace($format_value, '<span class="price_value">'.$format_value.'</span><span class="price_currency">', $specPrice.'</span>');
                    else:
                        $val = $specPrice;
                    endif;

                    if($specCurrency){
                        $val = str_replace('#CURRENCY#', $specCurrency, $val);
                    }
                }
                else
                {
                    $val = '<span class="price_value">'.\CCurrencyLang::CurrencyFormat($arPrice[$price_field], $arPrice['CURRENCY']).'</span><span class="price_currency">';
                }
            }
            else
            {
                $format_value_raw = \CCurrencyLang::CurrencyFormat($price_field, $arPrice['CURRENCY']);
                $format_value = \CCurrencyLang::CurrencyFormat($price_field, $arPrice['CURRENCY'], false);

                if(strpos($format_value_raw, $format_value) !== false):
                    $val = str_replace($format_value, '<span class="price_value">'.$format_value.'</span><span class="price_currency">', $format_value_raw.'</span>');
                else:
                    $val = $format_value_raw;
                endif;
            }

            return $val;
        }

        public static function showStickers($arParams = array(), $arItem = array(), $bShowFavItem = false, $wrapper = ''){
            if($arItem):?>
                <?
                ob_start();

                $favItem = (isset($arParams["FAV_ITEM"]) && $arParams["FAV_ITEM"] ? $arParams["FAV_ITEM"] : "FAVORIT_ITEM");
                $finalPrice = (isset($arParams["FINAL_PRICE"]) && $arParams["FINAL_PRICE"] ? $arParams["FINAL_PRICE"] : "FINAL_PRICE");
                $prop = (isset($arParams["STIKERS_PROP"]) && $arParams["STIKERS_PROP"] ? $arParams["STIKERS_PROP"] : "HIT");
                $saleSticker = (isset($arParams["SALE_STIKER"]) && $arParams["SALE_STIKER"] ? $arParams["SALE_STIKER"] : "SALE_TEXT");
                ?>
                <?if(
                    (
                        isset($arItem["PROPERTIES"][$favItem]["VALUE"]) &&
                        $arItem["PROPERTIES"][$favItem]["VALUE"] &&
                        $bShowFavItem
                    ) ||
                    (
                        isset($arItem["PROPERTIES"][$finalPrice]["VALUE"]) &&
                        $arItem["PROPERTIES"][$finalPrice]["VALUE"]
                    ) ||
                    \CMax::GetItemStickers($arItem["PROPERTIES"][$prop] ?? []) ||
                    (
                        isset($arItem["PROPERTIES"][$saleSticker]["VALUE"]) &&
                        $arItem["PROPERTIES"][$saleSticker]["VALUE"]
                    )
                ):?>
                    <?if($wrapper):?>
                        <div class="<?=$wrapper?>">
                    <?endif;?>
                    <div class="stickers custom-font">
                        <?if(
                            isset($arItem["PROPERTIES"][$favItem]["VALUE"]) &&
                            $arItem["PROPERTIES"][$favItem]["VALUE"] &&
                            $bShowFavItem
                        ):?>
                            <div><div class="sticker_sale_text font_sxs rounded2"><?=$arItem["PROPERTIES"][$favItem]["NAME"];?></div></div>
                        <?endif;?>

                        <?if(
                            isset($arItem["PROPERTIES"][$finalPrice]["VALUE"]) &&
                            $arItem["PROPERTIES"][$finalPrice]["VALUE"]
                        ):?>
                            <div><div class="sticker_sale_text font_sxs rounded2"><?=$arItem["PROPERTIES"][$finalPrice]["NAME"];?></div></div>
                        <?endif;?>

                        <?foreach(\CMax::GetItemStickers($arItem["PROPERTIES"][$prop]) as $arSticker):?>
                            <div><div class="<?=$arSticker['CLASS']?> font_sxs rounded2"><?=$arSticker['VALUE']?></div></div>
                        <?endforeach;?>

                        <?if(
                            isset($arItem["PROPERTIES"][$saleSticker]["VALUE"]) &&
                            $arItem["PROPERTIES"][$saleSticker]["VALUE"]
                        ):?>
                            <div><div class="sticker_sale_text font_sxs rounded2"><?=$arItem["PROPERTIES"][$saleSticker]["VALUE"];?></div></div>
                        <?endif;?>
                    </div>
                    <?if($wrapper):?>
                        </div>
                    <?endif;?>
                <?endif;?>
                <?$html = ob_get_contents();
                ob_end_clean();

                foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproShowStickers', true) as $arEvent) // event for manipulation item stickers
                    ExecuteModuleEventEx($arEvent, array($arParams, $arItem, &$html));

                echo $html;?>
            <?endif;?>
        <?}

        public static function showImg($arParams = array(), $arItem = array(), $bShowFW = true, $bWrapLink = true, $dopClassImg = ''){
            if($arItem):?>
                <?ob_start();?>
                <?if($bWrapLink):?>
                <a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="thumb shine">
                <?endif;?>
                    <?
                    $bNeedFindSkuPicture = empty($arItem["DETAIL_PICTURE"]) && empty($arItem["PREVIEW_PICTURE"]) && (\CMax::GetFrontParametrValue("SHOW_FIRST_SKU_PICTURE") == "Y") &&  isset($arItem['OFFERS']) && !empty($arItem['OFFERS']);
                    if($bNeedFindSkuPicture){
                        $arFirstSkuPicture = array();
                        foreach ($arItem['OFFERS'] as $keyOffer => $arOffer) {
                            if (!empty($arOffer['PREVIEW_PICTURE'])) {
                                $arFirstSkuPicture = $arOffer['PREVIEW_PICTURE'];
                                if (!is_array($arFirstSkuPicture)){
                                    $arFirstSkuPicture = \CFile::GetFileArray($arOffer['PREVIEW_PICTURE']);
                                }
                            } elseif (!empty($arOffer['DETAIL_PICTURE'])){
                                $arFirstSkuPicture = $arOffer['DETAIL_PICTURE'];
                                if (!is_array($arFirstSkuPicture)){
                                    $arFirstSkuPicture = \CFile::GetFileArray($arOffer['DETAIL_PICTURE']);
                                }
                            }

                            if(isset($arFirstSkuPicture["ID"])){
                                $arFirstSkuPicture = \CFile::ResizeImageGet($arFirstSkuPicture["ID"], array( "width" => 350, "height" => 350 ), BX_RESIZE_IMAGE_PROPORTIONAL,true );
                            }

                            if(!empty( $arFirstSkuPicture )){
                                break;
                            }
                        }
                    }

                    $img = [];
                    if (!empty($arItem["PREVIEW_PICTURE"])) {
                        if (is_array($arItem["PREVIEW_PICTURE"])) {
                            $img = $arItem["PREVIEW_PICTURE"];
                            $img['SRC'] = $arItem["PREVIEW_PICTURE"]["src"] ?? $arItem["PREVIEW_PICTURE"]["SRC"];
                        } else {
                            $img = \CFile::GetFileArray($arItem['PREVIEW_PICTURE']);
                        }
                    }
                    elseif (!empty($arItem["DETAIL_PICTURE"])) {
                        if (!isset($arItem["DETAIL_PICTURE"]["src"])) {
                            $arItem["DETAIL_PICTURE"] = \CFile::ResizeImageGet($arItem["DETAIL_PICTURE"], array( "width" => 350, "height" => 350 ), BX_RESIZE_IMAGE_PROPORTIONAL,true );
                        }

                        if (is_array($arItem["DETAIL_PICTURE"])) {
                            $img = $arItem["DETAIL_PICTURE"];
                            $img['SRC'] = $arItem["DETAIL_PICTURE"]["src"] ?? $arItem["DETAIL_PICTURE"]["SRC"];
                        } else {
                            $img = \CFile::GetFileArray($arItem['DETAIL_PICTURE']);
                        }
                    }
                    elseif ($bNeedFindSkuPicture && !empty($arFirstSkuPicture)) {
                        $img = $arFirstSkuPicture;
                        $img['SRC'] = $arFirstSkuPicture['src'] ?? $arFirstSkuPicture['SRC'];
                    }

                    if (
                        is_array($img) &&
                        $img['ID'] &&
                        isset($arItem['ALT_TITLE_GET']) &&
                        $arItem['ALT_TITLE_GET'] != 'SEO' &&
                        !array_key_exists('DESCRIPTION', $img)
                    ) {
                        // restore original picture description
                        $img = \CFile::GetFileArray($img['ID']);
                    }

                    $alt = (strlen($img['DESCRIPTION']) ? $img['DESCRIPTION'] : $arItem["NAME"]);
                    $title = (strlen($img['DESCRIPTION']) ? $img['DESCRIPTION'] : $arItem["NAME"]);

                    if(isset($arItem['ALT_TITLE_GET']) && $arItem['ALT_TITLE_GET'] == 'SEO') {
                        $alt = (isset($arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"]) && $arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] ? $arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] : (isset($arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"]) && $arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] ? $arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] : $arItem["NAME"]));

                        $title = (isset($arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]) && $arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] ? $arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] : (isset($arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]) && $arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] ? $arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] : $arItem["NAME"]));
                    }
                    ?>
                    <? if ($img): ?>
                        <img class="lazy img-responsive <?=$dopClassImg;?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($img["SRC"])?>" data-src="<?=$img["SRC"]?>" alt="<?=$alt;?>" title="<?=$title;?>" />
                    <? else: ?>
                        <img class="lazy img-responsive <?=$dopClassImg;?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg(SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg');?>" data-src="<?=SITE_TEMPLATE_PATH?>/images/svg/noimage_product.svg" alt="<?=$alt;?>" title="<?=$title;?>" />
                    <? endif; ?>
                    <?if($fast_view_text_tmp = \CMax::GetFrontParametrValue('EXPRESSION_FOR_FAST_VIEW'))
                        $fast_view_text = $fast_view_text_tmp;
                    else
                        $fast_view_text = Loc::getMessage('FAST_VIEW');?>
                <?if($bWrapLink):?>
                </a>
                <?endif;?>
                <?if($bShowFW):?>
                    <div class="fast_view_block wicons rounded2" data-event="jqm" data-param-form_id="fast_view" data-param-iblock_id="<?=$arParams["IBLOCK_ID"];?>" data-param-id="<?=$arItem["ID"];?>" data-param-item_href="<?=urlencode($arItem["DETAIL_PAGE_URL"]);?>" data-name="fast_view"><?=\CMax::showIconSvg("fw ncolor", SITE_TEMPLATE_PATH."/images/svg/quickview.svg");?><?=$fast_view_text;?></div>
                <?endif;?>
                <?$html = ob_get_contents();
                ob_end_clean();

                foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproShowImg', true) as $arEvent) // event for manipulation item img
                    ExecuteModuleEventEx($arEvent, array($arParams, $arItem, $bShowFW, $bWrapLink, $dopClassImg, &$html));

                echo $html;?>
            <?endif;?>
        <?}

        public static function showSectionImg($arParams = array(), $arItem = array(), $bIcons = false){
            if($arItem):?>
                <?ob_start();?>
                    <?if($bIcons && $arItem["UF_CATALOG_ICON"]):?>
                        <?$img = \CFile::ResizeImageGet($arItem["UF_CATALOG_ICON"], array( "width" => 40, "height" => 40 ), BX_RESIZE_IMAGE_EXACT, true );?>
                        <a href="<?=$arItem["SECTION_PAGE_URL"]?>" class="thumb">
                            <?if(strpos($img["src"], ".svg") !== false && \CMax::GetFrontParametrValue('COLORED_CATALOG_ICON') === 'Y'):?>
                                <?=\Aspro\Functions\CAsproMax::showSVG([
                                    'PATH' => $img["src"]
                                ]);?>
                            <?else:?>
                                <img class="lazy img-responsive" data-src="<?=$img["src"]?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($img["src"]);?>" alt="<?=($arItem["PICTURE"]["ALT"] ? $arItem["PICTURE"]["ALT"] : $arItem["NAME"])?>" title="<?=($arItem["PICTURE"]["TITLE"] ? $arItem["PICTURE"]["TITLE"] : $arItem["NAME"])?>" />
                            <?endif;?>
                        </a>
                    <?else:?>
                        <?if($arItem["PICTURE"]["SRC"]):?>
                            <?$img = \CFile::ResizeImageGet($arItem["PICTURE"]["ID"], array( "width" => 120, "height" => 120 ), BX_RESIZE_IMAGE_EXACT, true );?>
                            <a href="<?=$arItem["SECTION_PAGE_URL"]?>" class="thumb shine"><img class="lazy img-responsive" data-src="<?=$img["src"]?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($img["src"]);?>" alt="<?=($arItem["PICTURE"]["ALT"] ? $arItem["PICTURE"]["ALT"] : $arItem["NAME"])?>" title="<?=($arItem["PICTURE"]["TITLE"] ? $arItem["PICTURE"]["TITLE"] : $arItem["NAME"])?>" /></a>
                        <?elseif($arItem["~PICTURE"]):?>
                            <?$img = \CFile::ResizeImageGet($arItem["~PICTURE"], array( "width" => 120, "height" => 120 ), BX_RESIZE_IMAGE_EXACT, true );?>
                            <a href="<?=$arItem["SECTION_PAGE_URL"]?>" class="thumb shine"><img class="lazy img-responsive" data-src="<?=$img["src"]?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($img["src"]);?>" alt="<?=($arItem["PICTURE"]["ALT"] ? $arItem["PICTURE"]["ALT"] : $arItem["NAME"])?>" title="<?=($arItem["PICTURE"]["TITLE"] ? $arItem["PICTURE"]["TITLE"] : $arItem["NAME"])?>" /></a>
                        <?else:?>
                            <a href="<?=$arItem["SECTION_PAGE_URL"]?>" class="thumb"><img class="lazy img-responsive" data-src="<?=SITE_TEMPLATE_PATH?>/images/svg/noimage_product.svg" src="<?=\Aspro\Functions\CAsproMax::showBlankImg(SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg');?>" alt="<?=$arItem["NAME"]?>" title="<?=$arItem["NAME"]?>" /></a>
                        <?endif;?>
                    <?endif;?>
                <?$html = ob_get_contents();
                ob_end_clean();

                foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproShowSectionImg', true) as $arEvent) // event for manipulation item img
                    ExecuteModuleEventEx($arEvent, array($arParams, $arItem, $bIcons, &$html));

                echo $html;?>
            <?endif;?>
        <?}

        public static function showSectionGallery( $params = array() ){
            $arItem = isset($params['ITEM']) ? $params['ITEM'] : array();
            $key = isset($params['GALLERY_KEY']) ? $params['GALLERY_KEY'] : 'GALLERY';
            $bReturn = isset($params['RETURN']) ? $params['RETURN'] : false;
            $arResize = isset($params['RESIZE']) && !empty($params['RESIZE']) ? $params['RESIZE'] : array('WIDTH' => 600, 'HEIGHT' => 600);
            if($arItem):?>
                <?ob_start();?>
                    <?if($arItem[$key]):?>
                        <?$count = count($arItem[$key]);?>
                        <a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="thumb<?=($bReturn ? '' : ($count > 1 ? '' : ' shine'));?>">
                            <span class="section-gallery-wrapper flexbox">
                                <?foreach($arItem[$key] as $i => $arGalleryItem):?>
                                    <?
                                    if($arResize) {
                                        $resizeImage = \CFile::ResizeImageGet($arGalleryItem["ID"], array("width" => $arResize['WIDTH'], "height" => $arResize['HEIGHT']), BX_RESIZE_IMAGE_PROPORTIONAL, true, array());
                                        $arGalleryItem['SRC'] = $resizeImage['src'];
                                        $arGalleryItem['HEIGHT'] = $resizeImage['height'];
                                        $arGalleryItem['WIDTH'] = $resizeImage['width'];
                                    }else{
                                        $arGalleryItem['SRC'] = \CFile::GetPath($arGalleryItem['ID']);
                                    }?>
                                    <span class="section-gallery-wrapper__item<?=(!$i ? ' _active' : '');?>">
                                        <span class="section-gallery-wrapper__item-nav<?=($count > 1 ? ' ' : ' section-gallery-wrapper__item_hidden ');?>"></span>
                                        <img class="img-responsive" src="<?=$arGalleryItem["SRC"];?>" alt="<?=$arGalleryItem["ALT"];?>" title="<?=$arGalleryItem["TITLE"];?>" />
                                    </span>
                                <?endforeach;?>
                            </span>
                        </a>
                    <?else:?>
                        <a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="thumb"><img class="img-responsive " src="<?=SITE_TEMPLATE_PATH?>/images/svg/noimage_product.svg" alt="<?=$arItem["NAME"]?>" title="<?=$arItem["NAME"]?>" /></a>
                    <?endif;?>
                <?$html = ob_get_contents();
                ob_end_clean();

                foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproShowSectionGallery', true) as $arEvent) // event for manipulation item img
                    ExecuteModuleEventEx($arEvent, array($arItem, &$html));

                if(!$bReturn)
                    echo $html;
                else
                    return $html?>
            <?endif;?>
        <?}

        public static function showDelayCompareBtn($arParams = array(), $arItem = array(), $arAddToBasketData = array(), $totalCount, $bUseSkuProps = false, $class = '', $bShowFW = false, $bShowOCB = false, $typeSvg = '', $currentSKUID = '', $currentSKUIBlock = '', $bReturn = false){
            if (!$arItem)
                return;

            $arItem["OFFERS_MORE"] = $arItem["OFFERS_MORE"] ?? 'N';

            $bOutOfProduction = $arAddToBasketData['ACTION'] === 'OUT_OF_PRODUCTION';

            if ($bOutOfProduction) {
                $bShowOCB = false;
            }
        ?>
                <?ob_start();?>
                    <?
                    $i = 0;
                    if($arParams["DISPLAY_WISH_BUTTONS"] == "Y")
                    {
                        if(!$arItem["OFFERS"])
                        {
                            if(\CMax::checkShowDelay($arParams, $totalCount, $arItem))
                                $i++;
                        }
                        elseif($bUseSkuProps)
                        {
                            // if($arAddToBasketData["CAN_BUY"])
                                $i++;
                        }
                    }

                    if($arParams["DISPLAY_COMPARE"] == "Y")
                        $i++;

                    if($arItem["OFFERS_MORE"] != "Y" && $bShowOCB)
                    {
                        if($arAddToBasketData["CAN_BUY"])
                            $i++;
                    }

                    $bWithText = (strpos($class, 'list') !== false);
                    $bWithIcons = (strpos($class, 'icons') !== false);

                    if(!$currentSKUID)
                        $currentSKUID = $arItem["ID"];
                    ?>
                    <div class="like_icons <?=$class;?>" data-size="<?=$i;?>">
                        <?if($arParams["DISPLAY_WISH_BUTTONS"] != "N" || $arParams["DISPLAY_COMPARE"] == "Y"):?>
                            <?if($arParams["DISPLAY_WISH_BUTTONS"] == "Y"):?>
                                <?if(!$arItem["OFFERS"]):?>
                                    <div class="wish_item_button item-action">
                                        <span title="<?=Loc::getMessage('FAVORITE_ITEM')?>" data-title="<?=Loc::getMessage('FAVORITE_ITEM')?>" data-title_added="<?=Loc::getMessage('FAVORITE_ITEM_REMOVE')?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>" class="wish_item to rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?> js-item-action" data-action="favorite" data-item="<?=$arItem["ID"]?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>"><?=\CMax::showIconSvg("wish ncolor colored", SITE_TEMPLATE_PATH."/images/svg/chosen".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=Loc::getMessage('FAVORITE_ITEM')?></span><?endif;?></span>
                                    </div>
                                <?elseif($bUseSkuProps):?>
                                    <div class="wish_item_button item-action">
                                        <span title="<?=Loc::getMessage('FAVORITE_ITEM')?>" data-title="<?=Loc::getMessage('FAVORITE_ITEM')?>" data-title_added="<?=Loc::getMessage('FAVORITE_ITEM_REMOVE')?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>" class="wish_item to <?=$arParams["TYPE_SKU"];?> rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?> js-item-action" data-action="favorite" data-item="<?=$currentSKUID;?>" data-iblock="<?=$currentSKUIBlock?>" data-offers="Y" data-props="<?=$arOfferProps?>"><?=\CMax::showIconSvg("wish ncolor colored", SITE_TEMPLATE_PATH."/images/svg/chosen".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=Loc::getMessage('FAVORITE_ITEM')?></span><?endif;?></span>
                                    </div>
                                <?endif;?>
                            <?endif;?>
                            <?if($arParams["DISPLAY_COMPARE"] == "Y"):?>
                                <div class="compare_item_button item-action">
                                    <span title="<?=Loc::getMessage('CATALOG_COMPARE')?>" data-title="<?=Loc::getMessage('CATALOG_COMPARE')?>" data-title_added="<?=Loc::getMessage('CATALOG_COMPARE_OUT')?>" class="compare_item to rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?> js-item-action" data-action="compare" data-iblock="<?=($currentSKUIBlock ?: $arParams["IBLOCK_ID"]);?>" data-item="<?=$currentSKUID?>" <?if($bUseSkuProps):?>data-offers="Y" data-props="<?=$arOfferProps?>"<?endif;?>><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE')?></span><?endif;?></span>
                                </div>
                                <?/*if(!$bUseSkuProps):?>
                                    <div class="compare_item_button">
                                        <span title="<?=GetMessage('CATALOG_COMPARE')?>" class="compare_item to rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?>" data-iblock="<?=$arParams["IBLOCK_ID"]?>" data-item="<?=$arItem["ID"]?>" ><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE')?></span><?endif;?></span>
                                        <span title="<?=GetMessage('CATALOG_COMPARE_OUT')?>" class="compare_item in added rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_bg');?>" style="display: none;" data-iblock="<?=$arParams["IBLOCK_ID"]?>" data-item="<?=$arItem["ID"]?>"><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE_OUT')?></span><?endif;?></span>
                                    </div>
                                <?elseif($arItem["OFFERS"]):?>
                                    <div class="compare_item_button">
                                        <span title="<?=GetMessage('CATALOG_COMPARE')?>" class="compare_item to <?=$arParams["TYPE_SKU"];?> rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?>" data-item="<?=$currentSKUID;?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>" ><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE')?></span><?endif;?></span>
                                        <span title="<?=GetMessage('CATALOG_COMPARE_OUT')?>" class="compare_item in added <?=$arParams["TYPE_SKU"];?> rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_bg');?>" style="display: none;" data-item="<?=$currentSKUID;?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>"><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE_OUT')?></span><?endif;?></span>
                                    </div>
                                <?endif;*/?>
                            <?endif;?>
                        <?endif;?>
                        <?if($bShowOCB):?>
                            <div class="wrapp_one_click">
                                <?if(/*$arAddToBasketData["ACTION"] == "ADD" &&*/$arItem["OFFERS_MORE"] != "Y" && $arAddToBasketData["CAN_BUY"]):?>
                                        <?if($currentSKUID && $currentSKUIBlock):?>
                                            <span class="rounded3 colored_theme_hover_bg one_click" data-item="<?=$currentSKUID?>" data-iblockID="<?=$currentSKUIBlock?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"];?>" onclick="oneClickBuy('<?=$currentSKUID?>', '<?= $arItem['IBLOCK_ID']; ?>', this)" title="<?=Loc::getMessage('ONE_CLICK_BUY')?>">
                                        <?else:?>
                                            <span class="rounded3 colored_theme_hover_bg one_click" data-item="<?=$arItem["ID"]?>" data-iblockID="<?=$arItem["IBLOCK_ID"]?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"];?>" onclick="oneClickBuy('<?=$arItem["ID"]?>', '<?=$arItem["IBLOCK_ID"]?>', this)" title="<?=Loc::getMessage('ONE_CLICK_BUY')?>">
                                        <?endif;?>
                                            <?=\CMax::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH."/images/svg/quickbuy".$typeSvg.".svg");?>
                                        </span>
                                <?endif;?>
                            </div>
                        <?endif;?>
                        <?if($bShowFW):?>
                            <?if($fast_view_text_tmp = \CMax::GetFrontParametrValue('EXPRESSION_FOR_FAST_VIEW'))
                                $fast_view_text = $fast_view_text_tmp;
                            else
                                $fast_view_text = Loc::getMessage('FAST_VIEW');?>
                            <div class="fast_view_button">
                                <span title="<?=$fast_view_text?>" class="rounded3 colored_theme_hover_bg" data-event="jqm" data-param-form_id="fast_view" data-param-iblock_id="<?=$arParams["IBLOCK_ID"];?>" data-param-id="<?=$arItem["ID"];?>" data-param-item_href="<?=urlencode($arItem["DETAIL_PAGE_URL"]);?>" data-name="fast_view"><?=\CMax::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH."/images/svg/quickview".$typeSvg.".svg");?></span>
                            </div>
                        <?endif;?>
                    </div>
                <?$html = ob_get_contents();
                ob_end_clean();

                foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproShowDelayCompareBtn', true) as $arEvent) // event for manipulation item delay and compare buttons
                    ExecuteModuleEventEx($arEvent, array($arParams, $arItem, $arAddToBasketData, $totalCount, $bUseSkuProps, &$html));
                ?>
            <?
            if ($bReturn)
                return $html;
            else
                echo $html;
            ?>
        <?}

        public static function showItemPricesDefault($arParams = array()){
            if(is_array($arParams) && $arParams):?>
                <?ob_start();?>
                    <div class="with_matrix <?=(\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' ? 'pl' : '');?> <?=($arParams["SHOW_OLD_PRICE"]=="Y" ? 'with_old' : '');?> price_matrix_wrapper " style="display:none;">
                        <div class="prices-wrapper">
                            <div class="price price_value_block font-bold font_mxs"><span class="values_wrapper"></span></div>
                            <?if($arParams["SHOW_OLD_PRICE"]=="Y"):?>
                                <div class="price discount"><span class="values_wrapper font_xs muted"></span></div>
                            <?endif;?>
                        </div>
                        <?if($arParams["SHOW_DISCOUNT_PERCENT"]=="Y"){?>
                            <div class="sale_block matrix" style="display:none;">
                                <div class="sale_wrapper font_xxs">
                                    <?if($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] != "Y"):?>
                                        <div class="inner-sale rounded1">
                                            <div class="text">
                                                <span class="title"><?=Loc::getMessage("CATALOG_ECONOMY");?></span>
                                                <span class="values_wrapper"></span>
                                            </div>
                                        </div>
                                    <?else:?>
                                        <div class="sale-number rounded2">
                                            <div class="value">-<span></span>%</div>
                                            <div class="inner-sale rounded1">
                                                <div class="text">
                                                    <span class="title"><?=Loc::getMessage("CATALOG_ECONOMY");?></span>
                                                    <span class="values_wrapper"></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?endif;?>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        <?}?>
                    </div>
                <?$html = ob_get_contents();
                ob_end_clean();

                foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproItemPricesDefault', true) as $arEvent) // event for manipulation item delay and compare buttons
                    ExecuteModuleEventEx($arEvent, array($arParams, $arItem, $arAddToBasketData, $totalCount, $bUseSkuProps, &$html));

                echo $html;?>
            <?endif;
        }

        public static function getServicePrices($arParams = array(), $arPrices = array(), $product_data = array(), $vatRate = 0){

            if ($handler = \Aspro\Functions\CAsproMax::getCustomFunc(__FUNCTION__)) {
                call_user_func_array($handler, [$arParams, $arPrices, $product_data, $vatRate]);
                return;
            }

            global $USER;
            $userGroups = $USER->GetUserGroupArray();
            $baseCurrency = Currency\CurrencyManager::getBaseCurrency();

            $currencyConvert = $arParams['CONVERT_CURRENCY'] === 'Y';
            $resultCurrency = ($currencyConvert ? $arParams['CURRENCY_ID'] : null);



            $percentVat = $vatRate * 0.01;
            $percentPriceWithVat = 1 + $percentVat;
            $vatInclude = $product_data['VAT_INCLUDED'] === 'Y';

            foreach ($arPrices as $rawPrice)
            {
                $discounts = array();
                $priceType = (int)$rawPrice['CATALOG_GROUP_ID'];
                $price = (float)$rawPrice['PRICE'];
                if (!$vatInclude)
                    $price *= $percentPriceWithVat;
                $currency = $rawPrice['CURRENCY'];

                $changeCurrency = $currencyConvert && $currency !== $resultCurrency;
                if ($changeCurrency)
                {
                    $price = \CCurrencyRates::ConvertCurrency($price, $currency, $resultCurrency);
                    $currency = $resultCurrency;
                }

                if (\CIBlockPriceTools::isEnabledCalculationDiscounts())
                {
                    \CCatalogDiscountSave::Disable();
                    $discounts = \CCatalogDiscount::GetDiscount(
                        $product_data['ID'],
                        $arParams["IBLOCK_ID"],
                        array($priceType),
                        $userGroups,
                        'N',
                        SITE_ID,
                        array()
                    );
                    \CCatalogDiscountSave::Enable();
                }
                $discountPrice = \CCatalogProduct::CountPriceWithDiscount(
                    $price,
                    $currency,
                    $discounts
                );


                if ($discountPrice !== false)
                {
                    $priceWithVat = array(
                        'UNROUND_BASE_PRICE' => $price,
                        'UNROUND_PRICE' => $discountPrice,
                        'BASE_PRICE' => Catalog\Product\Price::roundPrice(
                            $priceType,
                            $price,
                            $currency
                        ),
                        'PRICE' => Catalog\Product\Price::roundPrice(
                            $priceType,
                            $discountPrice,
                            $currency
                        )
                    );

                    $price /= $percentPriceWithVat;
                    $discountPrice /= $percentPriceWithVat;

                    $priceWithoutVat = array(
                        'UNROUND_BASE_PRICE' => $price,
                        'UNROUND_PRICE' => $discountPrice,
                        'BASE_PRICE' => Catalog\Product\Price::roundPrice(
                            $priceType,
                            $price,
                            $currency
                        ),
                        'PRICE' => Catalog\Product\Price::roundPrice(
                            $priceType,
                            $discountPrice,
                            $currency
                        )
                    );

                    if ($arParams['PRICE_VAT_INCLUDE'] === 'Y')
                        $priceRow = $priceWithVat;
                    else
                        $priceRow = $priceWithoutVat;

                    $priceRow['ID'] = $rawPrice['ID'];
                    $priceRow['PRICE_TYPE_ID'] = $rawPrice['CATALOG_GROUP_ID'];
                    $priceRow['CURRENCY'] = $currency;

                    if (
                        empty($discounts)
                        || ($priceRow['BASE_PRICE'] <= $priceRow['PRICE'])
                    )
                    {
                        $priceRow['BASE_PRICE'] = $priceRow['PRICE'];
                        $priceRow['DISCOUNT'] = 0;
                        $priceRow['PERCENT'] = 0;
                    }
                    else
                    {
                        $priceRow['DISCOUNT'] = $priceRow['BASE_PRICE'] - $priceRow['PRICE'];
                        $priceRow['PERCENT'] = roundEx(100*$priceRow['DISCOUNT']/$priceRow['BASE_PRICE'], 0);
                    }


                    $priceRow['QUANTITY_FROM'] = $rawPrice['QUANTITY_FROM'];
                    $priceRow['QUANTITY_TO'] = $rawPrice['QUANTITY_TO'];
                    //$priceRow['QUANTITY_HASH'] = $this->getQuantityRangeHash($rawPrice);
                    $priceRow['MEASURE_RATIO_ID'] = $rawPrice['MEASURE_RATIO_ID'];
                    $priceRow['PRICE_SCALE'] = \CCurrencyRates::ConvertCurrency(
                        $priceRow['PRICE'],
                        $priceRow['CURRENCY'],
                        $baseCurrency
                    );

                    if ($minimalPrice === null || $minimalPrice['PRICE_SCALE'] > $priceRow['PRICE_SCALE'])
                        $minimalPrice = $priceRow;

                }

            }

            foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproItemGetServicePrices', true) as $arEvent) // event for manipulation item prices
                ExecuteModuleEventEx($arEvent, array($arParams, $arPrices, $product_data));

            return $minimalPrice ? $minimalPrice : 0;

        }



        public static function showItemPrices($arParams = array(), $arPrices = array(), $strMeasure = '', &$price_id = 0, $bShort = 'N', $popup = false, $bReturn = false){
            $price_id = 0;
            $sMissingGoodsPriceDisplay = \CMax::GetFrontParametrValue('MISSING_GOODS_PRICE_DISPLAY');
            $sMissingGoodsPriceDisplayText = \CMax::GetFrontParametrValue('MISSING_GOODS_PRICE_DISPLAY_TEXT_VALUE');
            $html = "";

            if((is_array($arParams) && $arParams)&& (is_array($arPrices) && $arPrices))
            {
                if(!$popup)
                    ob_start();

                $sDiscountPrices = \Bitrix\Main\Config\Option::get(FUNCTION_MODULE_ID, 'DISCOUNT_PRICE');
                $arDiscountPrices = array();
                if($sDiscountPrices)
                    $arDiscountPrices = array_flip(explode(',', $sDiscountPrices));
                if(!$popup)
                {
                    $iCountPriceGroup = 0;
                    foreach($arPrices as $key => $arPrice)
                    {
                        if($arPrice['CAN_ACCESS'])
                        {
                            $iCountPriceGroup++;
                            if($arPrice["MIN_PRICE"] == "Y" && $arPrice['CAN_BUY'] == 'Y'){
                                $price_id = $arPrice["PRICE_ID"];
                            }
                        }
                    }
                    if (!$price_id) {
                        $result = false;
                        $minPrice = 0;

                        /*get currency for convert*/
                        $arCurrencyParams = array();
                        if ("Y" == $arParams["CONVERT_CURRENCY"])
                        {
                            if(\CModule::IncludeModule("currency"))
                            {
                                $arCurrencyInfo = \CCurrency::GetByID($arParams["CURRENCY_ID"]);
                                if (is_array($arCurrencyInfo) && !empty($arCurrencyInfo))
                                {
                                    $arCurrencyParams["CURRENCY_ID"] = $arCurrencyInfo["CURRENCY"];
                                }
                            }
                        }
                        /**/
                        foreach($arPrices as $key => $arPrice)
                        {
                            if (empty($result))
                            {
                                $minPrice = (!$arCurrencyParams['CURRENCY_ID']
                                    ? $arPrice['DISCOUNT_VALUE']
                                    : \CCurrencyRates::ConvertCurrency($arPrice['DISCOUNT_VALUE'], $arPrice['CURRENCY'], $arCurrencyParams['CURRENCY_ID'])
                                );
                                $result = $price_id = $arPrice["PRICE_ID"];
                            }
                            else
                            {
                                $comparePrice = (!$arCurrencyParams['CURRENCY_ID']
                                    ? $arPrice['DISCOUNT_VALUE']
                                    : \CCurrencyRates::ConvertCurrency($arPrice['DISCOUNT_VALUE'], $arPrice['CURRENCY'], $arCurrencyParams['CURRENCY_ID'])
                                );
                                if ($minPrice > $comparePrice && $arPrice['CAN_BUY'] == 'Y')
                                {
                                    $minPrice = $comparePrice;
                                    $result = $price_id = $arPrice["PRICE_ID"];
                                }
                            }
                        }
                    }
                    if( (\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' || $arParams['ONLY_POPUP_PRICE'] == 'Y') && $arParams['SHOW_POPUP_PRICE'] != 'N')
                    {
                        if (isset($arPrices[0]) && !$arPrices[0] && $sMissingGoodsPriceDisplay === 'TEXT') {?>
                        <div class="price_matrix_wrapper">
                            <div class="prices-wrapper">
                                <div class="price font-bold font_mxs">
                                    <?= $sMissingGoodsPriceDisplayText; ?>
                                </div>
                            </div>
                        </div>
                        <? }elseif( !(isset($arPrices[0]) && !$arPrices[0] && $sMissingGoodsPriceDisplay === 'NOTHING') ) {
                            foreach($arPrices as $key => $arPrice){
                                if($price_id == $arPrice["PRICE_ID"]):?>
                                    <?if($iCountPriceGroup > 1):?>
                                        <div class="js-show-info-block more-item-info rounded3 bordered-block text-center"><?=\CMax::showIconSvg("fw", SITE_TEMPLATE_PATH."/images/svg/dots.svg");?></div>
                                    <?endif;?>
                                    <?if(!$arParams['HIDE_PRICE']):?>
                                        <?=self::showItemPrices($arParams, array($arPrice), $strMeasure, $price_id, $bShort, true)?>
                                    <?endif;?>
                                <?endif;
                            }
                        }
                        ?>
                        <div class="js-info-block rounded3">
                            <div class="block_title text-upper font_xs font-bold">
                                <?=Loc::getMessage('T_JS_PRICES_MORE')?>
                                <?=\CMax::showIconSvg("close", SITE_TEMPLATE_PATH."/images/svg/Close.svg");?>
                            </div>
                            <div class="block_wrap">
                                <div class="block_wrap_inner prices <?=($iCountPriceGroup > 1 ? 'scrollblock' : '')?>">
                        <?
                    }
                }
                foreach($arPrices as $key => $arPrice):?>
                    <?if($arPrice["CAN_ACCESS"]):?>
                        <?
                        $arPriceGroup = \CCatalogGroup::GetByID($arPrice["PRICE_ID"]);
                        $bMinPrice = (!$price_id && $arPrice['MIN_PRICE'] == 'Y') || ($price_id && ($price_id == $arPrice["PRICE_ID"]));
                        ?>
                        <?if($iCountPriceGroup > 1):?>
                            <div class="price_group <?=($arPrice['MIN_PRICE'] == 'Y' ? 'min ' : '');?><?=$arPriceGroup['XML_ID']?>"><div class="price_name muted font_xs"><?=$arPriceGroup["NAME_LANG"]?></div>
                        <?endif;?>
                        <div class="price_matrix_wrapper <?=($arDiscountPrices && $iCountPriceGroup > 1 ? (isset($arDiscountPrices[$arPriceGroup['ID']]) ? 'strike_block' : '') : '');?>">
                            <?if($arPrice["VALUE"] > $arPrice["DISCOUNT_VALUE"]):?>
                                <div class="prices-wrapper">
                                    <div class="price font-bold <?=($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs');?>" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["DISCOUNT_VALUE"];?>" <?=($bMinPrice ? ' itemprop="offers" itemscope itemtype="http://schema.org/Offer"' : '')?>>
                                        <?if($bMinPrice):?>
                                            <meta itemprop="price" content="<?=($arPrice['DISCOUNT_VALUE'] ? $arPrice['DISCOUNT_VALUE'] : $arPrice['VALUE'])?>" />
                                            <meta itemprop="priceCurrency" content="<?=$arPrice['CURRENCY']?>" />
                                            <link itemprop="availability" href="http://schema.org/<?=($arPrice['CAN_BUY'] ? 'InStock' : 'OutOfStock')?>" />
                                        <?endif;?>
                                        <?if(strlen($arPrice["PRINT_DISCOUNT_VALUE"])):?>
                                            <?=(($arParams["MESSAGE_FROM"] ?? '') ?: '')?><span class="values_wrapper">
                                                <?=self::getCurrentPrice("DISCOUNT_VALUE", $arPrice);?>
                                            </span><?if (($arParams["SHOW_MEASURE"]=="Y") && $strMeasure):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
                                        <?endif;?>
                                    </div>
                                    <?if($arParams["SHOW_OLD_PRICE"]=="Y"):?>
                                        <div class="price discount" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["VALUE"];?>">
                                            <span class="values_wrapper <?=($arParams['MD_PRICE'] ? 'font_sm' : 'font_xs');?> muted"><?=self::getCurrentPrice("VALUE", $arPrice);?></span>
                                        </div>
                                    <?endif;?>
                                </div>
                                <?if($arParams["SHOW_DISCOUNT_PERCENT"]=="Y"):?>
                                    <div class="sale_block">
                                        <div class="sale_wrapper font_xxs">
                                            <?if($bShort == 'Y'):?>
                                                <div class="inner-sale rounded1">
                                                    <span class="title"><?=Loc::getMessage("CATALOG_ECONOMY");?></span>
                                                    <div class="text"><span class="values_wrapper"><?=self::getCurrentPrice("DISCOUNT_DIFF", $arPrice);?></span></div>
                                                </div>
                                            <?else:?>
                                                <?
                                                if(isset($arPrice["DISCOUNT_DIFF_PERCENT"]))
                                                    $percent = $arPrice["DISCOUNT_DIFF_PERCENT"];
                                                else
                                                    $percent=round(($arPrice["DISCOUNT_DIFF"]/$arPrice["VALUE"])*100, 0);?>
                                                <div class="sale-number rounded2">
                                                    <?if($percent && $percent<100):?>
                                                        <div class="value">-<span><?=$percent;?></span>%</div>
                                                    <?endif;?>
                                                    <div class="inner-sale rounded1">
                                                        <div class="text"><?=Loc::getMessage("CATALOG_ECONOMY");?> <span class="values_wrapper"><?=self::getCurrentPrice("DISCOUNT_DIFF", $arPrice);?></span></div>
                                                    </div>
                                                </div>
                                            <?endif;?>
                                        </div>
                                    </div>
                                <?endif;?>
                            <?else:?>
                                <div class="price font-bold <?=($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs');?>" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["VALUE"];?>">
                                    <? if($arPrice['VALUE'] || $sMissingGoodsPriceDisplay === 'PRICE'): ?>
                                        <?if(strlen($arPrice["PRINT_VALUE"])):?>
                                            <?=($arParams["MESSAGE_FROM"] ? $arParams["MESSAGE_FROM"] : '')?><span class="values_wrapper"><?=self::getCurrentPrice("VALUE", $arPrice);?></span><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
                                        <?endif;?>
                                    <? elseif($sMissingGoodsPriceDisplay === 'TEXT'): ?>
                                        <?= $sMissingGoodsPriceDisplayText; ?>
                                    <? endif; ?>
                                </div>
                            <?endif;?>
                        </div>
                        <?if($iCountPriceGroup > 1):?>
                            </div>
                        <?endif;?>
                    <?endif;?>
                <?endforeach;
                if(!$popup)
                {
                    if( (\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' || $arParams['ONLY_POPUP_PRICE'] == 'Y') && $arParams['SHOW_POPUP_PRICE'] != 'N'):?>
                                </div>
                                <div class="more-btn text-center">
                                    <a href="" class="font_upper colored_theme_hover_bg"><?=Loc::getMessage("MORE_LINK")?></a>
                                </div>
                            </div>
                        </div>
                    <?endif;

                    $html = ob_get_contents();
                    ob_end_clean();

                    foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproItemShowItemPrices', true) as $arEvent) // event for manipulation item prices
                        ExecuteModuleEventEx($arEvent, array($arParams, $arPrices, $strMeasure, &$price_id, $bShort, &$html));
                }
            } elseif ($sMissingGoodsPriceDisplay === 'TEXT') { ?>
                <? if(!$popup) ob_start(); ?>
                <div class="price_matrix_wrapper">
                    <div class="prices-wrapper">
                        <div class="price font-bold font_mxs">
                            <?= $sMissingGoodsPriceDisplayText; ?>
                        </div>
                    </div>
                </div>
                <? if(!$popup) $html = ob_get_clean(); ?>
            <?
            }

            if($bReturn)
                return $html;
            else
                echo $html;
        }

        public static function getFormatedServicePrices($arItem = []){
            $result = '';

            ?>
            <? if( is_array($arItem) ): ?>
                <? ob_start(); ?>

                <div class="prices">
                    <div class="price font_mxs darken font-bold"><?= CurrencyFormat($arItem["BUTTON_RESULT_PRICE"]['PRICE'], $arItem["BUTTON_RESULT_PRICE"]['CURRENCY']); ?></div>

                    <? if(isset($arItem["BUTTON_RESULT_PRICE"]['BASE_PRICE']) && $arItem["BUTTON_RESULT_PRICE"]['BASE_PRICE'] !== $arItem["BUTTON_RESULT_PRICE"]['PRICE']): ?>
                        <div class="price_old muted font_xs"><?= CurrencyFormat($arItem["BUTTON_RESULT_PRICE"]['BASE_PRICE'], $arItem["BUTTON_RESULT_PRICE"]['CURRENCY']); ?></div>
                    <? endif; ?>
                </div>

                <?
                    if(
                        isset($arItem['BUTTON_RESULT_PRICE']['DISCOUNT']) && $arItem['BUTTON_RESULT_PRICE']['DISCOUNT'] > 0
                    ):
                ?>
                    <div class="sale_block">
                        <div class="sale_wrapper font_xxs">
                            <div class="sale-number rounded2">
                                <div class="value">-<span><?= $arItem['BUTTON_RESULT_PRICE']['PERCENT']; ?></span>%</div>
                                <div class="inner-sale rounded1">
                                    <div class="text">
                                        <?= Loc::getMessage("CATALOG_ECONOMY"); ?>
                                        <span class="values_wrapper">
                                            <?= self::getCurrentPrice($arItem['BUTTON_RESULT_PRICE']['DISCOUNT'], $arItem['BUTTON_RESULT_PRICE'], false); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                <? endif; ?>
                <?
                    $result = ob_get_contents();
                    ob_end_clean();
                ?>
            <? endif; ?>
            <?
            return $result;
        }

        public static function getTableSizesPath(array $arResult = [], array $arInherite = []): string
        {
            $resultPath = '';

            if ($arInherite['UF_TABLE_SIZES']) {
                $basePath = str_replace('//', '/', SITE_DIR.'/include/table_sizes/detail_'.strtolower($arInherite['UF_TABLE_SIZES']).'.php');
                if(file_exists($_SERVER['DOCUMENT_ROOT'].$basePath)) {
                    $resultPath = $basePath;
                }

                if($arResult['BRAND_ITEM'] && $arResult['BRAND_ITEM']['CODE']) {
                    $fileName = pathinfo($basePath, PATHINFO_FILENAME);
                    $brandSizesPath = str_replace($fileName, $fileName.'_'.$arResult['BRAND_ITEM']['CODE'], $basePath);
                    if(file_exists($_SERVER['DOCUMENT_ROOT'].$brandSizesPath)) {
                        $resultPath = $brandSizesPath;
                    }
                }
            }

            return $resultPath;
        }
    }
}?>

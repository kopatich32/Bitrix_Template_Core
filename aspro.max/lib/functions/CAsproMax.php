<?
namespace Aspro\Functions;

use \Bitrix\Main\Application,
    \Bitrix\Main\Web\DOM\Document,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Web\DOM\CssParser,
    \Bitrix\Main\Text\HtmlFilter,
    \Bitrix\Main\IO\File,
    \Bitrix\Main\IO\Directory,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\Web\Json,
    \Aspro\Functions\CAsproMaxCRM,
    CMax as Solution,
    CMaxCache as Cache;

Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');

if(!class_exists("CAsproMax"))
{
    class CAsproMax{
        const MODULE_ID = \CMax::moduleID;

        /*function OnAsproShowPriceMatrixHandler($arItem, $arParams, $strMeasure, $arAddToBasketData, &$html){
            // ... some code
        }*/

        /*function OnAsproShowPriceRangeTopHandler($arItem, $arParams, $strMeasure, &$html){
            // ... some code
        }*/

        /*function OnAsproItemShowItemPricesHandler($arParams, $arPrices, $strMeasure, &$price_id, $bShort, &$html){
            // ... some code
        }*/

        /*function OnAsproSkuShowItemPricesHandler($arParams, $arItem, &$item_id, &$min_price_id, $arItemIDs, $bShort, &$html){
            //... some code
        }*/

        /*function OnAsproGetTotalQuantityHandler($arItem, $arParams, &$totalCount){
            //... some code
        }*/

        /*function OnAsproGetTotalQuantityBlockHandler($totalCount, &$arOptions){
            //... some code
        }*/

        /*function OnAsproGetBuyBlockElementHandler($arItem, $totalCount, $arParams, &$arOptions){
            //... some code
        }*/

        //log to file
        public static function set_log($type="log", $path="log_file", $arMess=array()){
            $root = $_SERVER['DOCUMENT_ROOT'].'/upload/logs/'.self::MODULE_ID.'/'.$type.'/';
            if(!file_exists($root) || !is_dir($root))
                mkdir( $root, 0700, true );

            $path_date = $root.date('Y-m').'/';
            if(!file_exists($path_date) || !is_dir($path_date))
                mkdir( $path_date, 0700 );

            file_put_contents($path_date.$path.'.log', date('d-m-Y H-i-s', time()+\CTimeZone::GetOffset())."\n".print_r($arMess, true)."\n", LOCK_EX | FILE_APPEND);
        }

        public static function showRegionList(){
            global $arTheme, $APPLICATION;

            static $regionsList_call;
            $iCalledID = ++$regionsList_call;

            $template = strtolower($arTheme["USE_REGIONALITY"]["DEPENDENT_PARAMS"]["REGIONALITY_VIEW"]["VALUE"]);
            if(
                $arTheme["USE_REGIONALITY"]["DEPENDENT_PARAMS"]["REGIONALITY_SEARCH_ROW"]["VALUE"] == "Y" &&
                $template == "select"
            ) {
                $template = "popup_regions";
            }

            $frame = new \Bitrix\Main\Page\FrameHelper('allregions-list-block'.$iCalledID);
            $frame->begin();

            $APPLICATION->IncludeComponent(
                "aspro:regionality.list.max",
                $template,
                Array(),
                false,
                array('HIDE_ICONS' => 'Y')
            );

            $frame->end();
        }

        public static function showBlankImg($src = false){
            $bUseLazy = (Option::get(self::MODULE_ID, 'USE_LAZY_LOAD', 'Y', SITE_ID) == 'Y' && !\CMax::checkMask(Option::get('aspro.max', 'LAZY_LOAD_EXCEPTIONS', '')) );
            // return (!$src ? 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==': $src);
            return (!$src || $bUseLazy ? SITE_TEMPLATE_PATH.'/images/loaders/double_ring.svg': $src);
        }

        public static function checkActiveFilterPage($sefUrl = ''){
            $arParseStr = [];
            if($sefUrl)
            {
                global $APPLICATION;
                if(isset($sefUrl) && strripos($sefUrl, "#SMART_FILTER_PATH#"))
                {

                    $isSmartFilter = str_replace("#SMART_FILTER_PATH#", "(.*?)", $sefUrl);
                    $isSmartFilter = preg_replace('/^#[a-zA-Z_]+#/i', "", $isSmartFilter);
                    $isSmartFilter = str_replace("/", "\/", $isSmartFilter);
                    preg_match("/".$isSmartFilter."/i", $APPLICATION->GetCurPage(), $arParseStr);
                }
            }
            return $arParseStr;
        }

        public static function getPricesID($arPricesID = array(), $bUsePriceCode = false){
            $arPriceIDs = array();
            if($arPricesID)
            {
                global $USER;
                $arUserGroups = $USER->GetUserGroupArray();

                 if (!is_array($arUserGroups) && (int)$arUserGroups.'|' == (string)$arUserGroups.'|')
                    $arUserGroups = array((int)$arUserGroups);

                if (!is_array($arUserGroups))
                    $arUserGroups = array();

                if (!in_array(2, $arUserGroups))
                    $arUserGroups[] = 2;
                \Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($arUserGroups);

                $cacheKey = 'U'.implode('_', $arUserGroups).implode('_', $arPricesID);
                if (!isset($priceTypeCache[$cacheKey]))
                {
                    if($bUsePriceCode)
                    {
                        $dbPriceType = \CCatalogGroup::GetList(
                            array("SORT" => "ASC"),
                            array("NAME" => $arPricesID)
                            );
                        while($arPriceType = $dbPriceType->Fetch())
                        {
                            $arPricesID[] = $arPriceType["ID"];
                        }
                    }
                    $priceTypeCache[$cacheKey] = array();
                    $priceIterator = \Bitrix\Catalog\GroupAccessTable::getList(array(
                        'select' => array('CATALOG_GROUP_ID'),
                        'filter' => array('@GROUP_ID' => $arUserGroups, 'CATALOG_GROUP_ID' => $arPricesID, 'ACCESS' => array(\Bitrix\Catalog\GroupAccessTable::ACCESS_BUY, \Bitrix\Catalog\GroupAccessTable::ACCESS_VIEW)),
                        'order' => array('CATALOG_GROUP_ID' => 'ASC')
                    ));
                    while ($priceType = $priceIterator->fetch())
                    {
                        $priceTypeId = (int)$priceType['CATALOG_GROUP_ID'];
                        $priceTypeCache[$cacheKey][$priceTypeId] = $priceTypeId;
                        unset($priceTypeId);
                    }
                    unset($priceType, $priceIterator);
                }
                $arPriceIDs = $priceTypeCache[$cacheKey];
            }
            return $arPriceIDs;
        }

        public static function showItemCounter($arAddToBasketData, $id, $arItemIDs, $arParams = array(), $class = '', $svgSize = '', $bWrap = false, $bDetail = false){
            if(is_array($arParams) && $arParams):?>
                <?$bShowCounter = $bDetail ? $arAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_DETAIL"] : $arAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_LIST"] ;?>
                <?ob_start();?>
                    <?if(($bShowCounter && $arAddToBasketData["ACTION"] == "ADD") && $arAddToBasketData["CAN_BUY"]):?>
                        <?if($bWrap):?>
                            <div class="counter_block_inner">
                        <?endif;?>
                        <div class="counter_block <?=$class;?>" data-item="<?=$id;?>">
                            <span class="minus dark-color" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['QUANTITY_DOWN']; ?>" <?=(isset($arAddToBasketData["SET_MIN_QUANTITY_BUY"]) && $arAddToBasketData["SET_MIN_QUANTITY_BUY"] ? "data-min='".$arAddToBasketData["MIN_QUANTITY_BUY"]."'" : "")?>><?=\CMax::showIconSvg("wish ncolor colored1", SITE_TEMPLATE_PATH."/images/svg/minus".$svgSize.".svg");?></span>
                            <input type="text" class="text" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['QUANTITY']; ?>" name="<? echo $arParams["PRODUCT_QUANTITY_VARIABLE"]; ?>" value="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>" />
                            <span class="plus dark-color" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['QUANTITY_UP']; ?>" <?=($arAddToBasketData["MAX_QUANTITY_BUY"] ? "data-max='".$arAddToBasketData["MAX_QUANTITY_BUY"]."'" : "")?>><?=\CMax::showIconSvg("wish ncolor colored1", SITE_TEMPLATE_PATH."/images/svg/plus".$svgSize.".svg");?></span>
                        </div>
                        <?if($bWrap):?>
                            </div>
                        <?endif;?>
                    <?endif;?>
                <?$html = ob_get_contents();
                ob_end_clean();

                foreach(GetModuleEvents(self::MODULE_ID, 'OnAsproShowItemCounter', true) as $arEvent) // event for manipulation item delay and compare buttons
                    ExecuteModuleEventEx($arEvent, array($arAddToBasketData, $arItem, $arItemIDs, $arParams, &$html));

                echo $html;?>
            <?endif;
        }

        public static function showShareBlock($class = ''){?>
            <?ob_start();?>
                <?global $APPLICATION;?>
                <div class="share hover-block <?=$class;?>">
                    <div class="shares-block hover-block__item text-center colored_theme_hover_bg-block">
                        <?=\CMax::showIconSvg("down colored_theme_hover_bg-el-svg", SITE_TEMPLATE_PATH.'/images/svg/share.svg', '', '', true, false);?>
                        <?$APPLICATION->IncludeFile(SITE_DIR."include/share_buttons.php", Array(), Array("MODE" => "html", "NAME" => GetMessage('CT_BCE_CATALOG_SOC_BUTTON')));?>
                    </div>
                </div>
            <?$html = ob_get_contents();
            ob_end_clean();

            foreach(GetModuleEvents(self::MODULE_ID, 'OnAsproShowShareBlock', true) as $arEvent) // event for manipulation item delay and compare buttons
                ExecuteModuleEventEx($arEvent, array(&$html, $class));

            echo $html;?>
        <?}

        public static function getLinkedItems($arItem = array(), $field = "SERVICES", $arParams = array()){
            $arResult = [];
            $arSelect = array("ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL", "PROPERTY_LINK_GOODS_FILTER", "PROPERTY_LINK_GOODS");

            if(!empty($arItem["DISPLAY_PROPERTIES"][$field]["VALUE"]))
            {
                $arResult = $arItem["DISPLAY_PROPERTIES"][$field]["VALUE"];
            }

            if(intVal($arParams["IBLOCK_".$field."_ID"]))
            {
                $arItems = \CMaxCache::CIBLockElement_GetList(array('CACHE' => array("TAG" => \CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_".$field."_ID"]), "GROUP" => "ID")), array("IBLOCK_ID" => $arParams["IBLOCK_".$field."_ID"], "ACTIVE"=>"Y", "ACTIVE_DATE" => "Y"), false, false, $arSelect);

                if($arItems)
                {
                    foreach($arItems as $key => $arItem2)
                    {
                        if($arItem2['~PROPERTY_LINK_GOODS_FILTER_VALUE'])
                        {
                            try{
                                $arTmpGoods = (array)Json::decode($arItem2["~PROPERTY_LINK_GOODS_FILTER_VALUE"]);
                            }
                            catch(\Exception $e){
                                $arTmpGoods = array();
                            }

                            if(
                                array_key_exists('CHILDREN', $arTmpGoods) &&
                                $arTmpGoods['CHILDREN']
                            ){
                                if($arResult[$key])
                                    unset($arResult[$key]);

                                $cond = new \CMaxCondition();
                                try{
                                    $arExGoodsFilter = $cond->parseCondition($arTmpGoods, $arParams);
                                }
                                catch(\Exception $e){
                                    $arExGoodsFilter = array();
                                }
                                $arTmpParams['CUSTOM_FILTER'] = $arExGoodsFilter;


                                if($arTmpParams['CUSTOM_FILTER'])
                                {
                                    $arFilter = array(
                                        "LOGIC" => "AND",
                                        array(
                                            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                                            "ACTIVE"=>"Y",
                                            "ID" => $arItem["ID"],
                                        ),
                                        /*array(
                                            "ID" => $arItem['~PROPERTY_LINK_GOODS_VALUE']
                                        ),*/
                                        $arTmpParams['CUSTOM_FILTER']
                                    );
                                    $arTmpItems = \CMaxCache::CIBLockElement_GetList(array('CACHE' => array("TAG" => \CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_ID"]), "GROUP" => "ID")), $arFilter, false, false, array("ID"));

                                    if($arTmpItems)
                                    {
                                        if($arTmpItems[$arItem['ID']])
                                            $arResult[$arItem2['ID']] = $arItem2['ID'];
                                    }
                                }
                            }
                        }

                        if($arItem2['PROPERTY_LINK_GOODS_VALUE'] && in_array($arItem['ID'], (array)$arItem2['PROPERTY_LINK_GOODS_VALUE']))
                        {
                            $arResult[$arItem2['ID']] = $arItem2['ID'];
                        }
                    }
                }
            }
            return $arResult;
        }

        public static function getShowSites($bFromStatic = true){
            static $cacheSites;

            if(!isset($cacheSites)){
                $cacheSites = array();
            }

            $arSites =& $cacheSites;

            if(!$bFromStatic){
                $arSites = array();
            }

            if(!$arSites){
                $strSites = \CMax::GetFrontParametrValue('SITES_SHOW_IN_SELECTOR');
                $arSites = explode(',', $strSites);
                $arSites = array_diff($arSites, array(''));
            }

            return $arSites;
        }

        public static function showItemOCB($arAddToBasketData, $arResult, $arParams = array(), $bReturn = false, $class="btn-sm"){
            if(is_array($arParams) && $arParams):?>
                <?ob_start();?>
                    <?if($arAddToBasketData["ACTION"] !== "NOTHING"):?>
                        <?
                        $arParams["SHOW_ONE_CLICK_BUY"] = $arParams["SHOW_ONE_CLICK_BUY"] ?? 'Y';
                        ?>
                        <?if($arAddToBasketData["ACTION"] == "ADD" && $arAddToBasketData["CAN_BUY"] && $arParams["SHOW_ONE_CLICK_BUY"]!="N"):?>
                            <div class="wrapp-one-click">
                                <span class="btn btn-transparent-border-color <?=$class;?> type_block transition_bg one_click" data-item="<?=$arResult["ID"]?>" data-iblockID="<?=$arParams["IBLOCK_ID"]?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"];?>" onclick="oneClickBuy('<?=$arResult["ID"]?>', '<?= $arParams["IBLOCK_ID"]; ?>', this)">
                                    <span><?=\GetMessage('ONE_CLICK_BUY')?></span>
                                </span>
                            </div>
                        <?endif;?>
                    <?endif;?>
                <?$html = ob_get_contents();
                ob_end_clean();

                foreach(GetModuleEvents(self::MODULE_ID, 'OnAsproShowItemOCB', true) as $arEvent) // event for manipulation item delay and compare buttons
                    ExecuteModuleEventEx($arEvent, array($arAddToBasketData, $arItem, $arItemIDs, $arParams, &$html));
                if(!$bReturn)
                    echo $html;
                else
                    return $html;?>
            <?endif;
        }

        public static function prepareArray($arFields = array(), $arReplace = array(), $stamp = '_leads'){
            $arTmpFields = array();
            if($arFields && $arReplace)
            {
                foreach($arFields as $key => $value)
                {
                    $key = str_replace($stamp, '', $key);
                    if(in_array($key, $arReplace))
                        $arTmpFields[$key] = $value;
                }
                // $arTmpFields = self::prepareArray($arFields, array('name', 'tags', 'budget'), '_leads');
            }
            return $arTmpFields;
        }

        public static function declOfNum($number, $titles)
        {
            $cases = array (2, 0, 1, 1, 1, 2);
            return $number." ".$titles[ ($number%100>4 && $number%100<20)? 2 : $cases[min($number%10, 5)] ];
        }

        public static function showSideFormLink($formCode = '', $bCheckBlock = false)
        {
            if($formCode && $bCheckBlock):?>
                <div class="form-action">
                    <div class="form-action__inner bordered rounded2 box-shadow animate-load" data-event="jqm" data-param-form_id="<?=$formCode?>" data-name="<?=$formCode?>">
                        <?=\CMax::showIconSvg("icon colored", SITE_TEMPLATE_PATH.'/images/svg/'.strtolower($formCode).'.svg', '', '', true, false);?>
                        <div class="form-action__text font_xs"><?=\GetMessage($formCode)?></div>
                    </div>
                </div>
            <?endif;?>
        <?}

        public static function showSideFormLinkIcons($bWrapper = false, $bReturn = false)
        {?>
            <?ob_start();?>
                <?if($bWrapper):?>
                    <?global $arTheme;?>
                    <div class="basket_fly_forms basket_fill_<?=$arTheme['ORDER_BASKET_COLOR']['VALUE'];?>">
                        <div class="wrap_cont">
                            <div class="opener">
                <?endif;?>
                <?
                $callbackExploded = explode(',', \CMax::GetFrontParametrValue('SHOW_CALLBACK'));
                $bShowCallBackBlock = (in_array('SIDE', $callbackExploded));
                $questionExploded = explode(',', \CMax::GetFrontParametrValue('SHOW_QUESTION'));
                $bShowQuestionBlock = (in_array('SIDE', $questionExploded));
                $reviewExploded = explode(',', \CMax::GetFrontParametrValue('SHOW_REVIEW'));
                $bShowReviewBlock = (in_array('SIDE', $reviewExploded));
                ?>
                <?if($bShowCallBackBlock):?>
                    <div title="<?=GetMessage("CALLBACK");?>" class="colored_theme_hover_text">
                        <span class="forms callback-block animate-load" data-event="jqm" data-param-form_id="CALLBACK" data-name="callback"></span>
                        <div class="wraps_icon_block form callback">
                            <?=\CMax::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/header_icons_srite.svg#callback", "icon ", ['WIDTH' => 18,'HEIGHT' => 18]);?>
                        </div>
                    </div>
                <?endif;?>
                <?if($bShowReviewBlock):?>
                    <div title="<?=GetMessage("REVIEW");?>" class="colored_theme_hover_text">
                        <span class="forms review-block animate-load" data-event="jqm" data-param-form_id="REVIEW" data-name="review"></span>
                        <div class="wraps_icon_block form review">
                            <?=\CMax::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/header_icons_srite.svg#review", "icon ", ['WIDTH' => 19,'HEIGHT' => 20]);?>
                        </div>
                    </div>
                <?endif;?>
                <?if($bShowQuestionBlock):?>
                    <div title="<?=GetMessage("ASK");?>" class="colored_theme_hover_text">
                        <span class="forms ask-block animate-load" data-event="jqm" data-param-form_id="ASK" data-name="ask"></span>
                        <div class="wraps_icon_block ask">
                            <?=\CMax::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/header_icons_srite.svg#ask", "icon ", ['WIDTH' => 19,'HEIGHT' => 14]);?>
                        </div>
                    </div>
                <?endif;?>

                <?if($bWrapper):?>
                            </div>
                        </div>
                    </div>
                <?endif;?>
            <?$html = ob_get_contents();
            ob_end_clean();

            foreach(GetModuleEvents(self::MODULE_ID, 'OnAsproShowSideFormLinkIcons', true) as $arEvent) // event
                ExecuteModuleEventEx($arEvent, array($bWrapper, &$html));
            if(!$bReturn)
                echo $html;
            else
                return $html;?>
        <?}

        public static function formatUsageTime($time){
            $timeFormat = '';
            switch ($time) {
                case 'FEW_WEEKS':
                    $timeFormat = Loc::getMessage('FEW_WEEKS_USE');
                    break;
                case 'FEW_MONTHS':
                    $timeFormat = Loc::getMessage('FEW_MONTHS_USE');
                    break;
                case 'FEW_DAYS':
                    $timeFormat = Loc::getMessage('FEW_DAYS_USE');
                    break;
                default:
                    $timeFormat = Loc::getMessage('FEW_YEAR_USE');
                    break;
            }
            return $timeFormat;
        }
        public static function encode($arItem = array(), $options = null){
            if(class_exists('\Bitrix\Main\Web\Json'))
            {
                if(method_exists('\Bitrix\Main\Web\Json', 'encode'))
                    echo \Bitrix\Main\Web\Json::encode($arItem, $options);
                else
                    echo json_encode($arItem, $options);
            }
            else
            {
                echo json_encode($arItem, $options);
            }
        }

        public static function decode($arItem = array()){
            if(class_exists('\Bitrix\Main\Web\Json'))
            {
                if(method_exists('\Bitrix\Main\Web\Json', 'decode'))
                    echo \Bitrix\Main\Web\Json::decode($arItem);
                else
                    echo json_decode($arItem, true);
            }
            else
            {
                echo json_decode($arItem, true);
            }
        }

        public static function showComments($name){
            global $BLOG_DATA;
            $arPosts = [];
            if ($BLOG_DATA['COMMENT_ID'] && \Bitrix\Main\Loader::includeModule('blog')) {
                $SORT = Array("DATE_PUBLISH" => "DESC", "NAME" => "ASC");
                $arFilter = Array(
                    "BLOG_ID" => $BLOG_DATA['BLOG_DATA']['BLOG_ID'],
                    "POST_ID" => $BLOG_DATA['COMMENT_ID'],
                    "!PUBLISH_STATUS" => "K"
                    );
                $dbPosts = \CBlogComment::GetList(
                        $SORT,
                        $arFilter,
                        false,
                        ['nTopCount' => 4],
                        ['POST_TEXT', 'AUTHOR_NAME', 'AUTHOR_ID', 'AUTHOR_EMAIL', 'USER_LOGIN', 'USER_EMAIL', 'USER_NAME', 'USER_LAST_NAME', 'DATE_CREATE']
                    );

                while ($arPost = $dbPosts->GetNext())
                {
                    if (!$arPost['AUTHOR_NAME']) {
                        if ($arPost['USER_NAME']) {
                            $arPost['AUTHOR_NAME'] = $arPost['USER_NAME'];
                        }
                        if ($arPost['USER_LAST_NAME']) {
                            $arPost['AUTHOR_NAME'] .= ' '.$arPost['USER_LAST_NAME'];
                        }
                        if (!$arPost['AUTHOR_NAME'] && $arPost['AUTHOR_EMAIL']) {
                            $arPost['AUTHOR_NAME'] = $arPost['AUTHOR_EMAIL'];
                        }
                        if (!$arPost['AUTHOR_NAME'] && $arPost['USER_EMAIL']) {
                            $arPost['AUTHOR_NAME'] = $arPost['USER_EMAIL'];
                        }
                        if (!$arPost['AUTHOR_NAME'] && $arPost['USER_LOGIN']) {
                            $arPost['AUTHOR_NAME'] = $arPost['USER_LOGIN'];
                        }
                    }
                    $arPosts[] = $arPost;
                }
            }?>
            <?if ($arPosts):?>
                <?foreach($arPosts as $arPost):?>
                    <div class="hidden" itemprop="review" itemscope itemtype="http://schema.org/Review">
                        <meta itemprop="name" content="<?=strip_tags($name)?>">
                        <span style="display:none" itemprop="author" itemscope itemtype="https://schema.org/Person">
                            <meta itemprop="name" content="<?=$arPost["AUTHOR_NAME"]?>">
                        </span>
                        <meta itemprop="datePublished" content="<?=FormatDate('o-m-d', MakeTimeStamp($arPost['DATE_CREATE']))?>">
                        <span style="display:none" itemprop="itemReviewed" itemscope itemtype="http://schema.org/Thing">
                            <meta itemprop="name" content="<?=strip_tags($name)?>" />
                        </span>

                        <div class="hidden" itemprop="reviewBody">
                            <?=$arPost['POST_TEXT']?>
                        </div>
                    </div>
                <?endforeach;?>
            <?endif;
        }

        public static function showDiscountCounter($totalCount, &$arDiscount = array(), $arQuantityData, $arResult, $strMeasure = '', $type = 'v2', $item_id = 0, $bWrapper = false){?>
            <?if(!$arDiscount && $item_id)
            {
                global $USER;
                $arUserGroups = $USER->GetUserGroupArray();
                $priceTypeId = $arResult['PRICES_ALLOW'] ?? [];
                ?>
                <?$arDiscounts = \CCatalogDiscount::GetDiscountByProduct($item_id, $arUserGroups, "N",  $priceTypeId, SITE_ID);?>

                <?$arDiscount=array();
                if($arDiscounts)
                    $arDiscount=current($arDiscounts);
            }
            $bShowDiscont = ($arDiscount && $arDiscount["ACTIVE_TO"] && time() <= strtotime($arDiscount["ACTIVE_TO"]));

            $bOffers = $arResult["OFFERS"] && $arResult["OFFERS_PROP"];
            $bNeedHideDiscount = !$bShowDiscont && $bOffers;

            //$bNeedHideDiscount = false;
            /*if($arResult["OFFERS"] && $arResult["OFFERS_PROP"] && !$bShowDiscont){
                foreach ($arResult["OFFERS"] as $tmpOffer ) {
                    $arOfferDiscount = \CCatalogDiscount::GetDiscountByProduct($tmpOffer["ID"], $arUserGroups, "N", array(), SITE_ID);
                    if($arOfferDiscount){
                        $bNeedHideDiscount = true;
                        break;
                    }
                }
            }*/

            if( $bShowDiscont || $bOffers):?>
                <?ob_start();?>
                <?if($bWrapper):?>
                    <div class="view_sale_block_wrapper">
                <?endif;?>
                    <div class="view_sale_block <?=$type?> <?=($arQuantityData["HTML"] ? '' : 'wq');?> <?=$bNeedHideDiscount ? 'init-if-visible' : '' ?>" <?=$bNeedHideDiscount ? 'style="display:none;"' : '' ?> >
                        <?if($type != 'compact'):?>
                            <div class="icons">
                                <div class="values">
                                    <span class="item"><?=\CMax::showIconSvg("timer", SITE_TEMPLATE_PATH."/images/svg/timer.svg");?></span>
                                </div>
                            </div>
                        <?endif;?>
                        <div class="count_d_block">
                            <span class="active_to hidden"><?=$arDiscount["ACTIVE_TO"];?></span>
                            <span class="countdown values"><span class="item">0</span><span class="item">0</span><span class="item">0</span><span class="item">0</span></span>
                        </div>
                        <?if($arQuantityData["HTML"]):?>
                            <div class="quantity_block">
                                <div class="values">
                                    <span class="item">
                                        <span class="value" <?=(($arResult["OFFERS"] && isset($arResult["TYPE_SKU"]) && $arResult["TYPE_SKU"] == 'TYPE_1' && $arResult["OFFERS_PROP"]) ? 'style="opacity:0;"' : '')?>><?=$totalCount;?></span>
                                        <span class="text"><?=($strMeasure ? $strMeasure : GetMessage("TITLE_QUANTITY"));?></span>
                                    </span>
                                </div>
                            </div>
                        <?endif;?>
                    </div>
                <?if($bWrapper):?>
                    </div>
                <?endif;?>
                <?$html = ob_get_contents();
                ob_end_clean();

                foreach(GetModuleEvents(self::MODULE_ID, 'OnAsproShowDiscountCounter', true) as $arEvent) // event for manipulation item img
                    ExecuteModuleEventEx($arEvent, array($totalCount, $arDiscount, $arQuantityData, $arResult, $strMeasure, $type, &$html));

                echo $html;?>
            <?endif;?>
        <?}

        public static function showResponsiblePreview($bAll = true){
            ?>
                <?ob_start();?>
                    <?$context = \Bitrix\Main\Application::getInstance()->getContext();
                    $request = $context->getRequest();
                    $bIframeMode = ($request->offsetExists('iframe_mode'));
                    ?>
                    <?if(!$bIframeMode):?>
                        <?$url = ($request->offsetExists('viewurl') ? htmlspecialcharsbx($request['viewurl']) : SITE_DIR);?>
                        <?$device = ($request->offsetExists('device') ? (htmlspecialcharsbx($request['device']) == 'mobile' ? 'mobile' : 'tablet') : '')?>
                        <?if($bAll):?>
                            <style type="text/css">
                                body{margin:0px;background: #fafafa;}
                                body.no-device{overflow: hidden;}
                                .ui-view-wrapper{overflow: auto;/*min-height:100vh;*/}
                                .ui-view-iframe-wrapper{margin: auto;position: relative;transition: all ease 0.1s;}
                                .ui-view-iframe-wrapper[data-type="mobile"]{width: 452px;background: url('assets/img/Iphone.svg') 0px 0px no-repeat;}
                                .ui-view-iframe-wrapper[data-type="mobile"] > div{padding: 62px 48px 27px 30px;}
                                .ui-view-iframe-wrapper[data-type="mobile"] iframe{min-height: 782px;}
                                .ui-view-iframe-wrapper[data-type="mobile"] > div > div{border-radius: 0px 0px 40px 40px;overflow: hidden;-webkit-backface-visibility: hidden;-moz-backface-visibility: hidden;-webkit-transform: translate3d(0, 0, 0); -moz-transform: translate3d(0, 0, 0);}
                                .ui-view-iframe-wrapper[data-type="tablet"]{width: 860px;background: url('assets/img/Ipad.svg') 0px 0px no-repeat;}
                                .ui-view-iframe-wrapper[data-type="tablet"] > div{padding: 47px 48px 47px 44px;}
                                .ui-view-iframe-wrapper[data-type="tablet"] > div > div{border-radius: 15px;overflow: hidden;-webkit-backface-visibility: hidden;-moz-backface-visibility: hidden;-webkit-transform: translate3d(0, 0, 0); -moz-transform: translate3d(0, 0, 0);}
                                .ui-view-iframe-wrapper:not(.loaded):before{content: "";position: absolute;top:50%;left: 50%;width: 50px;height: 50px;background: url('<?=SITE_TEMPLATE_PATH?>/images/loaders/double_ring.svg') center no-repeat;margin: -25px 0px 0px -25px;}
                                .ui-view-iframe-wrapper iframe{width: 100%;min-height:1028px;border:none;opacity: 0;transition: opacity 0.3s ease;}
                                .ui-view-iframe-wrapper.loaded iframe{opacity: 1;}
                                .ui-view-wrapper.has-device .ui-view-iframe-wrapper{margin: 40px auto 30px;}
                                .ui-panel-top-devices-inner{-moz-user-select: -moz-none;-webkit-user-select: none;-ms-user-select: none;user-select: none;position: fixed;bottom: 0px;left: 0px;height: 58px;width: 158px;cursor: pointer;background-color: #fff;-webkit-transition: all .5s ease;-o-transition: all .5s ease;transition: all .5s ease;padding: 0;-webkit-box-shadow: 0 5px 10px 0 rgba(0,0,0,.15);box-shadow: 0 5px 10px 0 rgba(0,0,0,.15);z-index: 399;display: -webkit-box;display: -ms-flexbox;display: flex;-webkit-box-align: center;-ms-flex-align: center;align-items: center;-webkit-box-pack: center;-ms-flex-pack: center;justify-content: center;text-decoration: none;border-radius: 0px 5px 0px 0px;}
                                .ui-panel-top-devices-inner > div{opacity: 0.5;transition: opacity 0.3s ease;padding: 0px 10px;}
                                .ui-panel-top-devices-inner > .ui-button--active, .ui-panel-top-devices-inner > div:hover{opacity: 1;}
                            </style>
                            <script type="text/javascript">
                                document.addEventListener('DOMContentLoaded', function(){
                                    document.getElementById("site-view-frame").addEventListener("load", function(){document.getElementById("site-view-wrapper").classList.add("loaded");});
                                    var buttonClick = document.getElementsByClassName("ui-button"),
                                        view_wrapper = document.getElementById("view-wrapper"),
                                        bodyTag = document.getElementsByTagName("body")[0],
                                        site_view_wrapper = document.getElementById("site-view-wrapper");
                                    if(location.href.indexOf('device') == -1)
                                        bodyTag.classList.add("no-device");

                                    [].slice.call(buttonClick).forEach(function(item) {
                                        item.addEventListener('click', function() {
                                            for(var i = 0; i < buttonClick.length; i++){
                                                buttonClick[i].classList.remove("ui-button--active");
                                            }
                                            this.classList.add("ui-button--active");

                                            if(this.classList.contains("ui-button-desktop")){
                                                view_wrapper.classList.remove("has-device");
                                                site_view_wrapper.dataset.type = "desktop";
                                                bodyTag.scrollTop = 0;
                                                bodyTag.classList.add("no-device");
                                            }else if(this.classList.contains("ui-button-tablet")){
                                                view_wrapper.classList.add("has-device");
                                                site_view_wrapper.dataset.type = "tablet";
                                                bodyTag.classList.remove("no-device");
                                            }else if(this.classList.contains("ui-button-mobile")){
                                                view_wrapper.classList.add("has-device");
                                                site_view_wrapper.dataset.type = "mobile";
                                                bodyTag.classList.remove("no-device");
                                            }
                                        });
                                    })
                                })
                            </script>
                        <?else:?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function(){
                                    if(typeof inIframe !== 'undefined'){
                                        if(!inIframe()){
                                            document.querySelector('.ui-panel-top-devices-inner').classList.remove('hidden');
                                        }
                                    }
                                })
                            </script>
                        <?endif;?>
                        <div class="ui-panel-top-devices-inner hidden">
                            <?$arButtons = ['desktop', 'tablet', 'mobile'];?>
                            <?if($bAll):?>
                                <?foreach($arButtons as $button):?>
                                    <div class="ui-button ui-button-<?=$button;?><?=($button == $device || (!$device && $button == 'desktop') ? ' ui-button--active' : '');?>" title="<?=GetMessage("BUTTON_VIEW_".strtoupper($button))?>"><?=\CMax::showIconSvg("op", SITE_TEMPLATE_PATH.'/images/svg/responsible/'.$button.'.svg', '', '', true, false);?></div>
                                <?endforeach;?>
                            <?else:?>
                                <?if(!$bIframeMode):?>
                                    <?foreach($arButtons as $button):?>
                                        <a class="ui-button ui-button-<?=$button;?><?=($button == 'desktop' ? ' ui-button--active' : '');?>" title="<?=GetMessage("BUTTON_VIEW_".strtoupper($button))?>" href="/preview/?viewurl=<?=$GLOBALS['APPLICATION']->GetCurPage();?><?=($button != 'desktop' ? '&device='.$button : '');?>" target="_blank"><?=\CMax::showIconSvg("op", SITE_TEMPLATE_PATH.'/images/svg/responsible/'.$button.'.svg', '', '', true, false);?></a>
                                    <?endforeach;?>
                                <?endif;?>
                            <?endif;?>
                        </div>
                        <?if($bAll):?>
                            <div class="ui-view-wrapper<?=($device ? ' has-device' : '');?>"  id="view-wrapper">
                                <div class="ui-view-iframe-wrapper"<?=($device ? ' data-type="'.$device.'"' : '')?> id="site-view-wrapper">
                                    <div><div><iframe src="<?=$url;?>?iframe_mode=Y&bitrix_include_areas=N" class="site-ui-view" id="site-view-frame" allowfullscreen></iframe></div></div>
                                </div>
                            </div>
                        <?endif;?>
                    <?endif;?>
                <?
                $html = ob_get_contents();
                ob_end_clean();

                foreach(GetModuleEvents(self::MODULE_ID, 'OnAsproShowResponsiblePreview', true) as $arEvent) // event for manipulation responsible preview
                    ExecuteModuleEventEx($arEvent, array($bAll, &$html));

                echo $html;
                ?>
            <?
        }

        public static function showCalculateDeliveryBlock($productId, $arParams, $bSkipPreview = false){
            ?>
            <?if($productId > 0 && $arParams['CALCULATE_DELIVERY'] !== 'NOT'):?>
                <?
                $bIndexBot = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Lighthouse') !== false); // is indexed yandex/google bot
                $bWithPreview = $arParams['CALCULATE_DELIVERY'] === 'WITH_PREVIEW' && !$bSkipPreview && !$bIndexBot;
                ?>
                <?ob_start();?>
                    <div class="calculate-delivery text-form muted777 muted ncolor<?=($bWithPreview ? ' with_preview' : '')?>">
                        <?=\CMax::showIconSvg('delivery_calc', SITE_TEMPLATE_PATH.'/images/svg/catalog/delivery_calc.svg', '', '', true, false);?>
                        <span class="animate-load dotted font_sxs" data-event="jqm" data-param-form_id="delivery" data-name="delivery" data-param-product_id="<?=$productId?>" <?=(($arParams['USE_REGION'] === 'Y' && $arParams['STORES'] && is_array($arParams['STORES'])) ? 'data-param-region_stores_id="'.implode(',', $arParams['STORES']).'"' : '')?>><?=$arParams['EXPRESSION_FOR_CALCULATE_DELIVERY']?></span>
                        <?if($bWithPreview):?><span class="calculate-delivery-preview"></span><?endif;?>
                    </div>
                <?
                $html = ob_get_contents();
                ob_end_clean();

                foreach(GetModuleEvents(self::MODULE_ID, 'OnAsproShowCalculateDeliveryBlock', true) as $arEvent) // event for manipulation calculate delivery link
                    ExecuteModuleEventEx($arEvent, array($productId, $arParams, &$html));

                echo $html;
                ?>
            <?endif;?>
            <?
        }

        public static function getPriceList($elementID, $arPricesID = array(), $quantity = 1, $bUsePriceCode = false){
            $arPricesList = array();
            if($arPricesID)
            {
                $arPricesID = self::getPricesID($arPricesID, $bUsePriceCode);

                $arSelect = array('ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY');
                $arFilter = array(
                    '=PRODUCT_ID' => $elementID,
                    '@CATALOG_GROUP_ID' => $arPricesID,
                    array(
                        'LOGIC' => 'OR',
                        '<=QUANTITY_FROM' => $quantity,
                        '=QUANTITY_FROM' => null
                    ),
                    array(
                        'LOGIC' => 'OR',
                        '>=QUANTITY_TO' => $quantity,
                        '=QUANTITY_TO' => null
                    )
                );

                if(class_exists('\Bitrix\Catalog\PriceTable'))
                {
                    $iterator = \Bitrix\Catalog\PriceTable::getList(array(
                        'select' => $arSelect,
                        'filter' => $arFilter
                    ));
                }
                else
                {
                    $iterator = CPrice::GetList(array(), $arFilter, false, false, $arSelect);
                }
                while($row = $iterator->fetch())
                {
                    $row['ELEMENT_IBLOCK_ID'] = '';
                    $arPricesList[] = $row;
                }
                unset($row);
            }
            return $arPricesList;
        }

        public static function sendResultToIBlock($WEB_FORM_ID, $RESULT_ID){
            $bAdminSection = (defined('ADMIN_SECTION') && ADMIN_SECTION === true);
            if(!$bAdminSection)
            {
                //check REVIEW form
                $rsForm = \CForm::GetByID($WEB_FORM_ID);
                $arForm = $rsForm->Fetch();
                if($arForm && $arForm['SID'] == 'REVIEW')
                {
                    \CForm::GetResultAnswerArray(
                            $WEB_FORM_ID,
                            $arrColumns,
                            $arrAnswers,
                            $arrAnswersVarname,
                            array("RESULT_ID" => $RESULT_ID)
                        );
                    \CFormResult::GetDataByID($RESULT_ID, array(), $arResultFields, $arAnswers);

                    if($arrAnswersVarname)
                    {
                        $el = new \CIBlockElement;

                        $PROP = array(
                            'EMAIL' => $arrAnswersVarname[$RESULT_ID]['EMAIL'][0]['USER_TEXT'],
                            'POST' => $arrAnswersVarname[$RESULT_ID]['POST'][0]['USER_TEXT'],
                            'RATING' => $arrAnswersVarname[$RESULT_ID]['RATING'][0]['USER_TEXT'],
                        );

                        $arLoadProductArray = array(
                            "IBLOCK_ID" => \CMaxCache::$arIBlocks[SITE_ID]["aspro_max_content"]["aspro_max_add_review"][0],
                              "PROPERTY_VALUES"=> $PROP,
                              "ACTIVE"=> "N",
                              "NAME"=> $arrAnswersVarname[$RESULT_ID]['NAME'][0]['USER_TEXT'],
                              "PREVIEW_TEXT"=> $arrAnswersVarname[$RESULT_ID]['REVIEW_TEXT'][0]['USER_TEXT'],
                              "PREVIEW_PICTURE"=> \CFile::MakeFileArray($arrAnswersVarname[$RESULT_ID]['FILE'][0]['USER_FILE_ID']),
                        );

                        $el->Add($arLoadProductArray);
                    }
                }
            }
        }

        public static function sendLeadCrmFromForm(
            $WEB_FORM_ID,
            $RESULT_ID,
            $TYPE = 'ALL',
            $SITE_ID = SITE_ID,
            $CURL = false,
            $DECODE = false
        ){
            $result = "{'erorr':{'error_msg': 'error'}}";

            $bIntegrationFlowlu = CAsproMaxCRM::isEnabledIntegration('FLOWLU', $SITE_ID);
            $bIntegrationAcloud = CAsproMaxCRM::isEnabledIntegration('ACLOUD', $SITE_ID);
            $bIntegrationAmoCrm = CAsproMaxCRM::isEnabledIntegration('AMO_CRM', $SITE_ID);

            if(
                $bIntegrationFlowlu ||
                $bIntegrationAcloud ||
                $bIntegrationAmoCrm
            ){
                $arAllMatchValues = array();

                // flowlu
                if($bIntegrationFlowlu && ($TYPE == 'ALL' || $TYPE == 'FLOWLU')){
                    $arMatchValuesFlowlu = Solution::unserialize(Option::get(self::MODULE_ID, 'FLOWLU_CRM_FIELDS_MATCH_'.$WEB_FORM_ID, '', $SITE_ID));
                    $arAllMatchValues['FLOWLU'] = $arMatchValuesFlowlu;
                }

                // acloud
                if($bIntegrationFlowlu && ($TYPE == 'ALL' || $TYPE == 'ACLOUD')){
                    $arMatchValuesAcloud = Solution::unserialize(Option::get(self::MODULE_ID, 'ACLOUD_CRM_FIELDS_MATCH_'.$WEB_FORM_ID, '', $SITE_ID));
                    $arAllMatchValues['ACLOUD'] = $arMatchValuesAcloud;
                }

                // amocrm
                if($bIntegrationAmoCrm && ($TYPE == 'ALL' || $TYPE == 'AMO_CRM')){
                    $arMatchValuesAmoCrm = Solution::unserialize(Option::get(self::MODULE_ID, 'AMO_CRM_FIELDS_MATCH_'.$WEB_FORM_ID, '', $SITE_ID));
                    $arAllMatchValues['AMO_CRM'] = $arMatchValuesAmoCrm;
                }

                if($arAllMatchValues){
                    // get fields
                    \CForm::GetResultAnswerArray(
                        $WEB_FORM_ID,
                        $arrColumns,
                        $arrAnswers,
                        $arrAnswersVarname,
                        array("RESULT_ID" => $RESULT_ID)
                    );

                    // get form
                    \CFormResult::GetDataByID($RESULT_ID, array(), $arResultFields, $arAnswers);

                    $arPostFields = array();

                    // fill main fieds
                    foreach($arAllMatchValues as $crm => $arFields){
                        foreach($arFields as $key => $id){
                            switch($id){
                                case 'RESULT_ID':
                                    $arPostFields[$crm][$key] = $arResultFields['ID'];
                                break;
                                case 'FORM_SID':
                                    $arPostFields[$crm][$key] = $arResultFields['SID'];
                                break;
                                case 'FORM_NAME':
                                    $arPostFields[$crm][$key] = $arResultFields['NAME'];
                                break;
                                case 'SITE_ID':
                                    $arPostFields[$crm][$key] = $SITE_ID;
                                break;
                                case 'FORM_ALL':
                                    $arPostFields[$crm][$key] = self::_getAllFormFields($WEB_FORM_ID, $RESULT_ID, $arAnswers);
                                break;
                                case 'FORM_ALL_HTML':
                                    $arPostFields[$crm][$key] = self::_getAllFormFieldsHTML($WEB_FORM_ID, $RESULT_ID, $arAnswers);
                                break;
                            }
                        }
                    }

                    // fill form fieds
                    foreach($arAllMatchValues as $crm => $arFields){
                        foreach($arFields as $key => $id){
                            if($arrAnswers[$RESULT_ID][$id]){
                                $bCanPushCrm = true;

                                $arPostFields[$crm][$key] = (isset($arrAnswers[$RESULT_ID][$id][$id]['USER_TEXT']) && $arrAnswers[$RESULT_ID][$id][$id]['USER_TEXT'] ? $arrAnswers[$RESULT_ID][$id][$id]['USER_TEXT'] : $arrAnswers[$RESULT_ID][$id][$id]['ANSWER_TEXT']);
                            }
                        }
                    }

                    if($arPostFields){
                        $arHeaders = array();

                        if($crm === 'AMO_CRM'){
                            $arOAuth = array();
                            $arConfig = array(
                                'type' => 'AMO_CRM',
                                'siteId' => $SITE_ID,
                            );

                            CAsproMaxCRM::restore(
                                $arOAuth,
                                $arConfig
                            );

                            CAsproMaxCRM::updateOAuth(
                                $arOAuth,
                                $arConfig
                            );

                            CAsproMaxCRM::save(
                                $arOAuth,
                                $arConfig
                            );

                            $arHeaders = array(
                                'Authorization' => 'Bearer '.$arOAuth['accessToken']
                            );
                        }

                        foreach($arPostFields as $crm => $arFields){
                            if($crm == 'FLOWLU'){
                                $url = str_replace('#DOMAIN#', Option::get(self::MODULE_ID, 'DOMAIN_'.$crm, '', $SITE_ID), \Aspro\Functions\CAsproMaxCRM::FLOWLU_PATH);
                                $arFields['api_key'] = Option::get(self::MODULE_ID, 'TOKEN_FLOWLU', '', $SITE_ID);
                                $arFields['ref'] = 'form:aspro-max';
                                $arFields['ref_id'] = $WEB_FORM_ID.'_'.$RESULT_ID;
                                $name_field = 'name';
                            }
                            elseif($crm == 'ACLOUD'){
                                $url = str_replace('#DOMAIN#', Option::get(self::MODULE_ID, 'DOMAIN_'.$crm, '', $SITE_ID), CAsproMaxCRM::ACLOUD_PATH);
                                $arFields['api_key'] = Option::get(self::MODULE_ID, 'TOKEN_ACLOUD', '', $SITE_ID);
                                $arFields['ref'] = 'form:aspro-max';
                                $arFields['ref_id'] = $WEB_FORM_ID.'_'.$RESULT_ID;
                                $name_field = 'name';
                            }
                            else{
                                $name_field = 'name_leads';
                                $url = str_replace('#DOMAIN#', Option::get(self::MODULE_ID, 'DOMAIN_'.$crm, '', $SITE_ID), \Aspro\Functions\CAsproMaxCRM::AMO_CRM_PATH);
                                if(!$arFields['tags_leads'])
                                    $arFields['tags_leads'] = Option::get(self::MODULE_ID, 'TAGS_AMO_CRM_TITLE', '', $SITE_ID);
                            }

                            if(!$arFields[$name_field]){
                                $arFields[$name_field] = Option::get(self::MODULE_ID, 'LEAD_NAME_'.$crm.'_TITLE', \Bitrix\Main\Localization\Loc::getMessage('ASPRO_MAX_MODULE_LEAD_NAME_'.$crm), $SITE_ID);
                            }

                            $smCrmName = strtolower(str_replace('_', '', $crm));

                            // log to file form request
                            if(Option::get(self::MODULE_ID, 'USE_LOG_'.$crm, 'N', $SITE_ID) == 'Y'){
                                self::set_log('crm', $smCrmName.'_create_lead_request', $arFields);
                            }

                            //convert all to UTF8 encoding for send to flowlu
                            // foreach($arFields as $key => $value)
                            // {
                            // 	$arFields[$key] = iconv(LANG_CHARSET, 'UTF-8', $value);
                            // }

                            $arFieldsLead = $arFields;

                            if($crm == 'AMO_CRM'){
                                $arFieldsLeadTmp = $arFields;
                                $arCustomFields = Solution::unserialize(Option::get(self::MODULE_ID, 'CUSTOM_FIELD_AMO_CRM', '', $SITE_ID));

                                // prepare array
                                $arFieldsLeadTmp = self::prepareArray($arFields, array('name', 'tags', 'budget'), '_leads');
                                if($arCustomFields && $arCustomFields['leads']){
                                    foreach($arCustomFields['leads'] as $key => $arProp){
                                        if($arFields[$key.'_leads']){
                                            $arFieldsLeadTmp['custom_fields'][] = array(
                                                'id' => $key,
                                                'values' => array(
                                                    array(
                                                        'value' => $arFields[$key.'_leads']
                                                    )
                                                )
                                            );
                                        }
                                        elseif(isset($arProp['ENUMS']) && $arProp['ENUMS']){
                                            foreach($arProp['ENUMS'] as $key2 => $value){
                                                if($arFields[$key.'_'.$key2.'_leads']){
                                                    $arFieldsLeadTmp['custom_fields'][] = array(
                                                        'id' => $key,
                                                        'values' => array(
                                                            array(
                                                                'value' => $arFields[$key.'_'.$key2.'_leads'],
                                                                'enum' => $value
                                                            )
                                                        )
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }

                                $arFieldsLead = array(
                                    'request' => array(
                                        'leads' => array(
                                            'add' => array(
                                                $arFieldsLeadTmp
                                            )
                                        )
                                    )
                                );
                            }

                            $result = \Aspro\Functions\CAsproMaxCRM::query($url, \Aspro\Functions\CAsproMaxCRM::$arCrmMethods[$crm]["CREATE_LEAD"], $arFieldsLead, $arHeaders, $CURL, $DECODE);
                            $arCrmResult = Json::decode($result);
                            unset($arFieldsLead);

                            if(isset($arCrmResult['response'])){
                                if($crm == 'AMO_CRM' && $arCrmResult['response']['leads']){
                                    // create contact and company for amocrm
                                    $arLead = reset($arCrmResult['response']['leads']['add']);
                                    $leadID = $arLead['id'];

                                    // add notes
                                    if($arFields['notes_leads']){
                                        $arFieldsNote = array(
                                            'request' => array(
                                                'notes' => array(
                                                    'add' => array(
                                                        array(
                                                            'element_id' => $leadID,
                                                            'element_type' => 2,
                                                            'note_type' => 4,
                                                            'text' => $arFields['notes_leads']
                                                        ),
                                                    )
                                                )
                                            )
                                        );
                                        $resultNote = \Aspro\Functions\CAsproMaxCRM::query($url, \Aspro\Functions\CAsproMaxCRM::$arCrmMethods[$crm]["CREATE_NOTES"], $arFieldsNote, $arHeaders, $CURL, $DECODE);

                                        unset($arFieldsNote);
                                        unset($resultNote);
                                    }

                                    // add company
                                    $company_id = 0;
                                    if($arCustomFields && $arCustomFields['companies']){
                                        // prepare array
                                        $arFieldsCompanyTmp = self::prepareArray($arFields, array('name', 'tags'), '_companies');
                                        $arFieldsCompanyTmp['linked_leads_id'] = array($leadID);

                                        foreach($arCustomFields['companies'] as $key => $arProp){
                                            if($arFields[$key.'_companies']){
                                                $arFieldsCompanyTmp['custom_fields'][] = array(
                                                    'id' => $key,
                                                    'values' => array(
                                                        array(
                                                            'value' => $arFields[$key.'_companies']
                                                        )
                                                    )
                                                );
                                            }
                                            elseif(isset($arProp['ENUMS']) && $arProp['ENUMS']){
                                                foreach($arProp['ENUMS'] as $key2 => $value){
                                                    if($arFields[$key.'_'.$key2.'_companies']){
                                                        $arFieldsCompanyTmp['custom_fields'][] = array(
                                                            'id' => $key,
                                                            'values' => array(
                                                                array(
                                                                    'value' => $arFields[$key.'_'.$key2.'_companies'],
                                                                    'enum' => $value
                                                                )
                                                            )
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                        $arFieldsCompany = array(
                                            'request' => array(
                                                'contacts' => array(
                                                    'add' => array(
                                                        $arFieldsCompanyTmp
                                                    )
                                                )
                                            )
                                        );

                                        $resultCompany = \Aspro\Functions\CAsproMaxCRM::query($url, \Aspro\Functions\CAsproMaxCRM::$arCrmMethods[$crm]["CREATE_COMPANY"], $arFieldsCompany, $arHeaders, $CURL, $DECODE);
                                        $resultCompany = Json::decode($resultCompany);

                                        if(isset($resultCompany['response']['contacts']['add'][0]['id'])){
                                            $company_id = $resultCompany['response']['contacts']['add'][0]['id'];
                                        }

                                        //log to file crm response
                                        if(Option::get(self::MODULE_ID, 'USE_LOG_'.$crm, 'N', $SITE_ID) == 'Y'){
                                            self::set_log('crm', $smCrmName.'_create_company_response', $resultCompany);
                                        }

                                        unset($arFieldsCompany);
                                        unset($resultCompany);
                                    }

                                    //add contact
                                    $arFieldsContactTmp = self::prepareArray($arFields, array('name', 'tags'), '_contacts');
                                    $arFieldsContactTmp['linked_leads_id'] = array($leadID);

                                    if($company_id){
                                        $arFieldsContactTmp['linked_company_id'] = $company_id;
                                    }

                                    if($arCustomFields && $arCustomFields['contacts']){
                                        foreach($arCustomFields['contacts'] as $key => $arProp){
                                            if($arFields[$key.'_contacts']){
                                                $arFieldsContactTmp['custom_fields'][] = array(
                                                    'id' => $key,
                                                    'values' => array(
                                                        array(
                                                            'value' => $arFields[$key.'_contacts']
                                                        )
                                                    )
                                                );
                                            }
                                            elseif(isset($arProp['ENUMS']) && $arProp['ENUMS']){
                                                foreach($arProp['ENUMS'] as $key2 => $value){
                                                    if($arFields[$key.'_'.$key2.'_contacts']){
                                                        $arFieldsContactTmp['custom_fields'][] = array(
                                                            'id' => $key,
                                                            'values' => array(
                                                                array(
                                                                    'value' => $arFields[$key.'_'.$key2.'_contacts'],
                                                                    'enum' => $value
                                                                )
                                                            )
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    $arFieldsContact = array(
                                        'request' => array(
                                            'contacts' => array(
                                                'add' => array(
                                                    $arFieldsContactTmp
                                                )
                                            )
                                        )
                                    );

                                    $resultContact = \Aspro\Functions\CAsproMaxCRM::query($url, \Aspro\Functions\CAsproMaxCRM::$arCrmMethods['AMO_CRM']['CREATE_CONTACT'], $arFieldsContact, $arHeaders, $CURL, $DECODE);

                                    // log to file crm response
                                    if(Option::get(self::MODULE_ID, 'USE_LOG_'.$crm, 'N', $SITE_ID) == 'Y'){
                                        self::set_log('crm', $smCrmName.'_create_contact_response', Json::decode($resultContact));
                                    }

                                    unset($arFieldsContact);
                                    unset($resultContact);
                                }

                                if(
                                    (isset($arCrmResult['response']['id']) && $arCrmResult['response']['id']) ||
                                    (isset($arCrmResult['response']['leads']) && $leadID)
                                ){
                                    $arFormResultOption = Solution::unserialize(Option::get(self::MODULE_ID, 'CRM_SEND_FORM_'.$RESULT_ID, 'a:0:{}', $SITE_ID));
                                    if(!isset($arFormResultOption['FLOWLU']) && (isset($arCrmResult['response']['id']) && $arCrmResult['response']['id'])){
                                        $arFormResultOption['FLOWLU'] = $arCrmResult['response']['id'];
                                    }
                                    if(!isset($arFormResultOption['ACLOUD']) && (isset($arCrmResult['response']['id']) && $arCrmResult['response']['id'])){
                                        $arFormResultOption['ACLOUD'] = $arCrmResult['response']['id'];
                                    }
                                    if(!isset($arFormResultOption['AMO_CRM']) && (isset($arCrmResult['response']['leads']) && $leadID)){
                                        $arFormResultOption['AMO_CRM'] = $leadID;
                                    }

                                    Option::set(self::MODULE_ID, 'CRM_SEND_FORM_'.$RESULT_ID, serialize($arFormResultOption), $SITE_ID);
                                }
                            }

                            // log to file crm response
                            if(Option::get(self::MODULE_ID, 'USE_LOG_'.$crm, 'N', $SITE_ID) == 'Y'){
                                self::set_log('crm', $smCrmName.'_create_lead_response', $arCrmResult);
                            }
                        }
                    }
                }
            }

            return $result;
        }

        public static function checkAvailable($arFilter, $options = array(), $arParams = array()) {
            global $arRegion;

            if( in_array('REGION', $options) ) {
                if(\CMax::GetFrontParametrValue('REGIONALITY_FILTER_ITEM') == 'Y' && \CMax::GetFrontParametrValue('REGIONALITY_FILTER_CATALOG') == 'Y'){
                    $arFilter['PROPERTY_LINK_REGION'] = $arRegion['ID'];
                    \CMax::makeElementFilterInRegion($arFilter, $arRegion['ID']);
                } elseif($arParams['FILTER_NAME'] == 'arRegionLink' && is_array($GLOBALS['arRegionLink'])){
                    $arFilter = array_merge($GLOBALS['arRegionLink'], $arFilter);
                }
            }
            $resElements = \CMaxCache::CIblockElement_GetList( array( "CACHE" => array( "TAG" => \CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_ID"]) ) ) , $arFilter, array());

            return $resElements;
        }

        public static function getCustomFunc($method = '') {
            $arClass = explode('\\', __CLASS__);
            $className = end($arClass);
            $methodCall = $method.$className;
            $classCall = __CLASS__.'Custom';
            $handler = [$classCall, $methodCall];
            if (method_exists($classCall, $methodCall) && is_callable($handler)) {
                return $handler;
            }
            return false;
        }

        public static function showDeveloperBlock($color = "dark") {
            global $APPLICATION, $arTheme;
            if($arTheme['FOOTER_TOGGLE_DEVELOPER']["VALUE"] == 'Y'){
                @include $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/footer/developer.php';
            }
        }

        public static function showProgressBarBlock() {
            global $arTheme;
            if($arTheme['SHOW_PROGRESS_BAR']["VALUE"] == 'Y'){
                $html = '<div class="header-progress-bar">
                            <div class="header-progress-bar__inner"></div>
                        </div>';
                return $html;
            }
        }

        public static function showBonusBlockDetail($arItem) {
            if(\CMax::GetFrontParametrValue("BONUS_SYSTEM") === 'LOGICTIM' && \CModule::IncludeModule('logictim.balls')):?>
                <div class="bonus-system-block lb_bonus lb_ajax_<?=$arItem["ID"]?>" data-item="<?=$arItem["ID"]?>"></div>
            <?endif;
        }

        public static function showBonusComponentDetail($arResult) {
            global $APPLICATION;
            if(\CMax::GetFrontParametrValue("BONUS_SYSTEM") === 'LOGICTIM' && \CModule::IncludeModule('logictim.balls')){
                $APPLICATION->IncludeComponent(
                    "logictim:bonus.catalog",
                    "aspro_max",
                    Array(
                        "COMPONENT_TEMPLATE" => ".default",
                        "COMPOSITE_FRAME_MODE" => "A",
                        "COMPOSITE_FRAME_TYPE" => "AUTO",
                        "ITEMS" => array("ITEMS"=>$arResult)
                    )
                );
            }
        }

        public static function showBonusBlockList($arItem) {
            if(\CMax::GetFrontParametrValue("BONUS_SYSTEM") === 'LOGICTIM' && \CModule::IncludeModule('logictim.balls')):?>
                <div class="bonus-system-block lb_bonus lb_ajax_<?=$arItem["ID"]?>" data-item="<?=$arItem["ID"]?>"></div>
            <?endif;
        }

        public static function showBonusComponentList($arResult) {
            global $APPLICATION;
            if (
                \CMax::GetFrontParametrValue("BONUS_SYSTEM") === 'LOGICTIM' && \CModule::IncludeModule('logictim.balls')
                && !empty($arResult['ITEMS'])
            ) {
                $APPLICATION->IncludeComponent(
                    "logictim:bonus.catalog",
                    "aspro_max",
                    Array(
                        "COMPONENT_TEMPLATE" => ".default",
                        "COMPOSITE_FRAME_MODE" => "A",
                        "COMPOSITE_FRAME_TYPE" => "AUTO",
                        "ITEMS" => $arResult["ITEMS"]
                    )
                );
            }
        }

        public static function showBonusBlockCart($arBonus, $arItem) {
            if(\CMax::GetFrontParametrValue("BONUS_SYSTEM") === 'LOGICTIM' && \CModule::IncludeModule('logictim.balls')):?>
                <? //bonus for item
                if($arBonus["ITEMS"][$arItem["PRODUCT_ID"]]["ADD_BONUS"] > 0) {?>
                    <div class="bonus-system-block bonus">
                        + <?=$arBonus["ITEMS"][$arItem["PRODUCT_ID"]]["ADD_BONUS"];?> <?=\COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", '')?>
                    </div>
                <? }?>
            <?endif;
        }

        public static function showBonusBlockCartTotal($arBonus) {
            if(\CMax::GetFrontParametrValue("BONUS_SYSTEM") === 'LOGICTIM' && \CModule::IncludeModule('logictim.balls')):?>
                <?//all basket bonus
                if($arBonus["ALL_BONUS"] > 0) {?>
                    <div class="bonus-system-block bonus">
                        + <?=$arBonus["ALL_BONUS"];?> <?=\COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", '')?>
                    </div>
                <? }?>
            <?endif;
        }

        public static function isBonusSystemOn() {
            return \CMax::GetFrontParametrValue("BONUS_SYSTEM") === 'LOGICTIM' && \CModule::IncludeModule('logictim.balls');
        }

        public static function showBottomPanel() {
            global $arTheme, $APPLICATION, $arBasketPrices;

            $arCounters = null;

            if (\CMax::GetFrontParametrValue("BOTTOM_ICONS_PANEL") == 'Y') {
                $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/blocks/common.blocks/bottom-icons-panel/bottom-icons-panel.css');
                $iblockID = \CMaxCache::$arIBlocks[SITE_ID]['aspro_max_content']['aspro_max_bottom_icons'][0];

                /*custom functions call*/
                if ($handler = self::getCustomFunc(__FUNCTION__)) {
                    $arParams = [
                        'IBLOCK_ID' => $iblockID
                    ];
                    call_user_func_array($handler, [$arParams]);
                    return;
                }
                /**/

                if ($iblockID) {
                    ob_start();
                    $arFilter = [
                        'IBLOCK_ID' => $iblockID,
                        'ACTIVE' => 'Y'
                    ];
                    $arItems = \CMaxCache::CIBLockElement_GetList(array('SORT' => 'ASC', 'ID' => 'ASC', 'CACHE' => array('TAG' => \CMaxCache::GetIBlockCacheTag($iblockID))), $arFilter, false, false, array('ID', 'NAME', 'IBLOCK_ID', 'PROPERTY_IMG', 'PROPERTY_TYPE', 'PROPERTY_URL', 'PROPERTY_SHOW_TEXT'));?>

                    <?if ($arItems) {?>
                        <div class="bottom-icons-panel swipeignore" style="display:none">
                            <div class="bottom-icons-panel__content">
                                <?foreach($arItems as $arItem) {?>
                                    <?
                                    $count = 0;
                                    $title = '';
                                    $url = str_replace('//', '/', htmlspecialcharsbx($arItem['PROPERTY_URL_VALUE'] ? SITE_DIR.$arItem['PROPERTY_URL_VALUE'] : '#'));
                                    $dataURL = '';
                                    $title = $arItem['NAME'];
                                    $bActive = ($APPLICATION->GetCurPage() === $url);
                                    $additionalClass = '';
                                    ?>
                                    <?if ($arItem['PROPERTY_TYPE_VALUE']) {
                                        $arProperty = \CIBlockPropertyEnum::GetByID($arItem['PROPERTY_TYPE_ENUM_ID']);
                                        if (!$arCounters) {
                                            $arCounters = \CMax::getBasketCounters();
                                        }
                                        if ($arProperty['XML_ID'] === 'basket') {
                                            if ($arBasketPrices) {
                                                $count = $arBasketPrices['BASKET_COUNT'];
                                                $title = $arBasketPrices['BASKET_SUMM_TITLE'];
                                            } else if ($arCounters && isset($arCounters['READY'])) {
                                                $count = $arCounters['READY']['COUNT'];
                                                $title = $arCounters['READY']['TITLE'];
                                            }
                                        } else if ($arProperty['XML_ID'] === 'delay') {
                                            $url = Solution::GetFrontParametrValue('FAVORITE_PAGE_URL');
                                            $additionalClass = 'basket-link';
                                            if ($arCounters && isset($arCounters['FAVORITE'])) {
                                                $count = \Aspro\Max\Itemaction\Favorite::getCount();
                                                $title = $arCounters['FAVORITE']['TITLE'];
                                            }
                                        }
                                        if ($arProperty['XML_ID'] === 'compare') {
                                            if ($arCounters && isset($arCounters['COMPARE'])) {
                                                $count = \Aspro\Max\Itemaction\Compare::getCount();
                                                $title = $arCounters['COMPARE']['TITLE'];
                                            }
                                        }
                                    }?>
                                    <a href="<?=$url?>" <?=($dataURL ? 'data-href="'.$dataURL.'"' : '')?> class="bottom-icons-panel__content-link<?=($bActive ? ' bottom-icons-panel__content-link--active' : ' dark-color');?> bottom-icons-panel-item_<?=($arProperty['XML_ID'] ?? '')?>" title="<?=$title?>">
                                        <?$bShowText = ($arItem['PROPERTY_SHOW_TEXT_VALUE'] == 'Y')?>
                                        <?if ($arItem['PROPERTY_IMG_VALUE']) {?>
                                            <span class="bottom-icons-panel__content-picture-wrapper bottom-icons-panel__content-link--display--block<?=($bShowText ? ' bottom-icons-panel__content-picture-wrapper--mb-3' : '');?>">
                                                <?$arImg = \CFile::ResizeImageGet($arItem['PROPERTY_IMG_VALUE'], array('width' => 60, 'height' => 60), BX_RESIZE_IMAGE_PROPORTIONAL_ALT);
                                                if(is_array($arImg)) {
                                                    if(strpos($arImg["src"], ".svg") !== false) {?>
                                                        <?=\CMax::showIconSvg("cat_icons light-ignore", $arImg["src"]);?>
                                                    <?} else {?>
                                                        <img class="bottom-icons-panel__content-picture lazy" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arImg["src"]);?>" data-src="<?=$arImg["src"]?>" alt="<?=$arItem['NAME']?>" title="<?=$arItem['NAME']?>" />
                                                    <?}?>
                                                <?}?>
                                                <?if ($arItem['PROPERTY_TYPE_VALUE']) {?>
                                                    <div class="counter-state <?=$arProperty['XML_ID']?> <?=$additionalClass;?> counter-state--in-icons<?=(!$count ? ' counter-state--empty' : '');?>" title="<?=$title;?>">
                                                        <div class="counter-state__content colored_theme_bg">
                                                            <div class="counter-state__content-item">
                                                                <div class="counter-state__content-item-value js-count"><?=$count;?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?}?>
                                            </span>
                                        <?}?>
                                        <?if ($bShowText) {?>
                                            <span class="bottom-icons-panel__content-text font_sxs bottom-icons-panel__content-link--display--block"><?=$arItem['NAME'];?></span>
                                        <?}?>
                                    </a>
                                <?}?>
                            </div>
                        </div>
                    <?}

                    $html = ob_get_contents();
                    ob_end_clean();

                    foreach(GetModuleEvents(self::MODULE_ID, 'OnAsproShowBottomPanel', true) as $arEvent) // event for manipulation item
                        ExecuteModuleEventEx($arEvent, array($iblockID, $arItems, &$html));

                    echo $html;
                }
            }
        }
        public static function shemaOrganization(){
            global $arRegion, $APPLICATION;
            $arSite = \CSite::GetByID(SITE_ID)->Fetch();?>
            <?if($arRegion):
                $adress = strip_tags($arRegion['PROPERTY_ADDRESS_VALUE']['TEXT'] ?? '');
                $phone = implode(', ', array_column($arRegion['PHONES'], 'PHONE'));
                $email = $arRegion['PROPERTY_EMAIL_VALUE']['0'];
            else:
                $adress = strip_tags(file_get_contents($_SERVER['DOCUMENT_ROOT'].SITE_DIR."include/contacts-site-address.php"));
                $phone = strip_tags(file_get_contents($_SERVER['DOCUMENT_ROOT'].SITE_DIR."include/contacts-site-phone-one.php"));
                $email = strip_tags(file_get_contents($_SERVER['DOCUMENT_ROOT'].SITE_DIR."include/contacts-site-email.php"));
            endif;?>
            <?$imageFile = file_get_contents($_SERVER['DOCUMENT_ROOT'].SITE_DIR."include/contacts-site-image.php");
            if(stristr($imageFile, 'src=')) {
                $pattern = "/(.*)src=[\"'](.+?)[\"'](.*)/s";
                $replacement =  "$2";
                $image = preg_replace($pattern, $replacement, $imageFile);
            }
            ?>
            <?ob_start();?>
            <div itemscope itemtype="http://schema.org/Organization">
                <span itemprop="name" content="<?=($arSite['NAME'])?>"></span>
                <div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                    <span itemprop="streetAddress" content="<?=$adress?>"></span>
                </div>
                <span itemprop="telephone" content="<?=$phone?>"></span>
                <span itemprop="email" content="<?=$email?>"></span>
                <?if($image):?>
                    <span itemprop="image" content="<?=$image?>"></span>
                <?endif;?>
            </div>
            <?$html = ob_get_contents();
            ob_end_clean();
            echo $html;
        }

        public static function showBlockHtml($arOptions = [])
        {
            global $APPLICATION;
            $arDefaultOptions = [
                'TYPE' => '',
                'BASE_PATH' => SITE_DIR.'/include/blocks/',
                'FILE' => '',
                'PARAMS' => []
            ];
            $arConfig = array_merge($arDefaultOptions, $arOptions);

            if ($handler = self::getCustomFunc(__FUNCTION__)) {
                return call_user_func_array($handler, [$arConfig]);
            }

            if ($arConfig['FILE']) {
                $path = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].$arConfig['BASE_PATH'].$arConfig['FILE']);
                if (file_exists($path)) {
                    $customFile = str_replace('.php', '_custom.php', $arConfig['FILE']);
                    $customPath = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].$arConfig['BASE_PATH'].$customFile);
                    if (file_exists($customPath)) {
                        include($customPath);
                    } else {
                        include($path);
                    }
                }
            }
        }
        public static function replacePropsParams(&$arParams, $arReplaceCode = [])
        {
            $arReplaceCodeDefault = [
                'PROPERTY_CODE' => 'LIST_PROPERTY_CODE',
                'LIST_OFFERS_PROPERTY_CODE' => 'LIST_OFFERS_PROPERTY_CODE',
                'DETAIL_OFFERS_PROPERTY_CODE' => 'DETAIL_OFFERS_PROPERTY_CODE',
                'LIST_OFFERS_LIMIT' => 'LIST_OFFERS_LIMIT',
                'OFFERS_LIMIT' => 'OFFERS_LIMIT',
                'OFFER_ADD_PICT_PROP' => 'OFFER_ADD_PICT_PROP',
                'ADD_PICT_PROP' => 'ADD_PICT_PROP',
                'CONVERT_CURRENCY' => 'CONVERT_CURRENCY',
                'CURRENCY_ID' => 'CURRENCY_ID',
            ];
            $arReplaceCode = array_merge($arReplaceCodeDefault, (array)$arReplaceCode);

            /* SKU */
            $arParams[$arReplaceCode["DETAIL_OFFERS_PROPERTY_CODE"]] = explode(',',\CMax::GetFrontParametrValue('CATALOG_SKU_PROPERTY_CODE'));
            $arParams[$arReplaceCode["LIST_OFFERS_PROPERTY_CODE"]] = explode(',',\CMax::GetFrontParametrValue('CATALOG_SKU_PROPERTY_CODE'));

            $arParams["OFFER_TREE_PROPS"] = explode(',', \CMax::GetFrontParametrValue('CATALOG_SKU_TREE_PROPERTY_CODE'));
            $arParams["OFFER_SHOW_PREVIEW_PICTURE_PROPS"] = explode(',', \CMax::GetFrontParametrValue('CATALOG_SKU_SHOW_PREVIEW_PICTURE_PROPS'));

            $arParams[$arReplaceCode["LIST_OFFERS_LIMIT"]] = \CMax::GetFrontParametrValue('CATALOG_SKU_LIMIT');
            $arParams[$arReplaceCode["OFFERS_LIMIT"]] = \CMax::GetFrontParametrValue('CATALOG_SKU_LIMIT');

            $arParams[$arReplaceCode["ADD_PICT_PROP"]] = \CMax::GetFrontParametrValue('CATALOG_ADD_PICT_PROP');
            $arParams[$arReplaceCode["OFFER_ADD_PICT_PROP"]] = \CMax::GetFrontParametrValue('CATALOG_SKU_ADD_PICT_PROP');
            $arParams[$arReplaceCode["CONVERT_CURRENCY"]] = \CMax::GetFrontParametrValue('CONVERT_CURRENCY');
            $arParams[$arReplaceCode["CURRENCY_ID"]] = \CMax::GetFrontParametrValue('CURRENCY_ID');
        }

        /**
         * Show svg inline or svg use file by path
         *
         * @param array $arOptions
         * @return string
         */
        public static function showSVG($arOptions = [])
        {
            static $checkAgent;

            $path = $arOptions['PATH'];
            $sprite = new \Aspro\Max\SvgSprite();
            $sprite->add(\Aspro\Max\SvgSprite::getFullPath($path));
            $sprite->save();
            ?>
            <?if ($sprite->fullFilePath && $sprite->checkFile($sprite->fullFilePath)):?>
                <?=\CMax::showSpriteIconSvg($sprite->filePath."#svg", "cat_icons colored svg-inline-icon", ['WIDTH' => $sprite->maxWidth,'HEIGHT' => $sprite->maxHeight, 'VIEWBOX' => $sprite->viewBox, 'INLINE' => 'N']);?>

                <?
                if (!$checkAgent) {
                    $checkAgent = true;
                    \Aspro\Max\Agents\DeleteDirectorySVG::check();
                }
                ?>
            <?else:?>
                <?=\CMax::showIconSvg("cat_icons", $path);?>
            <?endif;?>
            <?unset($sprite);?>
        <?}

        /**
         * Draw view of small gallery
         *
         * @param array $arOptions = [
         * 	'ITEMS' => array,
         * 	'IS_ACTIVE' => boolead,
         * ]
         *
         * @param array $arItems
         */
        public static function showSmallGallery($arOptions = [], $arItems = []) {
            $arDefaultOptions = [
                'BREAKPOINTS' => ['xs', 'sm', 'md'],
                'IS_ACTIVE' => false
            ];
            $arConfig = array_merge($arDefaultOptions, $arOptions);

            if ($handler = self::getCustomFunc(__FUNCTION__)) {
                return call_user_func_array($handler, [$arConfig]);
            }

            // setup data-params for css
            $dataBreakpoints = '';
            $itemsCount = count($arItems);

            if ($itemsCount > 2) {
                if ($itemsCount === count($arConfig['BREAKPOINTS'])) {
                    array_pop($arConfig['BREAKPOINTS']);
                }
                foreach ($arConfig['BREAKPOINTS'] as $count => $breakpoint) {
                    if (($itemsLeft = $itemsCount - ($count+2))) {
                        $dataBreakpoints .= ' data-'.$breakpoint.'="'.$itemsLeft.'"';
                    }
                }

            }

            $arItems = array_map(fn($arItem) => [
                'src' => $arItem['DETAIL']['SRC'] ?? '',
                'stub' => self::showBlankImg($arItem['PREVIEW']['src']),
                'thumb' => $arItem['PREVIEW']['src'],
                'alt' => $arItem['ALT'] ?? '',
                'title' => $arItem['TITLE'] ?? '',
            ], $arItems);

            $dataItems = Json::encode($arItems);
            array_splice($arItems, 4);

            foreach ($arItems as $key => $arItem) {
                $arItems[$key]['visibility_class'] = '';
                if (isset($arConfig['BREAKPOINTS'][$key-2])) {
                    for ($i = $key-2; $i >= 0; $i--) {
                        $arItems[$key]['visibility_class'] .= ' hidden-'.$arConfig['BREAKPOINTS'][$i];
                    }
                }
            }

            self::showBlockHtml([
                'FILE' => 'images/detail_small_gallery.php',
                'PARAMS' => [
                    'CONFIG' => $arConfig,
                    'ITEMS' => $arItems,
                    'DATA_BREAKPOINTS' => $dataBreakpoints,
                    'DATA_ITEMS' => $dataItems,
                ],
            ]);
        }

        /**
         * Draw view of main gallery
         */
        public static function showMainGallery($arOptions = [], $arResult = [], $arParams = []) {
            $arDefaultOptions = [
                'NEED_STICKERS' => true,
                'SHOW_THUMBS' => true,
                'IS_FAST_VIEW' => false,
            ];
            $arConfig = array_merge($arDefaultOptions, $arOptions);

            $bVertical = ($arParams['GALLERY_THUMB_POSITION'] == 'vertical');
            $viewImgType = $arParams["DETAIL_PICTURE_MODE"];
            $bMagnifier = ($viewImgType=="MAGNIFIER");
            $topGalleryClassList = " detail-gallery-big--".($bVertical ? 'vertical' : $arParams['GALLERY_THUMB_POSITION']);
            if ($arConfig['POPUPVIDEO']) {
                $topGalleryClassList .= " detail-gallery-big--with-video";
            }
            if($arParams['PICTURE_RATIO']){
                $topGalleryClassList .= " detail-gallery-big--ratio-" . $arParams['PICTURE_RATIO'];
            }
            if(!$arConfig['SHOW_THUMBS']){
                $topGalleryClassList .= " detail-gallery-big--no-thumbs";
            }
            if($arConfig['ADDITIONAL_CLASS']){
                $topGalleryClassList .= ' ' . $arConfig['ADDITIONAL_CLASS'];
            }

            reset($arResult['MORE_PHOTO']);
            // $countPhoto = count($arResult["MORE_PHOTO"]);
            $arFirstPhoto = ($arParams['ADD_DETAIL_TO_SLIDER'] != 'Y' && !$arConfig['IS_CURRENT_SKU'] ? $arResult['PREVIEW_PICTURE'] : current($arResult['MORE_PHOTO']));

            if($arConfig['NEED_STICKERS']){
                ob_start();
                \Aspro\Functions\CAsproMaxItem::showStickers($arParams, $arResult, true, "product-info-headnote__stickers1");
                $stickersHtml = trim(ob_get_clean());
            }

            $arConfig['IS_MAGNIFIER'] = $bMagnifier;
            $arConfig['IS_ONE_IMAGE'] = $bMagnifier;
            $arConfig['COUNT_IMAGES'] = is_array($arResult["MORE_PHOTO"]) ? count($arResult["MORE_PHOTO"]) : 0;
            $arConfig['FIRST_PHOTO'] = $arFirstPhoto["BIG"]["src"] ? $arFirstPhoto["BIG"]["src"] : $arFirstPhoto["SRC"];
            $arConfig['FIRST_SKU_PICTURE'] = $arResult["OFFERS"] && $arResult["FIRST_SKU_PICTURE"] ? $arResult["FIRST_SKU_PICTURE"]["src"] : '';
            $arConfig['IS_VERTICAL_THUMBS'] = $bVertical;
            $arConfig['TOP_GALLERY_CLASS'] = $topGalleryClassList;
            $arConfig['VIEW_IMG_TYPE'] = $viewImgType;
            $mainGalleryWrapClass = $arConfig["IS_FAST_VIEW"] ? '#fast_view_item ' : '.detail-gallery-big-slider-main ';
            if($arConfig['COUNT_IMAGES'] <= 1){
                $topGalleryClassList .= ' gallery-wrapper--hide-thumbs';
            }

            $gallerySetting = [
                'MAIN' => [
                    'SLIDE_CLASS_LIST' => 'swiper-slide swiper-slide--height-auto detail-gallery-big__item detail-gallery-big__item--big',
                    'PLUGIN_OPTIONS' => [
                        'direction' => 'horizontal',
                        'init' => false,
                        'keyboard' => [
                            'enabled' => true,
                        ],
                        'loop' => false,
                        'touchEventsTarget' => 'container',
                        'pagination' => [
                            'enabled' => true,
                            'el' => $mainGalleryWrapClass . ' .swiper-pagination',
                        ],
                        'navigation' => [
                            'nextEl' => $mainGalleryWrapClass . ' .swiper-button-next',
                            'prevEl' => $mainGalleryWrapClass . ' .swiper-button-prev'
                        ],
                        'slidesPerView' => 1,
                        'type' => 'detail_gallery_main',
                        "preloadImages" => false,
                    ],
                ],
                'THUMBS' => [
                    'SLIDE_CLASS_LIST' => 'gallery__item gallery__item--thumb swiper-slide pointer',
                    'PLUGIN_OPTIONS' => [
                        'direction' => ($bVertical ? 'vertical' : 'horizontal'),
                        'init' => false,
                        'loop' => false,
                        'navigation' => [
                            'nextEl' => '.gallery-slider-thumb-button--next',
                            'prevEl' => '.gallery-slider-thumb-button--prev',
                        ],
                        'pagination' => false,
                        'slidesPerView' => 'auto',
                        'spaceBetween' => 4,
                        'slidesOffsetAfter' => 1,
                        'type' => 'detail_gallery_thumb',
                        'watchSlidesProgress' => true,
                        'threshold' => 7,
                    ],
                ]
            ];
            if($arConfig['SHOW_THUMBS']){
                $gallerySetting['MAIN']['PLUGIN_OPTIONS']['thumbs'] =  [
                    'swiper' => '.gallery-slider-thumb',
                ];
            }
            if($bMagnifier){
                $gallerySetting['THUMBS']['PLUGIN_OPTIONS']['magnifier'] = true;
            }

            $gallerySetting['MAIN']['PLUGIN_OPTIONS'] = \Bitrix\Main\Web\Json::encode($gallerySetting['MAIN']['PLUGIN_OPTIONS']);
            $gallerySetting['THUMBS']['PLUGIN_OPTIONS'] = \Bitrix\Main\Web\Json::encode($gallerySetting['THUMBS']['PLUGIN_OPTIONS']);

            self::showBlockHtml([
                'FILE' => 'detail_gallery/main_gallery.php',
                'PARAMS' => [
                    'CONFIG' => $arConfig,
                    'GALLERY_SETTINGS' => $gallerySetting,
                    'ITEMS' => $arResult["MORE_PHOTO"],
                    'STICKERS' => $stickersHtml,
                ],
            ]);
        }

        public static function prepareValuesForMapInProjects(array $arConfig = []) {
            $arItem = $arConfig['ITEM'];
            $arSections = $arConfig['SECTIONS'];

            $arResult = [
                'NAME' => $arItem['NAME'],
                'PREVIEW_PICTURE_ID' => $arItem['PREVIEW_PICTURE'],
                'SECTION_NAME' => current($arSections)['NAME'],
                'MAP_DOP_INFO' => '',
                'DETAIL_PAGE_LINK' => $arItem['DETAIL_PAGE_URL'],
            ];

            if (is_array($arItem['PREVIEW_PICTURE']) && isset($arItem['PREVIEW_PICTURE']['ID'])) {
                $arResult['PREVIEW_PICTURE_ID'] = $arItem['PREVIEW_PICTURE']['ID'];
            }

            if (is_array($arItem['IBLOCK_SECTION_ID'])) {
                $sectionID = $arItem['IBLOCK_SECTION_ID'][0];

                $arResult['SECTION_NAME'] = $arSections[$sectionID]['NAME'];

                if ($arItem['DETAIL_PAGE_URL']) {
                    $arResult['DETAIL_PAGE_LINK'] = $arItem['DETAIL_PAGE_URL'][$sectionID];
                }
            }

            if($arItem['PROPERTY_INFO_VALUE']) {
                $arResult['MAP_DOP_INFO'] = is_array($arItem['PROPERTY_INFO_VALUE']) && isset($arItem['PROPERTY_INFO_VALUE']['TEXT']) ? $arItem['PROPERTY_INFO_VALUE']['TEXT'] : '';
            }

            return $arResult;
        }

        public static function showTitleH($title, $class = '') {

            return '<h2 class="top_block_title '.$class.'">'.$title.'</h2>';
        }

        public static function getTransferParams(array $arParams = [], array $arConfig = []) : array {
            return array_merge([
                "SHOW_ABSENT" => $arParams["SHOW_ABSENT"],
                "HIDE_NOT_AVAILABLE_OFFERS" => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],
                "PRICE_CODE" => $arParams["PRICE_CODE"],
                "OFFER_TREE_PROPS" => $arParams["OFFER_TREE_PROPS"],
                "OFFER_SHOW_PREVIEW_PICTURE_PROPS" => $arParams["OFFER_SHOW_PREVIEW_PICTURE_PROPS"],
                "CACHE_TIME" => $arParams["CACHE_TIME"],
                "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
                "CURRENCY_ID" => $arParams["CURRENCY_ID"],
                "OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
                "OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
                "OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
                "OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
                "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                "SHOW_DISCOUNT_TIME" => $arParams["SHOW_DISCOUNT_TIME"],
                "SHOW_COUNTER_LIST" => $arParams["SHOW_COUNTER_LIST"],
                "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
                "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
                "SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
                "SHOW_OLD_PRICE" => $arParams["SHOW_OLD_PRICE"],
                "SHOW_DISCOUNT_PERCENT" => $arParams["SHOW_DISCOUNT_PERCENT"],
                "SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
                "STORES" => $arParams["STORES"],
                "USE_REGION" => $arParams["USE_REGION"],
                "DEFAULT_COUNT" => $arParams["DEFAULT_COUNT"],
                "BASKET_URL" => $arParams["BASKET_URL"],
                "OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
                "PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
                "PARTIAL_PRODUCT_PROPERTIES" => $arParams["PARTIAL_PRODUCT_PROPERTIES"],
                "SHOW_ONE_CLICK_BUY" => $arParams["SHOW_ONE_CLICK_BUY"],
                "SHOW_DISCOUNT_TIME_EACH_SKU" => $arParams["SHOW_DISCOUNT_TIME_EACH_SKU"],
                "SHOW_ARTICLE_SKU" => $arParams["SHOW_ARTICLE_SKU"],
                "ADD_PICT_PROP" => $arParams["ADD_PICT_PROP"],
                "OFFER_ADD_PICT_PROP" => $arParams["OFFER_ADD_PICT_PROP"],
                "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"] ?? 'quantity',
            ], $arConfig);
        }

        public static function showCollapsedBlockFromText(string $text) {
            if($text)
                static::displayContentWithSpoiler($text);
        }

        public static function showCollapsedBlockFromPath(array $arConfig) {
            global $APPLICATION;

            ob_start();
                $APPLICATION->IncludeFile(SITE_DIR.$arConfig['CONTENT_PATH'], array(), array("MODE" => "html", "NAME" => GetMessage($arConfig['EDIT_MESSAGE_KEY'])));
            $content = ob_get_clean();

            if (!$content) return;

            static::displayContentWithSpoiler($content);

        }

        public static function displayContentWithSpoiler(string $content) {?>
            <div class="ordered-block__content">
                <div class="ordered-block__text-wrap">
                    <div class="ordered-block__text show-collapsed-js">
                        <?=$content;?>
                    </div>
                        <div class="ordered-block__spoiler colored">
                            <button class="ordered-block__spoiler-btn btn--no-btn-appearance">
                                <?= Solution::showIconSvg("arrow", SITE_TEMPLATE_PATH.'/images/svg/arrow_down_accordion.svg', '', '', true, false); ?>
                                <span class="ordered-block__spoiler-btn-label"
                                data-show="<?=GetMessage("MORE_DETAIL_TEXT");?>"
                                data-hide="<?=GetMessage("HIDE_DETAIL_TEXT");?>"></span>
                            </button>
                        </div>
                </div>
            </div>
            <?
        }

        public static function getCustomBlocks($siteID = SITE_ID) {
            $arNewOptions = [];
            $customBlocksIblock = Cache::$arIBlocks[$siteID]['aspro_'.Solution::solutionName.'_mainblocks']['aspro_'.Solution::solutionName.'_mainblocks'][0] ?? false;

            if(!$customBlocksIblock)
                return;

            $arSectionFilter = array(
                'IBLOCK_ID' => $customBlocksIblock,
                'ACTIVE' => 'Y',
            );
            $arSectionSelect = array(
                'ID',
                'NAME',
                'CODE',
            );
            $arSections = Cache::CIblockSection_GetList(
                array(
                    'SORT' => 'ASC',
                    'ID' => 'ASC',
                    "CACHE" => array(
                        "TAG" => Cache::GetIBlockCacheTag($customBlocksIblock)
                    )
                ),
                $arSectionFilter,
                false,
                $arSectionSelect
            );
            if ($arSections) {
                $arSectionsID = array();
                foreach ($arSections as $arSection) {
                    $arSectionsID[] = $arSection['ID'];
                }

                $arElementFilter = array(
                    'IBLOCK_ID' => $customBlocksIblock,
                    'ACTIVE' => 'Y',
                    'IBLOCK_SECTION_ID' => $arSectionsID,
                );
                $arElementSelect = array(
                    'ID',
                    'NAME',
                    'CODE',
                    'PREVIEW_PICTURE',
                    'IBLOCK_SECTION_ID',
                );
                $arElements = Cache::CIblockElement_GetList(
                    array(
                        'SORT' => 'ASC',
                        'ID' => 'ASC',
                        "CACHE" => array(
                            "TAG" => Cache::GetIBlockCacheTag($customBlocksIblock),
                            "MULTI" => "Y",
                            'GROUP' => array('IBLOCK_SECTION_ID')
                        )
                    ),
                    $arElementFilter,
                    false,
                    false,
                    $arElementSelect
                );

                foreach ($arSections as $arSection) {
                    if ($arElements[$arSection['ID']]) {
                        $arElementsTemplate = array(
                            'TITLE' => Loc::getMessage('CUSTOM_BLOCK_SECTION').$arSection['NAME'],
                            'TYPE' => 'selectbox',
                            'IS_ROW' => 'Y',
                            'LIST' => array(),
                            'PREVIEW' => array(
                                'SCROLL_BLOCK' => '.'.strtoupper($arSection['CODE']),
                                'URL' => '',
                            ),
                        );
                        $currentTemplate = '';
                        foreach ($arElements[$arSection['ID']] as $arElement) {
                            if (!$currentTemplate) {
                                $currentTemplate = $arElement['CODE'];
                            }
                            $src = '';
                            if ($arElement['PREVIEW_PICTURE']) {
                                $img = \CFile::ResizeImageGet(
                                    $arElement['PREVIEW_PICTURE'],
                                    array( "width" => 200, "height" => 200 ),
                                    BX_RESIZE_IMAGE_PROPORTIONAL,
                                    true
                                );
                                $src = $img['src'];
                            }
                            $arElementsTemplate['LIST'][$arElement['CODE']] = array(
                                'TITLE' => $arElement['NAME'],
                                'IMG' => $src,
                                'ROW_CLASS' => 'col-md-4',
                                'POSITION_BLOCK' => 'block',
                                'IN_BLOCK' => 'Y',
                            );
                        }


                        $newOptionCode = strtoupper($arSection['CODE']);
                        $arNewOptions[$newOptionCode] = array(
                            'TITLE' => $arSection['NAME'],
                            'THEME' => 'Y',
                            'TYPE' => 'checkbox',
                            'DEFAULT' => 'Y',
                            'ONE_ROW' => 'Y',
                            'SMALL_TOGGLE' => 'Y',
                            'FON' => 'N',
                            'TEMPLATE' => $arElementsTemplate,
                        );
                        $arNewOptions[$newOptionCode]['TEMPLATE']['DEFAULT'] = $currentTemplate;
                    }
                }
            }
            return $arNewOptions;
        }
        public static function getGridClassByCount($arBreakPoints = [], $elementInRow, $prefix = 'from') {
            $strClassGrid = " grid-list--items";

            if($elementInRow > 1){
                $strClassGrid .= ' grid-list--items-2-from-601 ';
            }

            if($elementInRow > 2) {

                natsort($arBreakPoints);
                $arBreakPoints = array_values($arBreakPoints);

                $arClass = [];
                for($i = count($arBreakPoints)-1; $i >= 0; $i--){
                    if ($elementInRow < 2) break;
                    $arClass[] = ' grid-list--items-'. ($elementInRow--) .'-'.implode('-', array_filter([$prefix, $arBreakPoints[$i]]));
                }

                $strClassGrid .= implode(' ', $arClass);
            }

            return $strClassGrid;
        }

        public static function showTitleBlock($arOptions = [])
        {
            global $APPLICATION;
            $arDefaultOptions = [
                'WRAPPER' => true,
                'VISIBLE' => true,
                'PATH' => '',
                'POSITION' => 'TOP',
                'PARAMS' => [],
                'ITEM' => [],
                'TITLE_PREFIX' => '',
            ];
            $arConfig = array_merge($arDefaultOptions, $arOptions);

            if ($handler = self::getCustomFunc(__FUNCTION__)) {
                return call_user_func_array($handler, [$arConfig]);
            }

            $bShowTitle = ($arConfig['PARAMS']['SHOW_TITLE_IN_BLOCK'] === 'Y' || $arConfig['PARAMS']['SHOW_TITLE']) && $arConfig['PARAMS']['TITLE'];
            $bShowTitleLink = $arConfig['PARAMS']['RIGHT_TITLE'] && $arConfig['PARAMS']['RIGHT_LINK'];

            ob_start();
            ?>
                <?if($bShowTitle && $arConfig['VISIBLE']):?>
                    <?if($arConfig['WRAPPER']):?>
                    <div class="maxwidth-theme">
                    <?endif?>
                        <?
                        $bExternalLink = $arConfig['PARAMS']['RIGHT_LINK_EXTERNAL'];
                        $path = SITE_DIR.'include/mainpage/text-under-title/'.$arConfig['PATH'].'.php';
                        $bShowContent = $bShowTitle && Solution::checkContentFile($path) && ($arConfig['PARAMS']['SHOW_PREVIEW_TEXT'] === 'Y');
                        ?>

                        <div class="top_block">

                            <?=self::showTitleH($arConfig['TITLE_PREFIX'].$arConfig['PARAMS']['TITLE']);?>

                            <?if ($arConfig['PARAMS']['RIGHT_LINK']):?>
                                <?if ($bExternalLink):?>
                                    <a class="pull-right font_upper muted" href="<?=$arConfig['PARAMS']['RIGHT_LINK']?>" target="_blank" rel="nofollow">
                                <?else:?>
                                    <a class="pull-right font_upper muted" href="<?=preg_replace('/(?<!:)[\/]{2,}/', '/', SITE_DIR.$arConfig['PARAMS']['RIGHT_LINK'])?>">
                                <?endif;?>
                                    <?=$arConfig['PARAMS']['RIGHT_TITLE']?>
                                </a>
                            <?endif;?>

                            <?if($bShowContent):?>
                                <div class="text_before_items font_xs mt mt--16">
                                    <?$APPLICATION->IncludeFile($path, Array(), Array("MODE" => "html", "TEMPLATE" => "include_area.php", "NAME" => GetMessage("SECTIONS_INCLUDE_TEXT")));?>

                                </div>
                            <?endif;?>

                        </div>

                    <?if($arConfig['WRAPPER']):?>
                    </div>
                    <?endif;?>
                <?endif;?>
            <?
            $html = ob_get_contents();
            ob_end_clean();

            // event for manipulation
            foreach (GetModuleEvents(self::MODULE_ID, 'OnAspro'.ucfirst(__FUNCTION__), true) as $arEvent) {
                ExecuteModuleEventEx($arEvent, array($arConfig, &$html));
            }

            echo $html;
        }

        public static function processRetriveOptionValue(string $optionCode, string $optionValue): string {
            $value = self::getOptionValueFromSession($optionCode) ?: $optionValue ?: Solution::GetFrontParametrValue($optionCode);

            self::setValueInSessionForWidget($optionCode, $value);

            return $value;
        }

        public static function getOptionValueFromSession(string $optionCode): string {
            $value = '';
            if ($_SESSION['THEME'][SITE_ID][$optionCode]) {
                $value = $_SESSION['THEME'][SITE_ID][$optionCode];
            }
            return $value;
        }

        public static function setValueInSessionForWidget(string $optionCode, string $optionValue): void {
            global $arMergeOptions;

            $arMergeOptions[$optionCode] = $optionValue;
        }

        public static function getSectionInheritedUF($arParams = array()) {
            $arResult = array();

            if ($arParams) {
                $iblockId = $arParams['iblockId'] ?? false;
                $sectionId = $arParams['sectionId'] ?? false;

                $arParams['select'] = $arParams['select'] ?? array();
                $arSelect = is_array($arParams['select']) ? $arParams['select'] : array();
                $arSelect[] = 'ID';
                $arSelect[] = 'IBLOCK_ID';
                $arSelect[] = 'IBLOCK_SECTION_ID';

                $arParams['filter'] = $arParams['filter'] ?? array();
                $arFilter = is_array($arParams['filter']) ? $arParams['filter'] : array();

                $arParams['enums'] = $arParams['enums'] ?? array();
                $arEnums = is_array($arParams['enums']) ? $arParams['enums'] : array();

                if ($sectionId && $arSelect) {
                    $arFilter['ID'] = $sectionId;

                    if ($iblockId) {
                        $arFilter['IBLOCK_ID'] = $iblockId;
                    }
                    elseif ($arFilter['IBLOCK_ID']) {
                        $iblockId = $arFilter['IBLOCK_ID'];
                    }

                    $arSection = \CMaxCache::CIBlockSection_GetList(
                        array(
                            'CACHE' => array(
                                'MULTI' => 'N',
                                'TAG' => \CMaxCache::GetIBlockCacheTag($iblockId),
                            )
                        ),
                        $arFilter,
                        false,
                        $arSelect
                    );

                    if ($arSection) {
                        foreach ($arSelect as $i => $field) {
                            if (strpos($field, 'UF_') !== false) {
                                if ( array_key_exists($field, $arSection) && $arSection[$field] ) {
                                    unset($arSelect[$i]);

                                    if (in_array($field, $arEnums)) {
                                        $dbRes = \CUserFieldEnum::GetList(array(), array("ID" => $arSection[$field]));
                                        if ($arValue = $dbRes->GetNext()) {
                                            $arResult[$field] = $arValue['XML_ID'];
                                        }
                                    }
                                    else{
                                        $arResult[$field] = $arSection[$field];
                                    }
                                }
                            }
                        }

                        if (
                            $arSelect &&
                            $arSection['IBLOCK_SECTION_ID']
                        ) {
                            $arResult = array_merge($arResult, static::getSectionInheritedUF(
                                array(
                                    'iblockId' => $arSection['IBLOCK_ID'],
                                    'sectionId' => $arSection['IBLOCK_SECTION_ID'],
                                    'select' => $arSelect,
                                    'filter' => $arParams['filter'],
                                    'enums' => $arParams['enums'],
                                )
                            ));
                        }
                    }
                }
            }

            return $arResult;
        }

        public static function showBackUrl(array $arOptions)
        {
            $url = $arOptions['URL'];
            $text = $arOptions['TEXT'];

            ob_start();
            ?>
            <a class="muted back-url url-block" href="<?=$url?>">
                <?=Solution::showIconSvg("return_to_the_list", SITE_TEMPLATE_PATH."/images/svg/return_to_the_list.svg", "");?>
                <span class="font_upper back-url-text"><?=$text?></span>
            </a>
            <?
            $html = ob_get_contents();
            ob_end_clean();

            // event for manipulation
            foreach (GetModuleEvents(self::MODULE_ID, 'OnAspro'.ucfirst(__FUNCTION__), true) as $arEvent) {
                ExecuteModuleEventEx($arEvent, array($arOptions, &$html));
            }

            echo $html;
        }
    }
}?>

<?
use Aspro\Max\Preset,
Aspro\Max\Utils;

global $arTheme, $USER;

$bHideOnNarrow = $arTheme['BIGBANNER_HIDEONNARROW']['VALUE'] === 'Y';
$currentBannerIndex = intval($arParams['CURRENT_BANNER_INDEX']) > 0 ? intval($arParams['CURRENT_BANNER_INDEX']) - 1 : 0;

$templateData = array(
	'BANNERS_COUNT' => count($arResult['ITEMS']),
	'CURRENT_BANNER_INDEX' => $currentBannerIndex,
	'CURRENT_BANNER_COLOR' => '',
);
?>
<div class="top_slider_wrapp maxwidth-banner view_<?=$arResult['BIGBANNER_MOBILE']?><?=($bHideOnNarrow ? ' hidden_narrow' : '')?>">
	<?
	$arOptions = [
		// Disable preloading of all images
		'preloadImages' => false,
		// Enable lazy loading
		// 'lazy' => [
		// 	'loadPrevNext' => true,
		// ],
		//enable hash navigation
  		// 'hashNavigation' =>  true,
		//   'loopFillGroupWithBlank' => true,
		'keyboard' => true,
		'init' => false,
		'countSlides' => count($arResult["ITEMS"][$arParams["BANNER_TYPE_THEME"]]["ITEMS"]),
		'type' => 'main_banner'
	];
	if ($arOptions['countSlides'] > 10) {
		$arOptions['pagination']['dynamicBullets'] = true;
		$arOptions['pagination']['dynamicMainBullets'] = 3;
	}
	if ($arOptions['countSlides'] > 1) {
		$arOptions['loop'] = true;
	}

    $opacityClasses = [];
    if($arResult['BIGBANNER_MOBILE'] == 3) {
        $opacityClasses[] = 'banners-big--contrast-cover-desktop';
    }
	?>
	<div class="swiper slider-solution main-slider navigation_on_hover navigation_offset swipeignore" data-plugin-options='<?=json_encode($arOptions)?>' data-index="<?=$currentBannerIndex;?>">
		<div class="swiper-wrapper main-slider__wrapper">
			<?$bShowH1 = false;?>
			<?$strTypeHitProp = \Bitrix\Main\Config\Option::get('aspro.max', 'ITEM_STICKER_CLASS_SOURCE', 'PROPERTY_VALUE');?>
			<?foreach($arResult["ITEMS"][$arParams["BANNER_TYPE_THEME"]]["ITEMS"] as $i => $arItem):?>
				<?
				$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
				$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
				$background = is_array($arItem["DETAIL_PICTURE"]) ? $arItem["DETAIL_PICTURE"]["SRC"] : $this->GetFolder()."/images/background.jpg";
				$target = $arItem["PROPERTIES"]["TARGETS"]["VALUE_XML_ID"];
				$arItem["NAME"] = strip_tags($arItem["~NAME"]);

				$bannerColor = $arItem['PROPERTIES']['DARK_MENU_COLOR']['VALUE'] !== 'Y' ? 'light' : 'dark';
                $bOpacity = isset($arItem['PROPERTIES']['BANNER_OPACITY']['VALUE_XML_ID']) && $arItem['PROPERTIES']['BANNER_OPACITY']['VALUE_XML_ID'] === 'Y';

                if($bOpacity) {
                    $opacityClasses[] = 'banners-big__item--opacity';
                }

				// first visible slide is the first item or $currentBannerIndex
				// saving his color for to usage in component_epilog
				if (
					$i == $currentBannerIndex ||
					!$i
				) {
					$templateData['CURRENT_BANNER_COLOR'] = $bannerColor;
				}

				// video options
				$videoSource = strlen($arItem['PROPERTIES']['VIDEO_SOURCE']['VALUE_XML_ID']) ? $arItem['PROPERTIES']['VIDEO_SOURCE']['VALUE_XML_ID'] : 'LINK';
				$videoSrc = $arItem['PROPERTIES']['VIDEO_SRC']['VALUE'];
				if($videoFileID = $arItem['PROPERTIES']['VIDEO']['VALUE']){
					$videoFileSrc = CFile::GetPath($videoFileID);
				}
				$videoPlayer = $videoPlayerSrc = '';
				if($bShowVideo = $arItem['PROPERTIES']['SHOW_VIDEO']['VALUE_XML_ID'] === 'YES' && ($videoSource == 'LINK' ? strlen($videoSrc) : strlen($videoFileSrc))){
					$colorSubstrates = ($arItem['PROPERTIES']['COLOR_SUBSTRATES']['VALUE_XML_ID'] ? $arItem['PROPERTIES']['COLOR_SUBSTRATES']['VALUE_XML_ID'] : '');
					$buttonVideoText = $arItem['PROPERTIES']['BUTTON_VIDEO_TEXT']['VALUE'];
					$bVideoLoop = $arItem['PROPERTIES']['VIDEO_LOOP']['VALUE_XML_ID'] === 'YES';
					$bVideoDisableSound = $arItem['PROPERTIES']['VIDEO_DISABLE_SOUND']['VALUE_XML_ID'] === 'YES';
					$bVideoAutoStart = $arItem['PROPERTIES']['VIDEO_AUTOSTART']['VALUE_XML_ID'] === 'YES';
					$bVideoCover = $arItem['PROPERTIES']['VIDEO_COVER']['VALUE_XML_ID'] === 'YES';
					$bVideoUnderText = $arItem['PROPERTIES']['VIDEO_UNDER_TEXT']['VALUE_XML_ID'] === 'YES';
					if(strlen($videoSrc) && $videoSource === 'LINK'){
						// videoSrc available values
						// YOTUBE:
						// https://youtu.be/WxUOLN933Ko
						// <iframe width="560" height="315" src="https://www.youtube.com/embed/WxUOLN933Ko" frameborder="0" allowfullscreen></iframe>
						// VIMEO:
						// https://vimeo.com/211336204
						// <iframe src="https://player.vimeo.com/video/211336204?title=0&byline=0&portrait=0" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
						// RUTUBE:
						// <iframe width="720" height="405" src="//rutube.ru/play/embed/10314281" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>

						$videoPlayer = 'YOUTUBE';
						$videoSrc = htmlspecialchars_decode($videoSrc);
						if(strpos($videoSrc, 'iframe') !== false){
							$re = '/<iframe.*src=\"(.*)\".*><\/iframe>/isU';
							preg_match_all($re, $videoSrc, $arMatch);
							$videoSrc = $arMatch[1][0];
						}
						$videoPlayerSrc = $videoSrc;

						switch($videoSrc){
							case(($v = strpos($videoSrc, 'vimeo.com/')) !== false):
								$videoPlayer = 'VIMEO';
								if(strpos($videoSrc, 'player.vimeo.com/') === false){
									$videoPlayerSrc = str_replace('vimeo.com/', 'player.vimeo.com/', $videoPlayerSrc);
								}
								if(strpos($videoSrc, 'vimeo.com/video/') === false){
									$videoPlayerSrc = str_replace('vimeo.com/', 'vimeo.com/video/', $videoPlayerSrc);
								}
								break;
							case(($v = strpos($videoSrc, 'rutube.ru/')) !== false):
								$videoPlayer = 'RUTUBE';
								break;
							case(strpos($videoSrc, 'watch?') !== false && ($v = strpos($videoSrc, 'v=')) !== false):
								$videoPlayerSrc = 'https://www.youtube.com/embed/'.substr($videoSrc, $v + 2, 11);
								break;
							case(strpos($videoSrc, 'youtu.be/') !== false && $v = strpos($videoSrc, 'youtu.be/')):
								$videoPlayerSrc = 'https://www.youtube.com/embed/'.substr($videoSrc, $v + 9, 11);
								break;
							case(strpos($videoSrc, 'embed/') !== false && $v = strpos($videoSrc, 'embed/')):
								$videoPlayerSrc = 'https://www.youtube.com/embed/'.substr($videoSrc, $v + 6, 11);
								break;
						}

						$bVideoPlayerYoutube = $videoPlayer === 'YOUTUBE';
						$bVideoPlayerVimeo = $videoPlayer === 'VIMEO';
						$bVideoPlayerRutube = $videoPlayer === 'RUTUBE';

						if(strlen($videoPlayerSrc)){
							$videoPlayerSrc = trim($videoPlayerSrc.
								($bVideoPlayerYoutube ? '?autoplay=1&enablejsapi=1&controls=0&showinfo=0&rel=0&disablekb=1&iv_load_policy=3' :
								($bVideoPlayerVimeo ? '?autoplay=1&badge=0&byline=0&portrait=0&title=0' :
								($bVideoPlayerRutube ? '?quality=1&autoStart=0&sTitle=false&sAuthor=false&platform=someplatform' : '')))
							);
						}
					}
					else{
						$videoPlayer = 'HTML5';
						$videoPlayerSrc = $videoFileSrc;
					}
				}
				if ($bShowVideo && $videoPlayerSrc) {
					$templateData['HAS_VIDEO'] = true;
				}

				$bSwiperLazy = ($currentBannerIndex != $i);
				?>
				<div class="swiper-slide main-slider__item box <?=($arItem["PROPERTIES"]["TEXTCOLOR"]["VALUE_XML_ID"] ? " ".$arItem["PROPERTIES"]["TEXTCOLOR"]["VALUE_XML_ID"] : "");?><?=($arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"] ? " ".$arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"] : " left");?><?=($bShowVideo ? ' wvideo' : '');?>"
					data-nav_color="<?=($arItem["PROPERTIES"]["NAV_COLOR"]["VALUE_XML_ID"] ? $arItem["PROPERTIES"]["NAV_COLOR"]["VALUE_XML_ID"] : "");?>"
					data-text_color="<?=$bannerColor?>"
					data-slide_index="<?=$i?>"
					<?=($bShowVideo ? ' data-video_source="'.$videoSource.'"' : '')?>
					<?=(strlen($videoPlayer) ? ' data-video_player="'.$videoPlayer.'"' : '')?>
					<?=(strlen($videoPlayerSrc) ? ' data-video_src="'.$videoPlayerSrc.'"' : '')?>
					<?=($bVideoAutoStart ? ' data-video_autoplay="1"' : '')?>
					<?=($bVideoDisableSound ? ' data-video_disable_sound="1"' : '')?>
					<?=($bVideoLoop ? ' data-video_loop="1"' : '')?>
					<?=($bVideoCover ? ' data-video_cover="1"' : '')?>
					<?if ($bSwiperLazy):?>
						style="background-image: url('<?=\Aspro\Functions\CAsproMax::showBlankImg($background)?>');"
						data-src="<?=$background;?>"
					<?else:?>
						style="background-image: url('<?=$background?>');"
						data-bg=""
					<?endif;?>
				>
					<div id="<?=$this->GetEditAreaId($arItem['ID']);?>" class="<?=Aspro\Max\Utils::implodeClasses($opacityClasses)?>">
						<?if($arItem["PROPERTIES"]["URL_STRING"]["VALUE"]):?>
							<a class="target" href="<?=$arItem["PROPERTIES"]["URL_STRING"]["VALUE"]?>" <?=(strlen($target) ? 'target="'.$target.'"' : '')?>></a>
						<?endif;?>
						<div class="wrapper_inner">
							<?
							$position = "0% 100%";
							if($arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"])
							{
								if($arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"] == "left")
									$position = "100% 100%";
								elseif($arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"] == "right")
									$position = "0% 100%";
								else
									$position = "center center";
							}
							?>
							<table>
								<tbody>
									<?if($arResult['BIGBANNER_MOBILE'] == 3) {
										$tabletImgSrc = ($arItem["PROPERTIES"]['TABLET_IMAGE']['VALUE'] ? CFile::GetPath($arItem["PROPERTIES"]['TABLET_IMAGE']['VALUE']) : $background);
									}?>
									<tr class="main_info js-notice-block "
									<?if ($bSwiperLazy):?>
										style="background-image: url('<?=\Aspro\Functions\CAsproMax::showBlankImg($arResult['BIGBANNER_MOBILE'] == 3 ? $tabletImgSrc : $background)?>');"
										data-src="<?=($arResult['BIGBANNER_MOBILE'] == 3 ? $tabletImgSrc : $background)?>"
									<?else:?>
										style="background-image: url('<?=($arResult['BIGBANNER_MOBILE'] == 3 ? $tabletImgSrc : $background)?>');"
										data-bg=""
									<?endif;?>
									>
										<?if($arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"] != "image"):?>
											<?ob_start();?>
												<td class="text <?=$arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"];?>">
													<?if($arItem["PROPERTIES"]["LINK_ITEM"]["VALUE"]):?>
														<?
														$hitProp = (isset($arParams["HIT_PROP"]) ? $arParams["HIT_PROP"] : "HIT");
														$saleProp = (isset($arParams["SALE_PROP"]) ? $arParams["SALE_PROP"] : "SALE_TEXT");

														$arSelect = array("ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL", "PROPERTY_vote_count", "PROPERTY_rating", "PROPERTY_vote_sum", "CATALOG_TYPE", "PROPERTY_EXTENDED_REVIEWS_RAITING", "PROPERTY_EXTENDED_REVIEWS_COUNT");
														if($hitProp)
															$arSelect[] = "PROPERTY_".$hitProp;
														if($saleProp)
															$arSelect[] = "PROPERTY_".$saleProp;

														$arPricesID = array();
														if(!$arParams["PRICE_CODE_IDS"])
														{
															$dbPriceType = \CCatalogGroup::GetList(
																array("SORT" => "ASC"),
																array("NAME" => $arParams["PRICE_CODE"])
																);
															while($arPriceType = $dbPriceType->Fetch())
															{
																$arParams["PRICE_CODE_IDS"][] = array(
																	"ID" => $arPriceType["ID"]
																);
															}
														}
														if($arParams["PRICE_CODE_IDS"])
														{
															foreach($arParams["PRICE_CODE_IDS"] as $arPrices)
															{
																$arSelect[] = "CATALOG_GROUP_".$arPrices["ID"];
																$arPricesID[] = $arPrices["ID"];
															}
														}
														$arProduct = CMaxCache::CIBLockElement_GetList(array('CACHE' => array("MULTI" => "N", "TAG" => CMaxCache::GetIBlockCacheTag($arItem["PROPERTIES"]["LINK_ITEM"]["LINK_IBLOCK_ID"]))), array("IBLOCK_ID" => $arItem["PROPERTIES"]["LINK_ITEM"]["LINK_IBLOCK_ID"], "ACTIVE"=>"Y", "ACTIVE_DATE" => "Y", "ID" => $arItem["PROPERTIES"]["LINK_ITEM"]["VALUE"]), false, false, $arSelect);
														$arPriceList = \Aspro\Functions\CAsproMax::getPriceList($arProduct["ID"], $arPricesID, 1, true);

														?>
														<div class="banner_title item_info">
															<?if((($hitProp && $arProduct['PROPERTY_'.$hitProp.'_VALUE']) || ($saleProp && $arProduct['PROPERTY_'.$saleProp.'_VALUE'])) && $arItem["PROPERTIES"]["SHOW_STICKERS"]["VALUE"] == "Y"):?>
																<div class="stickers">
																	<?if($saleProp && $arProduct['PROPERTY_'.$saleProp.'_VALUE']):?>
																		<div class="sticker_sale_text"><?=$arProduct['PROPERTY_'.$saleProp.'_VALUE']?></div>
																	<?endif;?>
																	<?if($hitProp && $arProduct['PROPERTY_'.$hitProp.'_VALUE']):?>
																		<?foreach((array)$arProduct['PROPERTY_'.$hitProp.'_VALUE'] as $key => $value):?>
																			<?
																			$enumID = ((is_array($arProduct['PROPERTY_'.$hitProp.'_ENUM_ID'])) ? $arProduct['PROPERTY_'.$hitProp.'_ENUM_ID'][$key] : $arProduct['PROPERTY_'.$hitProp.'_ENUM_ID']);
																			$arTmpEnum = CIBlockPropertyEnum::GetByID($enumID);?>
																			<div class="sticker_<?=($strTypeHitProp == "PROPERTY_VALUE" ? CUtil::translit($value, 'ru') : strtolower($arTmpEnum["XML_ID"]));?>"><?=$value?></div>
																		<?endforeach;?>
																	<?endif;?>
																</div>
															<?endif;?>

															<?if($arItem['PROPERTIES']['TITLE_H1']['VALUE'] == "Y" && !$bShowH1):?>
																<h1 class="head-title js-notice-block__title">
															<?else:?>
																<span class="head-title js-notice-block__title">
															<?endif;?>

																<?if($arProduct["DETAIL_PAGE_URL"]):?>
																	<a href="<?=$arProduct["DETAIL_PAGE_URL"]?>" <?=(strlen($target) ? 'target="'.$target.'"' : '')?>>
																<?endif;?>
																<?=$arProduct["NAME"];?>
																<?if($arProduct["DETAIL_PAGE_URL"]):?>
																	</a>
																<?endif;?>

															<?if($arItem['PROPERTIES']['TITLE_H1']['VALUE'] == "Y" && !$bShowH1):?>
																<?$bShowH1 = true;?>
																</h1>
															<?else:?>
																</span>
															<?endif;?>
															<?
															$bHasOffers = (isset($arProduct["CATALOG_TYPE"]) && $arProduct["CATALOG_TYPE"] == 3);

															if($bHasOffers)
															{
																$arSelect = array("ID", "IBLOCK_ID", "NAME", "CATALOG_QUANTITY");
																$arOffers = CMaxCache::CIBLockElement_GetList(array('CACHE' => array("MULTI" => "Y", "TAG" => CMaxCache::GetIBlockCacheTag($arItem["PROPERTIES"]["LINK_ITEM"]["LINK_IBLOCK_ID"]))), array("PROPERTY_CML2_LINK" => $arProduct["ID"], "ACTIVE"=>"Y", "ACTIVE_DATE" => "Y"), false, false, $arSelect);
																$arProduct["OFFERS"] = $arOffers;
															}

															$arPrice = CCatalogProduct::GetOptimalPrice($arProduct["ID"], 1, $USER->GetUserGroupArray(), 'N', $arPriceList);
															$totalCount = CMax::GetTotalCount($arProduct, $arParams);
															$arQuantityData = CMax::GetQuantityArray($totalCount, array('ID' => $arProduct["ID"]), "N", false );
															$strMeasure = '';

															if($arProduct["CATALOG_MEASURE"] && $arParams["SHOW_MEASURE"] !== "N")
															{
																$arMeasure = CCatalogMeasure::getList(array(), array("ID" => $arProduct["CATALOG_MEASURE"]), false, false, array())->GetNext();
																$strMeasure = $arMeasure["SYMBOL_RUS"];
															}?>

															<?if($arQuantityData["HTML"] || $arItem['PROPERTIES']['SHOW_RATING']['VALUE'] == "Y"):?>

																<div class="votes_block nstar">
																	<?if($arItem['PROPERTIES']['SHOW_RATING']['VALUE'] == "Y"):?>
																		<div class="ratings">
																			<?
																			if($arParams['REVIEWS_VIEW'] == 'EXTENDED'):?>
																				<?$message = $arProduct['PROPERTY_EXTENDED_REVIEWS_RAITING_VALUE'] ? GetMessage('VOTES_RESULT', array('#VALUE#' => $arProduct['PROPERTY_EXTENDED_REVIEWS_RAITING_VALUE'])) : GetMessage('VOTES_RESULT_NONE')?>
																				<div class="inner_rating" title="<?=$message?>">
																					<?for($i=1;$i<=5;$i++):?>
																						<div class="item-rating <?=$i<=$arProduct['PROPERTY_EXTENDED_REVIEWS_RAITING_VALUE'] ? 'filed' : ''?>"><?=CMax::showIconSvg("star", SITE_TEMPLATE_PATH."/images/svg/catalog/star_small.svg");?></div>
																					<?endfor;?>

																					<?if($arProduct['PROPERTY_EXTENDED_REVIEWS_COUNT_VALUE']):?>
																						<span class="font_sxs"><?=$arProduct['PROPERTY_EXTENDED_REVIEWS_COUNT_VALUE']?></span>
																					<?endif;?>
																				</div>
																			<?else:?>
																				<?
																				if($arProduct["PROPERTY_VOTE_COUNT_VALUE"])
																					$display_rating = round($arProduct["PROPERTY_VOTE_SUM_VALUE"]/$arProduct["PROPERTY_VOTE_COUNT_VALUE"], 2);
																				else
																					$display_rating = 0;
																				?>
																				<div class="inner_rating">
																					<?for($i=1;$i<=5;$i++):?>
																						<div class="item-rating <?=(round($display_rating) >= $i ? "filed" : "");?>"><?=CMax::showIconSvg("star", SITE_TEMPLATE_PATH."/images/svg/star.svg");?></div>
																					<?endfor;?>
																				</div>
																			<?endif;?>
																		</div>
																	<?endif;?>
																	<div class="sa_block">
																		<?if($arQuantityData["HTML"]):?>
																			<?=$arQuantityData["HTML"];?>
																		<?endif;?>
																	</div>
																</div>
															<?endif;?>

															<?if($arItem['PROPERTIES']['SHOW_DATE_SALE']['VALUE'] == "Y"):?>
																<?\Aspro\Functions\CAsproMax::showDiscountCounter($totalCount, $arPrice["DISCOUNT"], $arQuantityData, $arProduct, $strMeasure);?>
															<?endif;?>

															<?if($arPrice["RESULT_PRICE"] && $arItem['PROPERTIES']['SHOW_PRICES']['VALUE'] == "Y"):?>
																<?
																$price = $arPrice["RESULT_PRICE"]["DISCOUNT_PRICE"];
																$arFormatPrice = $arPrice["RESULT_PRICE"];
																$arCurrencyParams = array();
																if($arParams["CONVERT_CURRENCY"] != "Y" && $arPrice["RESULT_PRICE"]["CURRENCY"] != $arPrice["PRICE"]["CURRENCY"])
																{
																	$price = roundEx(CCurrencyRates::ConvertCurrency($arPrice["RESULT_PRICE"]["DISCOUNT_PRICE"], $arPrice["RESULT_PRICE"]["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]),CATALOG_VALUE_PRECISION);
																	$arFormatPrice = $arPrice["PRICE"];
																}
																if($arParams["CONVERT_CURRENCY"] == "Y" && $arParams["CURRENCY_ID"])
																{
																	$arCurrencyInfo = CCurrency::GetByID($arParams["CURRENCY_ID"]);
																	if (is_array($arCurrencyInfo) && !empty($arCurrencyInfo))
																	{
																		$arCurrencyParams["CURRENCY_ID"] = $arCurrencyInfo["CURRENCY"];
																		$price = CCurrencyRates::ConvertCurrency($arPrice["RESULT_PRICE"]["DISCOUNT_PRICE"], $arPrice["RESULT_PRICE"]["CURRENCY"], $arCurrencyParams["CURRENCY_ID"]);
																		$arFormatPrice["CURRENCY"] = $arCurrencyParams["CURRENCY_ID"];
																	}
																}
																?>
																<div class="prices">
																	<span class="price font_lg">
																		<span class="values_wrapper"><?=($bHasOffers ? GetMessage("FROM")." " : "");?><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($price, $arFormatPrice, false);?></span>
																		<?if($strMeasure):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
																	</span>
																	<?if($arItem['PROPERTIES']['SHOW_OLD_PRICE']['VALUE'] == "Y" && ($arPrice["RESULT_PRICE"]["BASE_PRICE"] != $arPrice["RESULT_PRICE"]["DISCOUNT_PRICE"])):?>
																		<span class="price price_old font_sm">
																			<?if($arCurrencyParams)
																				$arPrice["RESULT_PRICE"]["BASE_PRICE"] = CCurrencyRates::ConvertCurrency($arPrice["RESULT_PRICE"]["BASE_PRICE"], $arPrice["RESULT_PRICE"]["CURRENCY"], $arCurrencyParams["CURRENCY_ID"])?>
																			<span class="values_wrapper"><?=($bHasOffers ? GetMessage("FROM")." " : "");?><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($arPrice["RESULT_PRICE"]["BASE_PRICE"], $arFormatPrice, false);?></span>
																			<?if($strMeasure):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
																		</span>
																	<?endif;?>
																</div>
																<?if($arItem['PROPERTIES']['SHOW_DISCOUNT']['VALUE'] == "Y" && $arPrice["RESULT_PRICE"]["DISCOUNT"]):?>
																	<div class="sale_block">
																		<div class="sale-number rounded2 font_xxs">
																			<div class="value">-<span><?=$arPrice["RESULT_PRICE"]["PERCENT"]?></span>%</div>
																			<div class="inner-sale rounded1">
																				<span><?=GetMessage("CATALOG_ITEM_ECONOMY");?></span>
																				<span class="price">
																					<?if($arCurrencyParams)
																					$arPrice["RESULT_PRICE"]["DISCOUNT"] = CCurrencyRates::ConvertCurrency($arPrice["RESULT_PRICE"]["DISCOUNT"], $arPrice["RESULT_PRICE"]["CURRENCY"], $arCurrencyParams["CURRENCY_ID"])?>
																					<span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($arPrice["RESULT_PRICE"]["DISCOUNT"], $arFormatPrice, false);?></span>
																				</span>
																			</div>
																		</div>
																	</div>
																<?endif;?>
															<?endif;?>
														</div>

														<div class="banner_buttons with_actions <?=$arProduct["CATALOG_TYPE"];?>">
															<a href="<?=$arProduct["DETAIL_PAGE_URL"]?>" class="<?=!empty($arItem["PROPERTIES"]["BUTTON1CLASS"]["VALUE"]) ? $arItem["PROPERTIES"]["BUTTON1CLASS"]["VALUE"] : "btn btn-default btn-lg"?>" <?=(strlen($target) ? 'target="'.$target.'"' : '')?>>
																<?=$arItem["PROPERTIES"]["BUTTON1TEXT"]["VALUE"]?>
															</a>
															<?if($arItem['PROPERTIES']['SHOW_BUTTONS']['VALUE'] == "Y"):?>
																<div class="wraps_buttons" data-id="<?=$arProduct["ID"];?>" data-iblockid="<?=$arProduct["IBLOCK_ID"];?>">
																	<?$arAllPrices = \CIBlockPriceTools::GetCatalogPrices(false, $arParams["PRICE_CODE"]);
																	$arProduct["CAN_BUY"] = CIBlockPriceTools::CanBuy($arProduct["IBLOCK_ID"], $arAllPrices, $arProduct);
																	?>
																	<?if($arPrice && $arProduct["CATALOG_TYPE"] == 1):?>
																		<?if($arProduct["CAN_BUY"]):?>
																			<div class="wrap colored_theme_hover_bg option-round  basket_item_add" data-title="<?=$arTheme["EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT"]["VALUE"];?>" title="<?=$arTheme["EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT"]["VALUE"];?>" data-href="<?=$arTheme["BASKET_PAGE_URL"]["VALUE"];?>" data-title2="<?=$arTheme["EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT"]["VALUE"];?>">
																				<?=CMax::showIconSvg("basket ", SITE_TEMPLATE_PATH."/images/svg/basket.svg");?>
																				<?=CMax::showIconSvg("basket-added", SITE_TEMPLATE_PATH."/images/svg/inbasket.svg");?>
																			</div>
																		<?endif;?>
																	<?endif;?>
																	<?if($arTheme['CATALOG_DELAY']['VALUE'] != 'N'):?>
																		<div class="wrap colored_theme_hover_bg option-round  wish_item_add js-item-action" data-action="favorite" data-item="<?=$arProduct["ID"];?>" title="<?=GetMessage('FAVORITE_ITEM')?>" data-title="<?=GetMessage('FAVORITE_ITEM')?>" data-title_added="<?=GetMessage('FAVORITE_ITEM_REMOVE')?>">
																			<?=CMax::showIconSvg("wish ", SITE_TEMPLATE_PATH."/images/svg/chosen.svg");?>
																		</div>
																	<?endif;?>
                                                                    <?if($arTheme['CATALOG_COMPARE']['VALUE'] != 'N'):?>
                                                                        <div class="wrap colored_theme_hover_bg option-round  compare_item_add js-item-action" data-action="compare" data-item="<?=$arProduct["ID"];?>" title="<?=GetMessage('CATALOG_ITEM_COMPARE')?>" data-title="<?=GetMessage('CATALOG_ITEM_COMPARE')?>" data-title_added="<?=GetMessage('CATALOG_ITEM_COMPARED')?>">
                                                                            <?=CMax::showIconSvg("compare ", SITE_TEMPLATE_PATH."/images/svg/compare.svg");?>
                                                                        </div>
                                                                    <?endif;?>
																</div>
															<?endif;?>
															<?if($bShowVideo && !$bVideoAutoStart):?>
																<span class="btn <?=(strlen($arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID']) ? $arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID'] : 'btn-default')?> btn-video" title="<?=$buttonVideoText?>"></span>
															<?endif;?>
														</div>
													<?else:?>
														<?
															$bShowButton1 = (strlen($arItem['PROPERTIES']['BUTTON1TEXT']['VALUE']) && strlen($arItem['PROPERTIES']['BUTTON1LINK']['VALUE']) || strlen($arItem['PROPERTIES']['FORM_CODE1']['VALUE']));
															$button1Form = $arItem['PROPERTIES']['FORM_CODE1']['VALUE'] ? 'data-event="jqm" data-param-form_id="'.$arItem['PROPERTIES']['FORM_CODE1']['VALUE'].'" data-name="order_from_banner"' : '';

															$bShowButton2 = (strlen($arItem['PROPERTIES']['BUTTON2TEXT']['VALUE']) && strlen($arItem['PROPERTIES']['BUTTON2LINK']['VALUE']) || strlen($arItem['PROPERTIES']['FORM_CODE2']['VALUE']));
															$button2Form = $arItem['PROPERTIES']['FORM_CODE2']['VALUE'] ? 'data-event="jqm" data-param-form_id="'.$arItem['PROPERTIES']['FORM_CODE2']['VALUE'].'" data-name="order_from_banner"' : '';
														?>
														<?if($arItem["NAME"]):?>
															<div class="banner_title">
																<?if(strlen($arItem['PROPERTIES']['TOP_TEXT']['VALUE'])):?>
																	<div class="section font_upper_md"><?=$arItem['PROPERTIES']['TOP_TEXT']['VALUE']?></div>
																<?endif?>

																<?if($arItem['PROPERTIES']['TITLE_H1']['VALUE'] == "Y" && !$bShowH1):?>
																	<h1 class="head-title">
																<?else:?>
																	<span class="head-title">
																<?endif;?>

																	<?if($arItem["PROPERTIES"]["URL_STRING"]["VALUE"]):?>
																		<a href="<?=$arItem["PROPERTIES"]["URL_STRING"]["VALUE"]?>" <?=(strlen($target) ? 'target="'.$target.'"' : '')?>>
																	<?endif;?>
																	<?=strip_tags($arItem["~NAME"], "<br><br/>");?>
																	<?if($arItem["PROPERTIES"]["URL_STRING"]["VALUE"]):?>
																		</a>
																	<?endif;?>

																<?if($arItem['PROPERTIES']['TITLE_H1']['VALUE'] == "Y" && !$bShowH1):?>
																	<?$bShowH1 = true;?>
																	</h1>
																<?else:?>
																	</span>
																<?endif;?>

															</div>
														<?endif;?>
														<?if($arItem["PREVIEW_TEXT"]):?>
															<div class="banner_text"><?=$arItem["PREVIEW_TEXT"];?></div>
														<?endif;?>
														<?if($bShowButton1 || $bShowButton2 || ($bShowVideo && !$bVideoAutoStart)):?>
															<div class="banner_buttons">
																<?if($bShowVideo && !$bVideoAutoStart && !$bShowButton1 && !$bShowButton2):?>
																	<span class="btn <?=(strlen($arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID']) ? $arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID'] : 'btn-default')?> btn-video" title="<?=$buttonVideoText?>"></span>
																<?elseif($bShowButton1 || $bShowButton2):?>
																	<?if($bShowVideo && !$bVideoAutoStart):?>
																		<span class="btn <?=(strlen($arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID']) ? $arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID'] : 'btn-default')?> btn-video" title="<?=$buttonVideoText?>"></span>
																	<?endif;?>
																	<?if($bShowButton1):?>
																		<a href="<?=$arItem["PROPERTIES"]["BUTTON1LINK"]["VALUE"]?>" <?=$button1Form?> class="<?=!empty($arItem["PROPERTIES"]["BUTTON1CLASS"]["VALUE"]) ? $arItem["PROPERTIES"]["BUTTON1CLASS"]["VALUE"] : "btn btn-default btn-lg"?>" <?=(strlen($target) ? 'target="'.$target.'"' : '')?>>
																			<?=$arItem["PROPERTIES"]["BUTTON1TEXT"]["VALUE"]?>
																		</a>
																	<?endif;?>
																	<?if($bShowButton2):?>
																		<a href="<?=$arItem["PROPERTIES"]["BUTTON2LINK"]["VALUE"]?>"  <?=$button2Form?> class="<?=!empty( $arItem["PROPERTIES"]["BUTTON2CLASS"]["VALUE"]) ? $arItem["PROPERTIES"]["BUTTON2CLASS"]["VALUE"] : "btn btn-transparent-border btn-lg"?>" <?=(strlen($target) ? 'target="'.$target.'"' : '')?>>
																			<?=$arItem["PROPERTIES"]["BUTTON2TEXT"]["VALUE"]?>
																		</a>
																	<?endif;?>
																<?endif;?>
															</div>
														<?endif;?>
													<?endif;?>
												</td>
											<?$text = ob_get_clean();?>
										<?endif;?>
										<?ob_start();?>
											<?$bHasVideo = ($bShowVideo && !$bVideoAutoStart && $arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"] == "image");?>
											<td class="img <?=($bHasVideo ? 'with_video' : '');?>">
												<?if($bHasVideo):?>
													<div class="video_block">
														<span class="play btn btn-video  <?=(strlen($arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID']) ? $arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID'] : 'btn-default')?>" title="<?=$buttonVideoText?>"></span>
													</div>
												<?elseif($arItem["PREVIEW_PICTURE"]):?>
													<?if(!empty($arItem["PROPERTIES"]["URL_STRING"]["VALUE"])):?>
														<a href="<?=$arItem["PROPERTIES"]["URL_STRING"]["VALUE"]?>" <?=(strlen($target) ? 'target="'.$target.'"' : '')?>>
													<?endif;?>
													<img class="plaxy" data-src2="<?=$arItem['PREVIEW_PICTURE']['SRC']?>"
														<?=($bSwiperLazy ? '' : 'data-src=""')?>
														src="<?=$arItem['PREVIEW_PICTURE']['SRC'];?>"
														alt="<?=($arItem['PREVIEW_PICTURE']['ALT'] ? $arItem['PREVIEW_PICTURE']['ALT'] : $arItem['NAME'])?>"
													/>
													<?if(!empty($arItem["PROPERTIES"]["URL_STRING"]["VALUE"])):?>
														</a>
													<?endif;?>
												<?endif;?>
											</td>
										<?$image = ob_get_clean();?>
										<?
										if($arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"]){
											if($arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"] == "left"){
												echo $text.$image;
											}
											elseif($arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"] == "right"){
												echo $image.$text;
											}
											elseif($arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"] == "center"){
												echo $text;
											}
											elseif($arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"] == "image"){
												echo $image;
											}
										}
										else{
											echo $text.$image;
										}
										?>
									</tr>
								<?if($arResult['BIGBANNER_MOBILE'] == 2):?>
									<tr class="adaptive_info js-notice-block">
										<?if($arResult['BIGBANNER_MOBILE'] == 2):?>
											<td class="tablet_text"<?=(strlen($text) && strlen($image) ? ' colspan="2"' : '')?>>
												<?ob_start();?>
													<?if($arItem["PROPERTIES"]["TEXT_POSITION"]["VALUE_XML_ID"] != "image"):?>
														<?if($arItem["PROPERTIES"]["LINK_ITEM"]["VALUE"]):?>
															<?
															$hitProp = (isset($arParams["HIT_PROP"]) ? $arParams["HIT_PROP"] : "HIT");
															$saleProp = (isset($arParams["SALE_PROP"]) ? $arParams["SALE_PROP"] : "SALE_TEXT");

															$arSelect = array("ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL", "PROPERTY_vote_count", "PROPERTY_rating", "PROPERTY_vote_sum", "CATALOG_TYPE", "PROPERTY_EXTENDED_REVIEWS_RAITING", "PROPERTY_EXTENDED_REVIEWS_COUNT");
															if($hitProp)
																$arSelect[] = "PROPERTY_".$hitProp;
															if($saleProp)
																$arSelect[] = "PROPERTY_".$saleProp;

															$arPricesID = array();
															if(!$arParams["PRICE_CODE_IDS"])
															{
																$dbPriceType = \CCatalogGroup::GetList(
																	array("SORT" => "ASC"),
																	array("NAME" => $arParams["PRICE_CODE"])
																	);
																while($arPriceType = $dbPriceType->Fetch())
																{
																	$arParams["PRICE_CODE_IDS"][] = array(
																		"ID" => $arPriceType["ID"]
																	);
																}
															}
															if($arParams["PRICE_CODE_IDS"])
															{
																foreach($arParams["PRICE_CODE_IDS"] as $arPrices)
																{
																	$arSelect[] = "CATALOG_GROUP_".$arPrices["ID"];
																	$arPricesID[] = $arPrices["ID"];
																}
															}
															$arProduct = CMaxCache::CIBLockElement_GetList(array('CACHE' => array("MULTI" => "N", "TAG" => CMaxCache::GetIBlockCacheTag($arItem["PROPERTIES"]["LINK_ITEM"]["LINK_IBLOCK_ID"]))), array("IBLOCK_ID" => $arItem["PROPERTIES"]["LINK_ITEM"]["LINK_IBLOCK_ID"], "ACTIVE"=>"Y", "ACTIVE_DATE" => "Y", "ID" => $arItem["PROPERTIES"]["LINK_ITEM"]["VALUE"]), false, false, $arSelect);
															$arPriceList = \Aspro\Functions\CAsproMax::getPriceList($arProduct["ID"], $arPricesID, 1, true);

															?>
															<div class="banner_title item_info">
																<?if((($hitProp && $arProduct['PROPERTY_'.$hitProp.'_VALUE']) || ($saleProp && $arProduct['PROPERTY_'.$saleProp.'_VALUE'])) && $arItem["PROPERTIES"]["SHOW_STICKERS"]["VALUE"] == "Y"):?>
																	<div class="stickers">
																		<?if($saleProp && $arProduct['PROPERTY_'.$saleProp.'_VALUE']):?>
																			<div class="sticker_sale_text"><?=$arProduct['PROPERTY_'.$saleProp.'_VALUE']?></div>
																		<?endif;?>
																		<?if($hitProp && $arProduct['PROPERTY_'.$hitProp.'_VALUE']):?>
																			<?foreach((array)$arProduct['PROPERTY_'.$hitProp.'_VALUE'] as $key => $value):?>
																				<?
																				$enumID = ((is_array($arProduct['PROPERTY_'.$hitProp.'_ENUM_ID'])) ? $arProduct['PROPERTY_'.$hitProp.'_ENUM_ID'][$key] : $arProduct['PROPERTY_'.$hitProp.'_ENUM_ID']);
																				$arTmpEnum = CIBlockPropertyEnum::GetByID($enumID);?>
																				<div class="sticker_<?=($strTypeHitProp == "PROPERTY_VALUE" ? CUtil::translit($value, 'ru') : strtolower($arTmpEnum["XML_ID"]));?>"><?=$value?></div>
																			<?endforeach;?>
																		<?endif;?>
																	</div>
																<?endif;?>

																<?if($arItem['PROPERTIES']['TITLE_H1']['VALUE'] == "Y" && !$bShowH1):?>
																	<h1 class="head-title js-notice-block__title">
																<?else:?>
																	<span class="head-title js-notice-block__title">
																<?endif;?>

																	<?if($arProduct["DETAIL_PAGE_URL"]):?>
																		<a href="<?=$arProduct["DETAIL_PAGE_URL"]?>" <?=(strlen($target) ? 'target="'.$target.'"' : '')?>>
																	<?endif;?>
																	<?=$arProduct["NAME"];?>
																	<?if($arProduct["DETAIL_PAGE_URL"]):?>
																		</a>
																	<?endif;?>

																<?if($arItem['PROPERTIES']['TITLE_H1']['VALUE'] == "Y" && !$bShowH1):?>
																	<?$bShowH1 = true;?>
																	</h1>
																<?else:?>
																	</span>
																<?endif;?>
																<?
																$bHasOffers = (isset($arProduct["CATALOG_TYPE"]) && $arProduct["CATALOG_TYPE"] == 3);

																if($bHasOffers)
																{
																	$arSelect = array("ID", "IBLOCK_ID", "NAME", "CATALOG_QUANTITY");
																	$arOffers = CMaxCache::CIBLockElement_GetList(array('CACHE' => array("MULTI" => "Y", "TAG" => CMaxCache::GetIBlockCacheTag($arItem["PROPERTIES"]["LINK_ITEM"]["LINK_IBLOCK_ID"]))), array("PROPERTY_CML2_LINK" => $arProduct["ID"], "ACTIVE"=>"Y", "ACTIVE_DATE" => "Y"), false, false, $arSelect);
																	$arProduct["OFFERS"] = $arOffers;
																}

																$arPrice = CCatalogProduct::GetOptimalPrice($arProduct["ID"], 1, $USER->GetUserGroupArray(), 'N', $arPriceList);
																$totalCount = CMax::GetTotalCount($arProduct, $arParams);
																$arQuantityData = CMax::GetQuantityArray($totalCount, array('ID' => $arProduct["ID"]), "N", false );
																$strMeasure = '';



																if($arProduct["CATALOG_MEASURE"] && $arParams["SHOW_MEASURE"] !== "N")
																{
																	$arMeasure = CCatalogMeasure::getList(array(), array("ID" => $arProduct["CATALOG_MEASURE"]), false, false, array())->GetNext();
																	$strMeasure = $arMeasure["SYMBOL_RUS"];
																}?>

																<?if($arQuantityData["HTML"] || $arItem['PROPERTIES']['SHOW_RATING']['VALUE'] == "Y"):?>

																	<div class="votes_block nstar">
																		<?if($arItem['PROPERTIES']['SHOW_RATING']['VALUE'] == "Y"):?>
																			<div class="ratings">
																				<?
																				if($arParams['REVIEWS_VIEW'] == 'EXTENDED'):?>
																					<?$message = $arProduct['PROPERTY_EXTENDED_REVIEWS_RAITING_VALUE'] ? GetMessage('VOTES_RESULT', array('#VALUE#' => $arProduct['PROPERTY_EXTENDED_REVIEWS_RAITING_VALUE'])) : GetMessage('VOTES_RESULT_NONE')?>
																					<div class="inner_rating" title="<?=$message?>">
																						<?for($i=1;$i<=5;$i++):?>
																							<div class="item-rating <?=$i<=$arProduct['PROPERTY_EXTENDED_REVIEWS_RAITING_VALUE'] ? 'filed' : ''?>"><?=CMax::showIconSvg("star", SITE_TEMPLATE_PATH."/images/svg/catalog/star_small.svg");?></div>
																						<?endfor;?>

																						<?if($arProduct['PROPERTY_EXTENDED_REVIEWS_COUNT_VALUE']):?>
																							<span class="font_sxs"><?=$arProduct['PROPERTY_EXTENDED_REVIEWS_COUNT_VALUE']?></span>
																						<?endif;?>
																					</div>
																				<?else:?>
																					<?
																					if($arProduct["PROPERTY_VOTE_COUNT_VALUE"])
																						$display_rating = round($arProduct["PROPERTY_VOTE_SUM_VALUE"]/$arProduct["PROPERTY_VOTE_COUNT_VALUE"], 2);
																					else
																						$display_rating = 0;
																					?>
																					<div class="inner_rating">
																						<?for($i=1;$i<=5;$i++):?>
																							<div class="item-rating <?=(round($display_rating) >= $i ? "filed" : "");?>"><?=CMax::showIconSvg("star", SITE_TEMPLATE_PATH."/images/svg/star.svg");?></div>
																						<?endfor;?>
																					</div>
																				<?endif;?>
																			</div>
																		<?endif;?>
																		<div class="sa_block">
																			<?if($arQuantityData["HTML"]):?>
																				<?=$arQuantityData["HTML"];?>
																			<?endif;?>
																		</div>
																	</div>
																<?endif;?>

																<div class="price_adaptive_wrapper">

																	<?if($arItem['PROPERTIES']['SHOW_DATE_SALE']['VALUE'] == "Y"):?>
																		<?\Aspro\Functions\CAsproMax::showDiscountCounter($totalCount, $arPrice["DISCOUNT"], $arQuantityData, $arProduct, $strMeasure);?>
																	<?endif;?>

																	<?if($arPrice["RESULT_PRICE"] && $arItem['PROPERTIES']['SHOW_PRICES']['VALUE'] == "Y"):?>
																		<div class="price_adaptive_wrapper_inner">
																			<?
																			$price = $arPrice["RESULT_PRICE"]["DISCOUNT_PRICE"];
																			$arFormatPrice = $arPrice["RESULT_PRICE"];
																			$arCurrencyParams = array();
																			if($arParams["CONVERT_CURRENCY"] != "Y" && $arPrice["RESULT_PRICE"]["CURRENCY"] != $arPrice["PRICE"]["CURRENCY"])
																			{
																				$price = roundEx(CCurrencyRates::ConvertCurrency($arPrice["RESULT_PRICE"]["DISCOUNT_PRICE"], $arPrice["RESULT_PRICE"]["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]),CATALOG_VALUE_PRECISION);
																				$arFormatPrice = $arPrice["PRICE"];
																			}
																			if($arParams["CONVERT_CURRENCY"] == "Y" && $arParams["CURRENCY_ID"])
																			{
																				$arCurrencyInfo = CCurrency::GetByID($arParams["CURRENCY_ID"]);
																				if (is_array($arCurrencyInfo) && !empty($arCurrencyInfo))
																				{
																					$arCurrencyParams["CURRENCY_ID"] = $arCurrencyInfo["CURRENCY"];
																					$price = CCurrencyRates::ConvertCurrency($arPrice["RESULT_PRICE"]["DISCOUNT_PRICE"], $arPrice["RESULT_PRICE"]["CURRENCY"], $arCurrencyParams["CURRENCY_ID"]);
																					$arFormatPrice["CURRENCY"] = $arCurrencyParams["CURRENCY_ID"];
																				}
																			}
																			?>
																			<div class="prices">
																				<span class="price font_lg">
																					<span class="values_wrapper"><?=($bHasOffers ? GetMessage("FROM")." " : "");?><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($price, $arFormatPrice, false);?></span>
																					<?if($strMeasure):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
																				</span>
																				<?if($arItem['PROPERTIES']['SHOW_OLD_PRICE']['VALUE'] == "Y" && ($arPrice["RESULT_PRICE"]["BASE_PRICE"] != $arPrice["RESULT_PRICE"]["DISCOUNT_PRICE"])):?>
																					<span class="price price_old font_sm">
																						<?if($arCurrencyParams)
																							$arPrice["RESULT_PRICE"]["BASE_PRICE"] = CCurrencyRates::ConvertCurrency($arPrice["RESULT_PRICE"]["BASE_PRICE"], $arPrice["RESULT_PRICE"]["CURRENCY"], $arCurrencyParams["CURRENCY_ID"])?>
																						<span class="values_wrapper"><?=($bHasOffers ? GetMessage("FROM")." " : "");?><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($arPrice["RESULT_PRICE"]["BASE_PRICE"], $arFormatPrice, false);?></span>
																						<?if($strMeasure):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
																					</span>
																				<?endif;?>
																			</div>
																			<?if($arItem['PROPERTIES']['SHOW_DISCOUNT']['VALUE'] == "Y" && $arPrice["RESULT_PRICE"]["DISCOUNT"]):?>
																				<div class="sale_block">
																					<div class="sale-number rounded2 font_xxs">
																						<div class="value">-<span><?=$arPrice["RESULT_PRICE"]["PERCENT"]?></span>%</div>
																						<div class="inner-sale rounded1">
																							<span><?=GetMessage("CATALOG_ITEM_ECONOMY");?></span>
																							<span class="price">
																								<?if($arCurrencyParams)
																								$arPrice["RESULT_PRICE"]["DISCOUNT"] = CCurrencyRates::ConvertCurrency($arPrice["RESULT_PRICE"]["DISCOUNT"], $arPrice["RESULT_PRICE"]["CURRENCY"], $arCurrencyParams["CURRENCY_ID"])?>
																								<span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($arPrice["RESULT_PRICE"]["DISCOUNT"], $arFormatPrice, false);?></span>
																							</span>
																						</div>
																					</div>
																				</div>
																			<?endif;?>
																		</div>
																	<?endif;?>

																</div>
															</div>

															<div class="banner_buttons with_actions 2 <?=$arProduct["CATALOG_TYPE"];?>">
																<a href="<?=$arProduct["DETAIL_PAGE_URL"]?>" class="<?=!empty($arItem["PROPERTIES"]["BUTTON1CLASS"]["VALUE"]) ? $arItem["PROPERTIES"]["BUTTON1CLASS"]["VALUE"] : "btn btn-default btn-lg"?>" <?=(strlen($target) ? 'target="'.$target.'"' : '')?>>
																	<?=$arItem["PROPERTIES"]["BUTTON1TEXT"]["VALUE"]?>
																</a>
																<?if($arItem['PROPERTIES']['SHOW_BUTTONS']['VALUE'] == "Y"):?>
																	<div class="wraps_buttons" data-id="<?=$arProduct["ID"];?>" data-iblockid="<?=$arProduct["IBLOCK_ID"];?>">
																		<?$arAllPrices = \CIBlockPriceTools::GetCatalogPrices(false, $arParams["PRICE_CODE"]);
																		$arProduct["CAN_BUY"] = CIBlockPriceTools::CanBuy($arProduct["IBLOCK_ID"], $arAllPrices, $arProduct);
																		?>
																		<?if($arPrice && $arProduct["CATALOG_TYPE"] == 1):?>
																			<?if($arProduct["CAN_BUY"]):?>
																				<div class="wrap colored_theme_hover_bg option-round  basket_item_add" data-title="<?=$arTheme["EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT"]["VALUE"];?>" title="<?=$arTheme["EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT"]["VALUE"];?>" data-href="<?=$arTheme["BASKET_PAGE_URL"]["VALUE"];?>" data-title2="<?=$arTheme["EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT"]["VALUE"];?>">
																					<?=CMax::showIconSvg("basket ", SITE_TEMPLATE_PATH."/images/svg/basket.svg");?>
																					<?=CMax::showIconSvg("basket-added", SITE_TEMPLATE_PATH."/images/svg/inbasket.svg");?>
																				</div>
																			<?endif;?>
																		<?endif;?>
                                                                        <?if($arTheme['CATALOG_DELAY']['VALUE'] != 'N'):?>
                                                                            <div class="wrap colored_theme_hover_bg option-round  wish_item_add js-item-action" data-action="favorite" data-item="<?=$arProduct["ID"];?>" title="<?=GetMessage('FAVORITE_ITEM')?>" data-title="<?=GetMessage('FAVORITE_ITEM')?>" data-title_added="<?=GetMessage('FAVORITE_ITEM_REMOVE')?>">
                                                                                <?=CMax::showIconSvg("wish ", SITE_TEMPLATE_PATH."/images/svg/chosen.svg");?>
                                                                            </div>
                                                                        <?endif;?>
                                                                        <?if($arTheme['CATALOG_COMPARE']['VALUE'] != 'N'):?>
                                                                            <div class="wrap colored_theme_hover_bg option-round  compare_item_add js-item-action" data-action="compare" data-item="<?=$arProduct["ID"];?>" title="<?=GetMessage('CATALOG_ITEM_COMPARE')?>" data-title="<?=GetMessage('CATALOG_ITEM_COMPARE')?>" data-title_added="<?=GetMessage('CATALOG_ITEM_COMPARED')?>">
                                                                                <?=CMax::showIconSvg("compare ", SITE_TEMPLATE_PATH."/images/svg/compare.svg");?>
                                                                            </div>
                                                                        <?endif;?>
																		<?if($bShowVideo && !$bVideoAutoStart):?>
																			<span class="btn <?=(strlen($arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID']) ? $arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID'] : 'btn-default')?> btn-video" title="<?=$buttonVideoText?>"></span>
																		<?endif;?>
																	</div>
																<?endif;?>
															</div>
														<?else:?>
															<?
																$bShowButton1 = (strlen($arItem['PROPERTIES']['BUTTON1TEXT']['VALUE']) && strlen($arItem['PROPERTIES']['BUTTON1LINK']['VALUE']) || strlen($arItem['PROPERTIES']['FORM_CODE1']['VALUE']));
																$bShowButton2 = (strlen($arItem['PROPERTIES']['BUTTON2TEXT']['VALUE']) && strlen($arItem['PROPERTIES']['BUTTON2LINK']['VALUE']) || strlen($arItem['PROPERTIES']['FORM_CODE2']['VALUE']));
															?>
															<?if($arItem["NAME"]):?>
																<div class="banner_title">
																	<?if($arItem['PROPERTIES']['TITLE_H1']['VALUE'] == "Y" && !$bShowH1):?>
																		<h1 class="head-title">
																	<?else:?>
																		<span class="head-title">
																	<?endif;?>

																		<?if($arItem["PROPERTIES"]["URL_STRING"]["VALUE"]):?>
																			<a href="<?=$arItem["PROPERTIES"]["URL_STRING"]["VALUE"]?>" <?=(strlen($target) ? 'target="'.$target.'"' : '')?>>
																		<?endif;?>
																		<?=strip_tags($arItem["~NAME"], "<br><br/>");?>
																		<?if($arItem["PROPERTIES"]["URL_STRING"]["VALUE"]):?>
																			</a>
																		<?endif;?>

																	<?if($arItem['PROPERTIES']['TITLE_H1']['VALUE'] == "Y" && !$bShowH1):?>
																		<?$bShowH1 = true;?>
																		</h1>
																	<?else:?>
																		</span>
																	<?endif;?>

																</div>
															<?endif;?>
															<?if($arItem["PREVIEW_TEXT"]):?>
																<div class="banner_text"><?=$arItem["PREVIEW_TEXT"];?></div>
															<?endif;?>
															<?if($bShowButton1 || $bShowButton2 || ($bShowVideo && !$bVideoAutoStart)):?>
																<div class="banner_buttons">
																	<?if($bShowVideo && !$bVideoAutoStart && !$bShowButton1 && !$bShowButton2):?>
																		<span class="btn <?=(strlen($arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID']) ? $arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID'] : 'btn-default')?> btn-video" title="<?=$buttonVideoText?>"></span>
																	<?elseif($bShowButton1 || $bShowButton2):?>
																		<?if($bShowVideo && !$bVideoAutoStart):?>
																			<span class="btn <?=(strlen($arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID']) ? $arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID'] : 'btn-default')?> btn-video" title="<?=$buttonVideoText?>"></span>
																		<?endif;?>
																		<?if($bShowButton1):?>
																			<a href="<?=$arItem["PROPERTIES"]["BUTTON1LINK"]["VALUE"]?>" <?=$button1Form?> class="<?=!empty($arItem["PROPERTIES"]["BUTTON1CLASS"]["VALUE"]) ? $arItem["PROPERTIES"]["BUTTON1CLASS"]["VALUE"] : "btn btn-default btn-lg"?>" <?=(strlen($target) ? 'target="'.$target.'"' : '')?>>
																				<?=$arItem["PROPERTIES"]["BUTTON1TEXT"]["VALUE"]?>
																			</a>
																		<?endif;?>
																		<?if($bShowButton2):?>
																			<a href="<?=$arItem["PROPERTIES"]["BUTTON2LINK"]["VALUE"]?>" <?=$button2Form?> class="<?=!empty( $arItem["PROPERTIES"]["BUTTON2CLASS"]["VALUE"]) ? $arItem["PROPERTIES"]["BUTTON2CLASS"]["VALUE"] : "btn btn-transparent-border btn-lg"?>" <?=(strlen($target) ? 'target="'.$target.'"' : '')?>>
																				<?=$arItem["PROPERTIES"]["BUTTON2TEXT"]["VALUE"]?>
																			</a>
																		<?endif;?>
																	<?endif;?>
																</div>
															<?endif;?>
														<?endif;?>
													<?else:?>
														<?if($bShowVideo && !$bVideoAutoStart):?>
															<div class="banner_buttons" style="margin-top:0;">
																<span class="btn <?=(strlen($arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID']) ? $arItem['PROPERTIES']['BUTTON_VIDEO_CLASS']['VALUE_XML_ID'] : 'btn-default')?> btn-video" title="<?=$buttonVideoText?>"><?=CMax::showIconSvg('playpause', SITE_TEMPLATE_PATH.'/images/svg/play_pause.svg', '', 'svg-playpause');?></span>
															</div>
														<?endif;?>
													<?endif;?>
												<?$tablet_text = trim(ob_get_clean());?>
												<div class="wrap"><?if(strlen($tablet_text)):?><div class="inner"><?=$tablet_text?></div><?endif;?></div>
											</td>
										<?endif;?>
									</tr>
								<?endif;?>
							</tbody>
							</table>
						</div>
					</div>
				</div>
			<?endforeach;?>
		</div>
		<?if ($arOptions['countSlides'] > 1):?>
			<div class="swiper-pagination"></div>
			<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>
		<?endif;?>
	</div>
</div>

<?
$bBannerLight = $templateData['BANNER_LIGHT'] = $templateData['CURRENT_BANNER_COLOR'] === 'light';
?>

<?if($bInitYoutubeJSApi):?>
	<script type="text/javascript">
	BX.ready(function(){
		var tag = document.createElement('script');
		tag.src = "https://www.youtube.com/iframe_api";
		var firstScriptTag = document.getElementsByTagName('script')[0];
		firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
	});
	</script>
<?endif;?>

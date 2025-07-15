<?
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(false);
Loc::loadMessages(__FILE__);

$bUseCustomMessages = $arParams['USE_CUSTOM_MESSAGES'] === 'Y';
$arMess = array();
foreach(
	array(
		'READY_ITEMS_COUNT',
		'READY_ONE_ITEM',
		'SHOW_ACTUAL_PRICES',
		'TOTAL_PRICE',
		'ADD_TO_BASKET',
		'REPLACE_BASKET',
		'PRODUCT_ECONOMY',
		'PRODUCT_NOT_EXISTS',
	) as $code
){
	if(
		$bUseCustomMessages &&
		isset($arParams['MESS_'.$code]) &&
		strlen($arParams['MESS_'.$code])
	){
		$arMess[$code] = $arParams['MESS_'.$code];
	}
	else{
		$arMess[$code] = Loc::getMessage('BSD_T_'.$code);
	}
}

if(!$arResult['ERRORS']){
	$bOriginal = $arResult['IS_ORIGINAL'] === 'Y';
	$bShowArticle = $arParams['PRODUCT_PROPERTIES'] && in_array('CML2_ARTICLE', $arParams['PRODUCT_PROPERTIES']);
	$arExcludePropertiesCodes = array(
		'CML2_ARTICLE',
		'HIT',
		'IN_STOCK',
		'MINIMUM_PRICE',
		'MAXIMUM_PRICE',
		'YM_ELEMENT_ID',
		'EXTENDED_REVIEWS_COUNT',
		'EXTENDED_REVIEWS_RAITING',
		'FAVORIT_ITEM',
		'BIG_BLOCK',
		'vote_count',
		'vote_sum',
		'rating',
		'VIDEO_YOUTUBE',
		'POPUP_VIDEO',
		'FORUM_TOPIC_ID',
		'FORUM_MESSAGE_CNT',
		'SALE_TEXT',
		'HELP_TEXT',
		'BLOG_POST_ID',
		'BLOG_COMMENTS_CNT',
		'FAV_ITEM',
		'FINAL_PRICE',
		'STIKERS_PROP',
		'SALE_STIKER',
	);

	$cntReady = $totalSum = $woDiscountTotalSum = 0;
	if($arResult['SHARE_BASKET']['ITEMS']['MAIN']){
		foreach($arResult['SHARE_BASKET']['ITEMS']['MAIN'] as $arItem){
			if(
				array_key_exists('PRODUCT', $arItem) &&
				$arItem['PRODUCT'] &&
				$arItem['PRODUCT']['AVAILABLE'] === 'Y' &&
				$arItem['PRODUCT']['CAN_BUY']
			){
				++$cntReady;
			}
		}
	}
}
?>
<div id="basket-share-detail" class="basket-share-detail form <?=($arResult['ERRORS'] ? 'basket-share-detail--haserror' : '')?>">
	<div class="basket-share-detail__error">
		<div class="basket-share-detail__error__icon"><?=CMax::showIconSvg('fail colored', $this->{'__folder'}.'/images/svg/fail.svg')?></div>
		<div class="basket-share-detail__error__text">
			<?if($arResult['ERRORS']):?>
				<?=implode('<br />', $arResult['ERRORS'])?>
			<?endif;?>
		</div>
	</div>
	<?if(!$arResult['ERRORS']):?>
		<?$fileTextBefore = SITE_DIR.'include/sharebasket_before_text.php';?>
		<div class="basket-share-detail__text--before <?=((CMax::checkContentFile($fileTextBefore) ? ' filed' : ''));?>">
			<?$GLOBALS['APPLICATION']->IncludeFile($fileTextBefore, Array(), Array('MODE' => 'html',  'NAME' => Loc::getMessage('BSD_T_EDIT_TEXT_BEFORE')));?>
		</div>
		<div class="basket-share-detail__table">
			<form action="<?=$arResult['ACTION_URL']?>" method="post" enctype="multipart/form-data"><?=bitrix_sessid_post()?>
				<input type="hidden" name="is_ajax_post" value="Y" />
				<input type="hidden" name="SITE_ID" value="<?=$this->__component->getSiteId()?>" />
				<input type="hidden" name="LANGUAGE_ID" value="<?=$this->__component->getLanguageId()?>" />
				<input type="hidden" name="ORIGINAL" value="<?=$arResult['IS_ORIGINAL']?>" />
				<input type="hidden" name="SIGNED_PARAMS" value="<?=$arResult['SIGNED_PARAMS']?>" />
				<input type="hidden" name="TEMPLATE" value="<?=$this->{'__name'}?>" />
				<?if($arResult['SHARE_BASKET']['ITEMS']):?>
					<?if($arResult['SHARE_BASKET']['ITEMS']['MAIN']):?>
						<div class="basket-share-detail__head">
							<div class="basket-share-detail__head__ready"><?=str_replace('#READY_ITEMS_COUNT#', Aspro\Functions\CAsproMax::declOfNum($cntReady, array(Loc::getMessage('BSD_T_ITEM1'), Loc::getMessage('BSD_T_ITEM2'), Loc::getMessage('BSD_T_ITEM0'))), ($cntReady == 1 ? $arMess['READY_ONE_ITEM'] : $arMess['READY_ITEMS_COUNT']))?></div>
							<?if($arParams['SHOW_VERSION_SWITCHER'] === 'Y'):?>
								<div class="basket-share-detail__head__onoff filter onoff">
									<input type="checkbox" id="basket-share-detail--actual" value="Y" <?=($bOriginal ? '' : 'checked')?>>
									<label for="basket-share-detail--actual"><?=$arMess['SHOW_ACTUAL_PRICES']?></label>
								</div>
								<?$fileActualHint = SITE_DIR.'include/sharebasket_actual_hint.php';?>
								<?if(CMax::checkContentFile($fileActualHint)):?>
									<div class="char_name">
										<div class="props_list">
											<div class="hint"><span class="icon">?</span><div class="tooltip"><?$GLOBALS['APPLICATION']->IncludeFile($fileActualHint, Array(), Array('MODE' => 'html',  'NAME' => Loc::getMessage('BSD_T_EDIT_ACTUAL_HINT')));?></div></div>
										</div>
									</div>
								<?endif;?>
							<?endif;?>
						</div>
						<div class="basket-share-detail__items">
							<?foreach($arResult['SHARE_BASKET']['ITEMS']['MAIN'] as $arItem):?>
								<?
								if($bExists = array_key_exists('PRODUCT', $arItem) && $arItem['PRODUCT']){
									$arProduct = $arItem['PRODUCT'];
								}

								$bReady = $bExists && $arProduct['AVAILABLE'] === 'Y' && $arProduct['CAN_BUY'];
								$bChecked = $arItem['CHECKED'] === 'Y';

								$name = $bExists ? $arProduct['NAME'] : $arItem['NAME'];
								$article = $bExists ? (strlen($arProduct['PROPERTY_CML2_ARTICLE_VALUE']) ? $arProduct['PROPERTY_CML2_ARTICLE_VALUE'] : ($arProduct['PROPERTY_CML2_LINK_VALUE'] ? $arItem['MAIN_PRODUCT']['PROPERTY_CML2_ARTICLE_VALUE'] : '')) : $arItem['ARTICLE'];

								$measureName = ($bOriginal || !$bExists) ? $arItem['MEASURE_NAME'] : $arProduct['MEASURE_NAME'];
								$ratio = ($bOriginal || !$bExists) ? $arItem['RATIO'] : $arProduct['RATIO'];
								$currency = ($bOriginal || !$bExists) ? $arItem['CURRENCY'] : $arProduct['CURRENCY'];
								$quantity = ($bOriginal || !$bExists) ? $arItem['QUANTITY'] : $arProduct['QUANTITY'];
								$quantityFormated = ($bOriginal || !$bExists) ? $arItem['QUANTITY'].' '.$arItem['MEASURE_NAME'] : $arProduct['QUANTITY'].' '.$arProduct['MEASURE_NAME'];
								$quantityOriginal = $arItem['QUANTITY'];

								$basePrice = $ratio * (($bOriginal || !$bExists) ? $arItem['BASE_PRICE'] : $arProduct['BASE_PRICE']);
								$basePriceFormated = CurrencyFormat($basePrice, $currency);
								$price = $ratio * (($bOriginal || !$bExists) ? $arItem['PRICE'] : $arProduct['PRICE']);
								$priceFormated = CurrencyFormat($price, $currency);
								$sum = ($bOriginal || !$bExists) ? $arItem['FINAL_PRICE'] : $arProduct['FINAL_PRICE'];
								$sumFormated = ($bOriginal || !$bExists) ? $arItem['FINAL_PRICE_FORMATED'] : $arProduct['FINAL_PRICE_FORMATED'];

								if($bWithDiscount = $basePrice > $price){
									$woDiscountSum = $basePrice * $quantity / $ratio;
									$woDiscountSumFormated = CurrencyFormat($woDiscountSum, $currency);
									$discountSum = $woDiscountSum - $sum;
									$discountSumFormated = CurrencyFormat($discountSum, $currency);
									$discountPercent = round($discountSum / $woDiscountSum * 100);
								}

								$totalSum += $sum;
								$woDiscountTotalSum += $bWithDiscount ? $woDiscountSum : $sum;

								if($bExists){
									$quantityAvailable = $arProduct['QUANTITY'];

									if($arProduct['PROPERTY_CML2_LINK_VALUE']){
										$arItem['MAIN_PRODUCT']['OFFERS'] = array(0);
									}
								}

								$bIsServices = isset($arItem['IS_SERVICES']) && $arItem['IS_SERVICES'] === 'Y';
								$bWithServices = isset($arResult['SHARE_BASKET']['PRODUCT_WITH_SERVICES']) && is_array($arResult['SHARE_BASKET']['PRODUCT_WITH_SERVICES']) && in_array($arItem['PRODUCT_ID'], $arResult['SHARE_BASKET']['PRODUCT_WITH_SERVICES']);
								$link_services = isset($arItem['LINK_SERVICES']) && is_array($arItem['LINK_SERVICES']) && count($arItem['LINK_SERVICES']) > 0 ? $arItem['LINK_SERVICES'] : array() ;
								
								?>
								<?if($bIsServices){continue;}?>
								<div class="basket-share-detail__item <?=($bIsServices ? 'basket-share-detail__item--services' : '')?> <?=($bWithServices ? 'basket-share-detail__item--with_services' : '')?>">
									<div class="basket-share-detail__item-wrapper">
										<div class="basket-share-detail__item__check">
											<?if($bReady):?>
												<div class="filter label_block">
													<input type="checkbox" name="CHECKED[<?=$arItem['ID']?>]" id="basket-share-detail__item__check--<?=$arItem['ID']?>" value="Y" <?=($bChecked ? 'checked' : '')?>>
													<label for="basket-share-detail__item__check--<?=$arItem['ID']?>"></label>
												</div>
											<?endif;?>
										</div>
										<div class="basket-share-detail__item__image">
											<div class="basket-share-detail__item__image-wrapper">
												<?if($bExists):?>
													<?
													$arTmp = ($arProduct['PROPERTY_CML2_LINK_VALUE'] ? array_merge($arItem['MAIN_PRODUCT'], array('DETAIL_PAGE_URL' => $arProduct['DETAIL_PAGE_URL'])) : $arProduct);
													if($arProduct['PREVIEW_PICTURE']){
														$arTmp['PREVIEW_PICTURE'] = $arProduct['PREVIEW_PICTURE'];
													}
													if($arProduct['DETAIL_PICTURE']){
														$arTmp['DETAIL_PICTURE'] = $arProduct['DETAIL_PICTURE'];
													}
													?>
													<?\Aspro\Functions\CAsproMaxItem::showImg(
														array('IBLOCK_ID' => $arProduct['IBLOCK_ID']),
														$arTmp,
														false,
														true
													);?>
													<?if($arParams['SHOW_STICKERS'] === 'Y'):?>
														<?\Aspro\Functions\CAsproMaxItem::showStickers(
															$arParams,
															$arProduct['PROPERTY_CML2_LINK_VALUE'] ? $arItem['MAIN_PRODUCT'] : $arProduct,
															true
														);?>
													<?endif;?>
													<?\Aspro\Functions\CAsproMaxItem::showDelayCompareBtn(
														array(
															'IBLOCK_ID' => $arProduct['PROPERTY_CML2_LINK_VALUE'] ? $arItem['MAIN_PRODUCT']['IBLOCK_ID'] : $arProduct['IBLOCK_ID'],
															'DISPLAY_WISH_BUTTONS' => $arParams['USE_DELAY'],
															'DISPLAY_COMPARE' => $arParams['USE_COMPARE'],
														),
														$arProduct['PROPERTY_CML2_LINK_VALUE'] ? $arItem['MAIN_PRODUCT'] : $arProduct,
														$arAddToBasketData = array(
															'CAN_BUY' => $bReady,
														),
														$arProduct['TOTAL_COUNT'],
														$bUseSkuProps = ($arProduct['PROPERTY_CML2_LINK_VALUE'] ? true : false),
														'block',
														$arParams['USE_FAST_VIEW'] === 'Y',
														$arParams['SHOW_ONE_CLICK_BUY'] === 'Y',
														'_small',
														$arProduct['PROPERTY_CML2_LINK_VALUE'] ? $arProduct['ID'] : false,
														$arProduct['PROPERTY_CML2_LINK_VALUE'] ? $arProduct['IBLOCK_ID'] : false
													);?>
												<?else:?>
													<?\Aspro\Functions\CAsproMaxItem::showImg(
														array(),
														array('NAME' => $arItem['NAME']),
														false,
														false
													);?>
												<?endif;?>
											</div>
										</div>
										<div class="basket-share-detail__item__info-wrapper">
											<div class="basket-share-detail__item__info item_info">
												<div class="basket-share-detail__item__info__left">
													<div class="basket-share-detail__item__name">
														<?if($bExists):?>
															<a href="<?=$arProduct['DETAIL_PAGE_URL']?>" class="dark_link"><?=$name?></a>
														<?else:?>
															<?=$name?>
														<?endif;?>
													</div>
													<?if($arParams['SHOW_AMOUNT'] === 'Y'):?>
														<div class="basket-share-detail__item__amount">
															<div class="sa_block">
																<?if($bExists):?>
																	<?=$arProduct['QUANTITY_ARRAY']['HTML']?>
																<?else:?>
																	<div class="item-stock">
																		<span class="icon order"></span>
																		<span class="value font_sxs">
																			<span class="store_view dotted"><?=$arMess['PRODUCT_NOT_EXISTS']?></span>
																		</span>
																	</div>
																<?endif;?>
															</div>
														</div>
													<?endif;?>
													<div class="basket-share-detail__item__properties">
														<?if(
															$bShowArticle &&
															strlen($article)
														):?>
															<div class="basket-share-detail__item__property">
																<div class="basket-share-detail__item__property-name"><?=Loc::getMessage('BSD_T_PRODUCT_ARTICLE')?>
																</div>
																<div class="basket-share-detail__item__property-value"><?=$article?></div>
															</div>
														<?endif;?>
														<?if(
															$bExists &&
															$arParams['PRODUCT_PROPERTIES']
														):?>
															<?foreach($arParams['PRODUCT_PROPERTIES'] as $propertyCode):?>
																<?$arProperty = $arProduct['PROPERTIES'][$propertyCode];?>
																<?if(
																	$arProperty['VALUE'] &&
																	!in_array(
																		$propertyCode,
																		$arExcludePropertiesCodes
																	)
																):?>
																	<?
																	if(
																		$arProperty['USER_TYPE'] === 'directory' &&
																		$arProperty['USER_TYPE_SETTINGS']['TABLE_NAME']
																	){
																		$rsData = \Bitrix\Highloadblock\HighloadBlockTable::getList(
																			array(
																				'filter' => array(
																					'=TABLE_NAME' => $arProperty['USER_TYPE_SETTINGS']['TABLE_NAME']
																				)
																			)
																		);
																		if($arData = $rsData->fetch()){
																			$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arData);
																			$entityDataClass = $entity->getDataClass();

																			$arFilter = array(
																				'limit' => 1,
																				'filter' => array(
																					'=UF_XML_ID' => $arProperty['VALUE']
																				)
																			);

																			$arValue = $entityDataClass::getList($arFilter)->fetch();

																			if(isset($arValue['UF_NAME']) && $arValue['UF_NAME']){
																				$value = $arValue['UF_NAME'];
																			}
																			else{
																				$value = $arProperty['VALUE'];
																			}
																		}
																	}
																	else{
																		$value = isset($arProperty['VALUE_ENUM']) ? $arProperty['VALUE_ENUM'] : $arProperty['VALUE'];
																		$value = is_array($value) ? implode('/', $value) : $value;
																	}
																	?>
																	<div class="basket-share-detail__item__property">
																		<div class="basket-share-detail__item__property-name"><?=$arProperty['NAME']?>
																		</div>
																		<div class="basket-share-detail__item__property-value"><?=$value?></div>
																	</div>
																<?endif;?>
															<?endforeach;?>
														<?endif;?>
														<?if($arItem['BASKET_PROPS']):?>
															<?foreach(
																array_diff(
																	array_keys($arItem['BASKET_PROPS']),
																	array('CATALOG.XML_ID', 'PRODUCT.XML_ID')
																) as $code
															):?>
																<?$arProperty = $arItem['BASKET_PROPS'][$code];?>
																<div class="basket-share-detail__item__property">
																	<div class="basket-share-detail__item__property-name"><?=$arProperty['NAME']?></div>
																	<div class="basket-share-detail__item__property-value"><?=$arProperty['VALUE']?></div>
																</div>
															<?endforeach;?>
														<?endif;?>
													</div>
												</div>
												<div class="basket-share-detail__item__info__right">
													<div class="basket-share-detail__item__price">
														<div class="cost prices">
															<div class="price">
																<div class="price_value_block values_wrapper font-bold font_mxs">
																	<span class="price_value"><?=$priceFormated?></span>
																</div>
															</div>
															<?if($bWithDiscount):?>
																<?if($arParams['SHOW_OLD_PRICE'] === 'Y'):?>
																	<div class="price discount">
																		<div class="discount values_wrapper font_xs muted">
																			<span class="price_value"><?=$basePriceFormated?></span>
																		</div>
																	</div>
																<?endif;?>
															<?endif;?>
														</div>
														<div class="basket-share-detail__item__measure-ratio"><span><?=Loc::getMessage('BSD_T_PRODUCT_MEASURE_RATIO', array('#RATIO#' => $ratio, '#MEASURE_NAME#' => $measureName))?></span></div>
													</div>
													<div class="basket-share-detail__item__quantity">
														<?=($quantity ? $quantityFormated : '')?>
														<?if($bExists && $quantityAvailable != $quantityOriginal):?>
															<?if($bOriginal):?>
																<div class="basket-share-detail__item__quantity-available"><span><?=Loc::getMessage('BSD_T_PRODUCT_QUANTITY_ORIGINAL', array('#QUANTITY#' => $quantityAvailable, '#MEASURE_NAME#' => $arProduct['MEASURE_NAME']))?></span></div>
															<?else:?>
																<div class="basket-share-detail__item__quantity-original"><span><?=Loc::getMessage('BSD_T_PRODUCT_QUANTITY_ORIGINAL', array('#QUANTITY#' => $quantityOriginal, '#MEASURE_NAME#' => $arItem['MEASURE_NAME']))?></span></div>
															<?endif;?>
														<?endif;?>
													</div>
													<div class="basket-share-detail__item__sum">
														<?if($quantity):?>
															<div class="cost prices">
																<div class="price">
																	<div class="price_value_block values_wrapper font-bold font_mxs">
																		<span class="price_value"><?=$sumFormated?></span>
																	</div>
																</div>
																<?if($bWithDiscount):?>
																	<?if($arParams['SHOW_OLD_PRICE'] === 'Y'):?>
																		<div class="price discount">
																			<div class="discount values_wrapper font_xs muted">
																				<span class="price_value"><?=$woDiscountSumFormated?></span>
																			</div>
																		</div>
																	<?endif;?>
																	<?if($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y'):?>
																		<div class="sale_block matrix">
																			<div class="sale_wrapper font_xxs">
																				<div class="sale-number rounded2">
																					<?if(
																						$arParams['SHOW_DISCOUNT_PERCENT_NUMBER'] === 'Y' &&
																						$discountPercent < 100 &&
																						$discountPercent > 0
																					):?>
																						<div class="value">-<span><?=$discountPercent?></span>%</div>
																					<?endif;?>
																					<div class="inner-sale rounded1">
																						<div class="text">
																						<span class="title"><?=$arMess['PRODUCT_ECONOMY']?></span>
																						<span class="values_wrapper"><span class="price_value"><?=$discountSumFormated?></span></span></div>
																					</div>
																				</div>
																				<div class="clearfix"></div>
																			</div>
																		</div>
																	<?endif;?>
																<?endif;?>
															</div>
														<?endif;?>
													</div>
												</div>
											</div>
											<?if($bWithServices):?>												
												<div class="basket-share-detail__item__services">
													<?foreach($link_services as $arService):?>
														<?
														if($bExists = array_key_exists('PRODUCT', $arService) && $arService['PRODUCT']){
															$arProductService = $arService['PRODUCT'];
														}

														$bReady = $bExists && $arProductService['AVAILABLE'] === 'Y' && $arProductService['CAN_BUY'];
														$bChecked = $arService['CHECKED'] === 'Y';

														$name = $bExists ? $arProductService['NAME'] : $arService['NAME'];
														$article = $bExists ? (strlen($arProductService['PROPERTY_CML2_ARTICLE_VALUE']) ? $arProductService['PROPERTY_CML2_ARTICLE_VALUE'] : ($arProductService['PROPERTY_CML2_LINK_VALUE'] ? $arService['MAIN_PRODUCT']['PROPERTY_CML2_ARTICLE_VALUE'] : '')) : $arService['ARTICLE'];

														$measureName = ($bOriginal || !$bExists) ? $arService['MEASURE_NAME'] : $arProductService['MEASURE_NAME'];
														$ratio = ($bOriginal || !$bExists) ? $arService['RATIO'] : $arProductService['RATIO'];
														$currency = ($bOriginal || !$bExists) ? $arService['CURRENCY'] : $arProductService['CURRENCY'];
														$quantity = ($bOriginal || !$bExists) ? $arService['QUANTITY'] : $arProductService['QUANTITY'];
														$quantityFormated = ($bOriginal || !$bExists) ? ' x '.$arService['QUANTITY'].' '.$arService['MEASURE_NAME'] : ' x '.$arProductService['QUANTITY'].' '.$arProductService['MEASURE_NAME'];
														$quantityOriginal = $arService['QUANTITY'];

														$basePrice = $ratio * (($bOriginal || !$bExists) ? $arService['BASE_PRICE'] : $arProductService['BASE_PRICE']);
														$basePriceFormated = CurrencyFormat($basePrice, $currency);
														$price = $ratio * (($bOriginal || !$bExists) ? $arService['PRICE'] : $arProductService['PRICE']);
														$priceFormated = CurrencyFormat($price, $currency);
														$sum = ($bOriginal || !$bExists) ? $arService['FINAL_PRICE'] : $arProductService['FINAL_PRICE'];
														$sumFormated = ($bOriginal || !$bExists) ? $arService['FINAL_PRICE_FORMATED'] : $arProductService['FINAL_PRICE_FORMATED'];

														if($bWithDiscount = $basePrice > $price){
															$woDiscountSum = $basePrice * $quantity / $ratio;
															$woDiscountSumFormated = CurrencyFormat($woDiscountSum, $currency);
															$discountSum = $woDiscountSum - $sum;
															$discountSumFormated = CurrencyFormat($discountSum, $currency);
															$discountPercent = round($discountSum / $woDiscountSum * 100);
														}

														if($bExists){
															$quantityAvailable = $arProductService['QUANTITY'];
														}
														?>
														<div class="basket-share-service__item">
															<div class="basket-share-service__item__info">
																<div class="basket-share-service__item__check">
																	<?if($bReady):?>
																		<div class="filter onoff  label_block">
																			<input type="checkbox" name="CHECKED[<?=$arService['ID']?>]" id="basket-share-service__item__check--<?=$arService['ID']?>" value="Y" <?=($bChecked ? 'checked' : '')?>>
																			<label for="basket-share-service__item__check--<?=$arService['ID']?>"></label>
																		</div>
																	<?endif;?>
																</div>
																<div class="basket-share-service__item__name__wrap font_sm">
																	<div class="basket-share-service__item__name">
																		<?if($bExists):?>
																			<a href="<?=$arProductService['DETAIL_PAGE_URL']?>" class="dark_link"><?=$name?></a>
																		<?else:?>
																			<?=$name?>
																		<?endif;?>
																	</div>
																	<div class="basket-share-service__item__quantity">
																		<?=($quantity ? $quantityFormated : '')?>
																	</div>
																</div>
															</div>
															<div class="basket-share-service__item_sum">
																<?if($quantity):?>
																	<div class="cost prices">
																		<div class="price">
																			<div class="price_value_block values_wrapper font-bold ">
																				<span class="price_value"><?=$sumFormated?></span>
																			</div>
																		</div>
																		<?if($bWithDiscount):?>
																			<?if($arParams['SHOW_OLD_PRICE'] === 'Y'):?>
																				<div class="price discount">
																					<div class="discount values_wrapper muted">
																						<span class="price_value"><?=$woDiscountSumFormated?></span>
																					</div>
																				</div>
																			<?endif;?>
																		<?endif;?>
																	</div>
																<?endif;?>
															</div>
														</div>
													<?endforeach;?>
												</div>
											<?endif;?>
										</div>
									</div>
								</div>
							<?endforeach;?>
						</div>
						<div class="basket-share-detail__foot">
							<?
							$totalSumFormated = CurrencyFormat($totalSum, $currency);

							if($bWithDiscount = $totalSum != $woDiscountTotalSum){
								$woDiscountTotalSumFormated = CurrencyFormat($woDiscountTotalSum, $currency);
								$totalDiscountSum = $woDiscountTotalSum - $totalSum;
								$totalDiscountSumFormated = CurrencyFormat($totalDiscountSum, $currency);
							}
							?>
							<div class="basket-share-detail__foot__total">
								<div class="basket-share-detail__foot__total__title"><?=$arMess['TOTAL_PRICE']?></div>
								<div class="basket-share-detail__foot__total__price">
									<div class="cost prices">
										<div class="price">
											<div class="price_value_block values_wrapper font-bold">
												<span class="price_value"><?=$totalSumFormated?></span>
											</div>
										</div>
										<?if($bWithDiscount):?>
											<?if($arParams['SHOW_OLD_PRICE'] === 'Y'):?>
												<div class="price discount">
													<div class="discount values_wrapper font_xs muted">
														<span class="price_value"><?=$woDiscountTotalSumFormated?></span>
													</div>
												</div>
											<?endif;?>
											<?if($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y'):?>
												<div class="sale_block matrix">
													<div class="sale_wrapper font_xxs">
														<div class="sale-number rounded2">
															<div class="inner-sale rounded1">
																<div class="text">
																<span class="title"><?=Loc::getMessage('BSD_T_PRODUCT_ECONOMY')?></span>
																<span class="values_wrapper"><span class="price_value"><?=$totalDiscountSumFormated?></span></span></div>
															</div>
														</div>
														<div class="clearfix"></div>
													</div>
												</div>
											<?endif;?>
										<?endif;?>
									</div>
								</div>
							</div>
							<div class="basket-share-detail__foot__btns">
								<button type="submit" class="basket-share-detail__foot__btn basket-share-detail__foot__btn--add2basket btn btn-default btn-lg"><?=$arMess['ADD_TO_BASKET']?></button>
								<button type="submit" class="basket-share-detail__foot__btn basket-share-detail__foot__btn--replacebasket btn btn-transparent-border-color btn-lg"><?=$arMess['REPLACE_BASKET']?></button>
							</div>
						</div>
					<?endif;?>
				<?endif;?>
			</form>
		</div>
	<?endif;?>
	<script>
	new JCBasketShareDetail(
		<?=CUtil::PhpToJSObject($arParams, false, true)?>,
		<?=CUtil::PhpToJSObject([
			'ERRORS' => $arResult['ERRORS'],
			'AJAX_URL' => $arResult['AJAX_URL'],
			'BASKET_PAGE_URL' => $arResult['BASKET_PAGE_URL'],
		], false, true)?>
	);
	</script>
</div>
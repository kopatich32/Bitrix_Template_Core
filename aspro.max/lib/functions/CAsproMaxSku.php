<?
namespace Aspro\Functions;

use Bitrix\Main\Application;
use Bitrix\Main\Web\DOM\Document;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\DOM\CssParser;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');

if(!defined('FUNCTION_MODULE_ID'))
	define('FUNCTION_MODULE_ID', 'aspro.max');

if(!class_exists("CAsproMaxSku"))
{
	class CAsproMaxSku{

		public static function getMeasureRatio($arParams = array(), $minPrice = array(), $arItem){
			$measure_block = '';
			if((is_array($arParams) && $arParams)&& (is_array($minPrice) && $minPrice))
			{
				if($arParams["SHOW_MEASURE"]=="Y" && $arParams["SHOW_MEASURE_WITH_RATIO"] == "Y")
				{
					$measure_block = "<span class=\"price_measure\">/";
					if (isset($minPrice["CATALOG_MEASURE_RATIO"]) && $minPrice["CATALOG_MEASURE_RATIO"] != 1) {
						$measure_block .= $minPrice["CATALOG_MEASURE_RATIO"]." ";
						$measure_block .= $minPrice["CATALOG_MEASURE_NAME"];
					} elseif ($arItem['OFFERS'] && $arItem['OFFERS'][0]['ITEM_MEASURE']) {
						$measure_block .= $arItem['OFFERS'][0]['ITEM_MEASURE']["TITLE"]." ";
					} else {
						$measure_block .= $minPrice["CATALOG_MEASURE_NAME"];
					}
					$measure_block .= "</span>";
				}
			}
			return $measure_block;
		}

		public static function showItemPrices($arParams = array(), $arItem = array(), &$item_id = 0, &$min_price_id = 0, $arItemIDs = array(), $bShort = 'N'){
			$item_id = $MIN_PRICE_ID = 0;
			$sMissingGoodsPriceDisplay = \CMax::GetFrontParametrValue('MISSING_GOODS_PRICE_DISPLAY');
			$sMissingGoodsPriceDisplayText = \CMax::GetFrontParametrValue('MISSING_GOODS_PRICE_DISPLAY_TEXT_VALUE');
			$bOutOfProduction = isset($arItem['PROPERTIES']['OUT_OF_PRODUCTION']) && $arItem['PROPERTIES']['OUT_OF_PRODUCTION']['VALUE'] === 'Y';

			if((is_array($arParams) && $arParams) && (is_array($arItem) && $arItem))
			{
				ob_start();

				$minPrice = false;
				if (isset($arItem['MIN_PRICE']) || isset($arItem['RATIO_PRICE']))
					$minPrice = $arItem['MIN_PRICE'];

				$offer_id = 0;

				if($arParams["TYPE_SKU"] == "N")
					$offer_id = $minPrice["MIN_ITEM_ID"] ?? false;

				$min_price_id = $minPrice["MIN_PRICE_ID"] ?? false;

				if(!$min_price_id)
					$min_price_id = $minPrice["PRICE_ID"] ?? false;

				if($minPrice["MIN_ITEM_ID"] ?? false)
					$item_id = $minPrice["MIN_ITEM_ID"];

				if($arItem["OFFERS"])
					$arTmpOffer = current($arItem["OFFERS"]);

				if(!$min_price_id)
					$min_price_id = $arTmpOffer["MIN_PRICE"]["PRICE_ID"];

				$item_id = $arTmpOffer["ID"];

				$prefix = '';
				//if(('N' == $arParams['TYPE_SKU'] || $arParams['DISPLAY_TYPE'] !== 'block' || empty($arItem['OFFERS_PROP'])) || $bOutOfProduction && ($minPrice !== false)) мое
					//$prefix = GetMessage("CATALOG_FROM");
				$str_price_id = $str_price_old_id = '';
				if($arItemIDs)
				{
					if(isset($arItemIDs["ALL_ITEM_IDS"]) && (isset($arItemIDs["ALL_ITEM_IDS"]['PRICE']) && $arItemIDs["ALL_ITEM_IDS"]['PRICE']))
						$str_price_id = 'id="'.$arItemIDs["ALL_ITEM_IDS"]['PRICE'].'"';
					if(isset($arItemIDs["ALL_ITEM_IDS"]) && (isset($arItemIDs["ALL_ITEM_IDS"]['DISCOUNT_PRICE']) && $arItemIDs["ALL_ITEM_IDS"]['DISCOUNT_PRICE']))
						$str_price_old_id = 'id="'.$arItemIDs["ALL_ITEM_IDS"]['DISCOUNT_PRICE'].'"';
				}
				?>
				<?$measure_block = self::getMeasureRatio($arParams, $minPrice, $arItem);?>
				<div class="price_matrix_wrapper">
					<div class="prices-wrapper">
						<?if($arParams["SHOW_OLD_PRICE"]=="Y"){?>
							<div class="price font-bold <?=($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs');?>" <?=$str_price_id;?>>
								<? if($minPrice["VALUE"] || $sMissingGoodsPriceDisplay === 'PRICE'): ?>
									<?=$prefix;?> <span class="values_wrapper"><?=$minPrice["PRINT_DISCOUNT_VALUE"];?></span> <?=$measure_block;?>
								<? elseif($sMissingGoodsPriceDisplay === 'TEXT'): ?>
									<?= $sMissingGoodsPriceDisplayText; ?>
								<? endif; ?>
							</div>
							<?if($arParams["SHOW_OLD_PRICE"]=="Y"):?>
								<div class="price discount">
									<span class="values_wrapper <?=($arParams['MD_PRICE'] ? 'font_sm' : 'font_xs');?> muted" <?=(!$minPrice["DISCOUNT_DIFF"] ? 'style="display:none;"' : '')?>><?=$minPrice["PRINT_VALUE"];?></span>
								</div>
							<?endif;?>
						<?}else{?>
							<div class="price only_price font-bold <?=($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs');?>" <?=$str_price_id;?>>
								<? if($minPrice["VALUE"] || $sMissingGoodsPriceDisplay === 'PRICE'): ?>
									<?=$prefix;?> <span class="values_wrapper"><?=$minPrice["PRINT_DISCOUNT_VALUE"];?></span> <?=$measure_block;?>
								<? elseif($sMissingGoodsPriceDisplay === 'TEXT'): ?>
									<?= $sMissingGoodsPriceDisplayText; ?>
								<? endif; ?>
							</div>
						<?}?>
					</div>
					<?if($arParams["SHOW_DISCOUNT_PERCENT"]=="Y"){?>
						<div class="sale_block" <?=(!$minPrice["DISCOUNT_DIFF"] ? 'style="display:none;"' : '')?>>
							<?if($minPrice["DISCOUNT_DIFF"]):?>
								<div class="sale_wrapper font_xxs">
									<?if($bShort == 'Y'):?>
										<div class="inner-sale rounded1">
											<span class="title"><?=GetMessage("CATALOG_ECONOMY");?></span>
											<div class="text"><span class="values_wrapper"><?=$minPrice["PRINT_DISCOUNT_DIFF"];?></span></div>
										</div>
									<?else:?>
										<?$percent=round(($minPrice["DISCOUNT_DIFF"]/$minPrice["VALUE"])*100, 0);?>
										<div class="sale-number rounded2">
											<?if($percent && $percent<100){?>
												<div class="value">-<span><?=$percent;?></span>%</div>
											<?}?>
											<div class="inner-sale rounded1">
												<div class="text"><?=GetMessage("CATALOG_ECONOMY");?> <span class="values_wrapper"><?=$minPrice["PRINT_DISCOUNT_DIFF"];?></span></div>
											</div>
										</div>
									<?endif;?>
									<div class="clearfix"></div>
								</div>
							<?endif;?>
						</div>
					<?}?>
				</div>

				<?$html = ob_get_contents();
				ob_end_clean();

				foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproSkuShowItemPrices', true) as $arEvent) // event for manipulation min price
					ExecuteModuleEventEx($arEvent, array($arParams, $arItem, &$item_id, &$min_price_id, $arItemIDs, $bShort, &$html));

				echo $html;?>

			<?} elseif ($sMissingGoodsPriceDisplay === 'TEXT') { ?>
				<div class="price_matrix_wrapper">
					<div class="prices-wrapper">
						<div class="price font-bold font_mxs">
							<?= $sMissingGoodsPriceDisplayText; ?>
						</div>
					</div>
				</div>
			<?
			}
		}
	}
}?>
<?
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(false);
Loc::loadMessages(__FILE__);

// show errors
if($arResult['ERRORS']){
	if($arParams['SHOW_ERRORS'] === 'Y'){
		if($GLOBALS['USER']->IsAdmin()){
			?>
			<div class="basket-file-error">
				<div class="basket-file-error-icon"><?=\CMax::showIconSvg('fail colored', $this->{'__folder'}.'/images/svg/fail.svg')?></div>
				<div class="basket-file-error-text">
					<?if($arResult['ERRORS']):?>
						<?=implode('<br />', $arResult['ERRORS'])?>
					<?endif;?>
				</div>
			</div>
			<?
		}
	}

	return;
}

$arSite =& $arResult['SITE'];
$arRegion =& $arResult['REGION'];
$arContacts =& $arResult['CONTACTS'];
$arBasketItems =& $arResult['BASKET_ITEMS'];

$bUseCustomMessages = $arParams['USE_CUSTOM_MESSAGES'] === 'Y';
if(
	$bUseCustomMessages &&
	isset($arParams['MESS_BASKET_TITLE']) &&
	strlen($arParams['MESS_BASKET_TITLE'])
){
	$title = $arParams['MESS_BASKET_TITLE'];
}
else{
	$title = Loc::getMessage('BF_T_DOCUMENT_TITLE');
}

$msoStyle = Loc::getMessage('BF_T_MSO_STYLE');

$arHeads = array(
	'IMAGE',
	'INDEX',
	'NAME',
	'ARTICLE',
	'AMOUNT',
	'PRICE',
	'QUANTITY',
	'SUM',
);

ob_start();
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Excel.Sheet>
<meta name=Generator content="Microsoft Excel 14">
<title>$title</title>
<style>
<!--
table{
	mso-displayed-decimal-separator:"\,";
	mso-displayed-thousand-separator:" ";
}
@page{
	margin:.75in .7in .75in .7in;
	mso-header-margin:.3in;
	mso-footer-margin:.3in;
}
-->
tr{
	mso-height-source:auto;
}
col{
	mso-width-source:auto;
}
br{
	mso-data-placement:same-cell;
}
.style0{
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	white-space:nowrap;
	mso-rotate:0;
	mso-background-source:auto;
	mso-pattern:auto;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-generic-font-family:auto;
	mso-font-charset:0;
	border:none;
	mso-protection:locked visible;
	mso-style-name:$msoStyle;
	mso-style-id:0;
}
td{
	mso-style-parent:style0;
	padding:0px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri;
	mso-generic-font-family:auto;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	border:none;
	mso-background-source:auto;
	mso-pattern:auto;
	mso-protection:locked visible;
	white-space:nowrap;
	mso-rotate:0;
}
a:link{
	mso-style-parent:style0;
	color:blue;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	text-underline-style:single;
	font-family:Calibri, sans-serif;
	mso-generic-font-family:auto;
	mso-font-charset:0;
}
a:visited{
	mso-style-parent:style0;
	color:purple;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	text-underline-style:single;
	font-family:Calibri, sans-serif;
	mso-generic-font-family:auto;
	mso-font-charset:0;
}
.xf{
	mso-style-parent:style0;
	border:.5pt solid black;
	text-align:center;
	font-weight:bold;
	vertical-align:middle;
}
.xfr{
	mso-style-parent:style0;
	border:.5pt solid black;
	border-left:none;
	text-align:center;
	font-weight:bold;
	vertical-align:middle;
}
.xfc{
	mso-style-parent:style0;
	border:.5pt solid black;
	border-top:none;
	text-align:center;
	vertical-align:middle;
}
.xfo{
	mso-style-parent:style0;
	border:.5pt solid black;
	border-top:none;
	border-left:none;
	text-align:center;
	vertical-align:middle;
}
.xt{
	mso-style-parent:style0;
	font-size:26px;
	text-align:left;
	vertical-align:middle;
}
.xts{
	mso-style-parent:style0;
	font-weight:bold;
	text-align:center;
}
.xtt{
	mso-style-parent:style0;
	font-weight:bold;
	text-align:right;
}
.xsn{
	mso-style-parent:style0;
	font-weight:bold;
	text-align:right;
}
.xsi{
	mso-style-parent:style0;
	text-align:right;
}
</style>
</head>
<body link="blue" vlink="purple">
	<table border="0" cellpadding="0" cellspacing="0">
		<tr><td></td></tr>

		<?
		// logo
		$logo = '';
		if(strlen($arContacts['LOGO']['SRC'])){
			$imageSrc = $_SERVER['DOCUMENT_ROOT'].$arContacts['LOGO']['SRC'];
			if($arSize = \CFile::GetImageSize($imageSrc, true)){

				$width = $arSize[0];
				$height = $arSize[1];
				$proportion = $width / $height;
				if($width > 190){
					$width = 190;
					$height = $width / $proportion;
				}
				if($height > 82){
					$height = 82;
					$width = $height * $proportion;
				}

				$logo = '<img src="'.$arContacts['URL'].$arContacts['LOGO']['SRC'].'" width="'.$width.'" height="'.$height.'" data-src />';
			}
		}
		?>

		<?// name?>
		<tr>
			<!--[if mso]><td></td><![endif]-->
			<td colspan="3" rowspan="4" style="vertical-align:top;"><?=$logo?></td>
			<td colspan="5" class="xsn" align="right"><b><a href="<?=$arContacts['URL']?>"><?=$arSite['SITE_NAME']?></b></a></td>
		</tr>

		<?// phone?>
		<?if(strlen($arContacts['PHONE']['TITLE'])):?>
			<tr>
				<!--[if mso]><td></td><![endif]-->
				<td colspan="5" class="xsi" align="right"><a href="<?=$arContacts['PHONE']['HREF']?>"><?=$arContacts['PHONE']['TITLE']?></a></td>
			</tr>
		<?endif;?>

		<?// email?>
		<?if(strlen($arContacts['EMAIL'])):?>
			<tr>
				<!--[if mso]><td></td><![endif]-->
				<td colspan="5" class="xsi" align="right"><?=$arContacts['EMAIL']?></td>
			</tr>
		<?endif;?>

		<?// address?>
		<?if(strlen($arContacts['ADDRESS'])):?>
			<tr>
				<!--[if mso]><td></td><![endif]-->
				<td colspan=5 class=xsi align=right><?=$arContacts['ADDRESS']?></td>
			</tr>
		<?endif;?>

		<tr><td></td></tr>
		<tr><td></td></tr>

		<?if($arBasketItems):?>
			<?foreach($arBasketItems as $block => $arItems):?>
				<?if($arItems):?>
					<?
					$totalSum = 0;

					if(
						$bUseCustomMessages &&
						isset($arParams['MESS_BASKET_'.$block.'_ITEMS_TITLE']) &&
						strlen($arParams['MESS_BASKET_'.$block.'_ITEMS_TITLE'])
					){
						$bockTitle = $arParams['MESS_BASKET_'.$block.'_ITEMS_TITLE'];
					}
					else{
						$bockTitle = Loc::getMessage('BF_T_'.$block.'_TITLE');
					}
					?>

					<tr>
						<!--[if mso]><td></td><![endif]-->
						<td colspan="8" class="xt"><b><?=$bockTitle?></b></td>
					</tr>

					<tr><td></td></tr>

					<tr>
						<!--[if mso]><td></td><![endif]-->
						<?foreach($arHeads as $i => $code):?>
							<td class="<?=(!$i ? '' : ($i == 1 ? 'xf' : 'xfr'))?>" align="<?=($code === 'NAME' ? 'left' : 'center')?>" style="<?=($code === 'NAME' ? 'text-align:left;' : 'text-align:center;')?>"><b><?=Loc::getMessage('BF_T_'.$code.'_TITLE')?></b></td>
						<?endforeach;?>
					</tr>

					<?$indexCounter = 1;?>
					<?foreach($arItems as $i => $arItem):?>
						<?if(isset($arItem['IS_SERVICES']) && $arItem['IS_SERVICES'] === 'Y' ){continue;}?>
						<tr height="50" style="mso-height-source:userset;height:50px;">
							<?
							$arProduct = $arItem['PRODUCT'];
							$url = $arContacts['URL'].$arProduct['DETAIL_PAGE_URL'];
							?>

							<!--[if mso]><td></td><![endif]-->

							<?
							// image
							$arPicture = $arProduct['PICTURE'] ? $arProduct['PICTURE'] : ($arProduct['PROPERTY_CML2_LINK_VALUE'] ? $arItem['MAIN_PRODUCT']['PICTURE'] : array());

							if(!$arPicture){
								// noimage
								$arPicture['SRC'] = $arPicture['SRC_ORIGINAL'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__).'/images/noimage_product.jpg';
							}
							?>
							<?if($arPicture):?>
								<?if($arSize = \CFile::GetImageSize($_SERVER['DOCUMENT_ROOT'].$arPicture['SRC_ORIGINAL'], true)):?>

									<?
									$width = $arSize[0];
									$height = $arSize[1];
									$proportion = $width / $height;
									if($width > 50){
										$width = 50;
										$height = $width / $proportion;
									}
									if($height > 50){
										$height = 50;
										$width = $height * $proportion;
									}
									?>

									<td><img src="<?=$arContacts['URL'].$arPicture['SRC_ORIGINAL']?>" width="<?=$width?>" height="<?=$height?>" data-src /></td>
								<?else:?>
									<td></td>
								<?endif;?>
							<?else:?>
								<td></td>
							<?endif;?>

							<?// index?>
							<td class="xfc" align="center"><?=($indexCounter)?></td>

							<?// name?>
							<td class="xfo" style="text-align:left;white-space:normal;word-break:normal;" ><a href="<?=$url?>" target="_parent"><?=$arProduct['NAME']?></a></td>

							<?
							// article
							$article = strlen($arProduct['PROPERTY_CML2_ARTICLE_VALUE']) ? $arProduct['PROPERTY_CML2_ARTICLE_VALUE'] : ($arProduct['PROPERTY_CML2_LINK_VALUE'] ? $arItem['MAIN_PRODUCT']['PROPERTY_CML2_ARTICLE_VALUE'] : '');
							?>
							<td class="xfo" align="center"><?=$article?></td>

							<?
							// amount
							$amountFormated = $arProduct['QUANTITY_ARRAY']['RIGHTS']['SHOW_QUANTITY_COUNT'] && $arProduct['QUANTITY_ARRAY']['OPTIONS']['USE_WORD_EXPRESSION'] !== 'Y' ? $arProduct['TOTAL_COUNT'].' '.$arProduct['MEASURE_NAME'] : $arProduct['QUANTITY_ARRAY']['TEXT'];
							$amountFormated = preg_replace('/(\d+)[\.](\d+)/', '$1,$2', $amountFormated);
							?>
							<td class="xfo" align="center"><?=$amountFormated?></td>

							<?
							// price
							$priceFormated = str_replace('&#8381;', Loc::getMessage('BF_T_RUB'), $arProduct['PRICE_FORMATED']);
							$priceFormated = preg_replace('/(\d+)[\.](\d+)/', '$1,$2', $priceFormated);
							?>
							<td class="xfo" align="center"><?=$priceFormated?></td>

							<?
							// quantity
							$quantityFormated = $arProduct['QUANTITY_FORMATED'];
							$quantityFormated = preg_replace('/(\d+)[\.](\d+)/', '$1,$2', $quantityFormated);
							?>
							<td class="xfo" align="center"><?=$quantityFormated?></td>

							<?
							// sum
							$totalSum += $arProduct['FINAL_PRICE'];
							$sumFormated = str_replace('&#8381;', Loc::getMessage('BF_T_RUB'), $arProduct['FINAL_PRICE_FORMATED']);
							$sumFormated = preg_replace('/(\d+)[\.](\d+)/', '$1,$2', $sumFormated);
							?>
							<td class="xfo" align="center"><?=$sumFormated?></td>
						</tr>
						<?if( isset($arItem['LINK_SERVICES']) && is_array($arItem['LINK_SERVICES']) && count($arItem['LINK_SERVICES'])>0 ):?>
							<?$sCounter = 1;?>
							<?foreach($arItem['LINK_SERVICES'] as $arService):?>
								<tr class="services-item">
									<!--[if mso]><td></td><![endif]-->
									<td></td>
									<?// index?>
									<td class="xfc" align="center"><?=$indexCounter?><?="_";?><?=$sCounter?></td>
									<?
									// services name
									?>
									<td class="xfo" style="text-align:left;white-space:normal;word-break:normal;" ><a href="<?=$arContacts['URL'].$arService['URL']?>" target="_parent"><?=$arService['NAME']?></a></td>

									<td class="xfo"></td>

									<td class="xfo"></td>

									<?
									// services price
									$priceFormatedServices = str_replace('&#8381;', Loc::getMessage('BF_T_RUB'), $arService['PRICE_FORMATED']);
									$priceFormatedServices = preg_replace('/(\d+)[\.](\d+)/', '$1,$2', $priceFormatedServices);
									?>
									<td class="xfo" align="center"><?=$priceFormatedServices?></td>

									
									<?
									// quantity
									$quantityFormatedServices = $arService['QUANTITY_FORMATED'];
									$quantityFormatedServices = preg_replace('/(\d+)[\.](\d+)/', '$1,$2', $quantityFormatedServices);
									?>
									<td class="xfo" align="center"><?=$quantityFormatedServices?></td>

									<?
									// sum
									$totalSum += $arService['FINAL_PRICE'];
									$sumFormatedServices = str_replace('&#8381;', Loc::getMessage('BF_T_RUB'), $arService['FINAL_PRICE_FORMATED']);
									$sumFormatedServices = preg_replace('/(\d+)[\.](\d+)/', '$1,$2', $sumFormatedServices);
									?>
									<td class="xfo" align="center"><?=$sumFormatedServices?></td>

								</tr>
								<?$sCounter++;?>
							<?endforeach;?>
						<?endif;?>
						<?$indexCounter++;?>
					<?endforeach;?>

					<?
					// total sum
					$currency = $arItem['PRODUCT']['CURRENCY'];
					$totalSumFormated = str_replace('&#8381;', Loc::getMessage('BF_T_RUB'), \CurrencyFormat($totalSum, $currency));
					$totalSumFormated = preg_replace('/(\d+)[\.](\d+)/', '$1,$2', $totalSumFormated);
					?>
					<tr>
						<!--[if mso]><td></td><![endif]-->
						<td></td>
						<td class="xtt" colspan="6" align="right"><b><?=Loc::getMessage('BF_T_TOTAL_TITLE')?>: </b></td>
						<td class="xts" align="center"><b><?=$totalSumFormated?></b></td>
					</tr>

					<tr><td></td></tr>
					<tr><td></td></tr>

				<?endif;?>
			<?endforeach;?>
		<?endif;?>

		<tr><td></td></tr>
	</table>
</body>
</html>
<?
$content = ob_get_clean();
$content = iconv(SITE_CHARSET, 'UTF-8//IGNORE', $content);

// echo $content;
// die();

echo $content;
?>
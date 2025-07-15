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

ob_start();
?><!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
<title><?=$title?></title>
<style>
html{
    height:100%;
    width:100%;
}
body{
	font:15px/25px Montserrat,Arial,sans-serif;
	background:#fff;
    min-height:100%;
    width:100%;
    position:relative;
    margin:0 auto;
    padding:0;
    margin:0;
    color:#333;
}
a{
	color:blue;
	font-style:normal;
	text-decoration:none;
	cursor:pointer;
}
a:visited{
	color:purple;
	font-style:normal;
	text-decoration:none;
}
h2{
    color:#333;
    font-weight:normal;
    line-height:1.126em;
    margin:0;
    padding:0px;
    text-overflow:ellipsis;
    display:inline;
    vertical-align:middle;
    font-size:2.133em;
}
table{
	width:100%;
	border-collapse:collapse;
}
table tr{
}
.header{}
.header-logo{
	text-align:center;
}
.header-logo a{
	vertical-align:top;
}
.footer{
	margin:42px 0 0;
	font-size:13px;
    line-height:16px;
    color:#888;
}
.footer tr td:last-of-type{
	text-align:right;
}
.site-info{
}
.site-info__name{
	font-weight:bold;
}
.site-info a{
	color:#333;
}
.title{}
.basket-item-image{
	min-height:180px;
	width:195px;
    text-align:center;
    padding:15px 15px 15px 30px;
    border-top:1px solid #f2f2f2;
    border-bottom:1px solid #f2f2f2;
    border-left:1px solid #f2f2f2;
}
.basket-item-image a{}
.basket-item-image img{
    display:inline-block;
    max-width:150px;
    max-height:150px;
    vertical-align:middle;
}
.basket-item-info{
    min-height:180px;
	text-align:left;
    padding:0 0 0 30px;
    vertical-align:top;
    padding:15px 30px 15px 15px;
    border-top:1px solid #f2f2f2;
    border-bottom:1px solid #f2f2f2;
    border-right:1px solid #f2f2f2;
}
.basket-item-name{
    padding:0 0 15px 0;
}
.basket-item-properties{}
.basket-item-property{
	color:#888;
    font-size:13px;
    line-height:16px;
}
.basket-item-property-name,.basket-item-property-value{
	padding:0 5px 5px 0;
}
.basket-item-property-value{
	color:#333333;
}
.basket-item-price,.basket-item-quantity,.basket-item-sum{
	padding:0 0 15px 0;
}
.basket-item-price,.basket-item-sum{
	font-size:17px;
    font-weight:bold;
    color:#333;
}
.basket-services-sum{
	font-size:14px;
    font-weight:bold;
    color:#333;
}
.basket-item-quantity-measure{
	color:#a1a1a1;
	font-size:12px;
    line-height:14px;
}
.basket-item-quantity, .basket-services-quantity{
	text-align:center;
}
.basket-item-sum, .basket-services-sum{
    text-align:right;
}
.basket-block-total{
    text-align:right;
    padding:15px 30px;
    font-weight:bold;
}
.basket-services-name{
	font-size: 14px;
}
</style>
</head>
<body>
<?if($arContacts):?>
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

			$imageType = pathinfo($imageSrc, PATHINFO_EXTENSION);
			?>
			<?if($imageContent = @file_get_contents($imageSrc)):?>
				<table><tbody>
					<tr>
						<td class="header">
							<div class="header-logo">
								<a href="<?=$arContacts['URL']?>" target="_blank"><img src="data:image/<?=$imageType?>;base64,<?=base64_encode($imageContent)?>" width="<?=$width?>" height="<?=$height?>" data-src /></a>
							</div>
							<br>
						</td>
					</tr>
				</tbody></table>
			<?endif;?>
			<?
		}
	}
	elseif(strlen($arContacts['LOGO']['SVG'])){
		$svgSrc = $_SERVER['DOCUMENT_ROOT'].$arContacts['LOGO']['SVG'];
		$svgType = 'svg+xml';

		$svgContent = @file_get_contents($svgSrc);
		$svgContent = preg_replace('/['.Loc::getMessage('BF_T_CYR_REPLACE').']+/'.BX_UTF_PCRE_MODIFIER, '', $svgContent);
		?>
		<?if($svgContent):?>
			<table><tbody>
				<tr>
					<td class="header">
						<div class="header-logo">
							<a href="<?=$arContacts['URL']?>" target="_blank"><img src="data:image/<?=$svgType?>;base64,<?=base64_encode($svgContent)?>" data-src /></a>
						</div>
						<br>
					</td>
				</tr>
			</tbody></table>
		<?endif;?>
		<?
	}
	?>
<?endif;?>
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
			
			<table><tbody>
				<tr>
					<td class="title">
						<h2><?=$bockTitle?></h2>
						<br><br>
					</td>
				</tr>
			</tbody></table>

			<table class="basket-items"><tbody>
			<?foreach($arItems as $i => $arItem):?>
				<?if(isset($arItem['IS_SERVICES']) && $arItem['IS_SERVICES'] === 'Y' ){continue;}?>
				<tr class="basket-item">
					<?
					$arProduct = $arItem['PRODUCT'];
					$url = $arContacts['URL'].$arProduct['DETAIL_PAGE_URL'];
					?>
					<?// image?>
					<td class="basket-item-image">
						<?
						$arPicture = $arProduct['PICTURE'] ? $arProduct['PICTURE'] : ($arProduct['PROPERTY_CML2_LINK_VALUE'] ? $arItem['MAIN_PRODUCT']['PICTURE'] : array());

						if(!$arPicture){
							// noimage
							$arPicture['SRC'] = $arPicture['SRC_ORIGINAL'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__).'/images/noimage_product.jpg';
						}

						if($arPicture){
							$imageSrc = $_SERVER['DOCUMENT_ROOT'].$arPicture['SRC'];
							if($arSize = \CFile::GetImageSize($imageSrc, true)){

								$width = $arSize[0];
								$height = $arSize[1];
								$proportion = $width / $height;
								if($width > 150){
									$width = 150;
									$height = $width / $proportion;
								}
								if($height > 150){
									$height = 150;
									$width = $height * $proportion;
								}

								$imageType = pathinfo($imageSrc, PATHINFO_EXTENSION);
								?>
								<?if($imageContent = @file_get_contents($imageSrc)):?>
									<a href="<?=$url?>" target="_blank"><img src="data:image/<?=$imageType?>;base64,<?=base64_encode($imageContent)?>" width="<?=$width?>" height="<?=$height?>" data-src /></a>
								<?endif;?>
								<?
							}
						}
						?>
					</td>

					<td class="basket-item-info">
						<table class="basket-item-info-inner"><tbody>
							<tr>
								<?// name?>
								<td colspan="3" class="basket-item-name"><a href="<?=$url?>" target="_blank"><?=$arProduct['NAME']?></a></td>
							</tr>

							<tr>
								<?
								// price
								$priceFormated = str_replace('&#8381;', Loc::getMessage('BF_T_RUB'), $arProduct['PRICE_FORMATED']);
								?>
								<td class="basket-item-price"><?=$priceFormated?></td>

								<?
								// quantity
								?>
								<td class="basket-item-quantity">
									<span class="basket-item-quantity"><?=$arProduct['QUANTITY']?></span>
									<span class="basket-item-quantity-measure"><?=$arProduct['MEASURE_NAME']?></span>
								</td>

								<?
								// sum
								$totalSum += $arProduct['FINAL_PRICE'];
								$sumFormated = str_replace('&#8381;', Loc::getMessage('BF_T_RUB'), $arProduct['FINAL_PRICE_FORMATED']);
								?>
								<td class="basket-item-sum"><?=$sumFormated?></td>
							</tr>

							<tr>
								<td colspan="3" class="basket-item-properties">
									<table class="basket-item-properties-inner" style="width:inherit;"><tbody>
										<?
										// price type
										$priceTypeFormated = $arProduct['PRICE_TYPE_FORMATED'];
										?>
										<?if(strlen($priceTypeFormated)):?>
											<tr class=basket-item-property>
												<td class="basket-item-property-name"><?=Loc::getMessage('BF_T_PRICETYPE_TITLE')?></td>
												<td class="basket-item-property-value"><?=$priceTypeFormated?></td>
											</tr>
										<?endif;?>

										<?
										// amount
										$amountFormated = $arProduct['QUANTITY_ARRAY']['RIGHTS']['SHOW_QUANTITY_COUNT'] && $arProduct['QUANTITY_ARRAY']['OPTIONS']['USE_WORD_EXPRESSION'] !== 'Y' ? $arProduct['TOTAL_COUNT'].' '.$arProduct['MEASURE_NAME'] : $arProduct['QUANTITY_ARRAY']['TEXT'];
										?>
										<?if(strlen($amountFormated)):?>
											<tr class=basket-item-property>
												<td class="basket-item-property-name"><?=Loc::getMessage('BF_T_AMOUNT_TITLE')?></td>
												<td class="basket-item-property-value"><?=$amountFormated?></td>
											</tr>
										<?endif;?>

										<?
										// article
										$article = strlen($arProduct['PROPERTY_CML2_ARTICLE_VALUE']) ? $arProduct['PROPERTY_CML2_ARTICLE_VALUE'] : ($arProduct['PROPERTY_CML2_LINK_VALUE'] ? $arItem['MAIN_PRODUCT']['PROPERTY_CML2_ARTICLE_VALUE'] : '');
										?>
										<?if(strlen($article)):?>
											<tr class="basket-item-property">
												<td class="basket-item-property-name"><?=Loc::getMessage('BF_T_ARTICLE_TITLE')?></td>
												<td class="basket-item-property-value"><?=$article?></td>
											</tr>
										<?endif;?>

										<?if($arItem['BASKET_PROPS']):?>
											<?foreach(
												array_diff(
													array_keys($arItem['BASKET_PROPS']),
													array('CATALOG.XML_ID', 'PRODUCT.XML_ID')
												) as $code
											):?>
												<?$arProperty = $arItem['BASKET_PROPS'][$code];?>
												<tr class="basket-item-property">
													<td class="basket-item-property-name"><?=$arProperty['NAME']?></td>
													<td class="basket-item-property-value"><?=$arProperty['VALUE']?></td>
												</tr>
											<?endforeach;?>
										<?endif;?>
									</tbody></table>
								</td>
							</tr>
							<?if( isset($arItem['LINK_SERVICES']) && is_array($arItem['LINK_SERVICES']) && count($arItem['LINK_SERVICES'])>0 ):?>
								<?foreach($arItem['LINK_SERVICES'] as $arService):?>
									<tr class="services-item">
										<?
										// services name
										?>
										<td class="basket-services-name"><a href="<?=$arContacts['URL'].$arService['URL']?>" target="_blank"><?=$arService['NAME']?></a></td>

										<?
										// quantity
										?>
										<td class="basket-services-quantity">
											<span class="basket-item-quantity"><?=$arService['QUANTITY']?></span>
											<span class="basket-item-quantity-measure"><?=$arService['MEASURE_NAME']?></span>
										</td>

										<?
										// sum
										$totalSum += $arService['FINAL_PRICE'];
										$sumServicesFormated = str_replace('&#8381;', Loc::getMessage('BF_T_RUB'), $arService['FINAL_PRICE_FORMATED']);
										?>
										<td class="basket-services-sum"><?=$sumServicesFormated?></td>
									</tr>
								<?endforeach;?>
							<?endif;?>
						</tbody></table>
					</td>
				</tr>
			<?endforeach;?>

			<tr>
				<td colspan="2" class="basket-block-total">
					<?
					$currency = $arItem['PRODUCT']['CURRENCY'];
					$totalSumFormated = str_replace('&#8381;', Loc::getMessage('BF_T_RUB'), \CurrencyFormat($totalSum, $currency));
					?>
					<span><?=Loc::getMessage('BF_T_TOTAL_TITLE')?>: </span><span><?=$totalSumFormated?></span>
				</td>
			</tr>
			</tbody></table>
		<?endif;?>
	<?endforeach;?>
<?endif;?>
<table class="footer"><tbody>
	<tr>
		<?//name?>
		<td class="site-info site-info__name">
			<a href="<?=$arContacts['URL']?>" target="_blank"><?=$arSite['SITE_NAME']?></a>
		</td>

		<?// phone?>
		<td class="site-info site-info__phone">
			<?if(strlen($arContacts['PHONE']['TITLE'])):?>
				<a href="<?=$arContacts['PHONE']['HREF']?>"><?=$arContacts['PHONE']['TITLE']?></a>
			<?endif;?>
		</td>
	</tr>
	<tr>
		<?// url?>
		<td class="site-info site-info__url">
			<?if(strlen($arContacts['URL'])):?>
				<a href="<?=$arContacts['URL']?>" target="_blank"><?=$arContacts['URL']?></a>
			<?endif;?>
		</td>

		<?// email?>
		<td class="site-info site-info__email">
			<?if(strlen($arContacts['EMAIL'])):?><?=$arContacts['EMAIL']?><?endif;?>
		</td>
	</tr>
	<tr>
		<td></td>

		<?// address?>
		<td class="site-info site-info__address">
			<?if(strlen($arContacts['ADDRESS'])):?><?=$arContacts['ADDRESS']?><?endif;?>
		</td>
	</tr>
</tbody></table>
</body>
</html>
<?
$content = ob_get_clean();
$content = iconv(SITE_CHARSET, 'UTF-8//IGNORE', $content);

// echo $content;
// die();

$options = new \Dompdf\Options();
$options->set('default_font', 'Montserrat');
$options->set('enable_html5_parser', false);
$options->set('enable_php', false);
$options->set('enable_remote', true);
$options->set('dpi', 96);
$options->set('pdf_backend', 'cpdf');

$dompdf = new \Dompdf\Dompdf($options);
$context = stream_context_create(array(
	'ssl' => array(
		'verify_peer' => false,
		'verify_peer_name' => false,
		'allow_self_signed' => true,
	)
));
$dompdf->setHttpContext($context);

$dompdf->loadHtml($content, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$content = $dompdf->output(array('compress' => 1));
$content = ltrim($content);

echo $content;
?>
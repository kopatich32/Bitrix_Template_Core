<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!\Bitrix\Main\Loader::includeModule('aspro.max')) return;

if(!isset($arParams['CACHE_TIME'])){
	$arParams['CACHE_TIME'] = 86400;
}

$arParams['PAGE_ELEMENT_COUNT'] = $arParams['PAGE_ELEMENT_COUNT'] ? $arParams['PAGE_ELEMENT_COUNT'] : 4;

$arResult['TOKEN'] = CMax::GetFrontParametrValue('API_TOKEN_INSTAGRAMM');
$arResult['TITLE'] = CMax::GetFrontParametrValue('INSTAGRAMM_TITLE_BLOCK');
$arResult['ALL_TITLE'] = CMax::GetFrontParametrValue('INSTAGRAMM_TITLE_ALL_BLOCK');
$arResult['TEXT_LENGTH'] = CMax::GetFrontParametrValue('INSTAGRAMM_TEXT_LENGTH');
$arResult['MOBILE_TEMPLATE'] = CMax::GetFrontParametrValue('MOBILE_INSTAGRAM');
$arResult['ITEMS'] = [];

if($arParams['INCLUDE_FILE']){
	$arResult['DOP_TEXT'] = SITE_DIR.'include/mainpage/inc_files/'.$arParams['INCLUDE_FILE'];
}

if(!is_object($GLOBALS['USER'])){
	$GLOBALS['USER'] = new CUser();
}

if(
	$this->startResultCache(
		$arParams['CACHE_TIME'],
		array(
			($arParams['CACHE_GROUPS'] === 'N'? false: $GLOBALS['USER']->GetGroups()),
			$arResult
		)
	)
){
	$obInstagram = TSolution\Social\Factory::create('instagram', $arResult['TOKEN'], $arParams['PAGE_ELEMENT_COUNT']);

	$arData = $obInstagram->getPosts();

	if($arData){
		if($arData['error']['message']){
			$arResult['ERROR'] = $arData['error']['message'];
		}
		elseif($arData['data']){
			$arResult['ITEMS'] = array_slice($arData['data'], 0, $arParams['PAGE_ELEMENT_COUNT']);
			$arResult['USER']['username'] = $arData['data'][0]['username'];

			$arParams['RIGHT_LINK'] = 'https://www.instagram.com/'.$arResult['USER']['username'].'/';
			$arParams['RIGHT_LINK_EXTERNAL'] = true;
		}
	}

	if($arResult['ERROR']){
		$this->AbortResultCache();
		?>
		<?if($GLOBALS['USER']->IsAdmin()):?>
			<div class="content_wrapper_block">
				<div class="maxwidth-theme" style="padding-top: 20px;">
					<div class="alert alert-danger">
						<strong>Error: </strong><?=htmlspecialcharsbx($arResult['ERROR']);?>
					</div>
				</div>
			</div>
		<?endif;?>
		<?
	}

	$this->IncludeComponentTemplate();
}
?>

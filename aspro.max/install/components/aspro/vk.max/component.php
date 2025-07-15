<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!\Bitrix\Main\Loader::includeModule('aspro.max')) return;

if(!isset($arParams['CACHE_TIME'])){
	$arParams['CACHE_TIME'] = 86400;
}

$arParams['PAGE_ELEMENT_COUNT'] = $arParams['ELEMENTS_ROW'] ? $arParams['ELEMENTS_ROW'] : 4;

$arResult['TOKEN'] = ( strlen($arParams["API_TOKEN_VK"]) > 0 ? htmlspecialcharsbx($arParams["API_TOKEN_VK"]) : CMax::GetFrontParametrValue('API_TOKEN_VK') );
$arResult['GROUP_ID'] = isset($arParams["GROUP_ID_VK"]) > 0 && strlen($arParams["GROUP_ID_VK"]) > 0 ? $arParams["GROUP_ID_VK"] : CMax::GetFrontParametrValue('GROUP_ID_VK');
if (intval($arResult['GROUP_ID']) > 0) {
	$arResult['GROUP_ID'] = '-'.htmlspecialcharsbx($arResult['GROUP_ID']);
}
$arResult['TEXT_LENGTH'] = ( $arParams["VK_TEXT_LENGTH"] ?? CMax::GetFrontParametrValue('VK_TEXT_LENGTH') );
$arResult['TITLE'] = ( $arParams['TITLE'] && $arParams['SHOW_TITLE'] ? $arParams['TITLE'] : CMax::GetFrontParametrValue('VK_TITLE_BLOCK') );
$arResult['ALL_TITLE'] = ( $arParams['VK_TITLE_ALL_BLOCK'] ?? CMax::GetFrontParametrValue('VK_TITLE_ALL_BLOCK') );

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
	$obVK = TSolution\Social\Factory::create('vk', $arResult['TOKEN'], $arParams['PAGE_ELEMENT_COUNT']);

	$arData = $obVK->getPosts([
		'OWNER_ID' => $arResult['GROUP_ID'],
	]);

	if($arData){
		if($arData['error']['error_msg']){
			$arResult['ERROR'] = $arData['error']['error_msg'];
		}
		elseif(isset($arData['response']) && isset($arData['response']['items'])){
			$arResult['ITEMS'] = $arData['response']['items'];
			if (count($arResult['ITEMS']) > $arParams['PAGE_ELEMENT_COUNT']){
				$arResult['ITEMS'] = array_slice($arResult['ITEMS'], 0, $arParams['PAGE_ELEMENT_COUNT']);
			}
			$arResult['GROUP'] = [];

			if (isset($arData['response']['groups']) && count($arData['response']['groups'])) {
				$arResult['GROUP'] = $arData['response']['groups'][0];
			}
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

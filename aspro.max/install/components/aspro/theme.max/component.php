<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?if(!\Bitrix\Main\Loader::includeModule('aspro.max')):?>
	<div class='alert alert-warning'><?=GetMessage('ASPRO_MAX_MODULE_NOT_INSTALLED')?></div>
	<?die;?>
<?endif;?>
<?
require_once('function.php');


$arResult = array();
$arFrontParametrs = CMax::GetFrontParametrsValues(SITE_ID);

$bAjaxWidget = isset($_REQUEST['BLOCK']) && $_REQUEST['BLOCK'] === 'widget';
$bShowTemplate = $bAjaxWidget || $arParams['SHOW_TEMPLATE'] === 'Y';

if($bShowTemplate){
	global $arMergeOptions;

	if(isset($_SESSION['arMergeOptions']) && !empty($_SESSION['arMergeOptions'])){
		$arFrontParametrs = array_merge((array)$arFrontParametrs, (array)$_SESSION['arMergeOptions']);
	}
	$_SESSION['arMergeOptions'] = (array)$arMergeOptions;
}

foreach(CMax::$arParametrsList as $blockCode => $arBlock)
{
	foreach($arBlock['OPTIONS'] as $optionCode => $arOption)
	{
		$arResult[$optionCode] = $arOption;
		$arResult[$optionCode]['VALUE'] = $arFrontParametrs[$optionCode];
		$arResult[$optionCode]['TYPE_BLOCK'] = $blockCode;

		if(isset($arResult[$optionCode]['ADDITIONAL_OPTIONS']) && $arResult[$optionCode]['ADDITIONAL_OPTIONS']) //additional params
		{
			if($arResult[$optionCode]['LIST'])
			{
				foreach($arResult[$optionCode]['LIST'] as $key => $arListOption)
				{
					if($arListOption['ADDITIONAL_OPTIONS'])
					{
						foreach($arListOption['ADDITIONAL_OPTIONS'] as $key2 => $arListOption2)
						{
							if($arListOption2['LIST'])
							{
								$bMulti = $arListOption2['TYPE'] == 'multiselectbox';
								if($bMulti) {
									$arFrontParametrs[$key2.'_'.$key] = explode(',', $arFrontParametrs[$key2.'_'.$key]);
								}
								foreach($arListOption2['LIST'] as $key3 => $arListOption3)
								{
									if(!is_array($arListOption3))
										$arResult[$optionCode]['LIST'][$key]['ADDITIONAL_OPTIONS'][$key2]['LIST'][$key3] = array('TITLE' => $arListOption3);

									if($bMulti) { 
										if( in_array($key3, $arFrontParametrs[$key2.'_'.$key]) )
										{
											$arResult[$optionCode]['LIST'][$key]['ADDITIONAL_OPTIONS'][$key2]['LIST'][$key3]['CURRENT'] = 'Y';
											$arResult[$optionCode]['LIST'][$key]['ADDITIONAL_OPTIONS'][$key2]['VALUE'] = $arFrontParametrs[$key2.'_'.$key];
										}
									} else {
										if($key3 == $arFrontParametrs[$key2.'_'.$key])
										{
											$arResult[$optionCode]['LIST'][$key]['ADDITIONAL_OPTIONS'][$key2]['LIST'][$key3]['CURRENT'] = 'Y';
											$arResult[$optionCode]['LIST'][$key]['ADDITIONAL_OPTIONS'][$key2]['VALUE'] = $arFrontParametrs[$key2.'_'.$key];
										}
									}
								}
								if($bMulti) {
									$arResult[$optionCode]['LIST'][$key]['ADDITIONAL_OPTIONS'][$key2]['VALUE'] = implode(',', (array)$arResult[$optionCode]['LIST'][$key]['ADDITIONAL_OPTIONS'][$key2]['VALUE']);
								}
							}
							elseif($arListOption2['TYPE'] == 'checkbox')
							{
								$arResult[$optionCode]['LIST'][$key]['ADDITIONAL_OPTIONS'][$key2]['VALUE'] = $arFrontParametrs[$key2.'_'.$key];
							}
						}
					}
				}
			}
		}

		if(isset($arResult[$optionCode]['SUB_PARAMS']) && $arResult[$optionCode]['SUB_PARAMS']) //nested params
		{
			if($arResult[$optionCode]['LIST'])
			{
				foreach($arResult[$optionCode]['LIST'] as $key => $arListOption)
				{
					if($arResult[$optionCode]['SUB_PARAMS'][$key])
					{
						foreach($arResult[$optionCode]['SUB_PARAMS'][$key] as $key2 => $arSubOptions)
						{
							//show fon index components
							if(isset($arSubOptions['FON']) && $arSubOptions['FON'])
							{
								$code_tmp = 'fon'.$key.$key2;
								$arResult['FON_PARAMS'][$code_tmp] = $arFrontParametrs[$code_tmp];
							}

							//show template index components
							if(isset($arSubOptions['TEMPLATE']) && $arSubOptions['TEMPLATE'])
							{
								$code_tmp = $key.'_'.$key2.'_TEMPLATE';
								$arResult['TEMPLATE_PARAMS'][$key][$code_tmp] = $arSubOptions['TEMPLATE'];
								$arResult['TEMPLATE_PARAMS'][$key][$code_tmp]['ACTIVE'] = $arFrontParametrs[$key.'_'.$key2];
								foreach($arResult['TEMPLATE_PARAMS'][$key][$code_tmp]['LIST'] as $keyTemplate => $template)
								{
									if($arFrontParametrs[$code_tmp] == $keyTemplate)
									{
										$arResult['TEMPLATE_PARAMS'][$key][$code_tmp]['LIST'][$keyTemplate]['CURRENT'] = 'Y';
										$arResult['TEMPLATE_PARAMS'][$key][$code_tmp]['VALUE'] = $keyTemplate;
										$arResult[$optionCode]['SUB_PARAMS'][$key][$key2]['TEMPLATE']['VALUE'] = $arFrontParametrs[$code_tmp];
									}

									if(isset($template['ADDITIONAL_OPTIONS']) && $template['ADDITIONAL_OPTIONS'])
									{
										foreach($template['ADDITIONAL_OPTIONS'] as $keyS2 => $arListOption2)
										{
											/*if($arListOption2['LIST'])
											{
												// [index1_CATALOG_SECTIONS_SHAPE_PICTURES_front_sections_only3] => round
												// print_r($arListOption2);
												foreach($arListOption2['LIST'] as $keyS3 => $arListOption3)
												{
													$arResult[$optionCode]['SUB_PARAMS'][$key][$key2]['TEMPLATE']['LIST'][$keyTemplate]['ADDITIONAL_OPTIONS'][$keyS2]['LIST'][$keyS3] = $arListOption2['DEFAULT'];
												}
												$arResult[$optionCode]['SUB_PARAMS'][$key][$key2]['TEMPLATE']['LIST'][$keyTemplate]['ADDITIONAL_OPTIONS'][$keyS2]['VALUE'] = $arFrontParametrs[$key.'_'.$key2.'_'.$keyS2.'_'.$keyTemplate];
												$arResult['TEMPLATE_PARAMS'][$key][$code_tmp]['LIST'][$keyTemplate]['ADDITIONAL_OPTIONS'][$keyS2]['VALUE'] = $arFrontParametrs[$key.'_'.$key2.'_'.$keyS2.'_'.$keyTemplate];
											}
											elseif($arListOption2['TYPE'] == 'checkbox')
											{*/
												$arResult[$optionCode]['SUB_PARAMS'][$key][$key2]['TEMPLATE']['LIST'][$keyTemplate]['ADDITIONAL_OPTIONS'][$keyS2]['VALUE'] = $arFrontParametrs[$key.'_'.$key2.'_'.$keyS2.'_'.$keyTemplate];
												$arResult['TEMPLATE_PARAMS'][$key][$code_tmp]['LIST'][$keyTemplate]['ADDITIONAL_OPTIONS'][$keyS2]['VALUE'] = $arFrontParametrs[$key.'_'.$key2.'_'.$keyS2.'_'.$keyTemplate];
											//}
										}
									}
								}
							}

							if($arResult[$optionCode]['SUB_PARAMS'][$key][$key2]['TYPE'] == 'selectbox')
							{
								foreach($arResult[$optionCode]['SUB_PARAMS'][$key][$key2]['LIST'] as $key3 => $value)
								{
									if($arFrontParametrs[$key.'_'.$key2] == $value)
										$arResult[$optionCode]['SUB_PARAMS'][$key][$key2]['LIST'][$key3]['CURRENT'] = 'Y';
								}
							}
							else
							{
								$arResult[$optionCode]['SUB_PARAMS'][$key][$key2]['VALUE'] = $arFrontParametrs[$key.'_'.$key2];
							}
						}

						//sort order prop for main page
						$param = 'SORT_ORDER_'.$optionCode.'_'.$key;
						$arResult[$param] = $arFrontParametrs[$param];
					}
				}
			}
		}

		if(isset($arResult[$optionCode]['DEPENDENT_PARAMS']) && $arResult[$optionCode]['DEPENDENT_PARAMS']) //dependent params
		{
			foreach($arResult[$optionCode]['DEPENDENT_PARAMS'] as $key => $arListOption)
			{
				$arResult[$optionCode]['DEPENDENT_PARAMS'][$key]['VALUE'] = $arFrontParametrs[$key];
				if(isset($arListOption['LIST']) && isset($arListOption['LIST']))
				{
					foreach($arListOption['LIST'] as $variantCode => $variant)
					{
						if(!is_array($variant))
							$arResult[$optionCode]['DEPENDENT_PARAMS'][$key]['LIST'][$variantCode] = array('TITLE' => $variant);
						if($arFrontParametrs[$key] == $variantCode)
							$arResult[$optionCode]['DEPENDENT_PARAMS'][$key]['LIST'][$variantCode]['CURRENT'] = 'Y';
					}
				}
			}
		}

		// CURRENT for compatibility with old versions
		if($arResult[$optionCode]['LIST'])
		{
			$bMulti = $arResult[$optionCode]['TYPE'] == 'multiselectbox';
			if($bMulti) {
				$arValue = strlen($arResult[$optionCode]['VALUE']) ? explode(',', $arResult[$optionCode]['VALUE']) : [];
			}
			foreach($arResult[$optionCode]['LIST'] as $variantCode => $variantTitle)
			{
				if(!is_array($variantTitle)) {
					$arResult[$optionCode]['LIST'][$variantCode] = array('TITLE' => $variantTitle);
				}

				if($bMulti) {
					if( in_array($variantCode, $arValue) ) {
						$arResult[$optionCode]['LIST'][$variantCode]['CURRENT'] = 'Y';
					}
				} else {
					if($arResult[$optionCode]['VALUE'] == $variantCode)
						$arResult[$optionCode]['LIST'][$variantCode]['CURRENT'] = 'Y';
				}
			}
		}
	}
}

if($arResult)
{
	$arGroups = $arGroups2 = array();
	foreach($arResult as $optionCode => $arOption)
	{
		if((isset($arOption['GROUP']) && $arOption['GROUP'])) //set groups option
		{
			$arGroups[$arOption['GROUP']]['TITLE'] = GetMessage($arOption['GROUP']);
			$arGroups[$arOption['GROUP']]['THEME'] = $arOption['THEME'];
			$arGroups[$arOption['GROUP']]['GROUPS_EXT'] = 'Y';
			$arGroups[$arOption['GROUP']]['TYPE_BLOCK'] = $arOption['TYPE_BLOCK'];
			$arGroups[$arOption['GROUP']]['OPTIONS'][$optionCode] = $arOption;
			unset($arResult[$optionCode]);

			if(isset($arOption['GROUP_HINT']) && $arOption['GROUP_HINT']) //set group hint
				$arGroups[$arOption['GROUP']]['HINT'] = $arOption['GROUP_HINT'];
		}
		elseif((isset($arOption['TAB_GROUP_BLOCK']) && $arOption['TAB_GROUP_BLOCK']))
		{
			$arGroups2['TABS'][$arOption['TYPE_BLOCK']][$arOption['TAB_GROUP_BLOCK']]['OPTIONS'][$optionCode] = $arOption;

		}
	}

	if($arGroups)
		$arResult = array_merge($arResult, $arGroups);
	if($arGroups2)
		$arResult = array_merge($arResult, $arGroups2);
	?>
<?}
    if($bShowTemplate){
		$bPageSpeedTest = CMax::isPageSpeedTest(); // it`s page speed test now
		$bLightVersion = CMax::checkIndexBot(); // it`s page speed test & need light version now

		$active = $arResult['THEME_SWITCHER']['VALUE'] == 'Y';
		$arResult['SHOW_RESET'] = ((isset($_SESSION['THEME']) && $_SESSION['THEME']) && (isset($_SESSION['THEME'][SITE_ID]) && $_SESSION['THEME'][SITE_ID]));
		$arResult['CAN_SAVE'] = ($GLOBALS['USER']->IsAdmin() && $arResult['SHOW_RESET']);

		$themeDir = strToLower($arResult['BASE_COLOR']['VALUE'].($arResult['BASE_COLOR']['VALUE'] !== 'CUSTOM' ? '' : '_'.SITE_ID));
		$themeBgDir = strToLower($arResult['BGCOLOR_THEME']['VALUE'].($arResult['BGCOLOR_THEME']['VALUE'] !== 'CUSTOM' ? '' : '_'.SITE_ID));
		$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/themes/'.$themeDir.'/theme.css', true);
		$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/bg_color/'.$themeBgDir.'/bgcolors.css', true);

		$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/widths/width-'.$arResult['PAGE_WIDTH']['VALUE'].'.css', true);
		$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/fonts/font-'.$arResult['FONT_STYLE']['VALUE'].'.css', true);

		if($bLightVersion){
			\Bitrix\Main\Data\StaticHtmlCache::getInstance()->markNonCacheable();
		}
		else{
			if(
				$active &&
				(
					(
						(!isset($_REQUEST['ajax']) || strtolower($_REQUEST['ajax']) !== 'y') &&
						(
							!isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
							strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
						)
					) ||
					(isset($_REQUEST['BLOCK']) && $_REQUEST['BLOCK'] === 'widget')
				)
			){
				\Bitrix\Main\Data\StaticHtmlCache::getInstance()->markNonCacheable();

				
				if(!$bPageSpeedTest){
					$this->IncludeComponentTemplate();
				}
			}

			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/custom.css', true);
		}

		$file = \Bitrix\Main\Application::getDocumentRoot().'/bitrix/components/aspro/theme.max/css/user_font_'.SITE_ID.'.css';

		if($arResult['CUSTOM_FONT']['VALUE'] && \Bitrix\Main\IO\File::isFileExists($file)){
			$APPLICATION->SetAdditionalCSS($componentPath.'/css/user_font_'.SITE_ID.'.css', true);
		}
	}

	return $arResult;
?>
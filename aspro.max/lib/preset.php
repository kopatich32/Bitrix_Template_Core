<?
namespace Aspro\Max;

use Bitrix\Main\Config\Option,
	CMax as Solution;

class Preset {
	public static $arThematicsList = array();
	public static $arPresetsList = array();

	public static function getModuleId() {
		return Solution::moduleID;
	}

	public static function getDefaultThematic() :string {
		return 'UNIVERSAL';
	}

	public static function getCurrentThematic(string $siteId) :string {
		$siteId = strlen($siteId) ? $siteId : (defined('SITE_ID') ? SITE_ID : '');

		return Option::get(static::getModuleId(), 'THEMATIC', static::getDefaultThematic(), $siteId);
	}

	public static function getCurrentPreset(string $siteId) :int {
		static $arCurPresets;

		if (!isset($arCurPresets)) {
			$arCurPresets = array();
		}

		$siteId = strlen($siteId) ? $siteId : (defined('SITE_ID') ? SITE_ID : '');

		if (!isset($arCurPresets[$siteId])) {
			$arCurPresets[$siteId] = false;

			$arPresets = array();
			if (strlen($curThematic = static::getCurrentThematic($siteId))) {
				if (static::$arThematicsList && static::$arThematicsList[$curThematic]) {
					$arPresets = static::$arPresetsList;
					foreach ($arPresets as $id => &$arPreset) {
						if (in_array($id, static::$arThematicsList[$curThematic]['PRESETS']['LIST'])) {
							if (static::$arThematicsList[$curThematic]['OPTIONS'] && is_array(static::$arThematicsList[$curThematic]['OPTIONS'])) {
								$arPreset['OPTIONS'] = static::options_replace($arPreset['OPTIONS'], static::$arThematicsList[$curThematic]['OPTIONS']);
							}
						}
						else {
							unset($arPresets[$id]);
						}
					}
					unset($arPreset);
				}
			}

			if ($arPresets) {
				$arFrontParametrs = Solution::GetFrontParametrsValues($siteId);

				foreach (Solution::$arParametrsList as $blockCode => $arBlock) {
					foreach ($arBlock['OPTIONS'] as $optionCode => $arOption) {
						if (isset($arOption['THEME']) && $arOption['THEME'] === 'Y') {
							foreach ($arPresets as $id => &$arPreset) {
								if ($arPreset['OPTIONS']) {
									if (array_key_exists($optionCode, $arPreset['OPTIONS'])) {
										$presetValue = $arPreset['OPTIONS'][$optionCode];

										if (array_key_exists($optionCode, $arFrontParametrs)) {
											if (is_array($presetValue)) {
												if (array_key_exists('VALUE', $presetValue)) {
													if ($arFrontParametrs[$optionCode] != $presetValue['VALUE']) {
														unset($arPresets[$id]);
														continue;
													}
												}

												if (is_array($presetValue['ADDITIONAL_OPTIONS'])) {
													// check only additional values of current option value
													if (is_array($presetValue['ADDITIONAL_OPTIONS'][$presetValue['VALUE']])) {
														foreach ($presetValue['ADDITIONAL_OPTIONS'][$presetValue['VALUE']] as $subAddOptionCode => $subAddOptionValue) {
															if (isset($arFrontParametrs[$subAddOptionCode.'_'.$presetValue['VALUE']])) {
																if ($arFrontParametrs[$subAddOptionCode.'_'.$presetValue['VALUE']] != $subAddOptionValue) {
																	unset($arPresets[$id]);
																	continue 2;
																}
															}
														}
													}
												}

												if (is_array($presetValue['SUB_PARAMS'])) {
													foreach ($presetValue['SUB_PARAMS'] as $subOptionCode => $subValue) {
														if (isset($arFrontParametrs[$presetValue['VALUE'].'_'.$subOptionCode])) {
															if (is_array($subValue)) {
																if (array_key_exists('VALUE', $subValue)) {
																	if ($arFrontParametrs[$presetValue['VALUE'].'_'.$subOptionCode] != $subValue['VALUE']) {
																		unset($arPresets[$id]);
																		continue 2;
																	}

																	if (array_key_exists('TEMPLATE', $subValue) && array_key_exists($presetValue['VALUE'].'_'.$subOptionCode.'_TEMPLATE', $arFrontParametrs)) {
																		if ($arFrontParametrs[$presetValue['VALUE'].'_'.$subOptionCode.'_TEMPLATE'] != $subValue['TEMPLATE']) {
																			unset($arPresets[$id]);
																			continue 2;
																		}

																		if (array_key_exists('ADDITIONAL_OPTIONS', $subValue)) {
																			foreach ($subValue['ADDITIONAL_OPTIONS'] as $addSubOptionTemplateCode => $addSubOptionTemplateValue) {
																				if (array_key_exists($presetValue['VALUE'].'_'.$subOptionCode.'_'.$addSubOptionTemplateCode.'_'.$subValue['TEMPLATE'], $arFrontParametrs)) {
																					if ($arFrontParametrs[$presetValue['VALUE'].'_'.$subOptionCode.'_'.$addSubOptionTemplateCode.'_'.$subValue['TEMPLATE']] != $addSubOptionTemplateValue) {
																						unset($arPresets[$id]);
																						continue 3;
																					}
																				}
																			}
																		}
																	}

																	if (array_key_exists('FON', $subValue) && array_key_exists('fon'.$presetValue['VALUE'].$subOptionCode, $arFrontParametrs)) {
																		if ($arFrontParametrs['fon'.$presetValue['VALUE'].$subOptionCode] != $subValue['FON']) {
																			unset($arPresets[$id]);
																			continue 2;
																		}
																	}
																}
															}
															else {
																if ($arFrontParametrs[$presetValue['VALUE'].'_'.$subOptionCode] != $subValue) {
																	unset($arPresets[$id]);
																	continue 2;
																}
															}
														}
													}
												}

												if (is_array($presetValue['DEPENDENT_PARAMS'])) {
													foreach ($presetValue['DEPENDENT_PARAMS'] as $depOptionCode => $depValue) {
														if (isset($arFrontParametrs[$depOptionCode])) {
															if ($arFrontParametrs[$depOptionCode] != $depValue) {
																unset($arPresets[$id]);
																continue 2;
															}
														}
													}
												}

												if (array_key_exists('ORDER', $presetValue)) {
													if (isset($arFrontParametrs['SORT_ORDER_'.$optionCode.'_'.$presetValue['VALUE']])) {
														if ($arFrontParametrs['SORT_ORDER_'.$optionCode.'_'.$presetValue['VALUE']] != $presetValue['ORDER']) {
															unset($arPresets[$id]);
															continue;
														}
													}
												}
											}
											else {
												if ($arFrontParametrs[$optionCode] != $presetValue) {
													unset($arPresets[$id]);
													continue;
												}
											}
										}
									}
								}
								else {
									unset($arPresets[$id]);
									continue;
								}
							}
							unset($arPreset);
						}
					}
				}
			}

			if ($arPresets) {
				return $arCurPresets[$siteId] = intval(key($arPresets));
			}
		}

		return $arCurPresets[$siteId];
	}

	public static function getOptionsOfPreset($presetId) :array {
		if (($presetId = intval($presetId) > 0 ? intval($presetId) : false) > 0) {
			if (
				static::$arPresetsList &&
				isset(static::$arPresetsList[$presetId]) &&
				is_array(static::$arPresetsList[$presetId])
			) {
				return static::$arPresetsList[$presetId]['OPTIONS'];
			}
		}

		return array();
	}

	public static function getThemeParametrsValues(bool $bFront, array $arExcludeBlockCodes, string $siteId, string $siteDir) :array {
		$arThemeParametrsValues = array();

		$siteId = strlen($siteId) ? $siteId : (defined('SITE_ID') ? SITE_ID : '');
		$siteDir = strlen($siteDir) ? $siteDir : (defined('SITE_DIR') ? SITE_DIR : '');

		$arParametrs = $bFront ? Solution::GetFrontParametrsValues($siteId, $siteDir, false) : Solution::GetBackParametrsValues($siteId, $siteDir, false);

		foreach (Solution::$arParametrsList as $blockCode => $arBlock) {
			if (
				$arBlock['OPTIONS'] &&
				$arBlock['THEME'] === 'Y' &&
				!in_array($blockCode, $arExcludeBlockCodes)
			) {
				foreach ($arBlock['OPTIONS'] as $optionCode => $arOption) {
					if ($arOption['THEME'] === 'Y') {
						if (isset($arParametrs[$optionCode])) {
							if (
								(
									$optionCode === 'MORE_COLOR' ||
									$optionCode === 'MORE_COLOR_CUSTOM'
								) &&
								$arParametrs['USE_MORE_COLOR'] === 'N'
							) {
								continue;
							}

							if ($arOption['TYPE'] === 'backButton') {
								continue;
							}

							$val = $arParametrs[$optionCode];

							if (
								$optionCode === 'BASE_COLOR_CUSTOM' ||
								$optionCode === 'CUSTOM_BGCOLOR_THEME' ||
								$optionCode === 'MORE_COLOR_CUSTOM'
							) {
								$val = str_replace('#', '', $val);
							}

							$arThemeParametrsValues[$optionCode] = $val;

							if (
								is_array($arOption['LIST']) &&
								$arOption['LIST'][$val] &&
								is_array($arOption['LIST'][$val])
							) {
								if (
									$arOption['LIST'][$val]['ADDITIONAL_OPTIONS'] &&
									is_array($arOption['LIST'][$val]['ADDITIONAL_OPTIONS'])
								) {
									foreach ($arOption['LIST'][$val]['ADDITIONAL_OPTIONS'] as $subAddOptionCode => $arSubAddOption) {
										if (isset($arParametrs[$subAddOptionCode.'_'.$val])) {
											$subAddOptionValue = $arParametrs[$subAddOptionCode.'_'.$val];
										}
										else {
											if ($arSubAddOption['TYPE'] === 'checkbox') {
												$subAddOptionValue = 'N';
											}
											else {
												$subAddOptionValue = $arSubAddOption['DEFAULT'];
											}
										}

										$arThemeParametrsValues[$subAddOptionCode.'_'.$val] = $subAddOptionValue;
									}
								}
							}

							if ($arOption['SUB_PARAMS']) {
								$arThemeParametrsValues['SORT_ORDER_INDEX_TYPE_index1'] = $arParametrs['SORT_ORDER_INDEX_TYPE_index1'];

								if ($arOption['SUB_PARAMS'][$val]) {
									if (!$arThemeParametrsValues['SORT_ORDER_INDEX_TYPE_index1']) {
										$arThemeParametrsValues['SORT_ORDER_INDEX_TYPE_index1'] = implode(',', array_keys($arOption['SUB_PARAMS'][$val]));
									}

									foreach ($arOption['SUB_PARAMS'][$val] as $subOptionCode => $arSubOption) {
										if ($arSubOption['THEME'] === 'Y') {
											if ($arSubOption['FON']) {
												if (isset($arParametrs['fon'.$val.$subOptionCode])) {
													$arThemeParametrsValues['fon'.$val.$subOptionCode] = $arParametrs['fon'.$val.$subOptionCode];
												}
											}

											if ($arSubOption['TEMPLATE']) {
												if (isset($arParametrs[$val.'_'.$subOptionCode])) {
													$arThemeParametrsValues[$val.'_'.$subOptionCode] = $arParametrs[$val.'_'.$subOptionCode];
												}
												else {
													$arThemeParametrsValues[$val.'_'.$subOptionCode] = 'N';
												}

												if (isset($arParametrs[$val.'_'.$subOptionCode.'_TEMPLATE'])) {
													$arThemeParametrsValues[$val.'_'.$subOptionCode.'_TEMPLATE'] = $arParametrs[$val.'_'.$subOptionCode.'_TEMPLATE'];

													if ($arSubOption['TEMPLATE']['TYPE'] === 'selectbox' && $arSubOption['TEMPLATE']['LIST']) {
														$arSubOptionTemplateValue = $arSubOption['TEMPLATE']['LIST'][$arThemeParametrsValues[$val.'_'.$subOptionCode.'_TEMPLATE']];
														if ($arSubOptionTemplateValue && is_array($arSubOptionTemplateValue) && $arSubOptionTemplateValue['ADDITIONAL_OPTIONS'] && is_array($arSubOptionTemplateValue['ADDITIONAL_OPTIONS'])) {
															foreach ($arSubOptionTemplateValue['ADDITIONAL_OPTIONS'] as $addSubOptionTemplateCode => $arAddSubOptionTemplate) {
																if (isset($arParametrs[$val.'_'.$subOptionCode.'_'.$addSubOptionTemplateCode.'_'.$arThemeParametrsValues[$val.'_'.$subOptionCode.'_TEMPLATE']])) {
																	$arThemeParametrsValues[$val.'_'.$subOptionCode.'_'.$addSubOptionTemplateCode.'_'.$arThemeParametrsValues[$val.'_'.$subOptionCode.'_TEMPLATE']] = $arParametrs[$val.'_'.$subOptionCode.'_'.$addSubOptionTemplateCode.'_'.$arThemeParametrsValues[$val.'_'.$subOptionCode.'_TEMPLATE']];
																}
																else {
																	if ($arAddSubOptionTemplate['TYPE'] === 'checkbox') {
																		$arThemeParametrsValues[$val.'_'.$subOptionCode.'_'.$addSubOptionTemplateCode.'_'.$arThemeParametrsValues[$val.'_'.$subOptionCode.'_TEMPLATE']] = 'N';
																	}
																	else {
																		$arThemeParametrsValues[$val.'_'.$subOptionCode.'_'.$addSubOptionTemplateCode.'_'.$arThemeParametrsValues[$val.'_'.$subOptionCode.'_TEMPLATE']] = $arAddSubOptionTemplate['DEFAULT'];
																	}
																}
															}
														}
													}
												}
											}
											else {
												if (isset($arParametrs[$val.'_'.$subOptionCode])) {
													$arThemeParametrsValues[$val.'_'.$subOptionCode] = $arParametrs[$val.'_'.$subOptionCode];
												}
												else {
													if ($arSubOption['TYPE'] === 'checkbox') {
														if ($arSubOption['THEME'] !== 'N') {
															$arThemeParametrsValues[$val.'_'.$subOptionCode] = 'N';
														}
													}
												}
											}
										}
									}
								}
							}

							if ($arOption['DEPENDENT_PARAMS']) {
								foreach ($arOption['DEPENDENT_PARAMS'] as $depOptionCode => $arDepOption) {
									if ($arDepOption['THEME'] === 'Y') {
										if (isset($arParametrs[$depOptionCode])) {
											$depOptionValue = $arParametrs[$depOptionCode];
										}
										else {
											if ($arDepOption['TYPE'] === 'checkbox') {
												$depOptionValue = 'N';
											}
											else {
												$depOptionValue = $arDepOption['DEFAULT'];
											}
										}

										$arThemeParametrsValues[$depOptionCode] = $depOptionValue;
									}
								}
							}
						}
						else {
							if ($arOption['TYPE'] === 'checkbox') {
								if ($arOption['THEME'] !== 'N') {
									$arThemeParametrsValues[$optionCode] = 'N';
								}
							}
						}
					}
				}
			}
		}

		return $arThemeParametrsValues;
	}

	public static function getPresetOptions(array $arThemeParametrsValues, array $arExcludeBlockCodes) :array {
		$arPresetOptions = array();

		foreach (Solution::$arParametrsList as $blockCode => $arBlock) {
			if (
				$arBlock['OPTIONS'] &&
				$arBlock['THEME'] === 'Y' &&
				!in_array($blockCode, $arExcludeBlockCodes)
			) {
				foreach ($arBlock['OPTIONS'] as $optionCode => $arOption) {
					if ($arOption['THEME'] === 'Y') {
						if (isset($arThemeParametrsValues[$optionCode])) {
							if (
								(
									$optionCode === 'MORE_COLOR' ||
									$optionCode === 'MORE_COLOR_CUSTOM'
								) &&
								$arThemeParametrsValues['USE_MORE_COLOR'] === 'N'
							) {
								continue;
							}

							if ($arOption['TYPE'] === 'backButton') {
								continue;
							}

							$val = $arThemeParametrsValues[$optionCode];

							if (
								$optionCode === 'BASE_COLOR_CUSTOM' ||
								$optionCode === 'CUSTOM_BGCOLOR_THEME' ||
								$optionCode === 'MORE_COLOR_CUSTOM'
							) {
								$val = str_replace('#', '', $val);
							}

							$arPresetOptions[$optionCode] = array(
								'VALUE' => $val,
							);

							if (
								is_array($arOption['LIST']) &&
								$arOption['LIST'][$val] &&
								is_array($arOption['LIST'][$val])
							) {
								if (
									$arOption['LIST'][$val]['ADDITIONAL_OPTIONS'] &&
									is_array($arOption['LIST'][$val]['ADDITIONAL_OPTIONS'])
								) {
									$arPresetOptions[$optionCode]['ADDITIONAL_OPTIONS'] = array();

									foreach ($arOption['LIST'][$val]['ADDITIONAL_OPTIONS'] as $subAddOptionCode => $arSubAddOption) {
										if (isset($arThemeParametrsValues[$subAddOptionCode.'_'.$val])) {
											$subAddOptionValue = $arThemeParametrsValues[$subAddOptionCode.'_'.$val];
										}
										else {
											if ($arSubAddOption['TYPE'] === 'checkbox') {
												$subAddOptionValue = 'N';
											}
											else {
												$subAddOptionValue = $arSubAddOption['DEFAULT'];
											}
										}

										$arPresetOptions[$optionCode]['ADDITIONAL_OPTIONS'][$subAddOptionCode] = $subAddOptionValue;
									}
								}
							}

							if ($arOption['SUB_PARAMS']) {
								$arPresetOptions[$optionCode]['SUB_PARAMS'] = array();
								$arPresetOptions[$optionCode]['ORDER'] = $arThemeParametrsValues['SORT_ORDER_INDEX_TYPE_index1'];

								if ($arOption['SUB_PARAMS'][$val]) {
									if (!$arPresetOptions[$optionCode]['ORDER']) {
										$arPresetOptions[$optionCode]['ORDER'] = implode(',', array_keys($arOption['SUB_PARAMS'][$val]));
									}

									foreach ($arOption['SUB_PARAMS'][$val] as $subOptionCode => $arSubOption) {
										if ($arSubOption['THEME'] === 'Y') {
											if (
												$arSubOption['TEMPLATE'] ||
												$arSubOption['FON']
											) {
												$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode] = array();
											}

											if ($arSubOption['FON']) {
												$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['FON'] = $arThemeParametrsValues['fon'.$val.$subOptionCode];
											}

											if ($arSubOption['TEMPLATE']) {
												if (isset($arThemeParametrsValues[$val.'_'.$subOptionCode])) {
													$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['VALUE'] = $arThemeParametrsValues[$val.'_'.$subOptionCode];
												}
												else {
													$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['VALUE'] = 'N';
												}

												if (isset($arThemeParametrsValues[$val.'_'.$subOptionCode.'_TEMPLATE'])) {
													$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['TEMPLATE'] = $arThemeParametrsValues[$val.'_'.$subOptionCode.'_TEMPLATE'];

													if ($arSubOption['TEMPLATE']['TYPE'] === 'selectbox' && $arSubOption['TEMPLATE']['LIST']) {
														$arSubOptionTemplateValue = $arSubOption['TEMPLATE']['LIST'][$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['TEMPLATE']];
														if ($arSubOptionTemplateValue && is_array($arSubOptionTemplateValue) && $arSubOptionTemplateValue['ADDITIONAL_OPTIONS'] && is_array($arSubOptionTemplateValue['ADDITIONAL_OPTIONS'])) {
															$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['ADDITIONAL_OPTIONS'] = array();
															foreach ($arSubOptionTemplateValue['ADDITIONAL_OPTIONS'] as $addSubOptionTemplateCode => $arAddSubOptionTemplate) {
																if (isset($arThemeParametrsValues[$val.'_'.$subOptionCode.'_'.$addSubOptionTemplateCode.'_'.$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['TEMPLATE']])) {
																	$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['ADDITIONAL_OPTIONS'][$addSubOptionTemplateCode] = $arThemeParametrsValues[$val.'_'.$subOptionCode.'_'.$addSubOptionTemplateCode.'_'.$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['TEMPLATE']];
																}
																else {
																	if ($arAddSubOptionTemplate['TYPE'] === 'checkbox') {
																		$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['ADDITIONAL_OPTIONS'][$addSubOptionTemplateCode] = 'N';
																	}
																	else {
																		$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['ADDITIONAL_OPTIONS'][$addSubOptionTemplateCode] = $arAddSubOptionTemplate['DEFAULT'];
																	}
																}
															}
														}
													}
												}
											}
											else {
												if (isset($arThemeParametrsValues[$val.'_'.$subOptionCode])) {
													if (is_array($arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode])) {
														$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['VALUE'] = $arThemeParametrsValues[$val.'_'.$subOptionCode];
													}
													else {
														$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode] = $arThemeParametrsValues[$val.'_'.$subOptionCode];
													}
												}
												else {
													if ($arSubOption['TYPE'] === 'checkbox') {
														if ($arSubOption['THEME'] !== 'N') {
															if (is_array($arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode])) {
																$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode]['VALUE'] = 'N';
															}
															else {
																$arPresetOptions[$optionCode]['SUB_PARAMS'][$subOptionCode] = 'N';
															}
														}
													}
												}
											}
										}
									}
								}
							}

							if ($arOption['DEPENDENT_PARAMS']) {
								$arPresetOptions[$optionCode]['DEPENDENT_PARAMS'] = array();

								foreach ($arOption['DEPENDENT_PARAMS'] as $depOptionCode => $arDepOption) {
									if ($arDepOption['THEME'] === 'Y') {
										if (isset($arThemeParametrsValues[$depOptionCode])) {
											$depOptionValue = $arThemeParametrsValues[$depOptionCode];
										}
										else {
											if ($arDepOption['TYPE'] === 'checkbox') {
												$depOptionValue = 'N';
											}
											else {
												$depOptionValue = $arDepOption['DEFAULT'];
											}
										}

										$arPresetOptions[$optionCode]['DEPENDENT_PARAMS'][$depOptionCode] = $depOptionValue;
									}
								}

								if (!$arPresetOptions[$optionCode]['DEPENDENT_PARAMS']) {
									unset($arPresetOptions[$optionCode]['DEPENDENT_PARAMS']);
								}
							}

							if (count(array_keys($arPresetOptions[$optionCode])) == 1) {
								$arPresetOptions[$optionCode] = $val;
							}
						}
						else {
							if ($arOption['TYPE'] === 'checkbox') {
								if ($arOption['THEME'] !== 'N') {
									$arPresetOptions[$optionCode] = 'N';
								}
							}
						}
					}
				}
			}
		}

		return $arPresetOptions;
	}

	public static function setFrontPresetOptions(array $arPresetOptions, string $siteId) :bool {
		if (
			$arPresetOptions &&
			is_array($arPresetOptions)
		) {
			$siteId = strlen($siteId) ? $siteId : (defined('SITE_ID') ? SITE_ID : '');

			if (strlen($curThematic = static::getCurrentThematic($siteId))) {
				if (static::$arThematicsList && static::$arThematicsList[$curThematic]) {
					$arPresetOptions = static::options_replace($arPresetOptions, static::$arThematicsList[$curThematic]['OPTIONS']);

					if ($arPresetOptions) {
						foreach ($arPresetOptions as $optionCode => $optionVal) {
							if (!is_array($optionVal)) {
								$_SESSION['THEME'][$siteId][$optionCode] = $optionVal;
							}
							else {
								if (array_key_exists('VALUE', $optionVal)) {
									$propValue = $optionVal['VALUE'];
									$_SESSION['THEME'][$siteId][$optionCode] = $propValue;

									if (array_key_exists('ADDITIONAL_OPTIONS', $optionVal)) {
										foreach ($optionVal['ADDITIONAL_OPTIONS'] as $subAddOptionCode => $subAddOptionValue) {
											$_SESSION['THEME'][$siteId][$subAddOptionCode.'_'.$propValue] = $subAddOptionValue;
										}
									}

									if (array_key_exists('SUB_PARAMS', $optionVal)) {
										foreach ($optionVal['SUB_PARAMS'] as $subOptionCode => $arSubOption) {
											if (is_array($arSubOption)) {
												if (array_key_exists('VALUE', $arSubOption)) {
													$_SESSION['THEME'][$siteId][$propValue.'_'.$subOptionCode] = $arSubOption['VALUE'];
												}

												if (array_key_exists('TEMPLATE', $arSubOption)) {
													$_SESSION['THEME'][$siteId][$propValue.'_'.$subOptionCode.'_TEMPLATE'] = $arSubOption['TEMPLATE'];

													if (is_array($arSubOption['ADDITIONAL_OPTIONS'])) {
														foreach ($arSubOption['ADDITIONAL_OPTIONS'] as $addSubOptionTemplateCode => $addSubOptionTemplateValue) {
															$_SESSION['THEME'][$siteId][$propValue.'_'.$subOptionCode.'_'.$addSubOptionTemplateCode.'_'.$arSubOption['TEMPLATE']] = $addSubOptionTemplateValue;
														}
													}
												}

												if (array_key_exists('FON', $arSubOption)) {
													$_SESSION['THEME'][$siteId]['fon'.$propValue.$subOptionCode] = $arSubOption['FON'];
												}
											}
											else {
												$_SESSION['THEME'][$siteId][$propValue.'_'.$subOptionCode] = $arSubOption;
											}
										}
									}

									if (array_key_exists('DEPENDENT_PARAMS', $optionVal)) {
										foreach ($optionVal['DEPENDENT_PARAMS'] as $depOptionCode => $depOptionVal) {
											if (is_array($depOptionVal)) {
												if (array_key_exists('VALUE', $depOptionVal)) {
													$_SESSION['THEME'][$siteId][$depOptionCode] = $depOptionVal['VALUE'];
												}
											}
											else {
												$_SESSION['THEME'][$siteId][$depOptionCode] = $depOptionVal;
											}
										}
									}

									if (array_key_exists('ORDER', $optionVal)) {
										$_SESSION['THEME'][$siteId]['SORT_ORDER_'.$optionCode.'_'.$propValue] = $optionVal['ORDER'];
									}
								}
							}
						}
					}

					return true;
				}
			}
		}

		return false;
	}

	public static function setFrontParametrsOfPreset(int $presetId, string $siteId) :bool {
		return static::setFrontPresetOptions(static::getOptionsOfPreset($presetId), $siteId);
	}

	public static function setBackPresetOptions(array $arPresetOptions, string $siteId) :bool {
		if (
			$arPresetOptions &&
			is_array($arPresetOptions)
		) {
			$siteId = strlen($siteId) ? $siteId : (defined('SITE_ID') ? SITE_ID : '');

			$_SESSION['THEME'][$siteId] = [];

			if (strlen($curThematic = static::getCurrentThematic($siteId))) {
				if (static::$arThematicsList && static::$arThematicsList[$curThematic]) {
					$arPresetOptions = static::options_replace($arPresetOptions, static::$arThematicsList[$curThematic]['OPTIONS']);

					if ($arPresetOptions) {
						foreach ($arPresetOptions as $optionCode => $optionVal) {
							if (!is_array($optionVal)) {
								Option::set(static::getModuleId(), $optionCode, $optionVal, $siteId);
							}
							else {
								if (array_key_exists('VALUE', $optionVal)) {
									$propValue = $optionVal['VALUE'];
									Option::set(static::getModuleId(), $optionCode, $propValue, $siteId);

									if (array_key_exists('ADDITIONAL_OPTIONS', $optionVal)) {
										foreach ($optionVal['ADDITIONAL_OPTIONS'] as $subAddOptionCode => $subAddOptionValue) {
											Option::set(static::getModuleId(), $subAddOptionCode.'_'.$propValue, $subAddOptionValue, $siteId);
										}
									}

									if (array_key_exists('SUB_PARAMS', $optionVal)) {
										$arSubValues = array();
										foreach ($optionVal['SUB_PARAMS'] as $subOptionCode => $arSubOption) {
											if (is_array($arSubOption)) {
												if (array_key_exists('VALUE', $arSubOption)) {
													$arSubValues[$subOptionCode] = $arSubOption['VALUE'];
												}

												if (array_key_exists('TEMPLATE', $arSubOption)) {
													Option::set(static::getModuleId(), $propValue.'_'.$subOptionCode.'_TEMPLATE', $arSubOption['TEMPLATE'], $siteId);

													if (array_key_exists('ADDITIONAL_OPTIONS', $arSubOption)) {
													 	foreach ($arSubOption['ADDITIONAL_OPTIONS'] as $addSubOptionTemplateCode => $addSubOptionTemplateValue) {
															// Option::set(static::getModuleId(), $propValue.'_'.$subOptionCode.'_'.$addSubOptionTemplateCode.'_'.$arSubOption['TEMPLATE'], $addSubOptionTemplateValue, $siteId);

															// !!! max strange !!!
															$addSubOptionTemplateOptionKey = 'N_O_'.$optionCode.'_'.$propValue.'_'.$subOptionCode.'_';
															$arTmpSubOption = Solution::unserialize(Option::get(static::getModuleId(), $addSubOptionTemplateOptionKey, serialize([]), $siteId));
															$arTmpSubOption = $arTmpSubOption && is_array($arTmpSubOption) ? $arTmpSubOption : [];
															$arTmpSubOption[$subOptionCode.'_'.$addSubOptionTemplateCode.'_'.$arSubOption['TEMPLATE']] = $addSubOptionTemplateValue;
															Option::set(static::getModuleId(), $addSubOptionTemplateOptionKey, serialize($arTmpSubOption), $siteId);
														}
													}
												}

												if (array_key_exists('FON', $arSubOption)) {
													Option::set(static::getModuleId(), 'fon'.$propValue.$subOptionCode, $arSubOption['FON'], $siteId);
												}
											}
											else {
												$arSubValues[$subOptionCode] = $arSubOption;
											}
										}

										if ($arSubValues) {
											Option::set(static::getModuleId(), 'NESTED_OPTIONS_'.$optionCode.'_'.$propValue, serialize($arSubValues), $siteId);
										}
									}

									if (array_key_exists('DEPENDENT_PARAMS', $optionVal)) {
										foreach ($optionVal['DEPENDENT_PARAMS'] as $depOptionCode => $depOptionVal) {
											if (is_array($depOptionVal)) {
												if (array_key_exists('VALUE', $depOptionVal)) {
													Option::set(static::getModuleId(), $depOptionCode, $depOptionVal['VALUE'], $siteId);
												}
											}
											else {
												Option::set(static::getModuleId(), $depOptionCode, $depOptionVal, $siteId);
											}
										}
									}

									if (array_key_exists('ORDER', $optionVal)) {
										Option::set(static::getModuleId(), 'SORT_ORDER_'.$optionCode.'_'.$propValue, $optionVal['ORDER'], $siteId);
									}
								}
							}
						}
					}

					return true;
				}
			}
		}

		return false;
	}

	public static function setBackParametrsOfPreset(int $presetId, string $siteId) :bool {
		return static::setBackPresetOptions(static::getOptionsOfPreset($presetId), $siteId);
	}

	public static function getCurrentPresetBannerIndex(string $siteId) :int {
		$siteId = strlen($siteId) ? $siteId : (defined('SITE_ID') ? SITE_ID : '');

		if ($curPreset = static::getCurrentPreset($siteId)) {
			$precetID = $curPreset;
		}
		elseif ($curThematic = static::getCurrentThematic($siteId)) {
			$precetID = static::$arThematicsList[$curThematic]['PRESETS']['DEFAULT'];
		}
		else {
			$precetID = static::$arThematicsList['UNVERSAL']['PRESETS']['DEFAULT'];
		}

		if ($precetID) {
			static::$arPresetsList[$precetID]['BANNER_INDEX'] = intval(static::$arPresetsList[$precetID]['BANNER_INDEX']);
			
			return static::$arPresetsList[$precetID]['BANNER_INDEX'] > 1 ? static::$arPresetsList[$precetID]['BANNER_INDEX'] : 1;
		}
		else {
			return 1;
		}
	}

	public static function options_replace($arA, $arB) {
		if (is_array($arA) && is_array($arB)) {
			foreach ($arA as $key => $value) {
				if (array_key_exists($key, $arB)) {
					if (is_array($value)) {
						$arA[$key] = static::options_replace($arA[$key], $arB[$key]);
					}
					else {
						$arA[$key] = $arB[$key];
					}
				}
			}
		}
		else {
			$arA = $arB;
		}

		return $arA;
	}
}

include_once __DIR__.'/../thematics.php';
include_once __DIR__.'/../presets.php';

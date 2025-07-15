<?
if (!defined('ASPRO_MAX_MODULE_ID'))
	define('ASPRO_MAX_MODULE_ID', 'aspro.max');

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	CMaxCache as Cache,
	CMax as Solution;

Loc::loadMessages(__FILE__);

if (!class_exists('CMaxRegionality')) {
	class CMaxRegionality {
		static protected $siteId;
		static protected $iblockId;
		static protected $arRegions;
		static protected $arCurrentRegion;
		static protected $arCurrentLocation;
		static protected $arRealRegion;

		static public $arSeoMarks = [
			'#REGION_NAME#' => 'NAME',
			'#REGION_NAME_DECLINE_RP#' => 'PROPERTY_REGION_NAME_DECLINE_RP_VALUE',
			'#REGION_NAME_DECLINE_PP#' => 'PROPERTY_REGION_NAME_DECLINE_PP_VALUE',
			'#REGION_NAME_DECLINE_TP#' => 'PROPERTY_REGION_NAME_DECLINE_TP_VALUE',
		];

		static public function cleanStaticData() {
			static::$iblockId = null;
			static::$arRegions = null;
			static::$arCurrentRegion = null;
			static::$arCurrentLocation = null;
			static::$arRealRegion = null;
		}

		static public function getSiteId() :string {
			return static::$siteId ?: SITE_ID;
		}

		static public function setSiteId(string $siteId) {
			static::cleanStaticData();
			static::$siteId = $siteId;
		}

		static public function checkUseRegionality() {
			return Solution::GetFrontParametrValue('USE_REGIONALITY', static::getSiteId()) === 'Y';
		}

        static public function checkUseRegionalityFilter() {
			return static::checkUseRegionality() && Solution::GetFrontParametrValue('REGIONALITY_FILTER_ITEM', static::getSiteId()) === 'Y';
		}

        static public function checkUseRegionalityCatalogFilter() {
			return static::checkUseRegionalityFilter() && Solution::GetFrontParametrValue('REGIONALITY_FILTER_CATALOG', static::getSiteId()) === 'Y';
		}

		static public function getRegionIBlockID() {
			if (!isset(static::$iblockId)) {
				$siteId = static::getSiteId();

				if (
					isset(Cache::$arIBlocks[$siteId]['aspro_max_regionality']['aspro_max_regions'][0]) &&
					Cache::$arIBlocks[$siteId]['aspro_max_regionality']['aspro_max_regions'][0]
				) {
					static::$iblockId = Cache::$arIBlocks[$siteId]['aspro_max_regionality']['aspro_max_regions'][0];
				}
				else {
					return;
				}
			}

			return static::$iblockId;
		}

		static public function addSeoMarks($arMarks = []) {
			static::$arSeoMarks = array_merge(static::$arSeoMarks, $arMarks);
		}

		static public function replaceSeoMarks() {
			global $APPLICATION, $arSite, $arRegion;

			$page_title = $APPLICATION->GetTitle();
			$page_seo_title = ((strlen($APPLICATION->GetPageProperty('title')) > 1) ? $APPLICATION->GetPageProperty('title') : $page_title);

			if ($arRegion && $page_title)
			{
				foreach (static::$arSeoMarks as $mark => $field)
				{
					if (strpos($page_title, $mark) !== false)
						$page_title = str_replace($mark, $arRegion[$field], $page_title);
					if (strpos($page_seo_title, $mark) !== false)
						$page_seo_title = str_replace($mark, $arRegion[$field], $page_seo_title);
				}
				if (!Solution::IsMainPage())
				{
					$bShowSiteName = (\Bitrix\Main\Config\Option::get(ASPRO_MAX_MODULE_ID, "HIDE_SITE_NAME_TITLE", "N") == "N");
					$sPostfix = ($bShowSiteName ? ' - '.$arSite['SITE_NAME'] : '');

					$APPLICATION->SetPageProperty("title", $page_seo_title.$sPostfix);
					$APPLICATION->SetTitle($page_title);
				}
				else
				{
					if (!empty($page_seo_title))
						$APPLICATION->SetPageProperty("title", $page_seo_title);
					else
						$APPLICATION->SetPageProperty("title", $arSite['SITE_NAME']);

					if (!empty($page_title))
						$APPLICATION->SetTitle($page_title);
					else
						$APPLICATION->SetTitle($arSite['SITE_NAME']);
				}
			}
			return true;
		}

		static public function getRegions() {
			if (!isset(static::$arRegions)) {
				static::$arRegions = [];

				$iRegionIBlockID = static::getRegionIBlockID();

				if (
					$iRegionIBlockID &&
					static::checkUseRegionality()
				) {
					$cache = new CPHPCache();
					$cache_time = 86400;
					$cache_path = __CLASS__.'/'.__FUNCTION__;
					$cache_id = 'aspro_max_regions'.$iRegionIBlockID.(is_object($GLOBALS['USER']) ? $GLOBALS['USER']->GetGroups() : '');
					$cache_tag = Cache::GetIBlockCacheTag($iRegionIBlockID);

					if (
						\Bitrix\Main\Config\Option::get('main', 'component_cache_on', 'Y') == 'Y' &&
						$cache->InitCache($cache_time, $cache_id, $cache_path)
					) {
						$res = $cache->GetVars();
						static::$arRegions = $res['arRegions'];
					}
					else {
						// get all items
						$arFilter = ['ACTIVE' => 'Y', 'IBLOCK_ID' => $iRegionIBlockID];
						$arSelect = ['ID', 'NAME', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'SORT', 'DETAIL_TEXT'];
						$arMainProps = ['DEFAULT', 'DOMAINS', 'MAIN_DOMAIN', 'FAVORIT_LOCATION', 'PHONES', 'PRICES_LINK', 'LOCATION_LINK', 'STORES_LINK', 'REGION_NAME_DECLINE_RP', 'REGION_NAME_DECLINE_PP', 'REGION_NAME_DECLINE_TP', 'SORT_REGION_PRICE', 'ADDRESS', 'EMAIL'];
						foreach ($arMainProps as $code) {
							$arSelect[] = 'PROPERTY_'.$code;
						}

						// property code need start REGION_TAG_ for auto add for cache
						$arProps = [];
						$rsProperty = CIBlockProperty::GetList(
							[],
							array_merge(
								$arFilter,
								['CODE' => 'REGION_TAG_%']
							)
						);
						while ($arProp = $rsProperty->Fetch()) {
							$arSelect[] = 'PROPERTY_'.$arProp['CODE'];
						}

						// event for add to select in region getlist elements
						foreach (GetModuleEvents(ASPRO_MAX_MODULE_ID, 'OnAsproRegionalityAddSelectFieldsAndProps', true) as $arEvent) {
							ExecuteModuleEventEx($arEvent, [&$arSelect]);
						}

						//$arItems = Cache::CIBLockElement_GetList(array('SORT' => 'ASC', 'NAME' => 'ASC', 'CACHE' => array('TAG' => Cache::GetIBlockCacheTag($iRegionIBlockID), 'GROUP' => 'ID', 'CAN_MULTI_SECTION' => 'N')), $arFilter, false, false, $arSelect);

						$arItems = [];
						$dbRes = \CIBLockElement::GetList(
							[
								'SORT' => 'ASC',
								'NAME' => 'ASC',
							],
							$arFilter,
							false,
							false,
							[
								'ID',
								'NAME',
								'IBLOCK_ID',
								'IBLOCK_SECTION_ID',
								'SORT',
								'DETAIL_TEXT',
							]
						);

						while ($ob = $dbRes->GetNextElement()) {
							$arFields = $ob->GetFields();
							$arProps = $ob->GetProperties();

							$arItem = [];
							foreach ($arFields as $code => $value) {
								if (in_array($code, $arSelect)) {
									$arItem[$code] = $value;
								}
							}

							foreach ($arProps as $code => $arProperty) {
								if (in_array('PROPERTY_'.$code, $arSelect)) {
									if ($arProperty['USER_TYPE'] === 'SAsproMaxRegionPhone') {
										$arProperty['~VALUE'] = (array)$arProperty['~VALUE'];
										foreach ($arProperty['~VALUE'] as &$value) {
											if (!is_array($value)) {
												$value = strlen($value) ? $value : '[]';

												try {
													$value = \Bitrix\Main\Web\Json::decode($value);
												}
												catch(\Exception $e) {
													$value = [];
												}
											}
										}
										unset($value);
									}

									if($arProperty['VALUE']) {
										$arItem['PROPERTY_'.$code.'_VALUE'] = $arProperty['~VALUE'];
									}

									if (isset($arProperty['WITH_DESCRIPTION']) && $arProperty['WITH_DESCRIPTION'] == "Y") {
									    $arItem['PROPERTY_'.$code.'_DESCRIPTION'] = $arProperty['~DESCRIPTION'];
									}
								}
							}

							$arItems[$arItem['ID']] = $arItem;
						}

						// event for manipulation with region elements
						foreach (GetModuleEvents(ASPRO_MAX_MODULE_ID, 'OnAsproRegionalityGetElements', true) as $arEvent){
							ExecuteModuleEventEx($arEvent, [&$arItems]);
						}

						if (
							$arItems &&
							Loader::includeModule('catalog')
						) {
							foreach ($arItems as $key => $arItem) {
								if (!$arItem['PROPERTY_MAIN_DOMAIN_VALUE'] && $arItem['PROPERTY_DEFAULT_VALUE'] == 'Y') {
									$arItems[$key]['PROPERTY_MAIN_DOMAIN_VALUE'] = $_SERVER['HTTP_HOST'];
								}

								// domains props
								if (!is_array($arItem['PROPERTY_DOMAINS_VALUE'])) {
									$arItem['PROPERTY_DOMAINS_VALUE'] = (array)$arItem['PROPERTY_DOMAINS_VALUE'];
								}
								$arItems[$key]['LIST_DOMAINS'] = array_merge((array)$arItem['PROPERTY_MAIN_DOMAIN_VALUE'], $arItem['PROPERTY_DOMAINS_VALUE']);
								unset($arItems[$key]['PROPERTY_DOMAINS_VALUE']);
								unset($arItems[$key]['PROPERTY_DOMAINS_VALUE_ID']);

								// stores props
								if (!is_array($arItem['PROPERTY_STORES_LINK_VALUE'])) {
									$arItem['PROPERTY_STORES_LINK_VALUE'] = (array)$arItem['PROPERTY_STORES_LINK_VALUE'];
								}

								$arItems[$key]['LIST_STORES'] = [];
								if (isset($arItem['PROPERTY_STORES_LINK_VALUE'][0]) && !empty($arItem['PROPERTY_STORES_LINK_VALUE'][0])) {
									if (reset($arItem['PROPERTY_STORES_LINK_VALUE']) != 'component') {
										$arItems[$key]['LIST_STORES'] = $arItem['PROPERTY_STORES_LINK_VALUE'];
									}
								}

								unset($arItems[$key]['PROPERTY_STORES_LINK_VALUE']);
								unset($arItems[$key]['PROPERTY_STORES_LINK_VALUE_ID']);

								// location props
								$arItems[$key]['LOCATION'] = is_array($arItem['PROPERTY_LOCATION_LINK_VALUE']) ? $arItem['PROPERTY_LOCATION_LINK_VALUE'] : ($arItem['PROPERTY_LOCATION_LINK_VALUE'] ? [$arItem['PROPERTY_LOCATION_LINK_VALUE']] : []);
								unset($arItems[$key]['PROPERTY_LOCATION_LINK_VALUE']);
								unset($arItems[$key]['PROPERTY_LOCATION_LINK_VALUE_ID']);

								// prices props
								if (!is_array($arItem['PROPERTY_PRICES_LINK_VALUE'])) {
									$arItem['PROPERTY_PRICES_LINK_VALUE'] = (array)$arItem['PROPERTY_PRICES_LINK_VALUE'];
								}

								$arItems[$key]['LIST_PRICES'] = [];
								if ($arItem['PROPERTY_PRICES_LINK_VALUE']) {
									if (reset($arItem['PROPERTY_PRICES_LINK_VALUE']) != 'component') {
										$dbPriceType = CCatalogGroup::GetList(
											['SORT' => 'ASC'],
											['ID' => $arItem['PROPERTY_PRICES_LINK_VALUE']],
											false,
											false,
											[
												'ID',
												'NAME',
												'CAN_BUY',
											]
										);
										while ($arPriceType = $dbPriceType->Fetch()) {
											$arItems[$key]['LIST_PRICES'][$arPriceType['NAME']] = $arPriceType;
										}
									}
								}
								unset($arItems[$key]['PROPERTY_PRICES_LINK_VALUE']);
								unset($arItems[$key]['PROPERTY_PRICES_LINK_VALUE_ID']);

								// email props
								if (!is_array($arItem['PROPERTY_EMAIL_VALUE'])) {
									$arItems[$key]['PROPERTY_EMAIL_VALUE'] = (array)$arItem['PROPERTY_EMAIL_VALUE'];
								}

								// phones props
								if (!is_array($arItem['PROPERTY_PHONES_VALUE'])) {
									$arItem['PROPERTY_PHONES_VALUE'] = (array)$arItem['PROPERTY_PHONES_VALUE'];
								}


								$arItems[$key]['PHONES'] = $arItem['PROPERTY_PHONES_VALUE'] ?? [];
								unset($arItems[$key]['PROPERTY_PHONES_VALUE']);
								unset($arItems[$key]['PROPERTY_PHONES_VALUE_ID']);
							}

							static::$arRegions = $arItems;
							unset($arItems);

							$cache->StartDataCache($cache_time, $cache_id, $cache_path);

							global $CACHE_MANAGER;
							$CACHE_MANAGER->StartTagCache($cache_path);
							$CACHE_MANAGER->RegisterTag($cache_tag);
							$CACHE_MANAGER->EndTagCache();

							$cache->EndDataCache([
								'arRegions' => static::$arRegions
							]);
						}
					}
				}
			}

			return static::$arRegions;
		}

		static public function InitBots() {
			$bots = [
				'ia_archiver', 'Wget', 'WebAlta', 'MJ12bot', 'aport', 'alexa.com', 'Baiduspider', 'Speedy Spider', 'abot', 'Indy Library',
			];

			foreach ($bots as $bot) {
				if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false) {
					return $bot;
				}
			}

			return false;
		}

		static public function getIP() {
			$ip = empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'];

			return $ip;
		}

		static public function getCityByIP($ip) {
			$city = '';

			if ($ip) {
				if (!is_array($_SESSION['GEOIP'])) {
					// by bitrix api
					$arGeoData = Cache::GeoIp_GetGeoData($ip, LANGUAGE_ID);
					$_SESSION['GEOIP'] = $arGeoData;
				}

				$city = $_SESSION['GEOIP']['cityName'] ?? '';
			}

			return $city;
		}

		static public function getLocationByIP($ip) {
			$arLocation = [];

			if ($ip) {
				if (!is_array($_SESSION['GEOIP_LOCATION'])) {
					// by bitrix api
					$arLocation = Cache::SaleGeoIp_GetLocation($ip, LANGUAGE_ID);
					$_SESSION['GEOIP_LOCATION'] = $arLocation;
				}

				if ($_SESSION['GEOIP_LOCATION']) {
					$arLocation = $_SESSION['GEOIP_LOCATION'];
				}
			}

			return $arLocation;
		}

		static public function getRealRegionByIP() {
			if (!isset(static::$arRealRegion)) {
				$arRegion = [];

				$arRegions = static::getRegions();
				if ($arRegions) {
					// get region by custom event handler
					foreach (GetModuleEvents(ASPRO_MAX_MODULE_ID, 'OnAsproRegionalityGetRealRegionByIP', true) as $arEvent) {
						ExecuteModuleEventEx($arEvent, [$arRegions, &$arRegion]);
					}

					if (!$arRegion) {
						if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
							// get ip
							$ip = static::getIP();

							// get location
							$arLocation = static::getLocationByIP($ip);

							// search by location & parents
							if ($arLocation) {
								$arLocationsIDs = array_merge([$arLocation['ID']], array_column($arLocation['PARENTS'], 'ID'));
								foreach ($arRegions as $arItem) {
									if ($arItem['LOCATION']) {
										if (array_intersect($arLocationsIDs, $arItem['LOCATION'])) {
											$arRegion = $arItem;
											break;
										}
									}
								}
							}

							if (!$arRegion) {
								// get city
								$city = static::getCityByIP($ip);

								// search by city name
								if ($city) {
									foreach ($arRegions as $arItem) {
										if ($city === $arItem['NAME']) {
											$arRegion = $arItem;
											break;
										}
									}
								}
							}
						}
					}
				}

				static::$arRealRegion = $arRegion;
			}

			return static::$arRealRegion;
		}

		static public function getCurrentRegion() {
			if (!isset(static::$arCurrentRegion)) {
				$arRegion = [];

				if ($arRegions = static::getRegions()) {

					global $arTheme;
					if (!$arTheme) {
						$arTheme = Solution::GetFrontParametrsValues(static::getSiteId());
					}

					// get region by custom event handler
					foreach (GetModuleEvents(ASPRO_MAX_MODULE_ID, 'OnAsproRegionalityGetCurrentRegion', true) as $arEvent) {
						ExecuteModuleEventEx($arEvent, [$arTheme, $arRegions, &$arRegion]);
					}

					if (!$arRegion) {
						// search current region
						if ($arTheme['REGIONALITY_TYPE'] === 'ONE_DOMAIN') {
							// search by cookie value
							if (isset($_COOKIE['current_region']) && $_COOKIE['current_region']) {
								if (isset($arRegions[$_COOKIE['current_region']]) && $arRegions[$_COOKIE['current_region']]) {
									$arRegion = $arRegions[$_COOKIE['current_region']];
								}
							}
						}

						// search by domain name
						if (!$arRegion) {
							if ($arTheme['REGIONALITY_TYPE'] !== 'ONE_DOMAIN') {
								foreach ($arRegions as $arItem) {
									if (in_array($_SERVER['SERVER_NAME'], $arItem['LIST_DOMAINS']) || in_array($_SERVER['HTTP_HOST'], $arItem['LIST_DOMAINS'])) {
										$arRegion = $arItem;
										break;
									}
								}
							}
						}

						// region not finded, set default
						if (!$arRegion) {
							foreach ($arRegions as $arItem) {
								if ($arItem['PROPERTY_DEFAULT_VALUE'] === 'Y') {
									$arRegion = $arItem;
									break;
								}
							}
						}

						// region not finded, set first region
						if (!$arRegion) {
							$arRegion = reset($arRegions);
						}
					}
				}

				static::$arCurrentRegion = $arRegion;
			}

			return static::$arCurrentRegion;
		}

		static public function getCurrentLocation() {
			if (!isset(static::$arCurrentLocation)) {
				$arLocation = [];

				if (\Bitrix\Main\Loader::includeModule('sale')) {
					$arLocationIDs = [];

					// current region
					$arCurrentRegion = static::getCurrentRegion();

					if (
						$arCurrentRegion &&
						$arCurrentRegion['LOCATION']
					) {
						// main current region location id
						$arLocationIDs = [$arCurrentRegion['LOCATION'][0]];
					}

					// selected location id
					$selectedLocationId = 0;
					if (
						$arCurrentRegion &&
						$arCurrentRegion['LOCATION']
					) {
						if (isset($_COOKIE['current_location'])) {
							$selectedLocationId = intval(trim($_COOKIE['current_location']));

							if (
								$selectedLocationId > 0 &&
								$selectedLocationId != $arLocationIDs[0]
							) {
								array_unshift($arLocationIDs, $selectedLocationId);
							}
						}
					}

					if ($arLocationIDs) {
						foreach ($arLocationIDs as $locationId) {
							if ($locationId <= 0) {
								continue;
							}

							$arLocation = [];

							$arTmp = Cache::SaleLocation_GetList(
								['PARENTS.TYPE_ID' => 'desc'],
								[
									'=ID' => $locationId,
									'=NAME.LANGUAGE_ID' => LANGUAGE_ID,
									'=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
								],
								[
									'ID',
									'CODE',
									'CITY_NAME' => 'NAME.NAME',
									'TYPE_ID',
									'TYPE_CODE' => 'TYPE.CODE',
									'PARENTS.ID',
									'PARENTS.NAME',
									'PARENTS.TYPE.CODE',
								]
							);

							if ($arTmp) {
								foreach ($arTmp as $loc) {
									if (!$arLocation) {
										$arLocation = [
											'ID' => $loc['ID'],
											'CODE' => $loc['CODE'],
											'CITY_NAME' => $loc['CITY_NAME'],
											'TYPE_ID' => $loc['TYPE_ID'],
											'TYPE_CODE' => $loc['TYPE_CODE'],
											'PARENTS' => [],
										];
									}

									if (
										$loc['SALE_LOCATION_LOCATION_PARENTS_ID'] &&
										$loc['SALE_LOCATION_LOCATION_PARENTS_NAME_NAME'] &&
										!isset($arLocation['PARENTS'][$loc['SALE_LOCATION_LOCATION_PARENTS_ID']]) &&
										$loc['SALE_LOCATION_LOCATION_PARENTS_ID'] != $loc['ID']
									) {
										$arLocation['PARENTS'][$loc['SALE_LOCATION_LOCATION_PARENTS_ID']] = [
											'ID' => $loc['SALE_LOCATION_LOCATION_PARENTS_ID'],
											'NAME' => $loc['SALE_LOCATION_LOCATION_PARENTS_NAME_NAME'],
											'TYPE_CODE' => $loc['SALE_LOCATION_LOCATION_PARENTS_TYPE_CODE'],
										];
									}
								}
							}

							if ($arLocation) {
								if ($arLocation['ID'] == $selectedLocationId) {
									// $arLocation is selected location

									// collect parent locations
									$arSelectedLocationParentsIDs = array_merge([$arLocation['ID']], array_column($arLocation['PARENTS'], 'ID'));

									// selected location is in current region, return it!
									if (array_intersect($arSelectedLocationParentsIDs, $arCurrentRegion['LOCATION'])) {
										break;
									}
									else {
										// remove not correct cookie, it maybe not works ))
										setcookie('current_location', '', time() - 1, '/');
									}
								}
								else {
									// $arLocation is real location by IP or main location of current region, return it!
									break;
								}
							}
						}
					}
				}

				static::$arCurrentLocation = $arLocation;
			}

			return static::$arCurrentLocation;
		}

        static public function getFilePath(string $filePath): string
        {
            $arRegion = static::getCurrentRegion();

            if (empty($arRegion)) {
                return $filePath;
            }

            $pathInfo = pathinfo($filePath);
            $pathAndName = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'];
            $extension = '.' . $pathInfo['extension'];

            foreach (['CODE', 'ID'] as $key) {
                if (!empty($arRegion[$key])) {
                    $newPath = "{$pathAndName}_{$arRegion[$key]}{$extension}";
                    if (file_exists($newPath)) {
                        return $newPath;
                    }
                }
            }

            return $filePath;
        }

        public static function mergeSmartPreFilterWithRegionFilter($prefilter)
		{
			$prefilter = (array)$prefilter;

			if (
				static::checkUseRegionalityCatalogFilter() &&
				($arRegion = static::getCurrentRegion())
			) {
                $prefilter['PROPERTY_LINK_REGION'] = $arRegion['ID'];
			}

			return $prefilter;
		}
	}
}

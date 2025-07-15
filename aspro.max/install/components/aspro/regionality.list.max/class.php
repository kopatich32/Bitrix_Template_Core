<?
namespace Aspro\Max\Components;

use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Config\Option,
    Bitrix\Main\SystemException,
    CMax as Solution,
    CMaxRegionality as Regionality,
	CMaxCache as Cache;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

class RegionalityList extends \CBitrixComponent {
	const CNT_MAIN_CITIES_IN_PAGE = 20;

	static public $lastResult = [];

	/**
	 * return list of regions with correct subdomain urls
	 */
	static public function getRegionsWithUrl($url, $regionalityType) :array {
		$scheme = (\Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? 'https://' : 'http://');
		$arRegions = Regionality::getRegions();

		foreach ($arRegions as $regionId => $arRegion) {
			$regionUrl = $url;

			if (
				$arRegion['PROPERTY_MAIN_DOMAIN_VALUE'] &&
				$regionalityType == 'SUBDOMAIN'
			) {
                $regionUrl = $scheme.$arRegion['PROPERTY_MAIN_DOMAIN_VALUE'].$url;
                $regionPrefixUrl = $scheme.$arRegion['PROPERTY_MAIN_DOMAIN_VALUE'];
			}
            $arRegions[$regionId]['URL'] = $regionUrl;
            $arRegions[$regionId]['PREFIX_URL'] = $regionPrefixUrl;
		}
		unset($arRegion);

		return $arRegions;
	}

	/**
	 * return main cities of all favorite regions
	 */
	static public function getFavoriteCities($url, $regionalityType, $lang = LANGUAGE_ID) :array {
		$arCities = [];

		$arRegions = static::getRegionsWithUrl($url, $regionalityType);
		if ($arRegions) {
			$lang = $lang ?: LANGUAGE_ID;

			$arIblockCities = $arLocationsCities = $arLocationsIds = $arMainLocationsIds = $arLocationsRegionsIds = $arLocationsOrder = [];

			foreach ($arRegions as $arRegion) {
				$mainLocationId = false;

				if (
					is_array($arRegion['LOCATION']) &&
					$arRegion['LOCATION']
				) {
					$mainLocationId = $arRegion['LOCATION'][0];

					foreach ($arRegion['LOCATION'] as $locationId) {
						// $arRegions already ordered by SORT=>ASC,NAME=>ASC
						if (!isset($arLocationsRegionsIds[$locationId])) {
							$arLocationsRegionsIds[$locationId] = $arRegion['ID'];
							$arLocationsOrder[$locationId] = count($arLocationsOrder);
						}
					}
				}

				if ($arRegion['PROPERTY_FAVORIT_LOCATION_VALUE'] == 'Y') {
					if ($mainLocationId) {
						$arMainLocationsIds[] = $mainLocationId;
						$arLocationsIds = array_merge($arLocationsIds, $arRegion['LOCATION']);
					}
					else {
						$arIblockCities[$arRegion['ID']] = [
							'ID' => $arRegion['ID'],
							// default sort of iblock elements is 100, but default sort of locations is 100
							'SORT' => $arRegion['SORT'] !== 500 ? $arRegion['SORT'] : 100,
							'URL' => $arRegion['URL'],
							'NAME' => trim($arRegion['NAME']),
							'LOCATION_ID' => false,
						];
					}
				}
			}

			// get locations (only cities)
			if ($arLocationsIds) {
				$arLocationsIds = array_unique($arLocationsIds);

				$arTmpLocations = Cache::SaleLocation_GetList(
					[],
					[
						'=ID' => $arMainLocationsIds,
						'=PARENTS.ID' => $arLocationsIds,
						'=NAME.LANGUAGE_ID' => $lang,
						'TYPE.CODE' => 'CITY',
					],
					[
						'ID',
						'CITY_NAME' => 'NAME.NAME',
						'PARENTS.ID',
					]
				);

				if ($arTmpLocations) {
					$arLocations = [];
					foreach ($arTmpLocations as $arLocation) {
						$bInRegionsLocations = in_array($arLocation['ID'], $arLocationsIds);

						if (
							$arLocation['SALE_LOCATION_LOCATION_PARENTS_TYPE_CODE'] !== 'CITY' ||
							$bInRegionsLocations
						) {
							$parentLocationId = $arLocation['SALE_LOCATION_LOCATION_PARENTS_ID'];
							$arLocation['ORDER'] = $parentLocationId ? $arLocationsOrder[$parentLocationId] : false;
							if (isset($arLocations[$arLocation['ID']])) {
								if (
									($arLocations[$arLocation['ID']]['ORDER'] > $arLocation['ORDER']) ||
									($arLocations[$arLocation['ID']]['ORDER'] === false && $arLocation['ORDER'] !== false)
								) {
									$arLocations[$arLocation['ID']] = $arLocation;
								}
							}
							else {
								$arLocations[$arLocation['ID']] = $arLocation;
							}
						}
					}

					if ($arLocations) {
						foreach ($arLocations as $arLocation) {
							$parentLocationId = $arLocation['SALE_LOCATION_LOCATION_PARENTS_ID'];
							$regionId = $arLocationsRegionsIds[$parentLocationId];
							if ($regionId) {
								$arLocationsCities[] = [
									'ID' => $regionId,
									'SORT' => $arRegions[$regionId]['SORT'],
									'URL' => $arRegions[$regionId]['URL'],
									'NAME' => $arLocation['CITY_NAME'],
									'LOCATION_ID' => $arLocation['ID'],
								];
							}
						}
					}
				}
			}

			$arCities = array_merge($arLocationsCities, $arIblockCities);
			if ($arCities) {
				\Bitrix\Main\Type\Collection::sortByColumn(
					$arCities,
					[
						'SORT' => [
							SORT_NUMERIC,
							SORT_ASC,
						],
						'NAME' => SORT_ASC,
					],
					'',
					null,
					true
				);
			}
		}

		return array_values($arCities);
	}

	/**
	 * return main cities of all regions locations
	 */
	static public function getMainCities($url, $regionalityType, $lang = LANGUAGE_ID, $lastId = 0) :array {
		$arCities = $pageRegionsIds = [];

		$arRegions = static::getRegionsWithUrl($url, $regionalityType);
		if ($arRegions) {
			$lang = $lang ?: LANGUAGE_ID;

			$arCurrentRegion = Regionality::getCurrentRegion();
			$arCurrentLocation = Regionality::getCurrentLocation();
			$arRegionsIds = array_keys($arRegions); // before sort
			$arSortedRegionsIds = $arRegionsIds; // after sort

			if ($arCurrentRegion) {
				usort(
					$arSortedRegionsIds,
					function($a, $b) use($arCurrentRegion, $arRegionsIds) {
						if ($a == $arCurrentRegion['ID']) {
							return -1;
						}
						elseif ($b == $arCurrentRegion['ID']) {
							return 1;
						}

						return array_search($a, $arRegionsIds) <=> array_search($b, $arRegionsIds);
					}
				);
			}

			$cntRegions = count($arRegions);

			$maxInPage = static::CNT_MAIN_CITIES_IN_PAGE;
			$bFirstPage = !$lastId;
			if (
				$bFirstPage &&
				$cntRegions > static::CNT_MAIN_CITIES_IN_PAGE &&
				static::CNT_MAIN_CITIES_IN_PAGE > 1
			) {
				$maxInPage = static::CNT_MAIN_CITIES_IN_PAGE - 1;
			}

			// collect regions ids for page
			$offset = $bFirstPage ? 0 : array_search($lastId, $arSortedRegionsIds) + 1;
			$pageRegionsIds = array_slice($arSortedRegionsIds, $offset, $maxInPage);

			if ($pageRegionsIds) {
				// collect all main locations of all regions
				$arIblockCities = $arLocationsCities = $arLocationsIds = $arMainLocationsIds = $arLocationsRegionsIds = $arLocationsOrder = $arRegionsSectionsIds = [];

				foreach ($arRegions as $arRegion) {
					$mainLocationId = false;

					if (
						is_array($arRegion['LOCATION']) &&
						$arRegion['LOCATION']
					) {
						$mainLocationId = $arRegion['LOCATION'][0];

						foreach ($arRegion['LOCATION'] as $locationId) {
							// $arRegions already ordered by SORT=>ASC,NAME=>ASC
							if (!isset($arLocationsRegionsIds[$locationId])) {
								$arLocationsRegionsIds[$locationId] = $arRegion['ID'];
								$arLocationsOrder[$locationId] = count($arLocationsOrder);
							}
						}
					}

					if (in_array($arRegion['ID'], $pageRegionsIds)) {
						if ($mainLocationId) {
							$arMainLocationsIds[] = $mainLocationId;
							$arLocationsIds = array_merge($arLocationsIds, $arRegion['LOCATION']);
						}
						else {
							if ($arRegion['IBLOCK_SECTION_ID']) {
								$arRegionsSectionsIds[$arRegion['ID']] = $arRegion['IBLOCK_SECTION_ID'];
							}

							$arIblockCities[$arRegion['ID']] = [
								'ID' => $arRegion['ID'],
								// default sort of iblock elements is 100, but default sort of locations is 100
								'SORT' => $arRegion['SORT'] !== 500 ? $arRegion['SORT'] : 100,
								'ORDER' => array_search($arRegion['ID'], $pageRegionsIds),
								'URL' => $arRegion['URL'],
								'NAME' => trim($arRegion['NAME']),
								'CURRENT' => $arRegion['ID'] == $arCurrentRegion['ID'],
								'LOCATION_ID' => false,
							];
						}
					}
				}

				if ($arRegionsSectionsIds) {
					$arSectionsParents = static::getSectionsParents($arRegionsSectionsIds, Regionality::getRegionIBlockID());
					$arSectionsPathes = static::getSectionsParentsPathes($arSectionsParents);

					foreach ($arIblockCities as &$arIblockCity) {
						$sectionId = $arRegionsSectionsIds[$arIblockCity['ID']];
						// $arIblockCity['PARENTS_PATHES'] = $arSectionsPathes[$sectionId];
						$arIblockCity['PATH'] = implode(', ', $arSectionsPathes[$sectionId]);
					}
					unset($arIblockCity);
				}

				if ($arLocationsIds) {
					$arLocationsIds = array_unique($arLocationsIds);

					// get main locations (only cities) of all regions
					$arTmpLocations = Cache::SaleLocation_GetList(
						[],
						[
							'=ID' => $arMainLocationsIds,
							'=PARENTS.ID' => $arLocationsIds,
							'=NAME.LANGUAGE_ID' => $lang,
							'TYPE.CODE' => 'CITY',
						],
						[
							'ID',
							'CITY_NAME' => 'NAME.NAME',
							'PARENTS.ID',
						]
					);

					if ($arTmpLocations) {
						$arLocations = [];
						foreach ($arTmpLocations as $arLocation) {
							$bInRegionsLocations = in_array($arLocation['ID'], $arLocationsIds);

							if (
								$arLocation['SALE_LOCATION_LOCATION_PARENTS_TYPE_CODE'] !== 'CITY' ||
								$bInRegionsLocations
							) {
								$parentLocationId = $arLocation['SALE_LOCATION_LOCATION_PARENTS_ID'];
								$arLocation['ORDER'] = $parentLocationId ? $arLocationsOrder[$parentLocationId] : false;
								if (isset($arLocations[$arLocation['ID']])) {
									if (
										($arLocations[$arLocation['ID']]['ORDER'] > $arLocation['ORDER']) ||
										($arLocations[$arLocation['ID']]['ORDER'] === false && $arLocation['ORDER'] !== false)
									) {
										$arLocations[$arLocation['ID']] = $arLocation;
									}
								}
								else {
									$arLocations[$arLocation['ID']] = $arLocation;
								}
							}
						}

						if ($arLocations) {
							$arCitiesLocationsIds = [];

							foreach ($arLocations as $arLocation) {
								$parentLocationId = $arLocation['SALE_LOCATION_LOCATION_PARENTS_ID'];
								$regionId = $arLocationsRegionsIds[$parentLocationId];
								if ($regionId) {
									$arCitiesLocationsIds[] = $arLocation['ID'];
									$arLocationsCities[$arLocation['ID']] = [
										'ID' => $regionId,
										'SORT' => $arRegions[$regionId]['SORT'],
										'ORDER' => array_search($regionId, $pageRegionsIds),
										'URL' => $arRegions[$regionId]['URL'],
										'NAME' => $arLocation['CITY_NAME'],
										'CURRENT' => $arCurrentLocation['ID'] == $arLocation['ID'],
										'LOCATION_ID' => $arLocation['ID'],
									];
								}
							}

							// get cities pathes
							if ($arCitiesLocationsIds) {
								$arLocationsParents = [];

								$res = \Bitrix\Sale\Location\LocationTable::getList([
									'order' => [
										'PARENTS.TYPE_ID' => 'desc',
									],
									'filter' => [
										'=ID' => $arCitiesLocationsIds,
										'=PARENTS.NAME.LANGUAGE_ID' => $lang,
									],
									'select' => [
										'ID',
										'PARENTS.ID',
										'PARENTS.NAME',
									],
								]);
								while ($loc = $res->fetch()) {
									if (!isset($arLocationsParents[$loc['ID']])) {
										$arLocationsParents[$loc['ID']] = [];
									}

									if (
										$loc['SALE_LOCATION_LOCATION_PARENTS_ID'] &&
										$loc['SALE_LOCATION_LOCATION_PARENTS_NAME_NAME'] &&
										!$arLocationsParents[$loc['ID']][$loc['SALE_LOCATION_LOCATION_PARENTS_ID']] &&
										$loc['SALE_LOCATION_LOCATION_PARENTS_ID'] != $loc['ID']
									) {
										$arLocationsParents[$loc['ID']][$loc['SALE_LOCATION_LOCATION_PARENTS_ID']] = [
											'ID' => $loc['SALE_LOCATION_LOCATION_PARENTS_ID'],
											'NAME' => $loc['SALE_LOCATION_LOCATION_PARENTS_NAME_NAME'],
										];
									}
								}

								if ($arLocationsParents) {
									foreach ($arLocationsParents as $locationId => $arLocationParents) {
										// $arLocationsCities[$locationId]['PARENTS_PATHES'] = array_column($arLocationParents, 'NAME');
										$arLocationsCities[$locationId]['PATH'] = implode(', ', array_column($arLocationParents, 'NAME'));
									}
								}
							}
						}
					}
				}
			}

			$arCities = array_merge(array_values($arLocationsCities), $arIblockCities);
			if ($arCities) {
				\Bitrix\Main\Type\Collection::sortByColumn(
					$arCities,
					[
						'ORDER' => SORT_ASC,
					],
					'',
					null,
					true
				);
			}
		}

		$lastId = $pageRegionsIds ? end($pageRegionsIds) : 0;

		return [
			'cities' => array_values($arCities),
			'lastId' => $pageRegionsIds ? end($pageRegionsIds) : false,
			'more' => ($arRegions && $arCities && $lastId) ? (array_search($lastId, $arSortedRegionsIds) + 1) < $cntRegions : false,
		];
	}

	/**
	 * return regions & cities of all regions locations
	 */
	static public function getLevelsAndCities($url, $regionalityType, $lang = LANGUAGE_ID, $okrugId = 0, $regionId = 0) :array {
		$arLevel1 = $arLevel2 = $arCities = [];

		$arRegions = static::getRegionsWithUrl($url, $regionalityType);
		if ($arRegions) {
			$lang = $lang ?: LANGUAGE_ID;

			$bFirstPage = !strlen($okrugId) && !strlen($regionId);

			$arCurrentLocationIds = $arCurrentSectionIds = [];
			$currentLocationOkrugId = $currentLocationRegionId = 0;
			$currentSectionOkrugId = $currentSectionRegionId = 0;

			$arCurrentRegion = Regionality::getCurrentRegion();
			$arCurrentLocation = Regionality::getCurrentLocation();

			// collect all locations of all regions
			$arIblockCities = $arLocationsIds = $arLocationsRegionsIds = $arLocationsOrder = [];
			foreach ($arRegions as $arRegion) {
				$mainLocationId = false;

				if (
					is_array($arRegion['LOCATION']) &&
					$arRegion['LOCATION']
				) {
					$mainLocationId = $arRegion['LOCATION'][0];

					foreach ($arRegion['LOCATION'] as $locationId) {
						// $arRegions already ordered by SORT=>ASC,NAME=>ASC
						if (!isset($arLocationsRegionsIds[$locationId])) {
							$arLocationsRegionsIds[$locationId] = $arRegion['ID'];
							$arLocationsOrder[$locationId] = count($arLocationsOrder);
						}
					}
				}

				if ($mainLocationId) {
					$arLocationsIds = array_merge($arLocationsIds, $arRegion['LOCATION']);
				}
				else {
					$arIblockCities[] = [
						'ID' => $arRegion['ID'],
						'SECTION_ID' => $arRegion['IBLOCK_SECTION_ID'],
						// default sort of iblock elements is 100, but default sort of locations is 100
						'SORT' => $arRegion['SORT'] !== 500 ? $arRegion['SORT'] : 100,
						'URL' => $arRegion['URL'],
						'NAME' => trim($arRegion['NAME']),
						'CURRENT' => $arRegion['ID'] == $arCurrentRegion['ID'],
						'LOCATION_ID' => false,
					];
				}
			}

			$arSectionsParents = static::getSectionsParents(array_column($arIblockCities, 'SECTION_ID'), Regionality::getRegionIBlockID());

			if ($bFirstPage) {
				if ($arCurrentRegion) {
					if (
						$arSectionsParents &&
						$arSectionsParents[$arCurrentRegion['IBLOCK_SECTION_ID']]
					) {
						$arParentsIDs = static::getSectionsParentsIds([$arSectionsParents[$arCurrentRegion['IBLOCK_SECTION_ID']]], Regionality::getRegionIBlockID());
						if ($arParentsIDs) {
							$arCurrentSectionIds = $arParentsIDs[0];

							if ($arCurrentSectionIds[0]) {
								$currentSectionRegionId = $arCurrentSectionIds[0];
							}

							if ($arCurrentSectionIds[1]) {
								$currentSectionOkrugId = $arCurrentSectionIds[1];
							}
						}
					}
				}

				if ($arCurrentLocation) {
					$arCurrentLocationIds = [$arCurrentLocation['ID']];

					if ($arCurrentLocation['PARENTS']) {
						$arCurrentLocationIds = array_merge(
							$arCurrentLocationIds,
							array_keys($arCurrentLocation['PARENTS'])
						);

						if ($arCurrentLocation['TYPE_CODE'] === 'COUNTRY_DISTRICT') {
							$currentLocationOkrugId = $arCurrentLocation['ID'];
						}
						elseif ($arCurrentLocation['TYPE_CODE'] === 'REGION') {
							$currentLocationRegionId = $arCurrentLocation['ID'];
						}

						foreach ($arCurrentLocation['PARENTS'] as $arParent) {
							if ($arParent['TYPE_CODE'] === 'COUNTRY_DISTRICT') {
								$currentLocationOkrugId = $arParent['ID'];
							}
							elseif ($arParent['TYPE_CODE'] === 'REGION') {
								$currentLocationRegionId = $arParent['ID'];
							}
						}
					}
				}
			}
			else {
				if (strlen($okrugId)) {
					[$currentLocationOkrugId, $currentSectionOkrugId] = explode('s', $okrugId);

					if ($currentLocationOkrugId) {
						$arCurrentLocationIds[] = $currentLocationOkrugId;
					}

					if ($currentSectionOkrugId) {
						$arCurrentSectionIds[] = $currentSectionOkrugId;
					}
				}

				if (strlen($regionId)) {
					[$currentLocationRegionId, $currentSectionRegionId] = explode('s', $regionId);

					if ($currentLocationRegionId) {
						$arCurrentLocationIds[] = [$currentLocationRegionId];
					}

					if ($currentSectionRegionId) {
						$arCurrentSectionIds[] = [$currentSectionRegionId];
					}
				}
			}

			if (
				$arIblockCities ||
				$arLocationsIds
			) {
				$arLocationsIds = array_unique($arLocationsIds);

				if ($bFirstPage) {
					// COLLECT LEVEL1 - COUNTRY_DISTRICT

					if ($arLocationsIds) {
						$arParentLocations = $arChildrenLocations = [];

						// get parents locations (only COUNTRY_DISTRICT) of all regions
						$arParentLocations = Cache::SaleLocation_GetList(
							[
								'PARENTS.SORT' => 'ASC',
							],
							[
								'=ID' => $arLocationsIds, // if there are REGIONS or CITIES in $arLocationsIds
								'!PARENTS.ID' => $arLocationsIds,
								'=PARENTS.NAME.LANGUAGE_ID' => $lang,
								'PARENTS.TYPE.CODE' => 'COUNTRY_DISTRICT',
							],
							[
								'ID',
								'PARENTS.ID',
								'PARENTS.NAME',
								'PARENTS.TYPE.CODE',
								'PARENTS.SORT',
								'PARENTS.PARENT_ID',
							]
						);

						if ($arParentLocations) {
							foreach ($arParentLocations as $arParentLocation) {
								if (!isset($arLevel1[$arParentLocation['SALE_LOCATION_LOCATION_PARENTS_ID']])) {
									$arLevel1[$arParentLocation['SALE_LOCATION_LOCATION_PARENTS_ID']] = [
										'ID' => $arParentLocation['SALE_LOCATION_LOCATION_PARENTS_ID'],
										'NAME' => $arParentLocation['SALE_LOCATION_LOCATION_PARENTS_NAME_NAME'],
										'SORT' => $arParentLocation['SALE_LOCATION_LOCATION_PARENTS_SORT'],
										'CURRENT' => in_array($arParentLocation['SALE_LOCATION_LOCATION_PARENTS_ID'], $arCurrentLocationIds),
										'PARENT_ID' => $arParentLocation['SALE_LOCATION_LOCATION_PARENTS_PARENT_ID'],
									];
								}
							}
						}

						// get children locations (only COUNTRY_DISTRICT) of all regions
						$arChildrenLocations = Cache::SaleLocation_GetList(
							[
								'SORT' => 'ASC',
							],
							[
								'=PARENTS.ID' => $arLocationsIds, // if there are COUNTRIES or DISTRICTS in $arLocationsIds
								'=NAME.LANGUAGE_ID' => $lang,
								'TYPE.CODE' => 'COUNTRY_DISTRICT',
							],
							[
								'ID',
								'SORT',
								'NAME',
								'TYPE_CODE' => 'TYPE.CODE',
								'PARENT_ID',
								'PARENTS.ID',
							]
						);

						if ($arChildrenLocations) {
							foreach ($arChildrenLocations as $arChildLocation) {
								if (!isset($arLevel1[$arChildLocation['ID']])) {
									$arLevel1[$arChildLocation['ID']] = [
										'ID' => $arChildLocation['ID'],
										'NAME' => $arChildLocation['SALE_LOCATION_LOCATION_NAME_NAME'],
										'SORT' => $arChildLocation['SORT'],
										'PARENT_ID' => $arChildLocation['PARENT_ID'],
										'CURRENT' => in_array($arChildLocation['ID'], $arCurrentLocationIds),
									];
								}
							}
						}
					}

					// get with sections depth level == 1
					$arLevel1Sections = static::getLevelSections($arSectionsParents, 1);

					if ($arLevel1Sections) {
						foreach ($arLevel1Sections as $arSection) {
							foreach ($arLevel1 as &$arLocation) {
								if ($arSection['NAME'] == $arLocation['NAME']) {
									$arLocation['SECTION_ID'] = $arSection['ID'];
									$arLocation['SORT'] = $arSection['SORT'];
									$arLocation['CURRENT'] = $arLocation['CURRENT'] || in_array($arSection['ID'], $arCurrentSectionIds);

									if ($arLocation['CURRENT']) {
										$currentLocationOkrugId = $arLocation['ID'];
										$currentSectionOkrugId = $arSection['ID'];
									}

									continue 2;
								}
							}
							unset($arLocation);

							$bCurrent = in_array($arSection['ID'], $arCurrentSectionIds);
							if ($bCurrent) {
								$currentSectionOkrugId = $arSection['ID'];
							}

							$arLevel1['s'.$arSection['ID']] = [
								'SECTION_ID' => $arSection['ID'],
								'NAME' => $arSection['NAME'],
								'SORT' => $arSection['SORT'],
								'PARENT_ID' => 0,
								'CURRENT' => $bCurrent,
							];
						}
						unset($arLocation); // !it`s need after continue 2
					}

					if ($arLevel1) {
						\Bitrix\Main\Type\Collection::sortByColumn(
							$arLevel1,
							[
								'SORT' => [
									SORT_NUMERIC,
									SORT_ASC,
								],
								'NAME' => SORT_ASC,
							],
							'',
							null,
							true
						);

						if (
							!$currentLocationOkrugId &&
							!$currentSectionOkrugId
						) {
							$key = reset(array_keys($arLevel1));
							$arLevel1[$key]['CURRENT'] = true;

							if (isset($arLevel1[$key]['SECTION_ID'])) {
								$currentSectionOkrugId = $arLevel1[$key]['SECTION_ID'];
							}

							if (isset($arLevel1[$key]['ID'])) {
								$currentLocationOkrugId = $arLevel1[$key]['ID'];
							}
						}
					}
				}

				$arCitiesWithoutRegion = [];
				if ($currentLocationOkrugId) {
					if ($arLocationsIds) {
						// COLLECT CITIES WITHOUT REGION.
						// THEY WILL VISIBLE IN LEVEL2
						$arChildrenFilter = [
							'=PARENTS.ID' => $arLocationsIds, // if there are COUNTRIES or DISTRICTS or REGIONS or CITIES in $arLocationsIds
							'TYPE.CODE' => 'CITY',
							'=NAME.LANGUAGE_ID' => $lang,
							'=PARENT_ID' => $currentLocationOkrugId,
							'REGION_ID' => false,
						];

						$arChildrenLocations = Cache::SaleLocation_GetList(
							[
								'SORT' => 'ASC',
							],
							$arChildrenFilter,
							[
								'ID',
								'SORT',
								'NAME',
								'PARENT_ID',
								'PARENTS.ID',
							]
						);

						if ($arChildrenLocations) {
							$arLocations = [];
							foreach ($arChildrenLocations as $arChildLocation) {
								$parentLocationId = $arChildLocation['SALE_LOCATION_LOCATION_PARENTS_ID'];
								$arChildLocation['ORDER'] = $parentLocationId ? $arLocationsOrder[$parentLocationId] : false;

								if (isset($arLocations[$arChildLocation['ID']])) {
									if (
										($arLocations[$arChildLocation['ID']]['ORDER'] > $arChildLocation['ORDER']) ||
										($arLocations[$arChildLocation['ID']]['ORDER'] === false && $arChildLocation['ORDER'] !== false)
									) {
										$arLocations[$arChildLocation['ID']] = $arChildLocation;
									}
								}
								else {
									$arLocations[$arChildLocation['ID']] = $arChildLocation;
								}
							}

							if ($arLocations) {
								foreach ($arLocations as $arLocation) {
									$parentLocationId = $arLocation['SALE_LOCATION_LOCATION_PARENTS_ID'];
									$_regionId = $arLocationsRegionsIds[$parentLocationId];
									if ($_regionId) {
										if (!isset($arCitiesWithoutRegion[$arChildLocation['ID']])) {
											$arCitiesWithoutRegion[$arChildLocation['ID']] = [
												'ID' => $_regionId,
												'URL' => $arRegions[$_regionId]['URL'],
												'NAME' => $arChildLocation['SALE_LOCATION_LOCATION_NAME_NAME'],
												'SORT' => 0, // top of level2
												'PARENT_ID' => $arChildLocation['PARENT_ID'],
												'CURRENT' => $arCurrentLocation['ID'] == $arLocation['ID'],
												'LOCATION_ID' => $arChildLocation['ID'],
											];
										}
									}
								}
							}
						}
					}
				}

				if (!strlen($regionId)) {
					// now is first page or some district selected
					// COLLECT LEVEL2 - REGION
					if ($arLocationsIds) {
						if (
							!$currentSectionOkrugId ||
							$currentLocationOkrugId
						) {
							$arParentLocations = $arChildrenLocations = [];

							// get parents locations (only REGION) of all regions or of current COUNTRY_DISTRICT
							$arParentsFilter = [
								'=ID' => $arLocationsIds, // if there are CITIES in $arLocationsIds
								'!PARENTS.ID' => $arLocationsIds,
								'=PARENTS.NAME.LANGUAGE_ID' => $lang,
								'PARENTS.TYPE.CODE' => 'REGION',
							];
							if ($currentLocationOkrugId) {
								$arParentsFilter['=PARENTS.PARENT_ID'] = $currentLocationOkrugId;
							}

							$arParentLocations = Cache::SaleLocation_GetList(
								[
									'PARENTS.SORT' => 'ASC',
								],
								$arParentsFilter,
								[
									'ID',
									'PARENTS.ID',
									'PARENTS.NAME',
									'PARENTS.TYPE.CODE',
									'PARENTS.SORT',
									'PARENTS.PARENT_ID',
								]
							);

							if ($arParentLocations) {
								foreach ($arParentLocations as $arParentLocation) {
									if (!isset($arLevel2[$arParentLocation['SALE_LOCATION_LOCATION_PARENTS_ID']])) {
										$arLevel2[$arParentLocation['SALE_LOCATION_LOCATION_PARENTS_ID']] = [
											'ID' => $arParentLocation['SALE_LOCATION_LOCATION_PARENTS_ID'],
											'NAME' => $arParentLocation['SALE_LOCATION_LOCATION_PARENTS_NAME_NAME'],
											'SORT' => $arParentLocation['SALE_LOCATION_LOCATION_PARENTS_SORT'],
											'CURRENT' => in_array($arParentLocation['SALE_LOCATION_LOCATION_PARENTS_ID'], $arCurrentLocationIds),
											'PARENT_ID' => $arParentLocation['SALE_LOCATION_LOCATION_PARENTS_PARENT_ID'],
										];
									}
								}
							}

							// get children locations (only REGION) of all regions or of current COUNTRY_DISTRICT
							$arChildrenFilter = [
								'=PARENTS.ID' => $arLocationsIds, // if there are COUNTRIES or DISTRICTS or REGIONS in $arLocationsIds
								'TYPE.CODE' => 'REGION',
								'=NAME.LANGUAGE_ID' => $lang,
							];
							if ($currentLocationOkrugId) {
								$arChildrenFilter['=PARENT_ID'] = $currentLocationOkrugId;
							}

							$arChildrenLocations = Cache::SaleLocation_GetList(
								[
									'SORT' => 'ASC',
								],
								$arChildrenFilter,
								[
									'ID',
									'SORT',
									'NAME',
									'TYPE_CODE' => 'TYPE.CODE',
									'PARENT_ID',
									'PARENTS.ID',
								]
							);

							if ($arChildrenLocations) {
								foreach ($arChildrenLocations as $arChildLocation) {
									if (!isset($arLevel2[$arChildLocation['ID']])) {
										$arLevel2[$arChildLocation['ID']] = [
											'ID' => $arChildLocation['ID'],
											'NAME' => $arChildLocation['SALE_LOCATION_LOCATION_NAME_NAME'],
											'SORT' => $arChildLocation['SORT'],
											'PARENT_ID' => $arChildLocation['PARENT_ID'],
											'CURRENT' => in_array($arChildLocation['ID'], $arCurrentLocationIds),
										];
									}
								}
							}

							if ($arCitiesWithoutRegion) {
								foreach ($arCitiesWithoutRegion as $arLocation) {
									if (!isset($arLevel2[$arLocation['LOCATION_ID']])) {
										$arLevel2[$arLocation['LOCATION_ID']] = $arLocation;
									}
								}
							}
						}
					}

					// get with sections depth level == 2
					$arLevel2Sections = static::getLevelSections($arSectionsParents, 2);
					if ($arLevel2Sections) {
						foreach ($arLevel2Sections as $arSection) {
							if (
								$currentSectionOkrugId &&
								$arSection['IBLOCK_SECTION_ID'] == $currentSectionOkrugId
							) {
								foreach ($arLevel2 as &$arLocation) {
									if ($arSection['NAME'] == $arLocation['NAME']) {
										$arLocation['SECTION_ID'] = $arSection['ID'];
										$arLocation['SORT'] = $arSection['SORT'];
										$arLocation['CURRENT'] = $arLocation['CURRENT'] || in_array($arSection['ID'], $arCurrentSectionIds);

										if ($arLocation['CURRENT']) {
											$currentLocationRegionId = $arLocation['ID'];
											$currentSectionRegionId = $arSection['ID'];
										}

										continue 2;
									}
								}
								unset($arLocation);

								$bCurrent = in_array($arSection['ID'], $arCurrentSectionIds);
								if ($bCurrent) {
									$currentSectionRegionId = $arSection['ID'];
								}

								$arLevel2['s'.$arSection['ID']] = [
									'SECTION_ID' => $arSection['ID'],
									'NAME' => $arSection['NAME'],
									'SORT' => $arSection['SORT'],
									'PARENT_ID' => $currentSectionOkrugId ?: 0,
									'CURRENT' => $bCurrent,
								];
							}
						}
						unset($arLocation); // !it`s need after continue 2
					}

					if ($arLevel2) {
						\Bitrix\Main\Type\Collection::sortByColumn(
							$arLevel2,
							[
								'SORT' => [
									SORT_NUMERIC,
									SORT_ASC,
								],
								'NAME' => SORT_ASC,
							],
							'',
							null,
							true
						);

						if (
							!$currentLocationRegionId &&
							!$currentSectionRegionId
						) {
							// select first REGION (not CITY without region)
							foreach ($arLevel2 as &$arLocation) {
								if ($arLocation['URL']) {
									if ($arLocation['CURRENT']) {
										break;
									}
								}
								else {
									$arLocation['CURRENT'] = true;

									if (isset($arLocation['SECTION_ID'])) {
										$currentSectionRegionId = $arLocation['SECTION_ID'];
									}

									if (isset($arLocation['ID'])) {
										$currentLocationRegionId = $arLocation['ID'];
									}

									break;
								}
							}
							unset($arLocation);
						}
					}
				}

				if ($currentLocationRegionId) {
					// COLLECT CITIES

					if ($arLocationsIds) {
						$arChildrenLocations = [];

						// get children locations (only CITIES) of all regions or of current REGION
						$arChildrenFilter = [
							'=PARENTS.ID' => $arLocationsIds, // if there are COUNTRIES or DISTRICTS or REGIONS or CITIES in $arLocationsIds
							'TYPE.CODE' => 'CITY',
							'=NAME.LANGUAGE_ID' => $lang,
						];
						if ($currentLocationRegionId) {
							$arChildrenFilter[] = [
								'LOGIC' => 'OR',
								[
									'PARENT_ID' => $currentLocationRegionId,
								],
								[
									'PARENT.PARENT_ID' => $currentLocationRegionId,
								],
							];
						}

						$arChildrenLocations = Cache::SaleLocation_GetList(
							[
								'SORT' => 'ASC',
							],
							$arChildrenFilter,
							[
								'ID',
								'SORT',
								'NAME',
								'PARENTS.ID',
								'PARENTS.TYPE.CODE',
							]
						);

						if ($arChildrenLocations) {
							$arLocations = [];
							foreach ($arChildrenLocations as $arChildLocation) {
								$bInRegionsLocations = isset($arLocationsRegionsIds[$arChildLocation['ID']]);

								if (
									$arChildLocation['SALE_LOCATION_LOCATION_PARENTS_TYPE_CODE'] !== 'CITY' ||
									$bInRegionsLocations
								) {
									$parentLocationId = $arChildLocation['SALE_LOCATION_LOCATION_PARENTS_ID'];
									$arChildLocation['ORDER'] = $parentLocationId ? $arLocationsOrder[$parentLocationId] : false;

									if (isset($arLocations[$arChildLocation['ID']])) {
										if (
											($arLocations[$arChildLocation['ID']]['ORDER'] > $arChildLocation['ORDER']) ||
											($arLocations[$arChildLocation['ID']]['ORDER'] === false && $arChildLocation['ORDER'] !== false)
										) {
											$arLocations[$arChildLocation['ID']] = $arChildLocation;
										}
									}
									else {
										$arLocations[$arChildLocation['ID']] = $arChildLocation;
									}
								}
							}

							foreach ($arLocations as $arLocation) {
								$parentLocationId = $arLocation['SALE_LOCATION_LOCATION_PARENTS_ID'];
								$_regionId = $arLocationsRegionsIds[$parentLocationId];
								if ($_regionId) {
									$arCities[] = [
										'ID' => $_regionId,
										'URL' => $arRegions[$_regionId]['URL'],
										'NAME' => $arLocation['SALE_LOCATION_LOCATION_NAME_NAME'],
										'SORT' => $arLocation['SORT'],
										'PARENT_ID' => $parentLocationId,
										'CURRENT' => $arCurrentLocation['ID'] == $arLocation['ID'],
										'LOCATION_ID' => $arLocation['ID'],
									];
								}
							}
						}
					}
				}

				if (
					$arIblockCities &&
					$currentSectionRegionId
				) {
					$arIblockCities = array_filter(
						$arIblockCities,
						function($arIblockCity) use ($currentSectionRegionId) {
							return $arIblockCity['SECTION_ID'] == $currentSectionRegionId;
						}
					);

					$arCities = array_merge($arCities, array_values($arIblockCities));
				}

				if ($arCities) {
					\Bitrix\Main\Type\Collection::sortByColumn(
						$arCities,
						[
							'CURRENT' => SORT_DESC,
							'SORT' => [
								SORT_NUMERIC,
								SORT_ASC,
							],
							'NAME' => SORT_ASC,
						],
						'',
						null,
						true
					);
				}
			}
		}

		return [
			'level1' => array_values($arLevel1),
			'level2' => array_values($arLevel2),
			'cities' => array_values($arCities),
		];
	}

	/**
	 * return cities by phrase
	 */
	static public function searchCities($term, $url, $regionalityType, $lang = LANGUAGE_ID) {
		$arCities = [];

		$term = trim($term);
		if (strlen($term)) {
			$arRegions = static::getRegionsWithUrl($url, $regionalityType);
			if ($arRegions) {
				$lang = $lang ?: LANGUAGE_ID;

				// collect all locations of all regions
				$arIblockCities = $arLocationsCities = $arLocationsIds = $arLocationsRegionsIds = $arLocationsOrder = $arRegionsSectionsIds = [];
				foreach ($arRegions as $arRegion) {
					$mainLocationId = false;

					if (
						is_array($arRegion['LOCATION']) &&
						$arRegion['LOCATION']
					) {
						$mainLocationId = $arRegion['LOCATION'][0];

						foreach ($arRegion['LOCATION'] as $locationId) {
							// $arRegions already ordered by SORT=>ASC,NAME=>ASC
							if (!isset($arLocationsRegionsIds[$locationId])) {
								$arLocationsRegionsIds[$locationId] = $arRegion['ID'];
								$arLocationsOrder[$locationId] = count($arLocationsOrder);
							}
						}
					}

					if ($mainLocationId) {
						$arLocationsIds = array_merge($arLocationsIds, $arRegion['LOCATION']);
					}
					else {
						// is city match to term?
						if (preg_match('/^'.preg_quote(mb_strtolower($term), '/').'/i'.BX_UTF_PCRE_MODIFIER, mb_strtolower($arRegion['NAME']))) {
							if ($arRegion['IBLOCK_SECTION_ID']) {
								$arRegionsSectionsIds[$arRegion['ID']] = $arRegion['IBLOCK_SECTION_ID'];
							}

							$arIblockCities[] = [
								'ID' => $arRegion['ID'],
								// default sort of iblock elements is 100, but default sort of locations is 100
								'SORT' => $arRegion['SORT'] !== 500 ? $arRegion['SORT'] : 100,
								'URL' => $arRegion['URL'],
								'NAME' => trim($arRegion['NAME']),
								'LOCATION_ID' => false,
							];
						}
					}
				}

				if ($arRegionsSectionsIds) {
					$arSectionsParents = static::getSectionsParents($arRegionsSectionsIds, Regionality::getRegionIBlockID());
					$arSectionsPathes = static::getSectionsParentsPathes($arSectionsParents);

					foreach ($arIblockCities as &$arIblockCity) {
						$sectionId = $arRegionsSectionsIds[$arIblockCity['ID']];
						// $arIblockCity['PARENTS_PATHES'] = $arSectionsPathes[$sectionId];
						$arIblockCity['PATH'] = implode(', ', $arSectionsPathes[$sectionId]);
					}
					unset($arIblockCity);
				}

				if ($arLocationsIds) {
					$arLocationsIds = array_unique($arLocationsIds);

					// get locations (only cities) by term
					$arFindedLocationsIds = [];
					$res = \Bitrix\Sale\Location\Search\Finder::find(
						[
							'order' => [
								'SORT' => 'ASC',
							],
							'filter' => [
								'=NAME.LANGUAGE_ID' => $lang,
								'TYPE.CODE' => 'CITY',
								'=PHRASE' => $term,
							],
							'select' => [
								'ID',
							],
						],
						[
							'USE_INDEX' => false,
						]
					);
					while ($arLocation = $res->fetch()) {
						$arFindedLocationsIds[] = $arLocation['ID'];
					}

					if ($arFindedLocationsIds) {
						// get locations (only cities) of all regions with term
						$arLocations = [];
						$res = \Bitrix\Sale\Location\LocationTable::getList([
							'order' => [
								'SORT' => 'ASC',
							],
							'filter' => [
								'=PARENTS.ID' => $arLocationsIds,
								'=ID' => $arFindedLocationsIds,
								'=NAME.LANGUAGE_ID' => $lang,
								'TYPE.CODE' => 'CITY',
							],
							'select' => [
								'ID',
								'SORT',
								'CITY_NAME' => 'NAME.NAME',
								'PARENTS.ID',
								'PARENTS.TYPE.CODE',
							],
						]);
						while ($arLocation = $res->fetch()) {
							$bInRegionsLocations = in_array($arLocation['ID'], $arLocationsIds);

							if (
								$arLocation['SALE_LOCATION_LOCATION_PARENTS_TYPE_CODE'] !== 'CITY' ||
								$bInRegionsLocations
							) {
								$parentLocationId = $arLocation['SALE_LOCATION_LOCATION_PARENTS_ID'];
								$arLocation['ORDER'] = $parentLocationId ? $arLocationsOrder[$parentLocationId] : false;
								if (isset($arLocations[$arLocation['ID']])) {
									if (
										($arLocations[$arLocation['ID']]['ORDER'] > $arLocation['ORDER']) ||
										($arLocations[$arLocation['ID']]['ORDER'] === false && $arLocation['ORDER'] !== false)
									) {
										$arLocations[$arLocation['ID']] = $arLocation;
									}
								}
								else {
									$arLocations[$arLocation['ID']] = $arLocation;
								}
							}
						}

						if ($arLocations) {
							$arCitiesLocationsIds = [];

							foreach ($arLocations as $arLocation) {
								$parentLocationId = $arLocation['SALE_LOCATION_LOCATION_PARENTS_ID'];
								$regionId = $arLocationsRegionsIds[$parentLocationId];
								if ($regionId) {
									$arCitiesLocationsIds[] = $arLocation['ID'];
									$arLocationsCities[$arLocation['ID']] = [
										'ID' => $regionId,
										'SORT' => $arLocation['SORT'],
										'URL' => $arRegions[$regionId]['URL'],
										'NAME' => $arLocation['CITY_NAME'],
										'LOCATION_ID' => $arLocation['ID'],
									];
								}
							}

							// get cities pathes
							if ($arCitiesLocationsIds) {
								$arLocationsParents = [];

								$res = \Bitrix\Sale\Location\LocationTable::getList([
									'order' => [
										'PARENTS.TYPE_ID' => 'desc',
									],
									'filter' => [
										'=ID' => $arCitiesLocationsIds,
										'=PARENTS.NAME.LANGUAGE_ID' => $lang,
									],
									'select' => [
										'ID',
										'PARENTS.ID',
										'PARENTS.NAME',
									],
								]);
								while ($loc = $res->fetch()) {
									if (!isset($arLocationsParents[$loc['ID']])) {
										$arLocationsParents[$loc['ID']] = [];
									}

									if (
										$loc['SALE_LOCATION_LOCATION_PARENTS_ID'] &&
										$loc['SALE_LOCATION_LOCATION_PARENTS_NAME_NAME'] &&
										!$arLocationsParents[$loc['ID']][$loc['SALE_LOCATION_LOCATION_PARENTS_ID']] &&
										$loc['SALE_LOCATION_LOCATION_PARENTS_ID'] != $loc['ID']
									) {
										$arLocationsParents[$loc['ID']][$loc['SALE_LOCATION_LOCATION_PARENTS_ID']] = [
											'ID' => $loc['SALE_LOCATION_LOCATION_PARENTS_ID'],
											'NAME' => $loc['SALE_LOCATION_LOCATION_PARENTS_NAME_NAME'],
										];
									}
								}

								if ($arLocationsParents) {
									foreach ($arLocationsParents as $locationId => $arLocationParents) {
										// $arLocationsCities[$locationId]['PARENTS_PATHES'] = array_column($arLocationParents, 'NAME');
										$arLocationsCities[$locationId]['PATH'] = implode(', ', array_column($arLocationParents, 'NAME'));
									}
								}
							}
						}
					}
				}
			}

			$arCities = array_merge(array_values($arLocationsCities), $arIblockCities);
			if ($arCities) {
				\Bitrix\Main\Type\Collection::sortByColumn(
					$arCities,
					[
						'SORT' => [
							SORT_NUMERIC,
							SORT_ASC,
						],
						'NAME' => SORT_ASC,
					],
					'',
					null,
					true
				);
			}
		}

		return $arCities;
	}

	/**
	 * return parent sections
	 */
	static protected function getLevelSections(array $arSectionsParents, int $depth) :array {
		$arSections = [];

		if (
			$depth &&
			$arSectionsParents
		) {
			$arSections = array_column($arSectionsParents, ($depth == 1 ? 1 : 0));
			if ($arSections) {
				$arSections = array_combine(array_column($arSections, 'ID'), $arSections);
			}
		}

		return $arSections;
	}

	/**
	 * return sections parents
	 */
	static protected function getSectionsParents(array $arSectionsIds, int $iblockId) :array {
		$arSectionsParents = [];

		if (
			$arSectionsIds &&
			$iblockId
		) {
			$arSections = Cache::CIBlockSection_GetList(
				[
					'SORT' => 'ASC',
					'NAME' => 'ASC',
					'CACHE' => [
						'GROUP' => 'ID',
						'TAG' => Cache::GetIBlockCacheTag($iblockId),
					]
				],
				[
					'GLOBAL_ACTIVE' => 'Y',
					'ID' => $arSectionsIds,
					'IBLOCK_ID' => $iblockId,
				],
				false,
				[
					'ID',
					'NAME',
					'SORT',
					'IBLOCK_SECTION_ID',
					'LEFT_MARGIN',
					'RIGHT_MARGIN',
					'DEPTH_LEVEL',
				]
			);

			if ($arSections) {
				foreach ($arSections as $arSection) {
					$arSectionsParents[$arSection['ID']] = [
						$arSection,
					];

					if ($arSection['DEPTH_LEVEL'] > 1) {
						$arRootSection = Cache::CIBlockSection_GetList(
							[
								'CACHE' => [
									'MULTI' => 'N',
									'TAG' => Cache::GetIBlockCacheTag($iblockId),
								]
							],
							[
								'IBLOCK_ID' => $iblockId,
								'GLOBAL_ACTIVE' => 'Y',
								'DEPTH_LEVEL' => 1,
								'<=LEFT_BORDER' => $arSection['LEFT_MARGIN'],
								'>=RIGHT_BORDER' => $arSection['RIGHT_MARGIN'],
							],
							false,
							[
								'ID',
								'NAME',
								'SORT',
							]
						);

						if ($arRootSection) {
							$arSectionsParents[$arSection['ID']] = [
								$arSection,
								$arRootSection,
							];
						}
					}
				}
			}
		}

		return $arSectionsParents;
	}

	/**
	 * return sections pathes
	 */
	static protected function getSectionsParentsPathes(array $arSectionsParents) :array {
		$arSectionsPathes = [];

		if ($arSectionsParents) {
			$arSectionsPathes = $arSectionsParents;
			array_walk(
				$arSectionsPathes,
				function(&$arParentSections, $sectionId) {
					$arParentSections = array_column($arParentSections, 'NAME');
				}
			);
		}

		return $arSectionsPathes;
	}

	/**
	 * return sections parent ids
	 */
	static protected function getSectionsParentsIds(array $arSectionsParents) :array {
		$arSectionsParentsIds = [];

		if ($arSectionsParents) {
			$arSectionsParentsIds = $arSectionsParents;
			array_walk(
				$arSectionsParentsIds,
				function(&$arParentSections, $sectionId) {
					$arParentSections = array_column($arParentSections, 'ID');
				}
			);
		}

		return $arSectionsParentsIds;
	}

	public function onPrepareComponentParams($arParams) {
    	if (isset($arParams['CUSTOM_SITE_ID'])) {
			$this->setSiteId($arParams['CUSTOM_SITE_ID']);
		}

		if (isset($arParams['CUSTOM_LANGUAGE_ID'])) {
			$this->setLanguageId($arParams['CUSTOM_LANGUAGE_ID']);
		}

		$arParams['POPUP'] = (isset($arParams['POPUP']) ? $arParams['POPUP'] : 'N');

        return $arParams;
    }

	protected function includeModules() {
        if (!Loader::includeModule(Solution::moduleID)) {
            throw new SystemException(Loc::getMessage('RLM_C_ERROR_MODULE_NOT_INSTALLED'));
        }
        if (!Loader::includeModule('sale')) {
            throw new SystemException(Loc::getMessage('RLM_C_ERROR_MODULE_SALE_NOT_INSTALLED'));
        }
    }

	public function executeComponent() {
		global $arTheme, $APPLICATION;

		$this->includeModules();

		$bPopup = ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') || $this->arParams['POPUP'] == 'Y');
		$bUseRegionality = ($bPopup ? $arTheme['USE_REGIONALITY'] : $arTheme['USE_REGIONALITY']['VALUE']) === 'Y';
		$regionalityType = $bPopup ? $arTheme['REGIONALITY_TYPE'] : $arTheme['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_TYPE']['VALUE'];
		$bOnlySearchRow = Option::get(Solution::moduleID, 'REGIONALITY_SEARCH_ROW', 'N') === 'Y';
		if ($bUseRegionality) {
			$bShowCity = ($bPopup ? $arTheme['REGIONALITY_CITY_IN_HEADER'] : $arTheme['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_CITY_IN_HEADER']['VALUE']) === 'Y';
		}
		else {
			$bShowCity = ($bPopup ? $arTheme['REGIONALITY_IPCITY_IN_HEADER'] : $arTheme['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_IPCITY_IN_HEADER']['VALUE']) === 'Y';
		}

		$uri = $APPLICATION->GetCurUri();
        //fix for landing in search for change url
        if (strpos($uri, 'ls=') === false) {
            $uri = urlencode($uri); // return ?ls=region_id
        } else {
            $uri = $APPLICATION->GetCurPageParam();
        }

		if (isset($this->arParams['URL']) && $this->arParams['URL'] != $_SERVER['REQUEST_URI']) {
			$uri = $this->arParams['URL'];
		}

		$this->arResult = [
			'POPUP' => $bPopup,
			'USE_REGIONALITY' => $bUseRegionality,
			'REGIONALITY_TYPE' => $regionalityType,
			'ONLY_SEARCH_ROW' => $bOnlySearchRow,
			'SHOW_CITY' => $bShowCity,
			'SCHEME' => (\Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? 'https://' : 'http://'),
			'URI' => htmlspecialcharsbx($uri),
			'LANG' => $this->getLanguageId(),
		];

		if ($bUseRegionality) {
			$this->arResult['REGIONS'] = static::getRegionsWithUrl($uri, $regionalityType);

			if ($bPopup) {
				if ($this->arResult['REGIONS']) {
					$this->arResult['FAVORITS'] = static::getFavoriteCities($uri, $regionalityType, $this->getLanguageId());
				}

				// for old templates compatibility
				$this->arResult['HOST'] = $this->arResult['SCHEME'];
				$this->arResult['SECTION_LEVEL1'] = [];
				$this->arResult['SECTION_LEVEL2'] = [];
				$this->arResult['JS_REGIONS'] = [];
			}
			else {
				$this->arResult['CURRENT_REGION'] = Regionality::getCurrentRegion();
				$this->arResult['IP'] = Regionality::getIP();
				$this->arResult['REAL_CITY'] = Regionality::getCityByIP($this->arResult['IP']);
				$this->arResult['REAL_REGION'] = Regionality::getRealRegionByIP();
				$this->arResult['REGION_SELECTED'] = isset($_COOKIE['current_region']) && $_COOKIE['current_region'];
				$this->arResult['REGION_GEOIP_ERROR'] = !isset($_SESSION['GEOIP']) || isset($_SESSION['GEOIP']['message']);
				$this->arResult['SHOW_REGION_CONFIRM'] = false;
				$this->arResult['IS_HOME_REGION'] = $this->arResult['CURRENT_REGION'] && $this->arResult['REAL_REGION'] && $this->arResult['REAL_REGION']['ID'] == $this->arResult['CURRENT_REGION']['ID'];
				$this->arResult['CURRENT_REGION_TITLE_IN_HEADER'] = $this->arResult['CURRENT_REGION']['NAME'];
				$this->arResult['REAL_REGION_TITLE_IN_HEADER'] = '';

				if (!$this->arResult['REGION_GEOIP_ERROR']) {
					// real region is defined by ip
					if ($this->arResult['REAL_REGION']) {
						// real region name for confirm popup
						$this->arResult['REAL_REGION_TITLE_IN_HEADER'] = $this->arResult['REAL_REGION']['NAME'];

						// not home region,
						if (!$this->arResult['IS_HOME_REGION']) {
							// if region didn`t selected,
							// than show confirm popup
							if (
								!$this->arResult['REGION_SELECTED'] ||
								$this->arResult['CURRENT_REGION']['ID'] != $_COOKIE['current_region']
							) {
								$this->arResult['SHOW_REGION_CONFIRM'] = true;
							}
						}
					}

					if ($this->arResult['REAL_CITY']) {
						// real city for confirm popup
						$this->arResult['REAL_REGION_TITLE_IN_HEADER'] = $this->arResult['REAL_CITY'];
					}

					// show city
					if ($bShowCity) {
						if ($this->arResult['REAL_CITY']) {
							if ($this->arResult['IS_HOME_REGION']) {
								// if home region,
								// than show real city
								$this->arResult['CURRENT_REGION_TITLE_IN_HEADER'] = $this->arResult['REAL_CITY'];
							}
						}

						// get seleted location in current region or main location in current region
						$arLocation = Regionality::getCurrentLocation();

						if ($arLocation) {
							// location is city/village (main location in current region can be a country/country_district/region/subregion/)
							if (
								$arLocation['TYPE_CODE'] === 'CITY' ||
								$arLocation['TYPE_CODE'] === 'VILLAGE'
							) {
								$this->arResult['CURRENT_REGION_TITLE_IN_HEADER'] = $arLocation['CITY_NAME'];
							}
						}
					}
				}
			}

			$this->includeComponentTemplate();
		}
		else {
			$this->arResult['IP'] = Regionality::getIP();
			$this->arResult['REAL_CITY'] = Regionality::getCityByIP($this->arResult['IP']);
			$this->arResult['SHOW_REGION_CONFIRM'] = false;

			if (
				$bShowCity &&
				$this->arResult['REAL_CITY']
			) {
				$this->arResult['CURRENT_REGION_TITLE_IN_HEADER'] = $this->arResult['REAL_CITY'];

				$this->includeComponentTemplate();
			}
		}

		static::$lastResult = $this->arResult;

		return $this->arResult;
	}
}

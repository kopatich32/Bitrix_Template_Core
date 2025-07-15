<?php

namespace Aspro\Max\Stores;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Highloadblock as HL,
	Bitrix\Main\Entity,
	Aspro\Max\Product\Quantity,
	Aspro\Max\Stores\HelperHL,
	Bitrix\Main\Security\Random,
	Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);


class Property
{
	const moduleID = ASPRO_MAX_MODULE_ID;
	const STORES_PROP_CODE = "STORES_FILTER";
	const STORES_HL_TABLE_NAME = "b_hlbd_aspromaxstores";
	const STORES_HL_NAME = "AsproMaxStores";
	const STORES_UF_ID = "UF_STORE_ID";

	public static function syncStores()
	{
		$arBXStores = self::getAllBXStores();
		$arBXStores = array_column($arBXStores, NULL, "ID");

		$arHLStores = self::getAllHLStores();
		$arHLStores = array_column($arHLStores, NULL, self::STORES_UF_ID);

		$arStoresToAdd = array_diff_key($arBXStores, $arHLStores);
		if (!empty($arStoresToAdd)) {
			foreach ($arStoresToAdd as $keyStore => $valueStore) {
				self::addHLStore($valueStore);
			}
		}

		$arStoresToDelete = array_diff_key($arHLStores, $arBXStores);
		if (!empty($arStoresToDelete)) {
			foreach ($arStoresToDelete as $keyStore => $valueStore) {
				$hlStoreId = $arHLStores[$keyStore]['ID'];
				self::deleteHLStore($hlStoreId);
			}
		}

		$arStoresToUpdate = array_intersect_key($arBXStores, $arHLStores);
		if (!empty($arStoresToUpdate)) {
			foreach ($arStoresToUpdate as $keyStore => $valueStore) {
				//todo: maybe need to check fields and don't update if nothing changed
				$hlStoreId = $arHLStores[$keyStore]['ID'];
				self::updateHLStore($hlStoreId, $valueStore);
			}
		}
	}

	public static function getAllBXStores(): array
	{
		$arStores = Quantity::CCatalogStore_GetList(["SORT" => "ASC"], [], false, false, []);

		return $arStores;
	}

	public static function getOneBXStore(string $idStore): array
	{
		$arStores = Quantity::CCatalogStore_GetList(["SORT" => "ASC"], ['ID' => $idStore], false, false, []);
		$arStoreFields = is_array($arStores) && !empty($arStores) ? reset($arStores) : [];

		return $arStoreFields;
	}

	public static function getAllHLStores(): array
	{
		// $arSelectFields = ["ID", "UF_SORT", "UF_NAME", "UF_STORE_ID"];
		$arSelectFields = ["*"];

		$dataManager = HelperHL::getInstance(self::STORES_HL_NAME);
		$arStores = $dataManager->get(['select' => $arSelectFields]);

		return $arStores;
	}


	public static function addHLStore(array $arStoreFields): string
	{
		$arFields = self::prepareStoresFields($arStoreFields);
		$arFields['UF_XML_ID'] = self::generateXMLField($arStoreFields);

		$dataManager = HelperHL::getInstance(self::STORES_HL_NAME);
		$newHLStoreID = $dataManager->add($arFields);

		return $newHLStoreID;
	}

	public static function checkStore(string $activeStore, string $issuingCenter) : bool 
	{
		return $activeStore === 'Y' && $issuingCenter === 'Y'  ? true : false;
	}

	public static function updateHLStore(string $idElement, array $arStoreFields): bool
	{
		$arFields = self::prepareStoresFields($arStoreFields);

		$dataManager = HelperHL::getInstance(self::STORES_HL_NAME);

		$bNeedUpdate = self::checkStore($arStoreFields['ACTIVE'], $arStoreFields['ISSUING_CENTER']);
		if ($bNeedUpdate) {
			$bUpdated = $dataManager->update($idElement, $arFields);
		} else {
			$bUpdated = $dataManager->delete($idElement);
		}

		return $bUpdated;
	}

	public static function deleteHLStore(string $idElement): bool
	{
		$dataManager = HelperHL::getInstance(self::STORES_HL_NAME);
		$bDeleted = $dataManager->delete($idElement);

		return $bDeleted;
	}

	public static function getHLStoreId(string $storeId): string
	{
		$dataManager = HelperHL::getInstance(self::STORES_HL_NAME);
		$arHLStore = $dataManager->get([
			'filter' => [self::STORES_UF_ID => $storeId],
			'select' => ['ID']
		]);

		$arHLStoreId = '';
		if (!empty($arHLStore) && is_array($arHLStore)) {
			$arHLStoreId = reset($arHLStore)['ID'] ?? '';
		}

		return $arHLStoreId;
	}

	public static function prepareStoresFields(array $arStoreFields): array
	{
		$arFields = [];
		if (isset($arStoreFields['TITLE'])) {
			$arFields['UF_NAME'] = $arStoreFields['TITLE'];
		}
		if (isset($arStoreFields['SORT'])) {
			$arFields['UF_SORT'] = $arStoreFields['SORT'];
		}
		if (isset($arStoreFields['ID'])) {
			$arFields[self::STORES_UF_ID] = $arStoreFields['ID'];
		}

		return $arFields;
	}

	public static function generateXMLField(array $arStoreFields): string
	{
		$strXML = '';
		if (isset($arStoreFields['ID'])) {
			$strXML = $arStoreFields['ID'];
		} else {
			$strXML = Random::getString(10);
		}

		return $strXML;
	}

	public static function createPropertyStores(string $iblockID, $bSmartFilter = true)
	{
		$codeProp = self::getStoresFilterPropCode();
		$tableName = self::STORES_HL_TABLE_NAME;

		$arFields = [
			"NAME" => Loc::getMessage('PROPERTY_STORES_PROP_NAME'),
			"ACTIVE" => "Y",
			"SORT" => "150",
			"CODE" => $codeProp,
			"PROPERTY_TYPE" => "S",
			"USER_TYPE" => "directory",
			"IBLOCK_ID" => $iblockID,
			"LIST_TYPE" => "L",
			"MULTIPLE" => "Y",
			"SMART_FILTER" => $bSmartFilter ? "Y" : "N",
			"USER_TYPE_SETTINGS" => [
				"size" => "1",
				"width" => "0",
				"group" => "N",
				"multiple" => "N",
				"TABLE_NAME" => $tableName
			]
		];

		$ibp = new \CIBlockProperty;
		$PropID = $ibp->Add($arFields);
	}

	public static function createHLBlockStores(): string
	{
		$entityName = self::STORES_HL_NAME;
		$tableName = self::STORES_HL_TABLE_NAME;

		Loader::IncludeModule('highloadblock');

		$dbHblock = HL\HighloadBlockTable::getList(array("filter" => array("=NAME" => $entityName)));

		if ($result = $dbHblock->Fetch()) {
			return $result['ID'] ?? '';
		}

		$arLangs = array(
			'ru' => Loc::getMessage('PROPERTY_STORES_HL_NAME'),
			//'en' => 'Warehouses for filter'
		);

		$result = HL\HighloadBlockTable::add(array(
			'NAME' => $entityName,
			'TABLE_NAME' => $tableName,
		));

		if (!$result->isSuccess()) {
			$errors = $result->getErrorMessages();
			$strErrors = implode('\n', $errors);
			throw new \Exception($strErrors);
		}

		$hlblockID = $result->getId();
		foreach ($arLangs as $lang_key => $lang_val) {
			HL\HighloadBlockLangTable::add(array(
				'ID' => $hlblockID,
				'LID' => $lang_key,
				'NAME' => $lang_val
			));
		}

		$entityId = "HLBLOCK_{$hlblockID}";

		$arUserFields = [
			[
				'ENTITY_ID' => $entityId,
				'FIELD_NAME' => 'UF_NAME',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_COLOR_NAME',
				'SORT' => '100',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'Y',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
			],
			[
				'ENTITY_ID' => $entityId,
				'FIELD_NAME' => 'UF_SORT',
				'USER_TYPE_ID' => 'double',
				'XML_ID' => 'UF_SORT',
				'SORT' => '200',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
			],
			[
				'ENTITY_ID' => $entityId,
				'FIELD_NAME' => 'UF_XML_ID',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_XML_ID',
				'SORT' => '300',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'Y',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
			],
			[
				'ENTITY_ID' => $entityId,
				'FIELD_NAME' => 'UF_STORE_ID',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_STORE_ID',
				'SORT' => '400',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
			],
			[
				'ENTITY_ID' => $entityId,
				'FIELD_NAME' => 'UF_DEF',
				'USER_TYPE_ID' => 'boolean',
				'XML_ID' => 'UF_DEF',
				'SORT' => '700',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
			]
		];

		$arLanguages = [];
		$by = 'lid';
		$order = 'asc';
		$rsLanguage = \CLanguage::GetList($by, $order, []);
		while ($arLanguage = $rsLanguage->Fetch()) {
			$arLanguages[] = $arLanguage["LID"];
		}

		$obUserField  = new \CUserTypeEntity;
		foreach ($arUserFields as $arFields) {
			$arLabelNames = array();
			foreach ($arLanguages as $languageID) {
				//WizardServices::IncludeServiceLang("references.php", $languageID);
				$arLabelNames[$languageID] = Loc::getMessage('STORES_' . $arFields["FIELD_NAME"]);
			}

			$arFields["EDIT_FORM_LABEL"] = $arLabelNames;
			$arFields["LIST_COLUMN_LABEL"] = $arLabelNames;
			$arFields["LIST_FILTER_LABEL"] = $arLabelNames;
			$ID_USER_FIELD = $obUserField->Add($arFields);
		}

		return $hlblockID;
	}


	public static function checkPropStores(string $iblockID): bool
	{
		$bPropExists = false;

		$propInfo = self::getStoresPropInfo($iblockID);
		if (isset($propInfo["ID"])) {
			$bPropExists = true;
		}

		return $bPropExists;
	}

	public static function checkPropTableStores(string $iblockID): bool
	{
		$bPropExists = false;

		$propInfo = self::getStoresPropInfo($iblockID);
		if (isset($propInfo["ID"])) {
			if (isset($propInfo["USER_TYPE_SETTINGS"]["TABLE_NAME"]) && $propInfo["USER_TYPE_SETTINGS"]["TABLE_NAME"] === self::STORES_HL_TABLE_NAME) {
				$bPropExists = true;
			}
		}

		return $bPropExists;
	}

	public static function checkHLStores(): bool
	{
		$entityName = self::STORES_HL_NAME;
		$tableName = self::STORES_HL_TABLE_NAME;

		Loader::IncludeModule('highloadblock');

		$dbHblock = HL\HighloadBlockTable::getList(array("filter" => array("=NAME" => $entityName)));

		$bHLExists = false;
		if ($result = $dbHblock->Fetch()) {
			$bHLExists = true;
		}

		return $bHLExists;
	}

	public static function checkUseFilterStores(string $iblockId, bool $bFromEvent = false): bool
	{
		static $arUseFilterStores = [];

		if (!isset($arUseFilterStores[$iblockId])) {
			$arUseFilterStores[$iblockId] = false;

			$rsSites = \CIBlock::GetSite($iblockId);
			while ($arSite = $rsSites->Fetch()) {
				$siteId = $arSite["LID"];

				if (Option::get(self::moduleID, "USE_STORES_FILTER", "N", $siteId) === "Y") {
					if (!$bFromEvent || Option::get(self::moduleID, "EVENT_SYNC_PRODUCT_STORES", "N", $siteId) === "Y") {
						$catalogIblockId = Option::get(self::moduleID, 'CATALOG_IBLOCK_ID', \CMaxCache::$arIBlocks[$siteId]['aspro_max_catalog']['aspro_max_catalog'][0], $siteId);

						//check is it catalog max
						if ($catalogIblockId == $iblockId) {
							$arUseFilterStores[$iblockId] = true;
							break;
						} else {
							//check is it offers
							$arCatalog = \CCatalog::GetByID($iblockId);
							if ($catalogIblockId == $arCatalog["PRODUCT_IBLOCK_ID"]) {
								$arUseFilterStores[$iblockId] = true;
								break;
							}
						}
					}
				}
			}
		}
		// if ($bUseFilterStores === NULL) {
		// 	$bUseFilterStores = Option::get(self::moduleID, "USE_STORES_FILTER", "N") === "Y";
		// }

		return $arUseFilterStores[$iblockId];
	}

	public static function checkLiveHandlers(): bool
	{
		static $bUseLiveHandlers;

		if (is_null($bUseLiveHandlers)) {
			$bUseLiveHandlers = false;
			$by = "sort";
			$order = "desc";
			$rsSites = \CSite::GetList($by, $order, ["ACTIVE" => "Y"]);
			while ($arSite = $rsSites->Fetch()) {
				if (Option::get(Property::moduleID, "USE_STORES_FILTER", "N", $arSite["ID"]) === "Y") {
					if (Option::get(Property::moduleID, "EVENT_SYNC_PRODUCT_STORES", "N", $arSite["ID"]) === "Y") {
						$bUseLiveHandlers = true;
						break;
					}
				}
			}
		}

		return $bUseLiveHandlers;
	}

	public static function setStoreFilterProp(array $options = []): void
	{
		$ID = $options["PRODUCT_ID"] ?? '';
		$bOffers = $options["IS_OFFERS"] ?? false;
		$bFullCalc = $options["FULL_CALC"] ?? false;
		$bFromEvent = $options["FROM_EVENT"] ?? false;

		Loader::IncludeModule('iblock');
		Loader::IncludeModule('catalog');
		$codeProp = self::getStoresFilterPropCode();
		$bNeedSetOffersStore = true;
		$bProductWithOffers = false;

		//Get iblock element
		$rsCatalogElement = \CIBlockElement::GetList(
			array(),
			array(
				"ID" => $ID,
			),
			false,
			false,
			array("ID", "IBLOCK_ID", "TYPE")
		);

		if ($arCatalogElement = $rsCatalogElement->Fetch()) {

			if (!$bFullCalc && !self::checkUseFilterStores($arCatalogElement["IBLOCK_ID"], $bFromEvent)) {
				return;
			}

			$arCatalog = \CCatalog::GetByID($arCatalogElement["IBLOCK_ID"]);
			if (is_array($arCatalog)) {
				//Check if it is offers iblock
				if ($arCatalog["OFFERS"] == "Y" && !$bOffers) {
					self::setStoreFilterProp(["PRODUCT_ID" => $ID, "IS_OFFERS" => true, "FULL_CALC" => $bFullCalc]);
					//Find product element
					$rsElement = \CIBlockElement::GetProperty(
						$arCatalogElement["IBLOCK_ID"],
						$arCatalogElement["ID"],
						"sort",
						"asc",
						array("ID" => $arCatalog["SKU_PROPERTY_ID"])
					);
					$arElement = $rsElement->Fetch();
					if ($arElement && $arElement["VALUE"] > 0) {
						$ELEMENT_ID = $arElement["VALUE"];
						$IBLOCK_ID = $arCatalog["PRODUCT_IBLOCK_ID"];
						$OFFERS_IBLOCK_ID = $arCatalog["IBLOCK_ID"];
						$OFFERS_PROPERTY_ID = $arCatalog["SKU_PROPERTY_ID"];
					}
					$bNeedSetOffersStore = false;
				}
				//or iblock which has offers
				elseif ($arCatalog["OFFERS_IBLOCK_ID"] > 0) {
					$ELEMENT_ID = $arCatalogElement["ID"];
					$IBLOCK_ID = $arCatalogElement["IBLOCK_ID"];
					$OFFERS_IBLOCK_ID = $arCatalog["OFFERS_IBLOCK_ID"];
					$OFFERS_PROPERTY_ID = $arCatalog["OFFERS_PROPERTY_ID"];
				}
				//or it's regular catalog
				else {
					$ELEMENT_ID = $arCatalogElement["ID"];
					$IBLOCK_ID = $arCatalogElement["IBLOCK_ID"];
					$OFFERS_IBLOCK_ID = false;
					$OFFERS_PROPERTY_ID = false;
				}
			}
		}
		if ($ELEMENT_ID) {
			static $arPropCache = array();
			static $arPropArray = array();

			if (!array_key_exists($IBLOCK_ID, $arPropCache)) {
				//Check for property
				$arProperty = self::getStoresPropInfo($IBLOCK_ID);
				if ($arProperty) {
					$arPropCache[$IBLOCK_ID] = $arProperty["ID"];
					$arPropArray[$codeProp] = $arProperty["ID"];
				} else {
					if (!$arPropCache[$IBLOCK_ID])
						$arPropCache[$IBLOCK_ID] = false;
				}
			}
			if ($arPropCache[$IBLOCK_ID]) {
				//Compose elements filter

				$arProductID = array();

				if ($arCatalogElement["TYPE"] === "2") {
					$resItems = \CCatalogProductSet::getAllSetsByProduct($ELEMENT_ID, \CCatalogProductSet::TYPE_SET);

					if (!empty($resItems)) {
						$arSet = reset($resItems);
						$arSetItems = array_column($arSet["ITEMS"], NULL, 'ITEM_ID');
						$arProductID = array_keys($arSetItems);
					} else {
						$arProductID = array($ELEMENT_ID);
					}
				} else if ($OFFERS_IBLOCK_ID) {
					$rsOffers = \CIBlockElement::GetList(
						array(),
						array(
							"IBLOCK_ID" => $OFFERS_IBLOCK_ID,
							"PROPERTY_" . $OFFERS_PROPERTY_ID => $ELEMENT_ID,
							"ACTIVE" => "Y"
						),
						false,
						false,
						array("ID")
					);
					while ($arOffer = $rsOffers->Fetch()) {
						$arProductID[] = $arOffer["ID"];
						if ($bFullCalc && $bNeedSetOffersStore) {
							self::setStoreFilterProp(["PRODUCT_ID" => $arOffer["ID"], "IS_OFFERS" => true, "FULL_CALC" => $bFullCalc]);
						}
					}

					if (!$arProductID) {
						$arProductID = array($ELEMENT_ID);
					} else {
						$bProductWithOffers = true;
					}
				} else
					$arProductID = array($ELEMENT_ID);


				if ($arPropArray[$codeProp]) {
					if ($arProductID) {
						static $bStores;
						if (!$bStores) {
							$dbRes = Quantity::CCatalogStore_GetList([], [], false, ["nPageSize" => '1'], []);

							if (!empty($dbRes)) {
								$bStores = true;
							}
						}

						if ($bStores) {
							static $arHLStores;

							if (is_null($arHLStores)) {
								$arHLStores = self::getAllHLStores();
								$arHLStores = array_column($arHLStores, NULL, self::STORES_UF_ID);
							}

							$arStoresFilter = [];

							if ($bProductWithOffers) {
								$rsOffers = \CIBlockElement::GetList(
									array(),
									array(
										"IBLOCK_ID" => $OFFERS_IBLOCK_ID,
										"ID" => $arProductID,
										"ACTIVE" => "Y"
									),
									["PROPERTY_" . $codeProp],
									false,
									array("ID", "PROPERTY_" . $codeProp)
								);
								while ($arOffer = $rsOffers->Fetch()) {
									if (isset($arOffer["PROPERTY_" . $codeProp . "_VALUE"])) {
										$storeVal = $arOffer["PROPERTY_" . $codeProp . "_VALUE"];
										if (is_array($storeVal)) {
											$arStoresFilter = array_merge($arStoresFilter, $storeVal);
										} else {
											if (!in_array($storeVal, $arStoresFilter)) {
												$arStoresFilter[] = $storeVal;
											}
										}
									}
								}
							} else {
								$arBXStores = Quantity::CCatalogStore_GetList([], ['PRODUCT_ID' => $arProductID, 'STORE.ACTIVE' => 'Y'], false, false, ['ID', 'PRODUCT_AMOUNT', 'PRODUCT_ID']);

								if (!empty($arBXStores)) {
									if (isset($arSetItems) && !empty($arSetItems)) {
										foreach ($arBXStores as $arStore) {
											if (!isset($arQuantity[$arStore['ID']])) {
												$arQuantity[$arStore['ID']] = [];
											}
											$arQuantity[$arStore['ID']][$arStore['PRODUCT_ID']] = $arStore['PRODUCT_AMOUNT'];
										}

										if ($arQuantity) {
											foreach ($arQuantity as $storeId => &$q) {
												$hlStore = $arHLStores[$storeId] ?? [];

												foreach ($arSetItems as $v) {
													$q[$v['ITEM_ID']] /= $v['QUANTITY'];
													$q[$v['ITEM_ID']] = floor($q[$v['ITEM_ID']]);
												}

												$q = !empty($q) ? min($q) : 0;
												if ($q > 0 && !empty($hlStore) && !in_array($hlStore['UF_XML_ID'], $arStoresFilter)) {
													$arStoresFilter[] = $hlStore['UF_XML_ID'];
												}
											}
											unset($q);
										}
									} else {
										foreach ($arBXStores as $arStore) {
											$hlStore = $arHLStores[$arStore['ID']] ?? [];
											if ($arStore['PRODUCT_AMOUNT'] > 0 && !empty($hlStore)) {
												if (!in_array($hlStore['UF_XML_ID'], $arStoresFilter)) {
													$arStoresFilter[] = $hlStore['UF_XML_ID'];
												}
											}
										}
									}
								}
							}

							\CIBlockElement::SetPropertyValuesEx(
								$ELEMENT_ID,
								$IBLOCK_ID,
								array(
									$codeProp => !empty($arStoresFilter) ? $arStoresFilter : false,
								)
							);
						}
					}

					if (class_exists('\Bitrix\Iblock\PropertyIndex\Manager')) {
						\Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($IBLOCK_ID, $ELEMENT_ID);
					}
				}
			}
		}
	}

	public static function getStoresPropInfo(string $iblockID): array
	{
		static $arStoresProps = [];

		if (!isset($arStoresProps[$iblockID])) {
			$storePropCode = self::getStoresFilterPropCode();

			$res = \CIBlockProperty::GetByID($storePropCode, $iblockID);
			$arPropInfo = $res->Fetch();

			$arStoresProps[$iblockID] = !empty($arPropInfo) ? $arPropInfo : [];
		}

		return $arStoresProps[$iblockID];
	}

	public static function getStoresFilterPropID(string $iblockID): string
	{
		$storePropID = '';

		$propInfo = self::getStoresPropInfo($iblockID);
		if (isset($propInfo["ID"])) {
			$storePropID = $propInfo["ID"];
		}

		return $storePropID;
	}

	public static function filterSmartProp(array &$arItems, array $arParams): void
	{
		// $storePropID = self::getStoresFilterPropID($arParams['IBLOCK_ID']);
		$storePropID = '';
		$storePropCode = self::getStoresFilterPropCode();
		
		foreach ($arItems as $propId => $propValue) {
			if($propValue["CODE"] === $storePropCode){
				$storePropID = $propId;
				break;
			}
		}
		
		if ($storePropID && isset($arItems[$storePropID]) && !empty($arItems[$storePropID]["VALUES"])) {
			if (!empty($arParams['STORES']) && self::checkUseFilterStores($arParams['IBLOCK_ID'])) {
				$arStoreIDs = array_flip((array)$arParams['STORES']);
				$newStores = array_intersect_key($arItems[$storePropID]["VALUES"], $arStoreIDs);
				$arItems[$storePropID]["VALUES"] = $newStores;
			} else {
				$arItems[$storePropID]["VALUES"] = [];
			}
		}
	}

	public static function getStoresFilterForOffers(array $arParams): array
	{
		if (!self::checkUseFilterStores($arParams['IBLOCK_ID'])) {
			return [];
		}

		$storePropID = self::getStoresFilterPropID($arParams['IBLOCK_ID']);
		$arStoresFromFilter = [];

		if ($storePropID && isset($GLOBALS[$arParams["FILTER_NAME"]]["=PROPERTY_" . $storePropID])) {
			$arStoresFromFilter = $GLOBALS[$arParams["FILTER_NAME"]]["=PROPERTY_" . $storePropID];
		}

		return (array)$arStoresFromFilter;
	}

	public static function getStoresFilterPropCode(): string
	{
		static $storesPropCode;
		if ($storesPropCode === NULL) {
			$storesPropCode = Option::get(self::moduleID, "STORES_FILTER_PROP_CODE", self::STORES_PROP_CODE);
		}

		return $storesPropCode;
	}

	public static function filterOffersByStore(array $arItemOffers, array $arParams): array
	{
		if (!self::checkUseFilterStores($arParams['IBLOCK_ID'])) {
			return [];
		}

		$arOffers = [];
		$storePropCode = self::getStoresFilterPropCode();
		$storePropID = self::getStoresFilterPropID($arParams['IBLOCK_ID']);

		if ($storePropID && isset($GLOBALS[$arParams["FILTER_NAME"]]["=PROPERTY_" . $storePropID])) {
			$arStoresFromFilter = $GLOBALS[$arParams["FILTER_NAME"]]["=PROPERTY_" . $storePropID];

			foreach ($arItemOffers as $keyOffer => $arOffer) {
				if (isset($arOffer['PROPERTIES'][$storePropCode])) {
					$arStoresFromOffer = $arOffer['PROPERTIES'][$storePropCode]["VALUE"];
					if (is_array($arStoresFromOffer) && !empty($arStoresFromOffer)) {
						if (!empty(array_intersect($arStoresFromFilter, $arStoresFromOffer))) {
							$arOffers[$keyOffer] = $arOffer;
						}
					}
				}
			}
		}

		return $arOffers;
	}

	public static function checkFilterOfferByStore(array $arOffer, array $arParams): bool
	{
		if (!self::checkUseFilterStores($arParams['IBLOCK_ID'])) {
			return true;
		}

		$bFiltred = true;
		$storePropCode = self::getStoresFilterPropCode();
		$storePropID = self::getStoresFilterPropID($arParams['IBLOCK_ID']);

		if ($storePropID && isset($GLOBALS[$arParams["FILTER_NAME"]]["=PROPERTY_" . $storePropID])) {
			$arStoresFromFilter = $GLOBALS[$arParams["FILTER_NAME"]]["=PROPERTY_" . $storePropID];

			if (isset($arOffer['PROPERTIES'][$storePropCode])) {
				$arStoresFromOffer = $arOffer['PROPERTIES'][$storePropCode]["VALUE"];
				if (is_array($arStoresFromOffer) && !empty($arStoresFromOffer)) {
					if (empty(array_intersect($arStoresFromFilter, $arStoresFromOffer))) {
						$bFiltred = false;
					}
				} else {
					$bFiltred = false;
				}
			}
		}

		return $bFiltred;
	}

	public static function clearPublicCache(string $siteId = 's1'): void
	{
		//clear cache
		\CBitrixComponent::clearComponentCache('bitrix:catalog.smart.filter', $siteId);
		\CBitrixComponent::clearComponentCache('bitrix:catalog.section', $siteId);
	}
}

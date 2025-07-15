<?php

namespace Aspro\Max\Stores;

use Aspro\Max\Stores\Property,
	Aspro\Max\Product\Quantity,
	Bitrix\Main\Loader;

class EventHandler
{
	static string $delProductID = '';
	static array $updateProductInfo = [];

	static public function OnCatalogStoreUpdate($idStore, $arFields)
	{
		if (!Property::checkLiveHandlers()) {
			return;
		}

		$arStoreFields = Property::getOneBXStore($idStore);
		$hlStoreId = Property::getHLStoreId($idStore);
		$bNeedAddStore = Property::checkStore($arStoreFields['ACTIVE'], $arStoreFields['ISSUING_CENTER']);
		if ($hlStoreId) {
			Property::updateHLStore($hlStoreId, $arStoreFields);
		} elseif($bNeedAddStore) {
			Property::addHLStore($arStoreFields);
		}
	}

	static public function OnCatalogStoreDelete($idStore)
	{
		if (!Property::checkLiveHandlers()) {
			return;
		}

		$hlStoreId = Property::getHLStoreId($idStore);
		if ($hlStoreId) {
			Property::deleteHLStore($hlStoreId);
		}
	}

	static public function OnCatalogStoreAdd($idStore, $arFields)
	{
		if (!Property::checkLiveHandlers()) {
			return;
		}

		$arStoreFields = Property::getOneBXStore($idStore);
		$hlStoreId = Property::getHLStoreId($idStore);
		if ($hlStoreId) {
			Property::updateHLStore($hlStoreId, $arStoreFields);
		} else {
			Property::addHLStore($arStoreFields);
		}
	}

	static public function OnStoreProductAdd($id, $arFields)
	{
		if (!Property::checkLiveHandlers()) {
			return;
		}

		if (isset($arFields['PRODUCT_ID']) && $arFields['PRODUCT_ID']) {
			Property::setStoreFilterProp(["PRODUCT_ID" => $arFields['PRODUCT_ID'], "FROM_EVENT" => true]);
		}
	}

	static public function OnStoreProductUpdate($id, $arFields)
	{
		if (!Property::checkLiveHandlers()) {
			return;
		}

		if (isset($arFields['PRODUCT_ID']) && $arFields['PRODUCT_ID']) {
						
			if(isset(self::$updateProductInfo[$id]) 
				&& isset($arFields['AMOUNT']) 
				&& self::$updateProductInfo[$id] !== $arFields['AMOUNT'] 
				&& ( (self::$updateProductInfo[$id] == 0 || $arFields['AMOUNT'] == 0) )
			) {
				Property::setStoreFilterProp(["PRODUCT_ID" => $arFields['PRODUCT_ID'], "FROM_EVENT" => true]);
				unset(self::$updateProductInfo[$id]);
			}
		}
	}

	static public function OnBeforeStoreProductUpdate($id, $arFields)
	{
		if (!Property::checkLiveHandlers()) {
			return;
		}

		if (isset($arFields['PRODUCT_ID']) && $arFields['PRODUCT_ID'] && isset($arFields['STORE_ID'])) {
			$arStoreProductInfo = Quantity::CCatalogStore_GetList([], ['PRODUCT_ID' => $arFields['PRODUCT_ID'], 'ID' => $arFields['STORE_ID']], false, false, ['ID', 'PRODUCT_AMOUNT', 'PRODUCT_ID', 'STORE_ID']);
			if(!empty($arStoreProductInfo) && is_array($arStoreProductInfo)){
				self::$updateProductInfo[$id] = reset($arStoreProductInfo)['PRODUCT_AMOUNT'];
			}
		}
	}

	static public function OnBeforeStoreProductDelete($id)
	{
		if (!Property::checkLiveHandlers()) {
			return;
		}

		Loader::IncludeModule('catalog');
		$rsStoreProduct = \Bitrix\Catalog\StoreProductTable::getlist(array(
			'filter' => array("=ID" => $id),
			'select' => array('PRODUCT_ID'),
		));
		if ($arStoreProduct = $rsStoreProduct->fetch()) {
			self::$delProductID = $arStoreProduct['PRODUCT_ID'];
		}
	}

	static public function OnStoreProductDelete($id)
	{
		if (!Property::checkLiveHandlers()) {
			return;
		}

		if (!empty(self::$delProductID)) {
			Property::setStoreFilterProp(["PRODUCT_ID" => self::$delProductID, "FROM_EVENT" => true]);
			self::$delProductID = '';
		}
	}

	static public function OnAfterIBlockElementUpdate($arFields)
	{
		if (!Property::checkLiveHandlers()) {
			return;
		}

		if (isset($arFields['ID']) && $arFields['ID']) {
			Property::setStoreFilterProp(["PRODUCT_ID" => $arFields['ID'], "FROM_EVENT" => true]);
		}
	}

	static public function OnProductSetUpdate($id, $arFields)
	{
		if (!Property::checkLiveHandlers()) {
			return;
		}

		Loader::IncludeModule('catalog');
		$setItem = \CCatalogProductSet::getSetByID($id);

		if ($setItem && isset($setItem['ITEM_ID']) && $setItem['ITEM_ID']) {
			Property::setStoreFilterProp(["PRODUCT_ID" => $setItem['ITEM_ID'], "FROM_EVENT" => true]);
		}
	}

	static public function OnProductSetAdd($id, $arFields)
	{
		if (!Property::checkLiveHandlers()) {
			return;
		}

		Loader::IncludeModule('catalog');
		$setItem = \CCatalogProductSet::getSetByID($id);

		if ($setItem && isset($setItem['ITEM_ID']) && $setItem['ITEM_ID']) {
			Property::setStoreFilterProp(["PRODUCT_ID" => $setItem['ITEM_ID'], "FROM_EVENT" => true]);
		}
	}
}

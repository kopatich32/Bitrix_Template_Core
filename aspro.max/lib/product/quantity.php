<?
namespace Aspro\Max\Product;

use Bitrix\Main\Loader,
	Bitrix\Main\SystemException,
    Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc;

use CMax as Solution,
    CMaxCache as SolutionCache,
    CMaxRegionality as SolutionRegionality,
    Aspro\Functions\CAsproMax as SolutionFunctions;

class Quantity {
    public static function getStoresAmount(array $ids, array $stores = []) {
		$arResult = $arRegion = [];

		if (!Loader::includeModule('catalog')) {
			throw new SystemException('Error include catalog');
		}

		if($bUseRegionality = SolutionRegionality::checkUseRegionality()){
			$arRegion = SolutionRegionality::getCurrentRegion();
		}

		if (!$stores) {
			$stores = explode(',', Solution::GetFrontParametrValue('STORES'));

			if ($arRegion) {
				if (
					array_key_exists('LIST_STORES', $arRegion) &&
					$arRegion['LIST_STORES']
				) {
					if (reset($arRegion['LIST_STORES']) !== 'component') {
						$stores = $arRegion['LIST_STORES'];
					}
				}
			}
		}

		if ($stores) {
			foreach ($stores as $i => $store) {
				if(!$store){
					unset($stores[$i]);
				}
			}

			$stores = array_values($stores);
		}

		foreach ($ids as $id) {
			if ($id <= 0) {
				continue;
			}

			$iterator = \Bitrix\Catalog\Model\Product::getList([
				'select' => [
					'ID',
					'QUANTITY',
					'TYPE',
					'BUNDLE',
				],
				'filter' => [
					'=ID' => $id,
				]
			]);
			$arProduct = $iterator->fetch();
			if (!$arProduct) {
				continue;
			}

			$arResult[$id] = '';
			if ($stores) {
				$arProductStores = [];

				if (!$arProduct['TYPE']) {
					$arProduct['TYPE'] = 1;
				}

				if ($arProduct['TYPE'] == 2) {
					if ($arSets = \CCatalogProductSet::getAllSetsByProduct($id, 1)) {
						$arSets = reset($arSets);

						$arProductSet = $arSets['ITEMS'] ? array_column($arSets['ITEMS'], 'ITEM_ID') : [];

						$arFilter = [
							'PRODUCT_ID' => $arProductSet,
						];
						if ($stores) {
							$arFilter['ID'] = $stores;
						}

						$arQuantity = [];
						$rsStore = self::CCatalogStore_GetList(
							[],
							$arFilter,
							false,
							false,
							[
								'ID',
								'ELEMENT_ID',
								'PRODUCT_AMOUNT',
							]
						);
						foreach ($rsStore as $arStore) {
							if (!isset($arQuantity[$arStore['ID']])) {
								$arQuantity[$arStore['ID']] = [];
							}

							$arQuantity[$arStore['ID']][$arStore['ELEMENT_ID']] = $arStore['PRODUCT_AMOUNT'];
						}

						if ($arQuantity) {
							foreach ($arQuantity as $storeId => &$q) {
								foreach ($arSets['ITEMS'] as $v) {
									$q[$v['ITEM_ID']] /= $v['QUANTITY'];
									$q[$v['ITEM_ID']] = floor($q[$v['ITEM_ID']]);
								}

								$q = min($q);
								if ($q > 0) {
									$arProductStores[] = $storeId;
								}
							}
							unset($q);
						}
					}
				}
				elseif ($arProduct['TYPE'] == 3) {
					$res = \CCatalogSKU::getOffersList([$arProduct['ID']], 0, [], ['ID'], []);
					if ($res && $res[$arProduct['ID']]) {
						$arOffersIds = array_keys($res[$arProduct['ID']]);

						$arFilter = [
							'PRODUCT_ID' => $arOffersIds,
						];
						if ($stores) {
							$arFilter['ID'] = $stores;
						}

						$rsStore = self::CCatalogStore_GetList(
							[],
							$arFilter,
							false,
							false,
							[
								'ID',
								'PRODUCT_AMOUNT',
							]
						);
						foreach ($rsStore as $arStore) {
							if ($arStore['PRODUCT_AMOUNT'] > 0) {
								$arProductStores[] = $arStore['ID'];
							}
						}
					}
				}
				else {
					$arFilter = [
						'PRODUCT_ID' => $arProduct['ID'],
					];
					if ($stores) {
						$arFilter['ID'] = $stores;
					}

					$rsStore = self::CCatalogStore_GetList(
						[],
						$arFilter,
						false,
						false,
						[
							'ID',
							'PRODUCT_AMOUNT',
						]
					);
					foreach ($rsStore as $arStore) {
						if ($arStore['PRODUCT_AMOUNT'] > 0) {
							$arProductStores[] = $arStore['ID'];
						}
					}
				}

				$arProductStores = array_unique($arProductStores);

				if ($arProductStores) {
					$count = count($arProductStores);
					$amount_shops = SolutionFunctions::declOfNum(
						$count,
						[
							Loc::getMessage('CATALOG_STORES_AMOUNT_SHOPS0'),
							Loc::getMessage('CATALOG_STORES_AMOUNT_SHOPS1'),
							Loc::getMessage('CATALOG_STORES_AMOUNT_SHOPS2')
						]
					);

					$arResult[$id] = Loc::getMessage('CATALOG_STORES_AMOUNT', [
						'#AMOUNT_SHOPS#' => $amount_shops,
					]);
				} else {
					$arResult[$id] = Loc::getMessage('CATALOG_STORES_NO_AMOUNT');
				}
			}
		}

		return $arResult;
	}

    /**
	 * This method is simply wrapper on CCatalogStore::GetList
	 * which uses methods of "d7 core" if module version support them
	 */
	public static function CCatalogStore_GetList(
		$arOrder = ["SORT" => "ASC"],
		$arFilter = [],
		$arGroupBy = false,
		$arNavStartParams = false,
		$arSelectFields = []
	){
		$arRes = $dbRes = [];
		$arResultGroupBy = [
			"GROUP" => is_array($arGroupBy) && isset($arGroupBy["GROUP"]) ? $arGroupBy["GROUP"] : [],
			"MULTI" => is_array($arGroupBy) && isset($arGroupBy["MULTI"]) ? $arGroupBy["MULTI"] : [],
			"RESULT" => isset($arSelectFields["RESULT"]) ? $arSelectFields["RESULT"] : [],
		];
		$arGroupBy = (isset($arGroupBy["BX"]) ? $arGroupBy["BX"] : $arGroupBy);

		if (Solution::checkVersionModule('17.0.4', 'catalog')) {
			$getListParams = $arRuntimeFields = [];
			$storeClass = empty($arSelectFields) ? '\Bitrix\Catalog\StoreTable' : '\Bitrix\Catalog\StoreProductTable';
			$oldFieldsList = [
				'PRODUCT_AMOUNT' => 'AMOUNT',
				'ID' => 'STORE_ID',
				'ELEMENT_ID' => 'PRODUCT_ID'
			];

			if (!empty($arFilter)) {
				$getListParams['filter'] = $arFilter;

				foreach ($arSelectFields as $arField) {
					if (isset($oldFieldsList[$arField]) && !isset($arRuntimeFields[$arField])) {
						$arRuntimeFields[$arField] = new \Bitrix\Main\Entity\ExpressionField($arField, $oldFieldsList[$arField]);
					}
				}
			}
			if (!empty($arGroupBy))
				$getListParams['group'] = $arGroupBy;

			if (!empty($arOrder))
				$getListParams['order'] = $arOrder;

			if (!empty($arSelectFields)) {
				$getListParams['select'] = $arSelectFields;
				$getListParams['filter']['STORE.ACTIVE'] = 'Y';

				foreach ($arSelectFields as $arField) {
					if (isset($oldFieldsList[$arField]) && !isset($arRuntimeFields[$arField])) {
						$arRuntimeFields[$arField] = new \Bitrix\Main\Entity\ExpressionField($arField, $oldFieldsList[$arField]);
					}
				}
			}

			if (!empty($arRuntimeFields)) {
				$getListParams['runtime'] = array_values($arRuntimeFields);
			}

			if (!empty($arNavStartParams)) {
				if (isset($arNavStartParams['nPageSize']))
					$getListParams['limit'] = $arNavStartParams['nPageSize'];

				if (isset($arNavStartParams['iNumPage']))
					$getListParams['offset'] = $arNavStartParams['iNumPage'];
			}

			$dbRes = $storeClass::getList($getListParams);
		} else {
			$dbRes = \CCatalogStore::GetList($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
		}

		while ($item = $dbRes->Fetch()) {
			$arRes[] = $item;
		}

		if($arResultGroupBy["MULTI"] || $arResultGroupBy["GROUP"] || $arResultGroupBy["RESULT"]){
			$arRes = SolutionCache::GroupArrayBy($arRes, $arResultGroupBy);
		}

		return $arRes;
	}
}

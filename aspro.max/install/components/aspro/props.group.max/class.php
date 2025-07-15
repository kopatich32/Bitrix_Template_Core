<?
use Bitrix\Main\SystemException,
	Bitrix\Main\Web\Json,
	CMax as Solution,
	Aspro\Max\Grupper,
	Aspro\Max\Functions\Extensions;

class ProrsGroup extends CBitrixComponent {
	const NO_GROUP_NAME = 'NO_GROUP';
	const NO_GROUP_CODE = 'no-group';

	public function getConfigPath($iblockId) {
		$configPath = '';

		$tmp = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.Solution::moduleID.'/admin/grupper/config/iblock_'.$iblockId.'.json';
		if (file_exists($tmp)) {
			$configPath = $tmp;
		}
		else {
			$tmp = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.Solution::moduleID.'/admin/propertygroups/json/prop_groups_iblock_'.$iblockId.'.json';
			if (file_exists($tmp)) {
				$configPath = $tmp;
			}
		}

		return $configPath;
	}

	public function getPropertiesFromFile($filePath) {
		$arProperties = [];

		if (!empty($filePath)) {
			try {
				$content = file_get_contents($filePath);
				if ($content) {
					$arProperties = Json::decode($content);
				}
			}
			catch (SystemException $e) {
				$arProperties = [];
			}
		}
		
		return $arProperties;
	}

	public function mkResult() {
		$this->arResult = [
			'GROUPS' => [],
		];
		$iblockId = $this->arParams['IBLOCK_ID'];
		if (
			$iblockId &&
			Grupper::isAspro($this->getSiteId()) &&
			Grupper::checkIblockId($iblockId)
		) {
			$this->mkGrupperResult();
		}
		else {
			$this->mkNormalResult();
		}

		return $this->arResult;
	}

	public function mkNormalResult() {
		Extensions::init('char');

		$this->arResult['DISPLAY_TYPE'] = $this->arParams['PROPERTIES_DISPLAY_TYPE'] ?? 'TABLE';
		$this->arResult['DISPLAY_PROPERTIES'] = $this->arParams['DISPLAY_PROPERTIES'] ?? [];
		$this->arResult['OFFER_DISPLAY_PROPERTIES'] = $this->arParams['OFFER_DISPLAY_PROPERTIES'] ?? [];

		$this->resortProperties($this->arResult['DISPLAY_PROPERTIES']);
		$this->resortProperties($this->arResult['OFFER_DISPLAY_PROPERTIES']);
	}

	public function mkGrupperResult() {
		$iblockId = $this->arParams['IBLOCK_ID'];
		$arDisplayProperties = $this->arParams['DISPLAY_PROPERTIES'];

		$tmpResult = [];
		if (
			$iblockId &&
			!empty($arDisplayProperties) &&
			is_array($arDisplayProperties)
		) {
			$filePath = $this->getConfigPath($iblockId);
			$arGroupProps = $this->getPropertiesFromFile($filePath);
			foreach ($arGroupProps as $keyGroup => $arGroup) {
				foreach ($arGroup['PROPS'] as $propCode) {
					if (isset($arDisplayProperties[$propCode])) {
						$tmpResult[$keyGroup]['NAME'] = $arGroup['NAME'];
						$tmpResult[$keyGroup]['CODE'] = $arGroup['CODE'];
						$tmpResult[$keyGroup]['DISPLAY_PROPERTIES'][$propCode] = $arDisplayProperties[$propCode];
					}
				}
			}

			if ('N' !== ($this->arParams['NO_GROUP_BOTTOM'] ?? 'Y')) {
				if (
					count($tmpResult) > 1 &&
					$tmpResult[0]['NAME'] === static::NO_GROUP_NAME
				) {
					$noGroup = array_shift($tmpResult);
					array_push($tmpResult, $noGroup);
				}
			}

			if (count($tmpResult) > 0) {
				$this->arResult['GROUPS'] = array_values($tmpResult);
			}
			else {
				$this->arResult['GROUPS'][] = [
					'NAME' => static::NO_GROUP_NAME,
					'CODE' => static::NO_GROUP_CODE,
					'DISPLAY_PROPERTIES' => $arDisplayProperties,
				];
			}
		}

		/* offers block */
		$offersIblockId = $this->arParams['OFFERS_IBLOCK_ID'];
		$arDisplayPropertiesOffer = $this->arParams['OFFER_DISPLAY_PROPERTIES'];

		if (
			$offersIblockId &&
			!empty($arDisplayPropertiesOffer) &&
			is_array($arDisplayPropertiesOffer)
		) {
			$countGroups = count($this->arResult['GROUPS']);
			$arGroupCodes = array_column($this->arResult['GROUPS'], 'CODE');

			$filePathOffer = $this->getConfigPath($offersIblockId);
			$tmpResultOffer = [];
			$arGroupPropsOffer = $this->getPropertiesFromFile($filePathOffer);
			if ($arGroupPropsOffer) {
				foreach ($arGroupPropsOffer as $keyGroupOffer => $arGroupOffer) {
					$keyGroup = array_search($arGroupOffer['CODE'], $arGroupCodes, true);
					foreach ($arGroupOffer['PROPS'] as $propCode) {					
						if (isset($arDisplayPropertiesOffer[$propCode])) {
							if ($keyGroup !== false) {
								$this->arResult['GROUPS'][$keyGroup]['DISPLAY_PROPERTIES'][$propCode] = $arDisplayPropertiesOffer[$propCode];
								$this->arResult['GROUPS'][$keyGroup]['DISPLAY_PROPERTIES'][$propCode]['IS_OFFER'] = true;
							}
							else {
								$tmpResultOffer[$keyGroupOffer]['NAME'] = $arGroupOffer['NAME'];
								$tmpResultOffer[$keyGroupOffer]['CODE'] = $arGroupOffer['CODE'];
								$tmpResultOffer[$keyGroupOffer]['DISPLAY_PROPERTIES'][$propCode] = $arDisplayPropertiesOffer[$propCode];
								$tmpResultOffer[$keyGroupOffer]['DISPLAY_PROPERTIES'][$propCode]['IS_OFFER'] = true;
								$tmpResultOffer[$keyGroupOffer]['OFFER_GROUP'] = true;
							}						
						}
					}
				}
				
				if (
					count($tmpResultOffer) > 1 &&
					$tmpResultOffer[0]['NAME'] === static::NO_GROUP_NAME
				) {
					$noGroupOffer = array_shift($tmpResultOffer);
					array_push($tmpResultOffer, $noGroupOffer);
				}

				if (count($tmpResultOffer) > 0) {
					if (
						$countGroups > 0 &&
						$this->arResult['GROUPS'][$countGroups - 1]['NAME'] === static::NO_GROUP_NAME
					) {
						$arNoGroup = array_pop($this->arResult['GROUPS']);
						$this->arResult['GROUPS'] = array_merge($this->arResult['GROUPS'], $tmpResultOffer, [$arNoGroup]);
					}
					else {
						$this->arResult['GROUPS'] = array_merge($this->arResult['GROUPS'], $tmpResultOffer);
					}
				}
			}
			else {
				foreach ($arDisplayPropertiesOffer as $offerPropKey => $offerProp) {
					$arDisplayPropertiesOffer[$offerPropKey]['IS_OFFER'] = true;
				}

				if (
					$countGroups > 0 &&
					$this->arResult['GROUPS'][$countGroups - 1]['NAME'] === static::NO_GROUP_NAME
				) {
					$this->arResult['GROUPS'][$countGroups - 1]['DISPLAY_PROPERTIES'] = array_merge($this->arResult['GROUPS'][$countGroups - 1]['DISPLAY_PROPERTIES'], $arDisplayPropertiesOffer);
				}
				else {
					$this->arResult['GROUPS'][] = [
						'NAME' => static::NO_GROUP_NAME,
						'CODE' => static::NO_GROUP_CODE,
						'OFFER_GROUP' => true,
						'DISPLAY_PROPERTIES' => $arDisplayPropertiesOffer,
					];
				}
			}
		}
	}

    public function executeComponent() {
		$this->mkResult();
		$this->includeComponentTemplate();

        return $this->arResult;
    }

	public function resortProperties(&$arProperties) {
		if ($arProperties) {
			uasort($arProperties, function($a, $b) {
				if ($a['SORT'] == $b['SORT']) {
					return $a['ID'] <=> $b['ID'];
				}
				else {
					return $a['SORT'] <=> $b['SORT'];
				}
			});
		}
	}
}

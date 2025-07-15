<?
namespace Aspro\Max;
use	CMax as Solution,
	CMaxCache as Cache,
	CMaxCondition as Condition,
	\Aspro\Max\Property\CustomFilter,
	\Bitrix\Main\Web\Json;

class MarketingPopup{
	const ALL_USERS_GROUP_ID = 2;
	const IBLOCK_TYPE = 'aspro_max_adv';
	const IBLOCK_CODE = 'aspro_max_marketings';
	const PROPERTY_FILTER_CODE = 'PROPERTY_FILTER_SHOW';

	protected $siteId;
	protected $iblockId;
	protected $arParams;
	protected $arRules;

	protected $arItemSelect;

	public function __construct($arParams = array()){
		$this->setInfo($arParams);
	}

	public function __set($name, $value){
		switch($name){
			case 'arParams':
				$this->setInfo($value);
				break;
			case 'siteId':
				$this->siteId = $value;
				$this->iblockId = self::_getSiteIblockId($this->siteId);
				$this->arParams = array();
				break;
		}

		return $value;
	}

	public function __get($name){
		if(property_exists($this, $name)){
			return $this->{$name};
		}

		return null;
	}

	protected function _reset(){
		$this->arRules = $this->siteId = $this->iblockId = false;
		$this->arParams = $this->arItemSelect = array();
	}

	public function setInfo($arParams = array()){
		$this->_reset();

		$this->siteId = defined('SITE_ID') ? SITE_ID : false;
		if($this->siteId == 'ru') //admin page
			$this->siteId = 's1';

		$this->iblockId = self::_getSiteIblockId($this->siteId);
		$this->arParams = $arParams && is_array($arParams) ? $arParams : array();

	}

	public function getRules(){
		if($this->arRules === false){
			$arRules = array();

			if($this->iblockId){
				$arRulesTmp = array();

				// get current user groups
				$arUserGroups = self::_getUserGroups();

				$arSelect = array(
					'ID',
					'IBLOCK_ID',
					'IBLOCK_SECTION_ID',
					'PROPERTY_PRIORITY',
					'PROPERTY_SORT',
					'PROPERTY_LAST_LEVEL_RULE',
					'PROPERTY_LAST_RULE',
					self::PROPERTY_FILTER_CODE,
					'PROPERTY_USER_GROUPS',
					'PROPERTY_LINK_REGION',
					'PROPERTY_DELAY_SHOW',
					'PROPERTY_LS_TIMEOUT',
					"PROPERTY_MODAL_TYPE",
					"PROPERTY_POSITION",
					"PROPERTY_HIDE_TITLE",
					"PROPERTY_NO_OVERLAY",
					"PROPERTY_REQUIRED_CONFIRM",
					"PROPERTY_SHOW_ON_DEVICES",
					"PROPERTY_STOP_ACTIONS",
				);

				$arFilter = array(
					'IBLOCK_ID' => $this->iblockId,
					'ACTIVE' => 'Y',
					array(
						'LOGIC' => 'OR',
						array('PROPERTY_USER_GROUPS' => $arUserGroups),
						array('PROPERTY_USER_GROUPS' => false),
					),
				);

				// use region
				if($GLOBALS['arRegion'] && $GLOBALS['arTheme']['USE_REGIONALITY']['VALUE'] === 'Y')
				{
					$arFilter[] = array(
						'LOGIC' => 'OR',
						array('PROPERTY_LINK_REGION' => $GLOBALS['arRegion']['ID']),
						array('PROPERTY_LINK_REGION' => false),
					);
				}

				// get all rules for current user groups in current region
				if($arRulesTmp = Cache::CIBLockElement_GetList(
					array(
						'SORT' => 'ASC',
						'CACHE' => array(
							'MULTI' => 'N',
							'TAG' => Cache::GetIBlockCacheTag($this->iblockId),
							'GROUP' => array('ID'),
						)
					),
					$arFilter,
					false,
					false,
					$arSelect
				)){
					// get active by date without cache
					$arRulesIDs = array_column($arRulesTmp, 'ID');
					$dbRes = \CIBlockElement::GetList(
						array(),
						array(
							'ID' => $arRulesIDs,
							'IBLOCK_ID' => $this->iblockId,
							'ACTIVE_DATE' => 'Y',
						),
						false,
						false,
						array('ID')
					);

					$arRulesIDs = array();
					while($arRule = $dbRes->Fetch()){
						$arRulesIDs[] = $arRule['ID'];
					}

					if($arRulesIDs){
						$obCache = new \CPHPCache();
						$cacheTime = 36000000;
						$cacheTag = Cache::GetIBlockCacheTag($this->iblockId);
						$cachePath = '/CMaxCache/iblock/CIBlockElement_GetList/'.$cacheTag.'/';
						$cacheID = 'CIBlockElement_GetList_'.$cacheTag.md5(serialize($arRulesIDs));
						if($obCache->InitCache($cacheTime, $cacheID, $cachePath)){
							$res = $obCache->GetVars();
							$arRulesTmp = $res['arRulesTmp'];
						}
						else{
							// collect parsed conditions from FILTER_SHOW property
							foreach($arRulesTmp as $i => &$arRule){
								if(in_array($arRule['ID'], $arRulesIDs)){
									$bBadFilter = true;

									if(is_string($arRule[self::PROPERTY_FILTER_CODE.'_VALUE'])){
										if($arRule[self::PROPERTY_FILTER_CODE.'_VALUE']){
											$arTmpFilter = Json::decode($arRule[self::PROPERTY_FILTER_CODE.'_VALUE']);
											if(is_array($arTmpFilter)){
												try{
													$arRule[self::PROPERTY_FILTER_CODE.'_VALUE'] =  $this->parseCondition($arTmpFilter, $this->arParams);
													$bBadFilter = false;
												}
												catch(\Exception $e){
													$arRule[self::PROPERTY_FILTER_CODE.'_VALUE'] = false;
												}
											}
										}
										else{
											$bBadFilter = false;
										}
									}

									if(!$bBadFilter){
										continue;
									}
								}

								// remove bad rule
								unset($arRulesTmp[$i]);
							}
							unset($arRule);

							$obCache->StartDataCache($cacheTime, $cacheID, $cachePath);
							if(strlen($cacheTag)){
								$GLOBALS['CACHE_MANAGER']->StartTagCache($cachePath);
								$GLOBALS['CACHE_MANAGER']->RegisterTag($cacheTag);
								$GLOBALS['CACHE_MANAGER']->EndTagCache();
							}

						$obCache->EndDataCache(array('arRulesTmp' => $arRulesTmp));
						}
					}

					// get some fields & properties of product
					if($arRulesTmp)
					{
						$arLastRule = $arLastLevelRule = array();
						foreach($arRulesTmp as $i => &$arRule)
						{
							if($this->_checkParsedCondition($arRule[self::PROPERTY_FILTER_CODE.'_VALUE']))
							{
								$arRules['ALL'][$arRule['ID']] = &$arRule;
							}
						}
						unset($arRule);
					}
				}
			}

			$this->arRules = $arRules;
		}

		return $this->arRules;
	}

	protected static function _getSiteIblockId($siteId){
		return Cache::$arIBlocks[$siteId][self::IBLOCK_TYPE][self::IBLOCK_CODE][0] ? Cache::$arIBlocks[$siteId][self::IBLOCK_TYPE][self::IBLOCK_CODE][0] : false;
	}

	protected static function _getUserGroups(){
		$arGroups = array();

		if(isset($GLOBALS['USER']) && $GLOBALS['USER']->IsAuthorized()){
			$resUserGroup = \Bitrix\Main\UserGroupTable::getList(
				array(
					'filter' => array(
						'USER_ID' => $GLOBALS['USER']->GetID(),
					),
					'select' => array('GROUP_ID'),
				)
			);
			while($arGroup = $resUserGroup->fetch()){
			   $arGroups[] = $arGroup['GROUP_ID'];
			}
		}

		$arGroups[] = self::ALL_USERS_GROUP_ID;

		return $arGroups;
	}

	public function parseCondition($condition, $params)
	{
		$result = array();

		if (!empty($condition) && is_array($condition))
		{
			if ($condition['CLASS_ID'] === 'CondGroup')
			{
				if (!empty($condition['CHILDREN']))
				{
					foreach ($condition['CHILDREN'] as $child)
					{
						$childResult = $this->parseCondition($child, $params);

						// is group
						if ($child['CLASS_ID'] === 'CondGroup')
						{
							$result[] = $childResult;
						}
						// same property names not overrides each other
						elseif (isset($result[key($childResult)]))
						{
							$fieldName = key($childResult);

							if (!isset($result['LOGIC']))
							{
								$result = array(
									'LOGIC' => $condition['DATA']['All'],
									array($fieldName => $result[$fieldName])
								);
							}

							$result[][$fieldName] = $childResult[$fieldName];
						}
						else
						{
							$result += $childResult;
						}
					}

					/*echo "<pre>";
					echo "Parse rule<br>";
					print_r($result);
					print_r($condition);
					echo "</pre>";*/

					if (!empty($result))
					{
						// $this->parsePropertyCondition($result, $condition, $params);

						if (count($result) > 1)
						{
							$result['LOGIC'] = $condition['DATA']['All'];
						}
					}
				}
			}
			else
			{
				$result += $this->parseConditionLevel($condition, $params);
			}
		}

		return $result;
	}

	protected function parseConditionLevel($condition, $params)
	{
		$arParsCondition = $result = array();

		if(!empty($condition) && is_array($condition)){
			$name = $this->parseConditionName($condition);

			if (!empty($name)){
				$operator = $this->parseConditionOperator($condition);
				$value = $this->parseConditionValue($condition, $name);
				$result[$operator.$name] = array(
					'NAME' => $name,
					'OPERATOR' => $operator,
					'VALUE' => $value,
				);
			}
		}

		return $result;
	}

	protected function parseConditionName(array $condition)
	{
		$result = [];
		$conditionNameMap = array(
			'CondPage' => 'PAGE',
			'CondServerName' => 'SERVER_NAME',
		);
		// $conditionNameMap = array(
		// 	'CondPage' => array(
		// 		'TYPE' => 'STRING',
		// 		'NAME' => 'PAGE',
		// 		'CONDITION_VALUE' => $GLOBALS["APPLICATION"]->GetCurPage(),
		// 	),
		// 	'CondServerName' => array(
		// 		'TYPE' => 'STRING',
		// 		'NAME' => 'PAGE',
		// 		'CONDITION_VALUE' => $_SERVER['SERVER_NAME'],
		// 	),
		// );

		if (isset($conditionNameMap[$condition['CLASS_ID']]))
		{
			$result = $conditionNameMap[$condition['CLASS_ID']];
		}
		else{
			$result = $condition['CLASS_ID'];
		}

		return $result;
	}

	protected function parseConditionOperator($condition)
	{
		$operator = '';

		switch ($condition['DATA']['logic'])
		{
			case 'Equal':
				$operator = '==';
				break;
			case 'Not':
				$operator = '!';
				break;
			case 'Contain':
				$operator = '%';
				break;
			case 'NotCont':
				$operator = '!%';
				break;
			case 'Great':
				$operator = '>';
				break;
			case 'Less':
				$operator = '<';
				break;
			case 'EqGr':
				$operator = '>=';
				break;
			case 'EqLs':
				$operator = '<=';
				break;
		}

		return $operator;
	}

	protected function parseConditionValue($condition, $name)
	{
		$value = $condition['DATA']['value'];

		switch ($name)
		{
			case 'DATE_ACTIVE_FROM':
			case 'DATE_ACTIVE_TO':
			case 'DATE_CREATE':
			case 'TIMESTAMP_X':
				$value = ConvertTimeStamp($value, 'FULL');
				break;
		}

		return $value;
	}

	protected function parsePropertyCondition(array &$result, array $condition, $params)
	{
		static $arPropertiesCodes;

		if (!empty($result))
		{
			$subFilter = array();

			foreach ($result as $name => $value)
			{
				if (!empty($result[$name]) && is_array($result[$name]) && !isset($result[$name]['NAME']))
				{
					$this->parsePropertyCondition($result[$name], $condition, $params);
				}
				else
				{
					if (($ind = strpos($name, 'CondIBProp')) !== false)
					{
						list($prefix, $iblock, $propertyId) = explode(':', $name);

						$operator = $ind > 0 ? substr($prefix, 0, $ind) : '';

						$catalogInfo = \CCatalogSku::GetInfoByIBlock($iblock);

						if(!isset($arPropertiesCodes)){
							$arPropertiesCodes = array();
						}

						if(!array_key_exists($propertyId, $arPropertiesCodes)){
							$propCode = \CIBlockProperty::GetByID($propertyId, $iblock)->Fetch()['CODE'];
							$propCode = strtoupper($propCode);
							$arPropertiesCodes[$propertyId] = $propCode;
						}
						else{
							$propCode = $arPropertiesCodes[$propertyId];
						}

						if (
							$catalogInfo['CATALOG_TYPE'] != \CCatalogSku::TYPE_CATALOG
							&& $catalogInfo['IBLOCK_ID'] == $iblock
						)
						{
							$subFilter[$operator.'PROPERTY_'.$propCode] = $value;
							$subFilter[$operator.'PROPERTY_'.$propCode]['NAME'] = 'PROPERTY_'.$propCode;
							$subFilter[$operator.'PROPERTY_'.$propCode]['PROPERTY'] = $propCode;
						}
						else
						{
							$result[$operator.'PROPERTY_'.$propCode] = $value;
							$result[$operator.'PROPERTY_'.$propCode]['NAME'] = 'PROPERTY_'.$propCode;
							$result[$operator.'PROPERTY_'.$propCode]['PROPERTY'] = $propCode;
						}

						$this->arProductSelect[] = 'PROPERTY_'.$propCode;

						unset($result[$name]);
					}
				}
			}

			if (!empty($subFilter) && !empty($catalogInfo))
			{
				$offerPropFilter = array(
					'IBLOCK_ID' => $catalogInfo['IBLOCK_ID'],
					'ACTIVE_DATE' => 'Y',
					'ACTIVE' => 'Y'
				);

				if ($params['HIDE_NOT_AVAILABLE_OFFERS'] === 'Y')
				{
					$offerPropFilter['HIDE_NOT_AVAILABLE'] = 'Y';
				}
				elseif ($params['HIDE_NOT_AVAILABLE_OFFERS'] === 'L')
				{
					$offerPropFilter[] = array(
						'LOGIC' => 'OR',
						'CATALOG_AVAILABLE' => 'Y',
						'CATALOG_SUBSCRIBE' => 'Y'
					);
				}

				if (count($subFilter) > 1)
				{
					$subFilter['LOGIC'] = $condition['DATA']['All'];
					$subFilter = array($subFilter);
				}

				$result += $subFilter;
			}
		}
	}

	protected function _checkParsedCondition($arParsedCondition){
		if($arParsedCondition && is_array($arParsedCondition))
		{
			foreach($arParsedCondition as $key => $value)
			{
				if(!is_array($value))
					continue;

				if(is_numeric($key))
				{
					$arParsedCondition[$key] = $this->_checkParsedCondition($value);

				}
				else
				{
					unset($find);

					if($value['NAME'] === 'PAGE'){
						$find = $GLOBALS["APPLICATION"]->GetCurPage();
					}
					elseif($value['NAME'] === 'SERVER_NAME'){
						$find = $_SERVER['SERVER_NAME'];
					}

					switch($value['OPERATOR']){
						case '==':
							if(is_array($value['VALUE']))
								$arParsedCondition[$key] = isset($find) && in_array($find, $value['VALUE']);
							else{
								if(isset($find) && is_array($find)){
									$arParsedCondition[$key] = in_array($value['VALUE'], $find);
								}
								else{
									$arParsedCondition[$key] = isset($find) && $find == $value['VALUE'];
								}
							}
							break;
						case '!':
							if(is_array($value['VALUE'])){
								$arParsedCondition[$key] = isset($find) && !in_array($find, $value['VALUE']);
							}
							else{
								if(isset($find) && is_array($find)){
									$arParsedCondition[$key] = !in_array($value['VALUE'], $find);
								}
								else{
									$arParsedCondition[$key] = isset($find) && $find != $value['VALUE'];
								}
							}
							break;
						case '<':
							$arParsedCondition[$key] = isset($find) && $find < $value['VALUE'];
							break;
						case '<=':
							$arParsedCondition[$key] = isset($find) && $find <= $value['VALUE'];
							break;
						case '>':
							$arParsedCondition[$key] = isset($find) && $find > $value['VALUE'];
							break;
						case '>=':
							$arParsedCondition[$key] = isset($find) && $find >= $value['VALUE'];
							break;
						case '%':
							$arParsedCondition[$key] = isset($find) && strpos($find, $value['VALUE']) !== false;
							break;
						case '!%':
							$arParsedCondition[$key] = isset($find) && strpos($find, $value['VALUE']) === false;
							break;
					}
				}
				

				if(isset($arParsedCondition['LOGIC'])) {
					if($arParsedCondition['LOGIC'] === 'AND' && $arParsedCondition[$key] == false)
					{
						$arParsedCondition = false;
						break;
					}
	
					if($arParsedCondition['LOGIC'] === 'OR' && $arParsedCondition[$key] == true)
					{
						$arParsedCondition = true;
						break;
					}
				}
				else{
					if($arParsedCondition[$key] == true)
						$arParsedCondition = true;
					else
						$arParsedCondition = false;
				}
			}

			if(is_array($arParsedCondition))
			{
				if($arParsedCondition['LOGIC'] === 'OR')
					$arParsedCondition = false;
				else
					$arParsedCondition = true;
			}
		}

		return boolval($arParsedCondition);
	}

	public static function getMediaQuery ($arItem) :string {
		$mediaPropCode = "SHOW_ON_DEVICES";
		$strMediaQuery = "";
		$mediaPropEnum = $arItem["PROPERTY_" . $mediaPropCode . "_ENUM_ID"];

		if($mediaPropEnum){
			$arMediaProp = Cache::CIBlockPropertyEnum_GetList(["sort" => "asc", "id" => "desc", "CACHE" => ["TAG" => Cache::GetPropertyCacheTag($mediaPropCode)]], ["ACTIVE" => "Y", "IBLOCK_ID" => $arItem["IBLOCK_ID"], "CODE" => $mediaPropCode, 'ID' => $mediaPropEnum]);

			if(!empty($arMediaProp)){
				$arSubQueries = array_map(function($xmlId){						
					$strQuery = "";
					$safeXmlId = preg_replace('/[^0-9:\-A-Za-z_]/m', '', $xmlId);
					$arQuery = explode('_', $safeXmlId);
					if(!empty($arQuery)){
						$strQuery = "(" . implode(') AND (', $arQuery) . ")";
					}
					return $strQuery;
				}, array_keys($arMediaProp));
				
				$strMediaQuery = "(" . implode(') OR (', $arSubQueries) . ")";
			}
		}

		return $strMediaQuery;
	}

	public static function getStopActions ($arItem) :string {
		$actionsPropCode = "STOP_ACTIONS";
		$strStopActions = "";
		$actionsPropEnum = $arItem["PROPERTY_" . $actionsPropCode . "_ENUM_ID"];

		if($actionsPropEnum){
			$arActionsProp = Cache::CIBlockPropertyEnum_GetList(["sort" => "asc", "id" => "desc", "CACHE" => ["TAG" => Cache::GetPropertyCacheTag($actionsPropCode)]], ["ACTIVE" => "Y", "IBLOCK_ID" => $arItem["IBLOCK_ID"], "CODE" => $actionsPropCode, 'ID' => $actionsPropEnum]);

			if(!empty($arActionsProp) && is_array($arActionsProp)){				
				$strStopActions = implode(',', array_keys($arActionsProp));
			}
		}

		return $strStopActions;
	}

	public static function getItemLink (?string $link) :string {
		$newLink = '';

		if ($link) {
			if (strpos($link, 'http') === 0 || strpos($link, '/') === 0) {
				$newLink = $link;
			} else {
				$newLink = SITE_DIR.$link;
			}
		}

		return $newLink;
	}

	public static function showNoindex (string $link, bool $end = false){
		if ($link) {
			$bNeedNoIndex = strpos($link, 'http') === 0 || strpos($link, '//') === 0;
			if($bNeedNoIndex){
				$strNoIndex = $end ? '<!--/noindex-->' : '<!--noindex-->';
				echo $strNoIndex;
			}
		}
	}
}
?>
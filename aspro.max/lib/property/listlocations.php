<?
namespace Aspro\Max\Property;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class ListLocations{
	static function OnIBlockPropertyBuildList(){
		return array(
			'PROPERTY_TYPE' => 'S',
			'USER_TYPE' => 'SAsproMaxListLocations',
			'DESCRIPTION' => Loc::getMessage('LOCATIONS_LINK_PROP_MAX_TITLE'),
			'GetPropertyFieldHtml' => array(__CLASS__, 'GetPropertyFieldHtml'),
			'GetSettingsHTML' => array(__CLASS__, 'GetSettingsHTML'),
			'PrepareSettings' => array(__CLASS__, 'PrepareSettings'),
		);
	}

	static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName){
		static $cache = array();
		$html = '';
		if (Loader::includeModule('sale')) {
			$cache["LOCATIONS"] = array();
			
			if (\CMax::checkVersionModule('14.10.1', 'sale')) {
				$bUseExtendedLocations =  $arProperty['USER_TYPE_SETTINGS']['USE_EXTENDED_LOCATIONS'] === 'Y';

				if ($bUseExtendedLocations) {
					$cssFile = '/bitrix/css/aspro.max/locationlist_control.css';
					$jsFile = '/bitrix/js/aspro.max/locationlist_control.js';

					if (file_exists($_SERVER['DOCUMENT_ROOT'].$cssFile)) {
						$GLOBALS['APPLICATION']->SetAdditionalCss($cssFile);
					}
					
					if (file_exists($_SERVER['DOCUMENT_ROOT'].$jsFile)) {
						$GLOBALS['APPLICATION']->AddHeadScript($jsFile);
					}

					$val = intval($value["VALUE"] ?: $arProperty["DEFAULT_VALUE"]);
					$arInitialValue = \Bitrix\Sale\Location\LocationTable::getList([
						'filter' => ['=NAME.LANGUAGE_ID' => LANGUAGE_ID, 'ID' => $val],
						'select' => ['TYPE_ID', 'CITY_NAME' => 'NAME.NAME'],
					])->Fetch();

					$arTypes = [];
					$rsTypes = \Bitrix\Sale\Location\TypeTable::getList([
						'filter' => ['=NAME.LANGUAGE_ID' => LANGUAGE_ID],
						'select' => ['ID', 'CODE', 'NAME_RU' => 'NAME.NAME']
					]);
					while ($arType = $rsTypes->Fetch()) {
						$arTypes[$arType['ID']] = [
							'NAME' => $arType['NAME_RU'],
							'CODE' => $arType['CODE'],
						];
					}

					$html = "
						<div class='location-container adm-location-popup-wrap'>
							<div class='location-container__col'>
								<input class='location-text__hidden' type='hidden' name='".$strHTMLControlName["VALUE"]."' value='".$val."' />
								<div class='location-text'>
									<input class='location-text__input' type='text' name='search' placeholder='".GetMessage('T_INPUT_PLACEHOLDER')."' value='".$arInitialValue['CITY_NAME']."' autocomplete='off' />
									<button class='location-text__clear".(!$arInitialValue['CITY_NAME'] ? ' hidden' : '')."' type='button'></button>
								</div>
							</div>
							<div class='location-results hidden'>
								<div class='results__table'></div>
							</div>
						</div>
					";
				} else {
					$arTypes = [];
					$rsTypes = \Bitrix\Sale\Location\TypeTable::getList([
						'filter' => ['=NAME.LANGUAGE_ID' => LANGUAGE_ID, 'CODE' => ['CITY']],
						'select' => ['ID', 'CODE']
					]);
					while ($arType = $rsTypes->Fetch()) {
						$arTypes[$arType['CODE']] = $arType['ID'];
					}
	
					$rsLoc = \Bitrix\Sale\Location\LocationTable::getList([
						'filter' => ['=NAME.LANGUAGE_ID' => LANGUAGE_ID, '@TYPE_ID' => $arTypes],
						'select' => ['ID', 'CITY_NAME' => 'NAME.NAME'],
						'order' => ['CITY_NAME' => 'asc'],
					]);
				}
			} else {
				$rsLoc = \CSaleLocation::GetList(
					["CITY_NAME" => "ASC"], 
					['=COUNTRY_LID' => LANGUAGE_ID, '!CITY_ID' => false]
				);
			}

			if (isset($rsLoc)) {
				while ($arLoc = $rsLoc->fetch())
					$cache['LOCATIONS'][$arLoc['ID']] = $arLoc;
	
				$varName = str_replace("VALUE", "DESCRIPTION", $strHTMLControlName["VALUE"]);
				$val = intval($value["VALUE"] ?: $arProperty["DEFAULT_VALUE"]);
				$html = '<select name="'.$strHTMLControlName["VALUE"].'" onchange="document.getElementById(\'DESCR_'.$varName.'\').value=this.options[this.selectedIndex].text">
							<option value="" >-</option>';
				foreach ($cache["LOCATIONS"] as $arLocation) {
					$html .= '<option 
						value="'.$arLocation["ID"].'"'.
						($val === intval($arLocation['ID']) ? ' selected' : '').
						'>'.$arLocation["CITY_NAME"].'</option>';
				}
				$html .= '</select>';
			}

		}
		return $html;
	}

	static function PrepareSettings($arFields) {
		$arFields['USER_TYPE_SETTINGS']['USE_EXTENDED_LOCATIONS'] = isset($arFields['USER_TYPE_SETTINGS']) && isset($arFields['USER_TYPE_SETTINGS']['USE_EXTENDED_LOCATIONS']) 
			? ($arFields['USER_TYPE_SETTINGS']['USE_EXTENDED_LOCATIONS'] === 'Y' ? 'Y' : 'N') 
			: 'N';

		return $arFields;
	}

	static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields){
		$arPropertyFields = array(
            'HIDE' => array(
            	'SMART_FILTER',
            	'SEARCHABLE',
            	'COL_COUNT',
            	'ROW_COUNT',
            	'FILTER_HINT',
            ),
            'SET' => array(
            	'SMART_FILTER' => 'N',
            	'SEARCHABLE' => 'N',
            	'ROW_COUNT' => '10',
            ),
        );

		$bUseExtendedLocations =  $arProperty['USER_TYPE_SETTINGS']['USE_EXTENDED_LOCATIONS'] === 'Y';

		$html = "
			<tr>
				<td>".GetMessage('BT_ADM_IEP_USE_EXTENDED_LOCATIONS')."</td>
				<td>
					<input 
						type='checkbox' 
						name='".$strHTMLControlName['NAME']."[USE_EXTENDED_LOCATIONS]' 
						value='Y' 
						".($bUseExtendedLocations ? 'checked' : '')." 
					/>
				</td>
			</tr>
		";

		return $html;
	}
}

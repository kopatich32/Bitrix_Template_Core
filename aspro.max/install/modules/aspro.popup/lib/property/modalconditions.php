<?
namespace Aspro\Popup\Property;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class ModalConditions{
	static function OnIBlockPropertyBuildList(){
		return array(
			'PROPERTY_TYPE' => 'S',
			'USER_TYPE' => 'SAsproModalConditionsPopup',
			'DESCRIPTION' => Loc::getMessage('POPUP_MODAL_CONDITIONS_PROP_TITLE'),
			'GetPropertyFieldHtml' => array(__CLASS__, 'GetPropertyFieldHtml'),
			'GetAdminListViewHTML' => array(__CLASS__, 'GetAdminListViewHTML'),
			'GetSettingsHTML' => array(__CLASS__, 'GetSettingsHTML'),
			'PrepareSettings' => array(__CLASS__, 'PrepareSettings'),
		);
	}

	static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName){
        return '';
	}

	static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName){
		static $cache, $jsFile, $ajaxFile;

		$html = '';

		$bEditProperty = $strHTMLControlName['MODE'] === 'EDIT_FORM';
		$bDetailPage = $strHTMLControlName['MODE'] === 'FORM_FILL';

		if($bDetailPage){
			if(!isset($cache)){
				$cache = array();
				$jsFile = '/bitrix/js/aspro/popup/modalconditions_control.js';
				$ajaxFile = '/bitrix/tools/aspro/popup/modalconditions_ajax.php';
				if(!file_exists($_SERVER['DOCUMENT_ROOT'].$jsFile) || !file_exists($_SERVER['DOCUMENT_ROOT'].$ajaxFile)){
					$jsFile = $ajaxFile = false;
				}
				else{
					$GLOBALS['APPLICATION']->AddHeadScript($jsFile);
				}
			}

			if($jsFile){
				if(Loader::includeModule('fileman')){
					$val = strlen($value['VALUE']) ? $value['VALUE'] : '[]';

					$html = '<input type="hidden" id="'.$strHTMLControlName['VALUE'].'" name="'.$strHTMLControlName['VALUE'].'" value="'.htmlspecialcharsbx(is_array($val) ? reset($val) : $val).'" data-bx-property-id="'.$arProperty['CODE'].'" data-bx-comp-prop="true" />';
					$html .= "\n".'<script>'.
						'var tv = BX(\'tr_PROPERTY_'.$arProperty['ID'].'\');'.
						'if(tv){'.
							'var iv = BX(\''.$strHTMLControlName['VALUE'].'\');'.
							'if(iv){'.
								'var td = BX.findParent(iv, {tag: \'td\'});'.
								'if(td){'.
									'var tdd = BX.findChildren(td, {tag: \'div\'}, true);'.
									'if(tdd){'.
										'for(var i in tdd){'.
											'BX.cleanNode(tdd[i]);'.
										'}'.
									'}'.
									'initAsproPopupModalConditionsControl({'.
										'data: \'{"iblockId": 1}\','.
										'oCont: td,'.
										'oInput: iv,'.
										'propertyID: \''.$arProperty['CODE'].'\','.
										'propertyParams: {'.
											'DEFAULT: \'\','.
											'ID: \''.$arProperty['CODE'].'\','.
											'IBLOCK_ID: \''.$arProperty['IBLOCK_ID'].'\','.
											'JS_DATA: \'{"iblockId":'.$iblockId.($offersIblockId ? ',"offersIblockId":'.$offersIblockId : '').'}\','.
											'JS_EVENT: \'initAsproPopupModalConditionsControl\','.
											'AJAX_FILE: \''.$ajaxFile.'\','.
											'NAME: \''.Loc::getMessage('MODAL_CONDITIONS_PROP_NAME').'\','.
											'JS_MESSAGES: \'{"invalid": "'.Loc::getMessage('MODAL_CONDITIONS_PROP_INVALID').'"}\','.
											'MULTIPLE: \'N\','.
											'PARENT: \'DATA_SOURCE\','.
											'ROWS: 0,'.
											'TOOLTIP: \'\','.
											'TYPE: \'CUSTOM\','.
											'_propId: \''.$strHTMLControlName['VALUE'].'\''.
										'}'.
									'});'.
								'}'.
							'}'.
						'}'.
						'</script>';
				}
			}
			else{
				$html = '<input type="text" id="'.$strHTMLControlName['VALUE'].'" name="'.$strHTMLControlName['VALUE'].'" value="'.htmlspecialcharsbx(is_array($val) ? reset($val) : $val).'" data-bx-property-id="'.$arProperty['CODE'].'" data-bx-comp-prop="true" />';
			}
		}

		return $html;
	}

	static function PrepareSettings($arFields){
		// if (
		// 	!isset($arFields['USER_TYPE_SETTINGS']) ||
		// 	!is_array($arFields['USER_TYPE_SETTINGS'])
		// ) {
		// 	$arFields['USER_TYPE_SETTINGS'] = [];
		// }

		// $arFields['USER_TYPE_SETTINGS']['IBLOCK_ID'] = isset($arFields['USER_TYPE_SETTINGS']['IBLOCK_ID']) ? intval($arFields['USER_TYPE_SETTINGS']['IBLOCK_ID']) : false;

		// $arFields['USER_TYPE_SETTINGS']['IBLOCK_TYPE_ID'] = isset($arFields['USER_TYPE_SETTINGS']['IBLOCK_TYPE_ID']) ? trim($arFields['USER_TYPE_SETTINGS']['IBLOCK_TYPE_ID']) : false;

		// $arFields['USER_TYPE_SETTINGS']['USE_OFFERS_IBLOCK'] = isset($arFields['USER_TYPE_SETTINGS']['USE_OFFERS_IBLOCK']) ? ($arFields['USER_TYPE_SETTINGS']['USE_OFFERS_IBLOCK'] === 'Y' ? 'Y' : 'N') : 'N';

		$arFields['FILTRABLE'] = $arFields['SMART_FILTER'] = $arFields['SEARCHABLE'] = $arFields['MULTIPLE'] = $arFields['WITH_DESCRIPTION'] = 'N';
		$arFields['MULTIPLE_CNT'] = 1;

        return $arFields;
	}

	static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields){
		$arPropertyFields = array(
            'HIDE' => array(
            	'SMART_FILTER',
            	'FILTRABLE',
            	'DEFAULT_VALUE',
            	'SEARCHABLE',
            	'MULTIPLE_CNT',
            	'COL_COUNT',
            	'MULTIPLE',
            	'WITH_DESCRIPTION',
            	'FILTER_HINT',
            ),
            'SET' => array(
            	'SMART_FILTER' => 'N',
            	'FILTRABLE' => 'N',
            	'SEARCHABLE' => 'N',
            	'MULTIPLE_CNT' => '1',
            	'MULTIPLE' => 'N',
            	'WITH_DESCRIPTION' => 'N',
            ),
        );

		/*
		$iblockId = $arProperty['USER_TYPE_SETTINGS']['IBLOCK_ID'];
		$useOffersIblock = $arProperty['USER_TYPE_SETTINGS']['USE_OFFERS_IBLOCK'];
		$b_f = ($arProperty['PROPERTY_TYPE'] == 'G' || ($arProperty['PROPERTY_TYPE'] == 'E' && $arProperty['USER_TYPE'] == BT_UT_SKU_CODE) ? array('!ID' => $iblockId) : array());
		$html = '<tr><td width="40%">'.GetMessage('BT_ADM_IEP_PROP_LINK_IBLOCK').'</td>'.
			'<td>'.GetIBlockDropDownList($iblockId, $strHTMLControlName['NAME'].'[IBLOCK_TYPE_ID]', $strHTMLControlName['NAME'].'[IBLOCK_ID]', $b_f, 'class="adm-detail-iblock-types"', 'class="adm-detail-iblock-list"').'</td></tr>'.
			'<tr><td width="40%">'.GetMessage('BT_ADM_IEP_USE_OFFERS_IBLOCK').'</td>'.
			'<td width="60%"><input type="checkbox" '.($useOffersIblock === 'Y' ? 'checked' : '').' name="'.$strHTMLControlName['NAME'].'[USE_OFFERS_IBLOCK]" value="Y" /></td></tr>';
		*/
		return $html;
	}
}

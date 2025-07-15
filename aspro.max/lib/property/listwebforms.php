<?
namespace Aspro\Max\Property;

use Bitrix\Main\Localization\Loc,
	Bitrix\Iblock,
	Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class ListWebForms{
	static function OnIBlockPropertyBuildList(){
		return array(
			'PROPERTY_TYPE' => 'S',
			'USER_TYPE' => 'SAsproMaxListWebForms',
			'DESCRIPTION' => Loc::getMessage('WEBFORMS_LINK_PROP_MAX_TITLE'),
			'GetPropertyFieldHtml' => array(__CLASS__, 'GetPropertyFieldHtml'),
			'GetPropertyFieldHtmlMulty' => array(__CLASS__, 'GetPropertyFieldHtmlMulty'),
			'GetSettingsHTML' => array(__CLASS__, 'GetSettingsHTML'),
		);
	}

	protected static function _getWebForms(){
		static $arResult;

		if(!isset($arResult)){
			if(Loader::includeModule('form'))
			{
				$arFilter = array();
				$rsForms = \CForm::GetList($by="s_id", $order="desc", $arFilter, $is_filtered);
				while ($arForm = $rsForms->Fetch())
				{
					$arResult[$arForm['ID']] = $arForm['NAME'];
				}
			}
		}

		return $arResult;
	}

	static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName){
		$bEditProperty = $strHTMLControlName['MODE'] === 'EDIT_FORM';
		$bDetailPage = $strHTMLControlName['MODE'] === 'FORM_FILL';

		$arWebForms = self::_getWebForms();
		$val = ($value['VALUE'] ? $value['VALUE'] : $arProperty['DEFAULT_VALUE']);
		ob_start();
		?>
		<select name="<?=$strHTMLControlName['VALUE']?>">
			<?//if($val == "" || $bEditProperty):?>
				<option value="" <?=($val == "" ? ' selected' : '')?>> - <?=Loc::getMessage('WEBFORMS_LINK_EMPTY_TITLE')?></option>
			<?//endif;?>
			<?foreach($arWebForms as $id => $name):?>
				<option value="<?=$id?>"<?=($val == $id ? ' selected' : '')?>><?=('['.$id.'] '.$name)?></option>
			<?endforeach;?>
		</select>
		<?
		return ob_get_clean();
	}

	static function GetPropertyFieldHtmlMulty($arProperty, $value, $strHTMLControlName){
		$bEditProperty = $strHTMLControlName['MODE'] === 'EDIT_FORM';
		$bDetailPage = $strHTMLControlName['MODE'] === 'FORM_FILL';

		$arWebForms = self::_getWebForms();
		$arValues = ($value && is_array($value) ? array_column($value, 'VALUE') : array($arProperty['DEFAULT_VALUE']));

		ob_start();
		?>
		<select name="<?=$strHTMLControlName['VALUE']?>[]" multiple size="<?=$arProperty['MULTIPLE_CNT']?>">
			<?foreach($arWebForms as $id => $name):?>
				<option value="<?=$id?>"<?=(in_array($id, $arValues) ? ' selected' : '')?>><?=('['.$id.'] '.$name)?></option>
			<?endforeach;?>
		</select>
		<?
		return ob_get_clean();
	}

	static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields){
		$arPropertyFields = array(
            'HIDE' => array(
            	'SMART_FILTER',
            	'SEARCHABLE',
            	'COL_COUNT',
            	'ROW_COUNT',
            	'FILTER_HINT',
            	'WITH_DESCRIPTION'
            ),
            'SET' => array(
            	'SMART_FILTER' => 'N',
            	'SEARCHABLE' => 'N',
            	'ROW_COUNT' => '10',
            	'WITH_DESCRIPTION' => 'N',
            ),
        );

		return $html;
	}
}

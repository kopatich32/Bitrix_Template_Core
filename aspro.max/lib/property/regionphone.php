<?
namespace Aspro\Max\Property;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Main\Web\Json,
	CMax as Solution,
	Aspro\Max\Iconset;

Loc::loadMessages(__FILE__);

class RegionPhone{
	public static function OnIBlockPropertyBuildList(){
		return array(
			'PROPERTY_TYPE' => 'S',
			'USER_TYPE' => 'SAsproMaxRegionPhone',
			'DESCRIPTION' => Loc::getMessage('MAX_REGION_PHONE_PROP_TITLE'),
			'PrepareSettings' => array(__CLASS__, 'PrepareSettings'),
			'GetSettingsHTML' => array(__CLASS__, 'GetSettingsHTML'),
			'GetAdminListViewHTML' => array(__CLASS__, 'GetAdminListViewHTML'),
			'GetPropertyFieldHtml' => array(__CLASS__, 'GetPropertyFieldHtml'),
			'ConvertToDB' => array(__CLASS__, 'ConvertToDB'),
		);
	}

	public static function PrepareSettings($arFields){
		$arFields['FILTRABLE'] = $arFields['SMART_FILTER'] = $arFields['SEARCHABLE'] = 'N';
		$arFields['MULTIPLE_CNT'] = 1;

        return $arFields;
	}

	public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields){
		$arPropertyFields = array(
            'HIDE' => array(
            	'SMART_FILTER',
            	'FILTRABLE',
            	//'DEFAULT_VALUE',
            	'SEARCHABLE',
            	'COL_COUNT',
            	'FILTER_HINT',
            ),
            'SET' => array(
            	'SMART_FILTER' => 'N',
            	'FILTRABLE' => 'N',
            	'SEARCHABLE' => 'N',
            	'MULTIPLE_CNT' => '1',
            ),
        );

		return '';
	}

	public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName){
		static $cache;

		$bAdminList = $strHTMLControlName['MODE'] === 'iblock_element_admin';
		$bMultiple = $arProperty['MULTIPLE'] === 'Y';
		$bWithDescription = $arProperty['WITH_DESCRIPTION'] === 'Y';

		if(!isset($cache)){
			$cache = array();

			Loader::includeModule('fileman');

			$moduleID = self::getModuleId();

			$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/'.$moduleID.'/sort/Sortable.js');

			\CJSCore::RegisterExt('iconset', array(
				'js' => '/bitrix/js/'.$moduleID.'/iconset.js',
				'css' => '/bitrix/css/'.$moduleID.'/iconset.css',
				'lang' => '/bitrix/modules/'.$moduleID.'/lang/'.LANGUAGE_ID.'/admin/iconset.php',
			));

			\CJSCore::RegisterExt('regionphone', array(
				'js' => '/bitrix/js/'.$moduleID.'/property/regionphone.js',
				'css' => '/bitrix/css/'.$moduleID.'/property/regionphone.css',
				'lang' => '/bitrix/modules/'.$moduleID.'/lang/'.LANGUAGE_ID.'/lib/property/regionphone.php',
			));

			\CJSCore::Init(array('iconset', 'regionphone'));
		}

		$value = self::ConvertFromDB($arProperty, $value);

		ob_start();

		if($value){
			$icon = $value['VALUE']['ICON'];
			$iconName = $strHTMLControlName['VALUE'].'[ICON]';
			$phone = $value['VALUE']['PHONE'];
			$phoneName = $strHTMLControlName['VALUE'].'[PHONE]';
			$href = $value['VALUE']['HREF'];
			$hrefName = $strHTMLControlName['VALUE'].'[HREF]';
			$description = $value['DESCRIPTION'];
			$descriptionName = str_replace('VALUE', 'DESCRIPTION', $strHTMLControlName['VALUE']);
		}
		?>
		<div class="aspro_property_regionphone_item aspro_property_regionphone_item--admlistview">
			<div class="wrapper">
				<div class="inner_wrapper">
					<?if($icon):?>
						<div class="inner">
							<div class="value_wrapper"><?=Iconset::showIcon($icon)?></div>
						</div>
					<?endif;?>
					<div class="inner">
						<div class="value_wrapper"><?=htmlspecialcharsbx($phone)?></div>
					</div>
					<div class="inner">
						<div class="value_wrapper"><?=htmlspecialcharsbx($href)?></div>
					</div>
					<?if($bWithDescription):?>
						<div class="inner">
							<div class="value_wrapper"><?=$description?></div>
						</div>
					<?endif;?>
				</div>
			</div>
		</div>
		<?

		return ob_get_clean();
	}

	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName){
		static $cache;

		$bAdminList = $strHTMLControlName['MODE'] === 'iblock_element_admin';
		$bEditProperty = $strHTMLControlName['MODE'] === 'EDIT_FORM';
		$bDetailPage = $strHTMLControlName['MODE'] === 'FORM_FILL';
		$bMultiple = $arProperty['MULTIPLE'] === 'Y';
		$bWithDescription = $arProperty['WITH_DESCRIPTION'] === 'Y';

		if(!isset($cache)){
			$cache = array();

			Loader::includeModule('fileman');

			$moduleID = self::getModuleId();

			$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/'.$moduleID.'/sort/Sortable.js');

			\CJSCore::RegisterExt('iconset', array(
				'js' => '/bitrix/js/'.$moduleID.'/iconset.js',
				'css' => '/bitrix/css/'.$moduleID.'/iconset.css',
				'lang' => '/bitrix/modules/'.$moduleID.'/lang/'.LANGUAGE_ID.'/admin/iconset.php',
			));

			\CJSCore::RegisterExt('regionphone', array(
				'js' => '/bitrix/js/'.$moduleID.'/property/regionphone.js',
				'css' => '/bitrix/css/'.$moduleID.'/property/regionphone.css',
				'lang' => '/bitrix/modules/'.$moduleID.'/lang/'.LANGUAGE_ID.'/lib/property/regionphone.php',
			));

			\CJSCore::Init(array('iconset', 'regionphone'));
		}

		$value = self::ConvertFromDB($arProperty, $value);

		if($bAdminList){
			preg_match('/FIELDS\[([\D]+)(\d+)\]\[PROPERTY_'.$arProperty['ID'].'\]\[([^\]]*)\]\[VALUE\]/', $strHTMLControlName['VALUE'], $arMatch);
			$elementType = $arMatch[1];
			$elementId = $arMatch[2];
			$valueId = $arMatch[3];
			$tableId = 'tb'.md5($elementType.$elementId.':'.$arProperty['ID']);

			if(!$valueId){
				return '';
			}
		}
		else{
			preg_match('/PROP\['.$arProperty['ID'].'\]\[([^\]]*)\]\[VALUE\]/', $strHTMLControlName['VALUE'], $arMatch);
			$valueId = $arMatch[1];
			if($bEditProperty){
				$tableId = 'form_content';
			}
			else{
				$tableId = 'tb'.md5(htmlspecialcharsbx('PROP['.$arProperty['ID'].']'));
			}
		}

		if($value){
			$icon = is_array($value['VALUE']) ? $value['VALUE']['ICON'] : '';
			$iconName = $strHTMLControlName['VALUE'].'[ICON]';
			$phone = is_array($value['VALUE']) ? $value['VALUE']['PHONE'] : '';
			$phoneName = $strHTMLControlName['VALUE'].'[PHONE]';
			$href = is_array($value['VALUE']) ? $value['VALUE']['HREF'] : '';
			$hrefName = $strHTMLControlName['VALUE'].'[HREF]';
			$description = $value['DESCRIPTION'];
			$descriptionName = str_replace('VALUE', 'DESCRIPTION', $strHTMLControlName['VALUE']);
		}

		ob_start();
		?>
		<?if($bAdminList ? !in_array($elementId, $cache) : !in_array($arProperty['ID'], $cache)):?>
			<?
			if($bAdminList){
				$cache[] = $elementId;
			}
			else{
				$cache[] = $arProperty['ID'];
			}

			$GLOBALS['APPLICATION']->AddHeadString('<script>new JRegionPhone(\''.$tableId.'\');</script>');
			?>
			<?if($bEditProperty):?>
				<table><tbody><tr><td>
			<?endif;?>
		<?endif;?>
		<div class="aspro_property_regionphone_item<?=($bAdminList ? ' aspro_property_regionphone_item--admlistedit' : '')?>">
			<div class="wrapper">
				<div class="inner_wrapper">
					<div class="inner">
						<div class="value_wrapper">
							<input type="text" name="<?=$phoneName?>" value="<?=htmlspecialcharsbx($phone)?>" maxlength="255" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('MAX_REGION_PHONE_PHONE_TITLE'))?>" title="<?=htmlspecialcharsbx(Loc::getMessage('MAX_REGION_PHONE_PHONE_TITLE'))?>" />
						</div>
					</div>
					<div class="inner">
						<div class="value_wrapper">
							<input type="text" name="<?=$hrefName?>" value="<?=htmlspecialcharsbx($href)?>" maxlength="255" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('MAX_REGION_PHONE_HREF_TITLE'))?>" title="<?=htmlspecialcharsbx(Loc::getMessage('MAX_REGION_PHONE_HREF_TITLE'))?>" />
						</div>
					</div><br />
					<div class="inner">
						<div class="value_wrapper">
							<div class="iconset_value" data-code="header_phones" title="<?=Loc::getMessage('MAX_REGION_PHONE_ICON_TITLE')?>"><div class="iconset_value_wrap"><?=Iconset::showIcon($icon)?></div><input type="hidden" value="<?=htmlspecialcharsbx($icon)?>" name="<?=$iconName?>"></div>
						</div>
					</div>
					<div class="inner">
						<div class="value_wrapper">
							<?if($bWithDescription):?>
								<input type="text" name="<?=$descriptionName?>" value="<?=htmlspecialcharsbx($description)?>" maxlength="255" placeholder="<?=htmlspecialcharsbx(Loc::getMessage('MAX_REGION_PHONE_DESCRIPTION_TITLE'))?>" title="<?=htmlspecialcharsbx(Loc::getMessage('MAX_REGION_PHONE_DESCRIPTION_TITLE'))?>" <?=($bEditProperty ? 'readonly disabled' : '')?> />
							<?else:?>
								<div class="description_disabled_note"><?=Loc::getMessage('MAX_REGION_PHONE_DESCRIPTION_FIELD_DISABLED_NOTE');?></div>
							<?endif;?>
						</div>
					</div>
					<?if(!$bEditProperty):?>
						<div class="remove" title="<?=Loc::getMessage('MAX_REGION_PHONE_DELETE_TITLE')?>"></div>
						<div class="drag" title="<?=Loc::getMessage('MAX_REGION_PHONE_DRAG_TITLE')?>"></div>
					<?endif;?>
				</div>
			</div>
		</div>
		<?if($bEditProperty):?>
			</td></tr></tbody></table>
		<?endif;?>
		<?

		return ob_get_clean();
	}

	public static function ConvertToDB($arProperty, $value){
		if (
			is_string($value['VALUE']) &&
			$value['VALUE']
		) {
			try {
				$value['VALUE'] = Json::decode($value['VALUE']);
			}
			catch (\Exception $e) {
			}
		}

		if(
			!is_array($value['VALUE']) ||
			!strlen($value['VALUE']['PHONE'])
		){
			return array(
				'VALUE' => '',
				'DESCRIPTION' => '',
			);
		}

		$value['VALUE'] = array(
			'ICON' => strlen($value['VALUE']['ICON']) ? $value['VALUE']['ICON'] : '',
			'PHONE' => strlen($value['VALUE']['PHONE']) ? $value['VALUE']['PHONE'] : '',
			'HREF' => strlen($value['VALUE']['HREF']) ? $value['VALUE']['HREF'] : '',
		);
		$value['VALUE'] = Json::encode($value['VALUE']);

		return $value;
	}

	public static function ConvertFromDB($arProperty, $value){
		if(!is_array($value['VALUE'])){
			$value['VALUE'] = strlen($value['VALUE']) ? $value['VALUE'] : '[]';
			
			try {
				$value['VALUE'] = Json::decode($value['VALUE']);
			}
			catch(\Exception $e) {
				$value['VALUE'] = [];
			}
		}

		if(
			!$value['VALUE'] ||
			!is_array($value['VALUE']) ||
			!strlen($value['VALUE']['PHONE'])
		){
			$value['VALUE'] = array(
				'ICON' => '',
				'PHONE' => '',
				'HREF' => '',
			);
		}

		return $value;
	}

	public static function getDefaultValue($arProperty){
		if(!is_array($arProperty['DEFAULT_VALUE'])){
			$defaultValue = strlen($arProperty['DEFAULT_VALUE']) ? $arProperty['DEFAULT_VALUE'] : '[]';

			try {
				$arProperty['DEFAULT_VALUE'] = Json::decode($defaultValue);
			}
			catch(\Exception $e) {
				$arProperty['DEFAULT_VALUE'] = [];
			}
		}

		if(
			!$arProperty['DEFAULT_VALUE'] ||
			!is_array($arProperty['DEFAULT_VALUE']) ||
			!strlen($arProperty['DEFAULT_VALUE']['PHONE'])
		){
			$arProperty['DEFAULT_VALUE'] = array(
				'ICON' => '',
				'PHONE' => '',
				'HREF' => '',
			);
		}

		return $arProperty['DEFAULT_VALUE'];
	}

	public static function getModuleId(){
		return Solution::moduleID;
	}
}

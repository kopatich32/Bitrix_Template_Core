<?
namespace Aspro\Max\Property;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Sale\Location\LocationTable,
	Bitrix\Main\UI\Extension,
	CMax as Solution;

Loc::loadMessages(__FILE__);

class RegionLocation {
	protected static $cache;

	public static function OnIBlockPropertyBuildList(){
		return array(
			'PROPERTY_TYPE' => 'S',
			'USER_TYPE' => 'SAsproMaxRegionLocation',
			'DESCRIPTION' => Loc::getMessage('MAX_REGION_LOCATION_PROP_TITLE'),
			'PrepareSettings' => array(__CLASS__, 'PrepareSettings'),
			'GetSettingsHTML' => array(__CLASS__, 'GetSettingsHTML'),
			'GetAdminListViewHTML' => array(__CLASS__, 'GetAdminListViewHTML'),
			'GetPropertyFieldHtml' => array(__CLASS__, 'GetPropertyFieldHtml'),
		);
	}

	protected static function init() {
		if (!isset(static::$cache)) {
			static::$cache = [
				'LOCATIONS' => [],
				'ITEMS' => [],
			];

			$moduleID = self::getModuleId();

			$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/'.$moduleID.'/sort/Sortable.js');

			Extension::load('ui.forms');

			\CJSCore::RegisterExt('regionlocation', array(
				'js' => '/bitrix/js/'.$moduleID.'/property/regionlocation.js',
				'css' => '/bitrix/css/'.$moduleID.'/property/regionlocation.css',
				'lang' => '/bitrix/modules/'.$moduleID.'/lang/'.LANGUAGE_ID.'/lib/property/regionlocation.php',
			));

			\CJSCore::Init(['regionlocation']);
		}
	}

	protected static function getLocation($id) {
		if (!isset(static::$cache['LOCATIONS'][$id])) {
			$arLocation = [];

			if (Loader::includeModule('sale')) {
				$rsLoc = LocationTable::getList([
					'order' => ['PARENTS.TYPE_ID' => 'desc'],
					'filter' => [
						'=ID' => $id,
						'=NAME.LANGUAGE_ID' => LANGUAGE_ID,
						'=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
					],
					'select' => [
						'ID',
						'CODE',
						'CITY_NAME' => 'NAME.NAME',
						'TYPE_ID',
						'TYPE_CODE' => 'TYPE.CODE',
						'PARENTS.ID',
						'PARENTS.NAME',
					],
				]);
				while ($loc = $rsLoc->fetch()) {
					if (!$arLocation) {
						$arLocation = [
							'ID' => $loc['ID'],
							'CODE' => $loc['CODE'],
							'CITY_NAME' => $loc['CITY_NAME'],
							'TYPE_ID' => $loc['TYPE_ID'],
							'TYPE_CODE' => $loc['TYPE_CODE'],
							'PARENTS' => [],
						];
					}

					if (
						$loc['SALE_LOCATION_LOCATION_PARENTS_ID'] &&
						$loc['SALE_LOCATION_LOCATION_PARENTS_NAME_NAME'] &&
						!$arLocation['PARENTS'][$loc['SALE_LOCATION_LOCATION_PARENTS_ID']] &&
						$loc['SALE_LOCATION_LOCATION_PARENTS_ID'] != $loc['ID']
					) {
						$arLocation['PARENTS'][$loc['SALE_LOCATION_LOCATION_PARENTS_ID']] = [
							'ID' => $loc['SALE_LOCATION_LOCATION_PARENTS_ID'],
							'NAME' => $loc['SALE_LOCATION_LOCATION_PARENTS_NAME_NAME'],
						];
					}
				}
			}

			static::$cache['LOCATIONS'][$id] = $arLocation;
		}

		return static::$cache['LOCATIONS'][$id];
	}

	public static function PrepareSettings($arFields) {
		$arFields['SMART_FILTER'] = $arFields['SEARCHABLE'] = 'N';
		$arFields['MULTIPLE_CNT'] = 1;

		if (
			!isset($arFields['USER_TYPE_SETTINGS']) ||
			!is_array($arFields['USER_TYPE_SETTINGS'])
		) {
			$arFields['USER_TYPE_SETTINGS'] = [];
		}

		if (isset($arFields['USER_TYPE_SETTINGS']['USE_EXTENDED_LOCATIONS'])) {
			$arFields['USER_TYPE_SETTINGS']['USE_EXTENDED_LOCATIONS'] = $arFields['USER_TYPE_SETTINGS']['USE_EXTENDED_LOCATIONS'] === 'Y' ? 'Y' : 'N';
		}
		else {
			$arFields['USER_TYPE_SETTINGS']['USE_EXTENDED_LOCATIONS'] = 'N';
		}

		return $arFields;
	}

	public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields){
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
				'MULTIPLE_CNT' => '1',
            ),
        );

		return '';
	}

	public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName){
		$bAdminList = $strHTMLControlName['MODE'] === 'iblock_element_admin';
		$bMultiple = $arProperty['MULTIPLE'] === 'Y';
		$bWithDescription = $arProperty['WITH_DESCRIPTION'] === 'Y';

		$description = $value['DESCRIPTION'];
		$descriptionName = str_replace('VALUE', 'DESCRIPTION', $strHTMLControlName['VALUE']);
		$value = intval($value['VALUE'] ?: $arProperty['DEFAULT_VALUE']) ?: '';
		$valueName = $strHTMLControlName['VALUE'];

		static::init();
		$arLocation = static::getLocation($value);

		ob_start();
		?>
		<div class="aspro_property_regionlocation_item aspro_property_regionlocation_item--admlistview">
			<div class="wrapper">
				<div class="inner_wrapper">
					<div class="inner">
						<?if ($arLocation):?>
							<div class="location-name"><?=$arLocation['CITY_NAME']?></div>
							<div class="location-path">
								<?=implode(', ', array_column($arLocation['PARENTS'], 'NAME'))?>
							</div>
						<?endif;?>
					</div>
				</div>
			</div>
		</div>
		<?

		return ob_get_clean();
	}

	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName){
		$bAdminList = $strHTMLControlName['MODE'] === 'iblock_element_admin';
		$bEditProperty = $strHTMLControlName['MODE'] === 'EDIT_FORM';
		$bDetailPage = $strHTMLControlName['MODE'] === 'FORM_FILL';
		$bMultiple = $arProperty['MULTIPLE'] === 'Y';
		$bWithDescription = $arProperty['WITH_DESCRIPTION'] === 'Y';

		$description = $value['DESCRIPTION'];
		$descriptionName = str_replace('VALUE', 'DESCRIPTION', $strHTMLControlName['VALUE']);
		$value = intval($value['VALUE'] ?: $arProperty['DEFAULT_VALUE']) ?: '';
		$valueName = $strHTMLControlName['VALUE'];

		static::init();
		$arLocation = static::getLocation($value);

		if ($bAdminList ){
			preg_match('/FIELDS\[([\D]+)(\d+)\]\[PROPERTY_'.$arProperty['ID'].'\]\[([^\]]*)\]\[VALUE\]/', $strHTMLControlName['VALUE'], $arMatch);
			$elementType = $arMatch[1];
			$elementId = $arMatch[2];
			$valueId = $arMatch[3];
			$tableId = 'tb'.md5($elementType.$elementId.':'.$arProperty['ID']);

			if (!$valueId) {
				return '';
			}
		}
		else {
			preg_match('/PROP\['.$arProperty['ID'].'\]\[([^\]]*)\]\[VALUE\]/', $strHTMLControlName['VALUE'], $arMatch);
			$valueId = $arMatch[1];
			if ($bEditProperty) {
				$tableId = 'form_content';
			}
			else {
				$tableId = 'tb'.md5(htmlspecialcharsbx('PROP['.$arProperty['ID'].']'));
			}
		}

		ob_start();
		?>
		<?if($bAdminList ? !in_array($elementId, static::$cache['ITEMS']) : !in_array($arProperty['ID'], static::$cache['ITEMS'])):?>
			<?
			if($bAdminList){
				static::$cache['ITEMS'][] = $elementId;
			}
			else{
				static::$cache['ITEMS'][] = $arProperty['ID'];
			}

			$GLOBALS['APPLICATION']->AddHeadString('<script>BX.ready(function() {new JRegionLocation(\''.$tableId.'\', '.\CUtil::PhpToJSObject($arProperty, false, true).');});</script>');
			?>
			<?if($bEditProperty):?>
				<table><tbody><tr><td>
			<?else:?>
				<?if ($bMultiple):?>
					<div class="adm-info-message-wrap">
						<div class="adm-info-message"><?=Loc::getMessage('MAX_REGION_LOCATION_MAIN_CITY_NOTE')?></div>
					</div>
					<input type="button" class="button aspro_property_regionlocation_btn--multi" value="<?=Loc::getMessage('MAX_REGION_LOCATION_BTN_MULTI_EDIT')?>" />
					</td></tr><tr><td>
				<?endif;?>
			<?endif;?>
		<?endif;?>
		<div class="aspro_property_regionlocation_item <?=($bMultiple ? 'aspro_property_regionlocation_item--multiple ' : '')?>aspro_property_regionlocation_item--<?=($bAdminList ? 'admlistedit' : ($bDetailPage ? 'detail' : 'propedit'))?>">
			<div class="wrapper">
				<div class="inner_wrapper">
					<div class="inner">
						<div class="value_wrapper">
							<div class="location-container adm-location-popup-wrap">
								<div class="location-container__col">
									<input class="location-text__hidden" type="hidden" name="<?=$valueName?>" value="<?=$value?>" />
									<div class="location-text">
										<input class="location-text__input" type="text" name="search" placeholder="<?=Loc::getMessage('MAX_REGION_LOCATION_INPUT_PLACEHOLDER')?>" value="<?=$arLocation['CITY_NAME']?>" autocomplete="off" />
										<div class="location-path">
											<?if ($arLocation):?>
												<?=implode(', ', array_column($arLocation['PARENTS'], 'NAME'))?>
											<?endif;?>
										</div>
									</div>
								</div>

								<div class="location-results hidden">
									<div class="results__table"></div>
								</div>
							</div>
						</div>
					</div>
					<?if(!$bEditProperty):?>
						<div class="remove" title="<?=Loc::getMessage('MAX_REGION_LOCATION_DELETE_TITLE')?>"></div>
						<?if ($bMultiple):?>
							<div class="drag" title="<?=Loc::getMessage('MAX_REGION_LOCATION_DRAG_TITLE')?>"></div>
						<?endif;?>
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

	public static function getModuleId(){
		return Solution::moduleID;
	}
}

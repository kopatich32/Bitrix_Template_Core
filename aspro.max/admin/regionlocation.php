<?
use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	CMax as Solution;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$APPLICATION->RestartBuffer();
$GLOBALS['APPLICATION']->ShowAjaxHead();

Loc::loadMessages(__FILE__);

$errorMessage = '';
$arResult = array(
	'error' => &$errorMessage,
);

$value = $_REQUEST['value'] ?? [];
$rand = random_int(1, 10000);

$moduleId = Solution::moduleID;
$RIGHT = $APPLICATION->GetGroupRight($moduleID);
if ($RIGHT >= 'R') {
	// include required modules
	foreach (
		array(
			'fileman',
			$moduleId,
		) as $moduleId
	) {
		if(!Loader::includeModule($moduleId)){
			$errorMessage = Loc::getMessage('REGION_LOCATION_ERROR_MODULE_NOT_INCLUDED', array('#MODULE_NAME#' => $moduleId));
		}
	}

	if (!$errorMessage) {
		$value = array_filter(array_unique($value), function($id) {
			return $id > 0;
		});

		if (!isset($_REQUEST['REGION_LOCATION'])) {
			$_REQUEST['REGION_LOCATION'] = [];
		}
		if (!isset($_REQUEST['REGION_LOCATION']['L'])) {
			$_REQUEST['REGION_LOCATION']['L'] = [];
		}

		$_REQUEST['REGION_LOCATION']['L'] = implode(':', array_merge((array)$_REQUEST['REGION_LOCATION']['L'], (array)$value));
	}
}
else {
	$errorMessage = Loc::getMessage('REGION_LOCATION_ERROR_NO_RIGHTS_FOR_VIEWING');
}
?>

<?// normal request?>
<?if(!strlen($errorMessage)):?>
	<div id="regionlocation-<?=$rand?>" class="regionlocation">
		<?
		$GLOBALS['APPLICATION']->IncludeComponent(
			"bitrix:sale.location.selector.system", "",
			[
				"ENTITY_PRIMARY" => 0,
				"LINK_ENTITY_NAME" => "Bitrix\Sale\Location\GroupLocation",
				"INPUT_NAME" => 'REGION_LOCATION',
				"SELECTED_IN_REQUEST" => [
					'L' => isset($_REQUEST['REGION_LOCATION']['L']) ? explode(':', $_REQUEST['REGION_LOCATION']['L']) : false
				]
			],
			false
		);
		?>
	</div>
<?else:?>
	<?\CAdminMessage::ShowMessage($errorMessage);?>
<?endif;?>

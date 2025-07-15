<?
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);
define('BX_PUBLIC_MODE', 1);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use \Bitrix\Main\Localization\Loc;

if (
	!check_bitrix_sessid() ||
	!\Bitrix\Main\Loader::includeModule('iblock') ||
	!\Bitrix\Main\Loader::includeModule('catalog') ||
	!\Bitrix\Main\Loader::includeModule('aspro.max')
)
	return;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);
$action = $request->get('action');
if($action){
	if($action === 'init' || $action === 'save'){
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_js.php');

		// AddEventHandler('catalog', 'OnCondCatControlBuildList', array('\Aspro\Max\Property\CustomFilter\CondCtrl', 'GetControlDescr'));
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->addEventHandlerCompatible(
			'catalog',
			'OnCondCatControlBuildList',
			array(
				'\Aspro\Max\Property\CustomFilter\CondCtrl',
				'GetControlDescr'
			)
		);

		$ids = $request->get('ids');
		$success = false;

		if(!empty($ids) && is_array($ids)){
			$condTree = new CCatalogCondTree();
			$success = $condTree->Init(
				BT_COND_MODE_DEFAULT,
				BT_COND_BUILD_CATALOG,
				array(
					'FORM_NAME' => $ids['form'],
					'CONT_ID' => $ids['container'],
					'JS_NAME' => $ids['treeObject']
				)
			);
		}

		if($success){
			if($action === 'init'){
				try{
					$condition = \Bitrix\Main\Web\Json::decode($request->get('condition'));
				}
				catch (Exception $e){
					$condition = array();
				}

				$condTree->Show($condition);
			}
			elseif($action === 'save'){
				$result = $condTree->Parse();

				$GLOBALS['APPLICATION']->RestartBuffer();
				echo \Bitrix\Main\Web\Json::encode($result);
			}
		}

		\CMain::FinalActions();
		die();
	}
	else{
		Loc::loadMessages(__FILE__);

		$arResult = array();

		if(check_bitrix_sessid() && $request->isPost()){
			if($action === 'get_crosssales_iblockfields'){
				$field = $request->get('field');
				$iblockId = (int)$request->get('iblockId');

				if(in_array($field, array('SECTION_ID', 'PARENT_SECTION_ID'))){

					if($field === 'SECTION_ID'){
						$arFields = array(
							'IBLOCK_SECTION_ID',
							'PARENT_IBLOCK_SECTION_ID',
						);
					}
					else{
						$arFields = array(
							$field,
						);
					}

					foreach($arFields as $field){
						$name = (strlen(Loc::getMessage('CUSTOM_FILTER_CONTROL_FIELD_NAME_'.$field)) ? Loc::getMessage('CUSTOM_FILTER_CONTROL_FIELD_NAME_'.$field) : $field);
						$arResult[] = array(
							'value' => $field,
							'label' => Loc::getMessage('CUSTOM_FILTER_CONTROL_CROSSALES_FIELD_PREFIX').' '.$name,
						);
					}
				}
			}
			elseif($action === 'get_crosssales_iblockprops'){
				$propertyId = (int)$request->get('propertyId');
				$iblockId = (int)$request->get('iblockId');
				if($propertyId > 0){
					$property = \Bitrix\Iblock\PropertyTable::getList(
						array(
							'filter' => array('=ID' => $propertyId),
							'select' => array(
								'ID',
								'PROPERTY_TYPE',
								'USER_TYPE',
								'USER_TYPE_SETTINGS'
							),
						)
					)->fetch();
					if($property){
						$arExcludeUserTypes = \Aspro\Max\Property\CustomFilter\CondCtrl::getCrossSalesExcludePropertyUserTypes();
						$properties = \Bitrix\Iblock\PropertyTable::getList(
							array(
								'filter' => array(
									'=IBLOCK_ID' => $iblockId,
									'PROPERTY_TYPE' => $property['PROPERTY_TYPE'],
								),
								'select' => array(
									'ID',
									'PROPERTY_TYPE',
									'CODE',
									'NAME',
									'USER_TYPE',
									'USER_TYPE_SETTINGS',
								),
							)
						);
						while($arProperty = $properties->fetch()){
							if(in_array($arProperty['USER_TYPE'], $arExcludeUserTypes)){
								continue;
							}

							$arResult[] = array(
								'value' => $arProperty['ID'],
								'label' => Loc::getMessage('CUSTOM_FILTER_CONTROL_CROSSALES_PROPERTY_PREFIX').' '.$arProperty['NAME'],
							);
						}
					}
				}
			}
		}

		$GLOBALS['APPLICATION']->RestartBuffer();
		header('Content-Type: application/json');
		echo Bitrix\Main\Web\Json::encode($arResult);
		die();
	}
}

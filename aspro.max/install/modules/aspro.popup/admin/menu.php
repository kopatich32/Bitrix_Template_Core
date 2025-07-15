<?
use Bitrix\Main\Localization\Loc;

AddEventHandler('main', 'OnBuildGlobalMenu', 'OnBuildGlobalMenuHandlerPopup');
function OnBuildGlobalMenuHandlerPopup(&$arGlobalMenu, &$arModuleMenu) {
	if (!defined('ASPRO_POPUP_MENU_INCLUDED')) {
		define('ASPRO_POPUP_MENU_INCLUDED', true);

		IncludeModuleLangFile(__FILE__);
		$moduleID = 'aspro.popup';
		$moduleAdminPath = str_replace('.', '/', $moduleID);

		$GLOBALS['APPLICATION']->SetAdditionalCss('/bitrix/css/'.$moduleAdminPath.'/menu.css');

		if ($GLOBALS['APPLICATION']->GetGroupRight($moduleID) >= 'R') {
			$arMenu = array(
				'menu_id' => 'global_menu_aspro_popup',
				'text' => Loc::getMessage('ASPRO_POPUP__MENU__ROOT_TEXT'),
				'title' => Loc::getMessage('ASPRO_POPUP__MENU__ROOT_TITLE'),
				'sort' => 1000,
				'items_id' => 'global_menu_aspro_popup_items',
				'icon' => 'imi-popup imi-popup--root',
				'items' => array(			   
					array(
						'text' => Loc::getMessage('ASPRO_POPUP__MENU__MODULE_UPDATE_TEXT'),
						'title' => Loc::getMessage('ASPRO_POPUP__MENU__MODULE_UPDATE_TITLE'),
						'sort' => 10,
						'url' => '/bitrix/admin/'.$moduleAdminPath.'/update_module.php?lang='.urlencode(LANGUAGE_ID),
						'more_url' => array(),
						'icon' => '',
						'page_icon' => '',
						'items_id' => 'menu_aspro_popup_update_module',
					),
					array(
						'text' => Loc::getMessage('ASPRO_POPUP__MENU__MODULE_GROUP_RIGHTS_TEXT'),
						'title' => Loc::getMessage('ASPRO_POPUP__MENU__MODULE_GROUP_RIGHTS_TITLE'),
						'sort' => 40,
						'url' => '/bitrix/admin/'.$moduleAdminPath.'/group_rights.php?lang='.urlencode(LANGUAGE_ID),
						'icon' => 'learning_icon_groups',
						'page_icon' => 'pi_group_rights',
						'items_id' => 'group_rights',
					),
				),
			);

			if (!isset($arGlobalMenu['global_menu_aspro'])) {
				$arGlobalMenu['global_menu_aspro'] = array(
					'menu_id' => 'global_menu_aspro',
					'text' => Loc::getMessage('ASPRO_POPUP__MENU__GLOBAL_ASPRO_MENU_TEXT'),
					'title' => Loc::getMessage('ASPRO_POPUP__MENU__GLOBAL_ASPRO_MENU_TITLE'),
					'sort' => 1000,
					'items_id' => 'global_menu_aspro_items',
				);
			}

			$arGlobalMenu['global_menu_aspro']['items'][$moduleID] = $arMenu;
		}
	}
}
?>
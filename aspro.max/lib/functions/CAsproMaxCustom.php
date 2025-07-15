<?
namespace Aspro\Functions;

use Bitrix\Main\Application;
use Bitrix\Main\Web\DOM\Document;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\DOM\CssParser;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');

//user custom functions

if(!class_exists("CAsproMaxCustom"))
{
	class CAsproMaxCustom{
		const MODULE_ID = \CMax::moduleID;

		public static function OnBeforeUserUpdateHandler($arFields)
		{
			if ($arFields['EMAIL'] === 'demo@aspro.ru' && $arFields['LOGIN'] === 'demo@aspro.ru' && !$GLOBALS['USER']->IsAdmin()) {
				global $APPLICATION;
				$APPLICATION->throwException(Loc::getMessage("USER_FORBIDDEN_UPDATE_PROFILE"));
				return false;
			}
		}


        public static function setPageDetail($pageDetailFile)
        {
            global $arTheme;
            $bSettedThemeValues = ((isset($_SESSION['THEME']) && $_SESSION['THEME']) && (isset($_SESSION['THEME'][SITE_ID]) && $_SESSION['THEME'][SITE_ID]));
            if (!$bSettedThemeValues) {
                unset($_COOKIE['current_page_detail']);
            }

            if ($_COOKIE['current_page_detail'] && $bSettedThemeValues && $arTheme['CATALOG_PAGE_DETAIL']) {
                if (in_array($_COOKIE['current_page_detail'], array_keys($arTheme['CATALOG_PAGE_DETAIL']['LIST']))) {
                    $pageDetailFile = $_COOKIE['current_page_detail'];
                }
            }
            return $pageDetailFile;
        }
	}
}?>
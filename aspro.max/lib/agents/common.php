<?
namespace Aspro\Max\Agents;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader;

use CMax as Solution;

Loc::loadMessages(__FILE__);

class Common{
	
	public static function update($ID, $arFields = []){
		\CAgent::Update($ID, $arFields);
	}

	public static function getByName($name){
		return \CAgent::GetList([], ['NAME' => $name])->Fetch();
	}
}

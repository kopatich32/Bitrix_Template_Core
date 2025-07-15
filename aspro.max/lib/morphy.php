<?
namespace Aspro\Max;

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Morphy {
	static public function castWord($word, $parameters) {
		if (Loader::includeModule('aspro.smartseo')) {
			$morphy = \Aspro\Smartseo\Morphy\Morphology::getInstance();
			$word = $morphy->castWord($word, array_filter(array_unique($parameters)));
		}

		return $word;
	}

	static public function getLocationAccusative($locationName) {
		return mb_convert_case(static::castWord($locationName, [Loc::getMessage('MORPHOLOGY_ACCUSATIV')]), MB_CASE_TITLE);
	}
}
<?
namespace Aspro\Max\Agents;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Main\Config\Option;

use CMax as Solution;

Loc::loadMessages(__FILE__);

class Brand{
    public static $alphabetFilter = '\\'.__CLASS__.'::getAlphabet(#IBLOCK_ID#);';

	public static function getInfo($name){
		return \CAgent::GetList([], ['NAME' => $name])->Fetch();
	}

	public static function getAlphabetAgentName($iblockID = 0){
        static $agentName;
        if (!$agentName) $agentName = str_replace('#IBLOCK_ID#', $iblockID, self::$alphabetFilter);
        return $agentName;
    }

	public static function addAphabet($agentName){
		$id = \CAgent::AddAgent(
			$agentName,
			Solution::moduleID,
			'N',
			60*60*24, // once a day
			'',
			'Y',
			\ConvertTimeStamp(time() + (60*1),'FULL'), //start after 10 minutes
			10);
		return $id;
	}
	
    public static function getAlphabet($iblockID = 0){
        if (!Loader::includeModule(Solution::moduleID)) return;

        $arFilterLetters = \Aspro\Max\Brand::getAlphabet($iblockID);

		$arSites = [];
		$rsSites = \CIBlock::GetSite($iblockID);
		while ($arSite = $rsSites->Fetch()) {
			$arSites[] = \htmlspecialchars($arSite["SITE_ID"]);
		}
		if ($arSites) {
			foreach ($arSites as $site) {
				Option::set(Solution::moduleID, 'FILTER_BRANDS_LETTERS', \serialize($arFilterLetters), $site);
			}
		}
		return '\\'.__METHOD__.'('.$iblockID.');';
	}
}

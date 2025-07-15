<?
namespace Aspro\Max\Agents;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Main\Config\Option;

use CMax as Solution;
use \Aspro\Max as SolutionLib;

Loc::loadMessages(__FILE__);

class DeleteDirectorySVG{
    public static $agentName = '\\'.__CLASS__.'::exec("#PATH#");';

	public static function getInfo($name)
	{
		return \CAgent::GetList([], ['NAME' => $name])->Fetch();
	}

	public static function getName($path = '')
	{
		if (!$path) {
			$path = SolutionLib\SvgSprite::$uploadPath;
		}

        return str_replace('#PATH#', $path, self::$agentName);
    }

	public static function add($agentName)
	{
		$id = \CAgent::AddAgent(
			$agentName,
			Solution::moduleID,
			'N',
			60*60*24, // once a day
			'',
			'Y',
			\ConvertTimeStamp(time() + (60*1),'FULL'), //start after 1 minute
			10);
		
		return $id;
	}
	
    public static function exec($path)
	{
        if (!Loader::includeModule(Solution::moduleID)) return;
        if (!$path) return;

		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'].$path);

		return '\\'.__METHOD__.'("'.$path.'");';
	}

	public static function check($name = '')
	{
		$agentName = self::getName($name);
		
		$arAgentInfo = self::getInfo($agentName);
		if ($arAgentInfo) {
			if ($arAgentInfo['ACTIVE'] === 'N') {
				SolutionLib\Agents\Common::update($arAgentInfo['ID'], ['ACTIVE' => 'Y']);
			}
		} else {
			self::add($agentName);
		}
	}
}

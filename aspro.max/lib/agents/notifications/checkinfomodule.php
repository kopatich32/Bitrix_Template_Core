<?

namespace Aspro\Max\Agents\Notifications;

use Bitrix\Main\Localization\Loc,
	CMax as Solution,
	CMaxTools as SolutionTools,
	Aspro\Max\Agents\Common;

Loc::loadMessages(__FILE__);

class CheckInfoModule
{
	const BUY_MODULE_LINK = "https://aspro.ru/shop/renewal.php?solution=".Solution::moduleID;
	const BUY_BX_LINK = "https://aspro.ru/shop/bitrix/update/";

	static $agentName = '\\'.__CLASS__.'::run();';
	static $arUpdateList = [];
	static $arInfoModuleAspro = [];

	public static function run()
	{
		static::$arUpdateList = SolutionTools::___1596018891(array(Solution::moduleID), LANGUAGE_ID);
		if (!empty(static::$arUpdateList)) {
			static::$arInfoModuleAspro = SolutionTools::___1596018809(static::$arUpdateList);
			if($message = static::getMessageBXEndLicense()){
				static::addNotifyBX($message);
			}

			if(!static::$arUpdateList['ERROR']){
				if($message = static::getMessageAsproEndLicense()){
					static::addNotifyAspro($message);
				}
			}
		}
		return '\\'.__METHOD__.'();';
	}

	protected static function getMessageBXEndLicense()
	{
		$message = '';
		if(static::checkClientBX() && !static::isDateActive(static::$arUpdateList['CLIENT'][0]['@']['DATE_TO'])){
			$dateBXExpired = str_replace('/', '.',static::$arUpdateList['CLIENT'][0]['@']['DATE_TO']);
			$message = Loc::getMessage('AG_BX_EXPIRED', array('#BUY_BX_LINK#' => static::BUY_BX_LINK,  '#DATE_BX_EXPIRED#' => $dateBXExpired));
		}

		return $message;
	}

	protected static function getMessageAsproEndLicense(){
		$message = '';
		if (static::checkClientBX() && !static::isDateActive(static::$arInfoModuleAspro['DATE_TO'])) {
			$dateAsproExpired  = str_replace('/', '.', static::$arInfoModuleAspro['DATE_TO']);
			$message = Loc::getMessage('AG_ASPRO_EXPIRED', array('#NAME_MODULE#' => Solution::moduleID, '#DATE_MODULE_EXPIRED#' => $dateAsproExpired, '#BUY_MODULE_LINK#' => static::BUY_MODULE_LINK));
			return $message;
		}
	}

	protected static function isDateActive($dateTo)
	{
		if (!empty($dateTo)) {
			return (strtotime($dateTo) + 86399) > time();
		}

		return false;
	}


	protected static function checkClientBX()
	{
		return (bool)static::$arUpdateList['CLIENT'];
	}
	
	protected static function addNotifyAspro($strNotify)
	{
		\CAdminNotify::Add(array(
			'MESSAGE' => $strNotify,
			'MODULE_ID' => Solution::moduleID,
			'TAG' => 'message_about_expiried_license_'.Solution::moduleID,
			'NOTIFY_TYPE' => \CAdminNotify::TYPE_ERROR,
		));
	}

	protected static function addNotifyBX($strNotify)
	{
		\CAdminNotify::Add(array(
			'MESSAGE' => $strNotify,
			'MODULE_ID' => Solution::moduleID,
			'TAG' => 'message_about_expiried_license_bx',
			'NOTIFY_TYPE' => \CAdminNotify::TYPE_ERROR,
		));
	}

	public static function add()
	{
		$arAgentInfo = Common::getByName(static::$agentName);
		if(!$arAgentInfo){
			\CAgent::AddAgent(
				static::$agentName,
				Solution::moduleID,
				'Y',
				2592000, //one month
				'',
				'Y',
				\ConvertTimeStamp(time() + (60 * 1), 'FULL'),
				10
			);
		}
	}
}

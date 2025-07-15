<?php

namespace Aspro\Popup;

use \Bitrix\Main\Localization\Loc;


class General
{
	const partnerName	= 'aspro';
	const solutionName	= 'popup';
	const moduleID		= self::partnerName . '.' . self::solutionName;
	const wizardID		= self::partnerName . ':' . self::solutionName;
	const moduleId = self::partnerName . '.' . self::solutionName;

	public static function checkAjaxRequest() {
		return (
			(
				isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
				strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
			) ||
			(strtolower($_REQUEST['ajax']) == 'y' || strtolower($_REQUEST['ajax_get']) == 'y')
		);
	}
}
<?
namespace Aspro\Max\Itemaction\Trait;

trait User {
	protected static $userId = false;
	protected static $bModified = true;

	public static function getUserId(): int {
		return static::$userId ?: $GLOBALS['USER']->GetID() ?: 0;
	}

	public static function setUserId(int $userId) {
		static::$bModified = true;
		static::$userId = $userId;
	}
}

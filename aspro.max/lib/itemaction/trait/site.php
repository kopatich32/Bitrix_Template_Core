<?
namespace Aspro\Max\Itemaction\Trait;

trait Site {
	protected static $siteId = '';
	protected static $bModified = true;

	public static function getSiteId(): string {
		return static::$siteId ?: SITE_ID;
	}

	public static function setSiteId(string $siteId) {
		static::$bModified = true;
		static::$siteId = trim($siteId);
	}
}

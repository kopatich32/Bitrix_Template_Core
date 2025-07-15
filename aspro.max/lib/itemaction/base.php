<?
namespace Aspro\Max\Itemaction;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

abstract class Base {
	abstract public static function getItems(): array;

	public static function getCount(): int {
		return count(static::getItems());
	}

	// abstract public static function getTitle(): string;
}

<?php
namespace Aspro\Max\Traits;

trait Serialize {
	public static function unserialize($data, array $arOptions = []) {
		if (!is_string($data)) return false;
		
		$arDefaultConfig = [
			'allowed_classes' => false,
		];
		$arConfig = array_merge($arDefaultConfig, $arOptions);

		return \unserialize($data, $arConfig);
	}
}
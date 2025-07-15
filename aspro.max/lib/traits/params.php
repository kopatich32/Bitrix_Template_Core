<?php
namespace Aspro\Max\Traits;

use \Bitrix\Main\IO;

use CMax as Solution;

trait Params {
	private static function getContentFromCatalogPage(): ?string
	{
		static $content;

		if ($content) return $content;

		$catalogPage = IO\Path::combine(\Bitrix\Main\Application::getDocumentRoot(),Solution::GetFrontParametrValue('CATALOG_PAGE_URL'),'index.php');

		if (!IO\File::isFileExists($catalogPage)) {
			return null;
		}

		$content = IO\File::getFileContents($catalogPage);

		return $content;
	}
		
	public static function getArrayValueFromCatalogPageByParamCode(string $code): ?array
	{
		if (!$content = self::getContentFromCatalogPage()) {
			return null;
		}

		$pattern = '/[\'"]'.$code.'[\'"]\s*=>\s*array\((.*?)\),/is';
		preg_match($pattern, $content, $matches);

		if (count($matches) < 2) {
			return null;
		}

		$props = preg_replace(['/\d+\s*=>\s*/', '/\s{2,}/', '/["\']/', '/,,/', '/,$/'], '', $matches[1]);

		if (strlen($props) < 1) {
			return null;
		}

		return explode(',', $props);
	}
	
	public static function getStringValueFromCatalogPageByParamCode(string $code): ?string
	{
		if (!$content = self::getContentFromCatalogPage()) {
			return null;
		}

		$pattern = '/[\'"]'.$code.'[\'"]\s*=>\s*[\'"](.+?)[\'"],/i';
		preg_match($pattern, $content, $matches);
		
		if (count($matches) < 2) {
			return null;
		}

		return $matches[1];
	}
}
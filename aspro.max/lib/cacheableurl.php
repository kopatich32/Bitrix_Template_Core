<?
namespace Aspro\Max;

use CMax as Solution,
CMaxRegionality as Regionality;

class CacheableUrl
{
	protected static ?\Bitrix\Main\HttpRequest $request;
	protected static $arParams;
	protected static $url;

	const OPTION_DELIMITER = ';';

	public static function initParams()
    {
		$arDefaultSolutionParams = [
			'arrFilter*',
			'set_filter',
			'del_filter',
			'sort',
			'order',
			'display',
			'linerow',
		];

		$siteId = defined('SITE_ID') ? SITE_ID : 's1';
		$tmp = \Bitrix\Main\Config\Option::get(Solution::moduleID, 'CATALOG_CACHEABLE_PARAMS', '', $siteId);
		static::$arParams = array_unique(
			array_diff(
				array_map(
					function($param) {
						return trim($param);
					},
					array_merge(
						$arDefaultSolutionParams,
						explode(static::OPTION_DELIMITER, $tmp)
					)
				)
			)
		);
    }

    public static function getParams(): array
    {
		if (!isset(static::$arParams)) {
			static::initParams();
        }

        return static::$arParams;
    }

    public static function addParams(string | array $arParams): array
    {
		$arParams = is_array($arParams) ? $arParams : explode(',', $arParams);

		static::$arParams = array_merge(static::getParams(), $arParams);

		// clear last cached url value
		static::$url = null;

		return static::$arParams;
    }

	public static function addSmartFilterNameParam($filterName = null): array
	{
		$filterName = (string)($filterName ?? '');
		if (
			$filterName === ''
			|| !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $filterName)
		)
		{
			$filterName = 'arrFilter';
		}

		if (strlen($filterName)) {
			return static::addParams($filterName.'*');
		}

		return static::$arParams;
	}

    public static function removeParams(string | array $arParams): array
    {
		$arParams = is_array($arParams) ? $arParams : explode(',', $arParams);

		static::$arParams = array_filter(
			static::getParams(),
			function($param) use ($arParams) {
				return !in_array($param, $arParams);
			}
		);

		// clear last cached url value
		static::$url = null;

		return static::$arParams;
    }

    public static function get()
    {
		if (!isset(static::$url)) {
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			/**
			 * @var \Bitrix\Main\HttpRequest static::$request
			 */
			static::$request = $context->getRequest();

			// available parameters
			$arParams = static::getParams();

			// get current url
			// do not use $GLOBALS['APPLICATION']->GetCurPageParam()
			$currentUrl = static::$request->getRequestUri();

			// search landing with url condition
			// $currentUrl = static::defineLandingSearchCanonicalUrl() ?? $currentUrl;

			$uri = new \Bitrix\Main\Web\Uri($currentUrl);

			// delete bitrix system parameters
			$arSystemParameters = array_merge(\Bitrix\Main\HttpRequest::getSystemParameters(), ['bxajaxid']);
			$uri->deleteParams($arSystemParameters);

			// collect query parameters
			$arRealUrlParams = [];
			parse_str($uri->getQuery(), $arRealUrlParams);

			// prepare patterns*
			$patterns = [];
			foreach ($arParams as $param) {
				if (strpos($param, '*') !== false) {
					$patterns[] = str_replace('\*', '.*', preg_quote($param, '/'));
				}
			}

			// check each query parameter
			$arParams2Delete = [];
			foreach ($arRealUrlParams as $param => $val) {
				if (!in_array($param, $arParams)) {
					if ($patterns) {
						if (!preg_match('/^'.implode('|', $patterns).'$/', $param)) {
							$arParams2Delete[] = $param;
						}
					}
					else {
						$arParams2Delete[] = $param;
					}
				}
			}

			// delete parameters
			if ($arParams2Delete) {
				$uri->deleteParams($arParams2Delete);
			}

			static::$url = $uri->getUri();
		}

        return static::$url;
    }

	protected static function defineLandingSearchCanonicalUrl(): ?string
	{
		if (
			!empty($_REQUEST['ls'])
			&& ($landingID = intval($_REQUEST['ls'])) > 0
			&& isset($_REQUEST['q'])
		) {
			// get one landing
			$searchQuery = $_REQUEST['q'];
			$oSearchQuery = new SearchQuery($searchQuery);

			$arLandingsFilter = [
				'ACTIVE' => 'Y',
				'ID' => $landingID,
			];

			// current region
			$arRegion = Regionality::getCurrentRegion();
			if ($arRegion) {
				// filter landings by property LINK_REGION (empty or ID of current region)
				$arLandingsFilter[] = array(
					'LOGIC' => 'OR',
					array('PROPERTY_LINK_REGION' => false),
					array('PROPERTY_LINK_REGION' => $arRegion['ID']),
				);
			}

			$arLanding = $oSearchQuery->getLandings(
				array(),
				$arLandingsFilter,
				false,
				false,
				array(
					'ID',
					'IBLOCK_ID',
					'PROPERTY_URL_CONDITION',
				),
				true
			);

			if ($arLanding) {
				if (strlen($arLanding['PROPERTY_URL_CONDITION_VALUE'])) {
					$urlCondition = ltrim(trim($arLanding['PROPERTY_URL_CONDITION_VALUE']), '/');
					$canonicalUrl = '/'.$urlCondition;
					$currentUrl = $GLOBALS['APPLICATION']->GetCurPageParam(false);
					if (str_starts_with($currentUrl, $canonicalUrl)) {
						return $currentUrl;
					}
				}
			}
		}

		return null;
	}
}

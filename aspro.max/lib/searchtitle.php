<?
namespace Aspro\Max;

use Bitrix\Main\Loader,
    CMax as Solution, 
    CMaxCache as SolutionCache;

class SearchTitle {
    const TYPE_ALL = 'all';
    const TYPE_CATALOG = 'catalog';
    const COOKIE_NAME_TYPE = 'searchtitle_type';

    static protected $config = [];

    static public function isNeed2ShowWhere() {
        return Solution::GetFrontParametrValue('SEARCHTITLE_SHOW_WHERE') !== 'N';
    }

    static public function getType() :string {
        static $type;

        if (!isset($type)) {
            $type = self::TYPE_CATALOG;

            if (self::isNeed2ShowWhere()) {
                if (Solution::IsSearchPage()) {
                    $type = self::TYPE_ALL;
                }
                elseif (
                    Solution::IsCatalogPage()
                    && isset($_REQUEST['q'])
                ) {
                    $type = self::TYPE_CATALOG;
                }
                elseif (
                    isset($_COOKIE[self::COOKIE_NAME_TYPE])
                    && $_COOKIE[self::COOKIE_NAME_TYPE] === self::TYPE_ALL
                ) {
                    $type = self::TYPE_ALL;
                }

                if (
                    isset($_REQUEST['type']) &&
                    isset($_REQUEST['q']) &&
                    (
                        Solution::IsSearchPage() ||
                        Solution::IsCatalogPage()
                    )
                ) {
                    if ($_REQUEST['type'] === self::TYPE_ALL) {
                        $type = self::TYPE_ALL;
                    }
                    else {
                        $type = self::TYPE_CATALOG;
                    }
                }
            }
        }

        return $type;
    }

    static public function getConfig(string $type = '') :array {
        if (!strlen($type)) {
            $type = self::getType();
        }

        if (!isset(self::$config[$type])) {
            $topCount = intval(trim(Solution::GetFrontParametrValue('SEARCHTITLE_TOP_COUNT')));
            $topCount = $topCount >= 0 ? $topCount : intval(Solution::$arParametrsList['MAIN']['OPTIONS']['SEARCHTITLE_TOP_COUNT']['DEFAULT']);

            self::$config[$type] = [
                'PAGE' => Solution::GetFrontParametrValue($type === self::TYPE_ALL ? 'SEARCH_PAGE_URL' : 'CATALOG_PAGE_URL'),
                'ORDER' => Solution::GetFrontParametrValue('SEARCHTITLE_ORDER'),
                'USE_LANGUAGE_GUESS' => Solution::GetFrontParametrValue('SEARCHTITLE_USE_LANGUAGE_GUESS'),
                'CHECK_DATES' => Solution::GetFrontParametrValue('SEARCHTITLE_CHECK_DATES'),
                'TOP_COUNT' => $topCount,
                'CATEGORY_0_TITLE' => 'ALL',
                'CATEGORY_0' => [],
                'CONVERT_CURRENCY' => Solution::GetFrontParametrValue('CONVERT_CURRENCY'),
			    'CURRENCY_ID' => Solution::GetFrontParametrValue('CURRENCY_ID'),
                'PRICE_VAT_INCLUDE' => Solution::GetFrontParametrValue('PRICE_VAT_INCLUDE'),
            ];

            self::setPriceConfig($type);

            $cats = $type === self::TYPE_ALL ? Solution::GetFrontParametrValue('SEARCHTITLE_SITE_CATS') : Solution::GetFrontParametrValue('SEARCHTITLE_CATALOG_CATS');
            $arCats = explode(',', $cats);
            if ($arCats) {
                $bAllIblocks = in_array('iblock_all', $arCats);
                $bAllBlogs = in_array('blog_all', $arCats);
                $bAllForums = in_array('forum_all', $arCats);

                foreach ($arCats as $cat) {
                    if ($cat === 'main') {
                        self::$config[$type]['CATEGORY_0'][] = 'main';
                        self::$config[$type]['CATEGORY_0_main'] = [];
                    }
                    elseif (strpos($cat, 'iblock_') !== false) {
                        if ($bAllIblocks) {
                            if (SolutionCache::$arIBlocks[SITE_ID]) {
                                foreach (SolutionCache::$arIBlocks[SITE_ID] as $iblockType => $arIblockCodes) {
                                    if ($arIblockCodes) {
                                        foreach ($arIblockCodes as $iblockCode => $iblocksIDs) {
                                            if (!in_array('iblock_'.$iblockType, self::$config[$type]['CATEGORY_0'])){
                                                self::$config[$type]['CATEGORY_0'][] = 'iblock_'.$iblockType;
                                            }
            
                                            if (!isset(self::$config[$type]['CATEGORY_0_iblock_'.$iblockType])) {
                                                self::$config[$type]['CATEGORY_0_iblock_'.$iblockType] = [];
                                            }
            
                                            if (!in_array('all', self::$config[$type]['CATEGORY_0_iblock_'.$iblockType])) {
                                                self::$config[$type]['CATEGORY_0_iblock_'.$iblockType][] = 'all';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        else {
                            $iblockId = preg_replace('/^iblock_/', '', $cat);
                            if (intval($iblockId) > 0) {
                                if ($arIblock = SolutionCache::$arIBlocksInfo[$iblockId]) {
                                    $iblockType = $arIblock['IBLOCK_TYPE_ID'];

                                    if (!in_array('iblock_'.$iblockType, $arCats)) {
                                        if (!in_array('iblock_'.$iblockType, self::$config[$type]['CATEGORY_0'])){
                                            self::$config[$type]['CATEGORY_0'][] = 'iblock_'.$iblockType;
                                        }
    
                                        if (!isset(self::$config[$type]['CATEGORY_0_iblock_'.$iblockType])) {
                                            self::$config[$type]['CATEGORY_0_iblock_'.$iblockType] = [];
                                        }
    
                                        if (!in_array($iblockId, self::$config[$type]['CATEGORY_0_iblock_'.$iblockType])) {
                                            self::$config[$type]['CATEGORY_0_iblock_'.$iblockType][] = $iblockId;
                                        }
                                    }
                                }
                            }
                            else {
                                $iblockType = $iblockId;
                                if ($arIblockCodes = SolutionCache::$arIBlocks[SITE_ID][$iblockType]) {
                                    foreach ($arIblockCodes as $iblockCode => $iblocksIDs) {
                                        if (!in_array('iblock_'.$iblockType, self::$config[$type]['CATEGORY_0'])){
                                            self::$config[$type]['CATEGORY_0'][] = 'iblock_'.$iblockType;
                                        }

                                        if (!isset(self::$config[$type]['CATEGORY_0_iblock_'.$iblockType])) {
                                            self::$config[$type]['CATEGORY_0_iblock_'.$iblockType] = [];
                                        }

                                        if (!in_array('all', self::$config[$type]['CATEGORY_0_iblock_'.$iblockType])) {
                                            self::$config[$type]['CATEGORY_0_iblock_'.$iblockType][] = 'all';
                                        }
                                    }
                                }
                            }
                        }
                    }
                    elseif (strpos($cat, 'blog_') !== false) {
                        if ($bAllBlogs) {
                            if (!in_array('blog', self::$config[$type]['CATEGORY_0'])){
                                self::$config[$type]['CATEGORY_0'][] = 'blog';
                            }

                            if (!isset(self::$config[$type]['CATEGORY_0_blog'])) {
                                self::$config[$type]['CATEGORY_0_blog'] = [];
                            }

                            if (!in_array('all', self::$config[$type]['CATEGORY_0_blog'])) {
                                self::$config[$type]['CATEGORY_0_blog'][] = 'all';
                            }
                        }
                        else {
                            $blogId = str_replace('blog_', '', $cat);
                            if ($blogId) {
                                if (!in_array('blog', self::$config[$type]['CATEGORY_0'])){
                                    self::$config[$type]['CATEGORY_0'][] = 'blog';
                                }

                                if (!isset(self::$config[$type]['CATEGORY_0_blog'])) {
                                    self::$config[$type]['CATEGORY_0_blog'] = [];
                                }

                                if (!in_array($blogId, self::$config[$type]['CATEGORY_0_blog'])) {
                                    self::$config[$type]['CATEGORY_0_blog'][] = $blogId;
                                }
                            }
                        }
                    }
                    elseif (strpos($cat, 'forum_') !== false) {
                        if ($bAllForums) {
                            if (!in_array('forum', self::$config[$type]['CATEGORY_0'])){
                                self::$config[$type]['CATEGORY_0'][] = 'forum';
                            }

                            if (!isset(self::$config[$type]['CATEGORY_0_forum'])) {
                                self::$config[$type]['CATEGORY_0_forum'] = [];
                            }

                            if (!in_array('all', self::$config[$type]['CATEGORY_0_forum'])) {
                                self::$config[$type]['CATEGORY_0_forum'][] = 'all';
                            }
                        }
                        else {
                            $forumId = str_replace('forum_', '', $cat);
                            if ($forumId) {
                                if (!in_array('forum', self::$config[$type]['CATEGORY_0'])){
                                    self::$config[$type]['CATEGORY_0'][] = 'forum';
                                }

                                if (!isset(self::$config[$type]['CATEGORY_0_forum'])) {
                                    self::$config[$type]['CATEGORY_0_forum'] = [];
                                }

                                if (!in_array($forumId, self::$config[$type]['CATEGORY_0_forum'])) {
                                    self::$config[$type]['CATEGORY_0_forum'][] = $forumId;
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach(\GetModuleEvents(Solution::moduleID, 'OnAsproGetSearchTitkeConfig', true) as $arEvent) // event for manipulation config
			\ExecuteModuleEventEx($arEvent, array($type, &self::$config[$type]));

        return self::$config[$type];
    }

    static protected function setPriceConfig(string $type) {
        if (
            $GLOBALS['arRegion'] 
            && $GLOBALS['arRegion']['LIST_PRICES'] 
            && !in_array('component', $GLOBALS['arRegion']['LIST_PRICES'])
        ) {
            self::$config[$type]['PRICE_CODE'] = array_keys($GLOBALS['arRegion']['LIST_PRICES']);
        }
        else {
            $arOptionPrices = strlen(trim(Solution::GetFrontParametrValue('PRICES_TYPE'))) 
                ? explode(',', Solution::GetFrontParametrValue('PRICES_TYPE'))
                : false;
            if ($arOptionPrices) {
                self::$config[$type]['PRICE_CODE'] = $arOptionPrices;
            }
        }
    }

    static public function getPageTitle(string $pageLocalPath, string $siteId = '') :string {
        $title = '';

        $pageLocalPath = trim($pageLocalPath);

        $siteId = trim($siteId);
        if (!$siteId) {
            $siteId = SITE_ID;
        }

        if (
            strlen($pageLocalPath)
            && strlen($siteId)
        ) {
            Loader::includeModule('fileman');

            $io = \CBXVirtualIo::GetInstance();
            $DOC_ROOT = \CSite::GetSiteDocRoot($siteId);

            $pageLocalPath = '/'.ltrim($pageLocalPath, '/');
            $basename = basename($pageLocalPath);

            if (preg_match('/^index\./i', $basename)) {
                $pageLocalPath = str_replace($basename, '', $pageLocalPath);
            }

            $bDir = preg_match('/\/$/', $pageLocalPath);
        
            if ($bDir) {
                if($io->FileExists($DOC_ROOT.$pageLocalPath.'.section.php')){
                    $sSectionName = '';
                    @include($io->GetPhysicalName($DOC_ROOT.$pageLocalPath.'.section.php'));
                    $title = $sSectionName;
                }

                if (!strlen($title)) {
					$title = $GLOBALS['APPLICATION']->GetDirProperty('title', [$siteId, $pageLocalPath]);
				}
            }

            if (!strlen($title)) {
				$pageLocalPathOrig = $bDir ? $pageLocalPathOrig.'index.php' : $pageLocalPathOrig;

				if($io->FileExists($DOC_ROOT.$pageLocalPathOrig)){
					$fileContent = @file_get_contents($DOC_ROOT.$pageLocalPathOrig);
					if ($fileContent) {
						$arPageSlice = ParseFileContent($fileContent);
						if ($arPageSlice && is_array($arPageSlice)) {
                            $title = trim($arPageSlice['TITLE']);

							if (
                                !strlen($title)
                                && $arPageSlice['PROPERTIES']
                            ) {
								$title = $arPageSlice['PROPERTIES']['title'];
							}
						}
					}
				}
			}
        }

        return $title;
    }
}
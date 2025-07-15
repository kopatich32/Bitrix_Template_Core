<?php

CModule::IncludeModule('main');
CModule::IncludeModule('iblock');

set_time_limit(0);

if (!function_exists('ClearAllSitesCacheComponents')) {
    function ClearAllSitesCacheComponents($arComponentsNames)
    {
        if ($arComponentsNames && is_array($arComponentsNames)) {
            global $CACHE_MANAGER;
            $arSites = [];
            $rsSites = CSite::GetList($by = 'sort', $order = 'desc', ['ACTIVE' => 'Y']);
            while ($arSite = $rsSites->Fetch()) {
                $arSites[] = $arSite;
            }
            foreach ($arComponentsNames as $componentName) {
                foreach ($arSites as $arSite) {
                    CBitrixComponent::clearComponentCache($componentName, $arSite['ID']);
                }
            }
        }
    }
}

if (!function_exists('ClearAllSitesCacheDirs')) {
    function ClearAllSitesCacheDirs($arDirs)
    {
        if ($arDirs && is_array($arDirs)) {
            foreach ($arDirs as $dir) {
                $obCache = new CPHPCache();
                $obCache->CleanDir('', $dir);
            }
        }
    }
}

if (!function_exists('GetIBlocks')) {
    function GetIBlocks()
    {
        $arRes = [];
        $dbRes = CIBlock::GetList([], ['ACTIVE' => 'Y']);
        while ($item = $dbRes->Fetch()) {
            $dbIBlockSites = CIBlock::GetSite($item['ID']);
            while ($arIBlockSite = $dbIBlockSites->Fetch()) {
                $arRes[$arIBlockSite['SITE_ID']][$item['IBLOCK_TYPE_ID']][$item['CODE']][] = $item['ID'];
            }
        }

        return $arRes;
    }
}

if (!function_exists('GetSites')) {
    function GetSites()
    {
        $arRes = [];
        $dbRes = CSite::GetList($by = 'sort', $order = 'desc', ['ACTIVE' => 'Y']);
        while ($item = $dbRes->Fetch()) {
            $arRes[$item['LID']] = $item;
        }

        return $arRes;
    }
}

if (!function_exists('GetCurVersion')) {
    function GetCurVersion($versionFile)
    {
        $ver = false;
        if (file_exists($versionFile)) {
            $arModuleVersion = [];
            include $versionFile;
            $ver = trim($arModuleVersion['VERSION']);
        }

        return $ver;
    }
}

if (!function_exists('CreateBakFile')) {
    function CreateBakFile($file, $curVersion = CURRENT_VERSION)
    {
        $file = trim($file);
        if (file_exists($file)) {
            $arPath = pathinfo($file);
            $backFile = $arPath['dirname'].'/_'.$arPath['basename'].'.back'.$curVersion;
            if (!file_exists($backFile)) {
                @copy($file, $backFile);
            }
        }
    }
}

if (!function_exists('RemoveFileFromModuleWizard')) {
    function RemoveFileFromModuleWizard($file)
    {
        @unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.MODULE_NAME.'/install/wizards/'.PARTNER_NAME.'/'.MODULE_NAME_SHORT.$file);
        @unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards/'.PARTNER_NAME.'/'.MODULE_NAME_SHORT.$file);
    }
}

if (!function_exists('RemoveFileFromTemplate')) {
    function RemoveFileFromTemplate($file, $bModule = true)
    {
        @unlink($_SERVER['DOCUMENT_ROOT'].TEMPLATE_PATH.$file);
        if ($bModule) {
            RemoveFileFromModuleWizard('/site/templates/'.TEMPLATE_NAME.$file);
        }
    }
}

if (!function_exists('SearchFilesInPublicRecursive')) {
    function SearchFilesInPublicRecursive($dir, $pattern, $flags = 0)
    {
        $arDirExclude = ['bitrix', 'upload'];
        $pattern = str_replace('//', '/', str_replace('//', '/', $dir.'/').$pattern);
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            if (!in_array(basename($dir), $arDirExclude)) {
                $files = array_merge($files, SearchFilesInPublicRecursive($dir, basename($pattern), $flags));
            }
        }

        return $files;
    }
}

if (!function_exists('RemoveOldBakFiles')) {
    function RemoveOldBakFiles()
    {
        $arDirs = $arFiles = [];

        foreach (
            $arExclude = [
                'bitrix',
                'local',
                'upload',
                'webp-copy',
                'cgi',
                'cgi-bin',
            ] as $dir) {
            $arDirExclude[] = $_SERVER['DOCUMENT_ROOT'].'/'.$dir;
        }

        // public
        if ($arSites = GetSites()) {
            foreach ($arSites as $siteID => $arSite) {
                $arSite['DIR'] = str_replace('//', '/', '/'.$arSite['DIR']);
                if (!strlen($arSite['DOC_ROOT'])) {
                    $arSite['DOC_ROOT'] = $_SERVER['DOCUMENT_ROOT'];
                }
                $arSite['DOC_ROOT'] = str_replace('//', '/', $arSite['DOC_ROOT'].'/');
                $siteDir = str_replace('//', '/', $arSite['DOC_ROOT'].$arSite['DIR']);

                if ($arPublicDirs = glob($siteDir.'*', GLOB_ONLYDIR | GLOB_NOSORT)) {
                    foreach ($arPublicDirs as $dir) {
                        foreach ($arExclude as $exclude) {
                            if (strpos($dir, '/'.$exclude) !== false) {
                                continue 2;
                            }
                        }

                        $arDirs[] = str_replace('//', '/', $dir.'/');
                    }
                }
            }

            $i = 0;
            while ($arDirs && ++$i < 10000) {
                $dir = array_pop($arDirs);
                $arFiles = array_merge($arFiles, (array) glob($dir.'_*.back*', GLOB_NOSORT));
                foreach ((array) glob($dir.'*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
                    if (
                        strlen($dir)
                    ) {
                        foreach ($arExclude as $exclude) {
                            if (strpos($dir, '/'.$exclude) !== false) {
                                continue 2;
                            }
                        }

                        $arDirs[] = str_replace('//', '/', $dir.'/');
                    }
                }
            }
        }

        $arDirs = [];

        // aspro components
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/')) {
            if ($arComponents = glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.PARTNER_NAME.'*', 0)) {
                foreach ($arComponents as $componentPath) {
                    $arDirs[] = str_replace('//', '/', $componentPath.'/');
                }
            }
        }
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/local/components/')) {
            if ($arComponents = glob($_SERVER['DOCUMENT_ROOT'].'/local/components/'.PARTNER_NAME.'*', 0)) {
                foreach ($arComponents as $componentPath) {
                    $arDirs[] = str_replace('//', '/', $componentPath.'/');
                }
            }
        }

        // aspro and other templates
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/')) {
            if ($arTemplates = glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/*', 0)) {
                foreach ($arTemplates as $templatePath) {
                    $arDirs[] = str_replace('//', '/', $templatePath.'/');
                }
            }
        }
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/local/templates/')) {
            if ($arTemplates = glob($_SERVER['DOCUMENT_ROOT'].'/local/templates/*', 0)) {
                foreach ($arTemplates as $templatePath) {
                    $arDirs[] = str_replace('//', '/', $templatePath.'/');
                }
            }
        }

        // aspro modules
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/')) {
            if ($arModules = glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.PARTNER_NAME.'*', 0)) {
                foreach ($arModules as $modulePath) {
                    $arDirs[] = str_replace('//', '/', $modulePath.'/');
                }
            }
        }
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/local/modules/')) {
            if ($arModules = glob($_SERVER['DOCUMENT_ROOT'].'/local/modules/'.PARTNER_NAME.'*', 0)) {
                foreach ($arModules as $modulePath) {
                    $arDirs[] = str_replace('//', '/', $modulePath.'/');
                }
            }
        }

        // aspro js
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/')) {
            if ($arJs = glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.MODULE_NAME.'*', 0)) {
                foreach ($arJs as $jsPath) {
                    $arDirs[] = str_replace('//', '/', $jsPath.'/');
                }
            }
        }

        // aspro css
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/css/')) {
            if ($arCss = glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/css/'.MODULE_NAME.'*', 0)) {
                foreach ($arCss as $cssPath) {
                    $arDirs[] = str_replace('//', '/', $cssPath.'/');
                }
            }
        }

        // aspro wizards
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards/')) {
            if ($arWizards = glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards/'.PARTNER_NAME.'*', 0)) {
                foreach ($arWizards as $wizardPath) {
                    $arDirs[] = str_replace('//', '/', $wizardPath.'/');
                }
            }
        }
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/local/wizards/')) {
            if ($arWizards = glob($_SERVER['DOCUMENT_ROOT'].'/local/wizards/'.PARTNER_NAME.'*', 0)) {
                foreach ($arWizards as $wizardPath) {
                    $arDirs[] = str_replace('//', '/', $wizardPath.'/');
                }
            }
        }

        $i = 0;
        while ($arDirs && ++$i < 10000) {
            $popdir = array_pop($arDirs);
            $arFiles = array_merge($arFiles, (array) glob($popdir.'_*.back*', GLOB_NOSORT));
            foreach ((array) glob($popdir.'{,.}*', GLOB_ONLYDIR | GLOB_NOSORT | GLOB_BRACE) as $dir) {
                if (
                    strlen($dir)
                    && !in_array($dir, [$popdir.'.', $popdir.'..'])
                    && !in_array($dir, $arDirExclude)
                    && (
                        strpos($dir, PARTNER_NAME) !== false
                        || strpos($dir, '/templates/') !== false
                    )
                ) {
                    $arDirs[] = str_replace('//', '/', $dir.'/');
                }
            }
        }

        if ($arFiles) {
            foreach ($arFiles as $file) {
                if (file_exists($file) && !is_dir($file)) {
                    if (time() - filemtime($file) >= 1209600) { // 14 days
                        @unlink($file);
                    }
                }
            }
        }
    }
}

if (!function_exists('GetDBcharset')) {
    function GetDBcharset()
    {
        $sql = 'SHOW VARIABLES LIKE "character_set_database";';
        if (method_exists('\Bitrix\Main\Application', 'getConnection')) {
            $db = Bitrix\Main\Application::getConnection();
            $arResult = $db->query($sql)->fetch();

            return $arResult['Value'];
        } elseif (defined('BX_USE_MYSQLI') && BX_USE_MYSQLI == true) {
            if ($result = @mysqli_query($sql)) {
                $arResult = mysql_fetch_row($result);

                return $arResult[1];
            }
        } elseif ($result = @mysql_query($sql)) {
            $arResult = mysql_fetch_row($result);

            return $arResult[1];
        }

        return false;
    }
}

if (!function_exists('GetMes')) {
    function GetMes($str)
    {
        if (method_exists('\Bitrix\Main\Text\Encoding', 'convertEncodingToCurrent')) {
            return \Bitrix\Main\Text\Encoding::convertEncodingToCurrent($str);
        }

        static $isUTF8;
        if ($isUTF8 === null) {
            if (method_exists('\Bitrix\Main\Application', 'isUtfMode')) {
                $isUTF8 = \Bitrix\Main\Application::isUtfMode();
            } else {
                $isUTF8 = stripos(GetDBcharset(), 'utf8') !== false;
            }
        }

        return $isUTF8 ? iconv('CP1251', 'UTF-8', $str) : $str;
    }
}

if (!function_exists('UpdaterLog')) {
    function UpdaterLog($str)
    {
        static $fLOG;
        if ($bFirst = !$fLOG) {
            $fLOG = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.MODULE_NAME.'/updaterlog.txt';
        }
        if (is_array($str)) {
            $str = print_r($str, 1);
        }
        @file_put_contents($fLOG, ($bFirst ? PHP_EOL : '').date('d.m.Y H:i:s', time()).' '.$str.PHP_EOL, FILE_APPEND);
    }
}

if (!function_exists('InitComposite')) {
    function InitComposite($arSites)
    {
        if (class_exists('CHTMLPagesCache')) {
            if (method_exists('CHTMLPagesCache', 'GetOptions')) {
                if ($arHTMLCacheOptions = CHTMLPagesCache::GetOptions()) {
                    if ($arHTMLCacheOptions['COMPOSITE'] !== 'Y') {
                        $arDomains = [];
                        if ($arSites) {
                            foreach ($arSites as $arSite) {
                                if (strlen($serverName = trim($arSite['SERVER_NAME'], " \t\n\r"))) {
                                    $arDomains[$serverName] = $serverName;
                                }
                                if (strlen($arSite['DOMAINS'])) {
                                    foreach (explode("\n", $arSite['DOMAINS']) as $domain) {
                                        if (strlen($domain = trim($domain, " \t\n\r"))) {
                                            $arDomains[$domain] = $domain;
                                        }
                                    }
                                }
                            }
                        }

                        if (!$arDomains) {
                            $arDomains[$_SERVER['SERVER_NAME']] = $_SERVER['SERVER_NAME'];
                        }

                        if (!$arHTMLCacheOptions['GROUPS']) {
                            $arHTMLCacheOptions['GROUPS'] = [];
                        }
                        $rsGroups = CGroup::GetList($by = 'id', $order = 'asc', []);
                        while ($arGroup = $rsGroups->Fetch()) {
                            if ($arGroup['ID'] > 2) {
                                if (in_array($arGroup['STRING_ID'], ['RATING_VOTE_AUTHORITY', 'RATING_VOTE']) && !in_array($arGroup['ID'], $arHTMLCacheOptions['GROUPS'])) {
                                    $arHTMLCacheOptions['GROUPS'][] = $arGroup['ID'];
                                }
                            }
                        }

                        $arHTMLCacheOptions['COMPOSITE'] = 'Y';
                        $arHTMLCacheOptions['DOMAINS'] = array_merge((array) $arHTMLCacheOptions['DOMAINS'], (array) $arDomains);
                        CHTMLPagesCache::SetEnabled(true);
                        CHTMLPagesCache::SetOptions($arHTMLCacheOptions);
                        bx_accelerator_reset();
                    }
                }
            }
        }
    }
}

if (!function_exists('GetCompositeOptions')) {
    function GetCompositeOptions()
    {
        if (class_exists('CHTMLPagesCache')) {
            if (method_exists('CHTMLPagesCache', 'GetOptions')) {
                return CHTMLPagesCache::GetOptions();
            }
        }

        return [];
    }
}

if (!function_exists('IsCompositeEnabled')) {
    function IsCompositeEnabled()
    {
        if (class_exists('CHTMLPagesCache')) {
            if ($arHTMLCacheOptions = GetCompositeOptions()) {
                if (method_exists('CHTMLPagesCache', 'isOn')) {
                    if (CHTMLPagesCache::isOn()) {
                        if (isset($arHTMLCacheOptions['AUTO_COMPOSITE']) && $arHTMLCacheOptions['AUTO_COMPOSITE'] === 'Y') {
                            return 'AUTO_COMPOSITE';
                        } else {
                            return 'COMPOSITE';
                        }
                    }
                } else {
                    if ($arHTMLCacheOptions['COMPOSITE'] === 'Y') {
                        return 'COMPOSITE';
                    }
                }
            }
        }

        return false;
    }
}

if (!function_exists('EnableComposite')) {
    function EnableComposite($auto = false, $arHTMLCacheOptions = [])
    {
        if (class_exists('CHTMLPagesCache')) {
            if (method_exists('CHTMLPagesCache', 'GetOptions')) {
                $arHTMLCacheOptions = is_array($arHTMLCacheOptions) ? $arHTMLCacheOptions : [];
                $arHTMLCacheOptions = array_merge(CHTMLPagesCache::GetOptions(), $arHTMLCacheOptions);

                $arHTMLCacheOptions['COMPOSITE'] = $arHTMLCacheOptions['COMPOSITE'] ?? 'Y';
                $arHTMLCacheOptions['AUTO_UPDATE'] = $arHTMLCacheOptions['AUTO_UPDATE'] ?? 'Y'; // standart mode
                $arHTMLCacheOptions['AUTO_UPDATE_TTL'] = $arHTMLCacheOptions['AUTO_UPDATE_TTL'] ?? '0'; // no ttl delay
                $arHTMLCacheOptions['AUTO_COMPOSITE'] = ($auto ? 'Y' : 'N'); // auto composite mode

                CHTMLPagesCache::SetEnabled(true);
                CHTMLPagesCache::SetOptions($arHTMLCacheOptions);
                bx_accelerator_reset();
            }
        }
    }
}

if (!function_exists('AddNewProps')) {
    function AddNewProps($arPropertiesIBlocks = [], $lang = 'ru')
    {
        if (!count($arPropertiesIBlocks)) {
            return;
        }

        foreach ($arPropertiesIBlocks as $IBlockID => $arProperties) {
            $arUserOptionsForm = CUserOptions::GetOption('form', 'form_element_'.$IBlockID, []);
            $strOptionTab = '';

            foreach ($arProperties as $key => $property) {
                if ($property['PROPS_DELIMETER']) {
                    $strOptionTab .= ',--editAspro_csection_'.$property['ID'].'--#--'.$property['LANG'][$lang].'--';
                } else {
                    $dbProperty = CIBlockProperty::GetList([], ['IBLOCK_ID' => $IBlockID, 'CODE' => $property['CODE']]);

                    if (!$dbProperty->SelectedRowsCount()) {
                        $arFields = [
                            'NAME' => $property['LANG'][$lang],
                            'ACTIVE' => $property['ACTIVE'],
                            'SORT' => $property['SORT'],
                            'CODE' => $property['CODE'],
                            'PROPERTY_TYPE' => $property['PROPERTY_TYPE'],
                            'USER_TYPE' => $property['USER_TYPE'],
                            'LIST_TYPE' => $property['LIST_TYPE'],
                            'MULTIPLE' => $property['MULTIPLE'],
                            'IBLOCK_ID' => $IBlockID,
                            'USER_TYPE' => $property['USER_TYPE'] ?? '',
                            'HINT' => $property['HINT'] ?? '',
                        ];

                        if ($property['PROPERTY_TYPE'] === 'E' && $property['LINK_IBLOCK_ID']) {
                            $arFields['LINK_IBLOCK_ID'] = $property['LINK_IBLOCK_ID'];
                        }

                        if ($property['WITH_DESCRIPTION']) {
                            $arFields['WITH_DESCRIPTION'] = $property['WITH_DESCRIPTION'];
                        }

                        $ibp = new CIBlockProperty();
                        $propID = $ibp->Add($arFields);

                        if ($propID) {
                            $strOptionTab .= ',--PROPERTY_'.$propID.'--#--'.$property['LANG'][$lang].'--';
                        }
                    } else {
                        $propID = $dbProperty->Fetch()['ID'];
                    }

                    if (
                        $propID
                        && $property['ENUMS']
                    ) {
                        $arEnumValue = [];
                        $ibpenum = new CIBlockPropertyEnum();
                        $propertyEnums = CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $IBlockID, 'CODE' => $property['CODE']]);
                        if ($propertyEnums->SelectedRowsCount()) {
                            while ($enumFields = $propertyEnums->GetNext()) {
                                $arEnumValue[] = $enumFields['VALUE'];
                                $arEnumXMLValue[] = $enumFields['XML_ID'];
                            }

                            foreach ($property['ENUMS'] as $arEnum) {
                                if (
                                    !in_array($arEnum['VALUE'][$lang], $arEnumValue)
                                    && (empty($arEnum['XML_ID']) || !in_array($arEnum['XML_ID'], $arEnumXMLValue))
                                ) {
                                    $ibpenum->Add([
                                        'PROPERTY_ID' => $propID,
                                        'VALUE' => $arEnum['VALUE'][$lang],
                                        'XML_ID' => $arEnum['XML_ID'] ?? '',
                                    ]);
                                }
                            }
                        } else {
                            foreach ($property['ENUMS'] as $arEnum) {
                                $ibpenum->Add([
                                    'PROPERTY_ID' => $propID,
                                    'VALUE' => $arEnum['VALUE'][$lang],
                                    'XML_ID' => $arEnum['XML_ID'] ?? '',
                                ]);
                            }
                        }
                    }
                }
            }

            if ($strOptionTab && isset($arUserOptionsForm['tabs'])) {
                $matches = [];
                $subject = '/(--Aspro--.*?);/s';
                preg_match($subject, $arUserOptionsForm['tabs'], $matches);

                if ($matches[0]) {
                    $patternNewProperty = $matches[1].$strOptionTab;
                    $arUserOptionsForm['tabs'] = str_replace($matches[1], $patternNewProperty, $arUserOptionsForm['tabs']);
                } else {
                    $matches = [];
                    preg_match_all('/\bedit(\d)\b/', $arUserOptionsForm['tabs'], $matches, false);
                    sort($matches[1]);
                    $editNumber = array_pop($matches[1]);

                    $addPropForm = 'edit'.($editNumber + 1).'--#--Aspro--'.$strOptionTab.';--';
                    $arUserOptionsForm['tabs'] .= $addPropForm;
                }

                $arUserOptionsForm = CUserOptions::SetOption('form', 'form_element_'.$IBlockID, $arUserOptionsForm);
            }
        }
    }
}

if (!function_exists('addUserFields')) {
    function addUserFields(string $IBlockID, array $arUserFields, bool $debug = false, string $lang = 'ru'): void
    {
        if (!$IBlockID) {
            throw new InvalidArgumentException('IBlockID cannot be empty');
        }

        $oUserTypeEntity = new CUserTypeEntity();

        $strOptionTab = '';
        foreach ($arUserFields as $userField) {
            if (empty($userField['FIELD_NAME']) || empty($userField['USER_TYPE_ID'])) {
                if ($debug) {
                    echo "Missing property: FIELD_NAME and USER_TYPE_ID are required. Your passed array {$userField}";
                }
                continue;
            }

            $rsEntity = CUserTypeEntity::getList([], ['ENTITY_ID' => 'IBLOCK_'.$IBlockID.'_SECTION', 'FIELD_NAME' => 'UF_MENU_BANNER']);
            if ($rsEntity->Fetch()) {
                continue;
            }

            $addedUserField = [
                'ENTITY_ID' => 'IBLOCK_'.$IBlockID.'_SECTION',
                'FIELD_NAME' => $userField['FIELD_NAME'],
                'USER_TYPE_ID' => $userField['USER_TYPE_ID'],
                'XML_ID' => $userField['XML_ID'] ?? null,
                'SORT' => $userField['SORT'] ?? 500,
                'MULTIPLE' => $userField['MULTIPLE'] ?? 'N',
                'EDIT_FORM_LABEL' => $userField['EDIT_FORM_LABEL'] ?? ['ru' => '', 'en' => ''],
                'SETTINGS' => $userField['SETTINGS'] ?? [],
            ];

            $propertyId = $oUserTypeEntity->Add($addedUserField);

            if ($debug) {
                if ($propertyId) {
                    echo "Custom property {$userField['FIELD_NAME']} successfully created with ID: {$propertyId}";
                } else {
                    global $APPLICATION;
                    $error = $APPLICATION->GetException();
                    echo sprintf('Error adding property %s : %s', $userField['FIELD_NAME'], $error ? $error->GetString() : 'Unknown error');
                }
            }

            if ($propertyId) {
                $strOptionTab .= ',--'.$userField['FIELD_NAME'].'--#--'.$userField['EDIT_FORM_LABEL'][$lang].'--';
            }
        }

        if ($strOptionTab) {
            $arUserOptionsSection = CUserOptions::GetOption('form', 'form_section_'.$IBlockID);

            $matches = [];
            preg_match('/(--Aspro--.*?);/s', $arUserOptionsSection['tabs'], $matches);

            if ($matches[0]) {
                $patternNewProperty = $matches[1].$strOptionTab;
                $arUserOptionsSection['tabs'] = str_replace($matches[1], $patternNewProperty, $arUserOptionsSection['tabs']);
            } else {
                $matches = [];
                preg_match_all('/\bcedit(\d)\b/', $arUserOptionsSection['tabs'], $matches, false);
                sort($matches[1]);
                $editNumber = array_pop($matches[1]);

                $addPropForm = 'cedit'.($editNumber + 1).'--#--Aspro--'.$strOptionTab.';--';
                $arUserOptionsSection['tabs'] .= $addPropForm;
            }

            CUserOptions::SetOption('form', 'form_section_'.$IBlockID, $arUserOptionsSection);
        }
    }
}

if (!function_exists('UpdateVendorSolutionClasses')) {
    function UpdateVendorSolutionClasses()
    {
        if (
            defined('MODULE_NAME')
            && defined('MODULE_NAME_SHORT')
            && defined('PARTNER_NAME')
        ) {
            $arTemplates = $arMobileTemplates = $arCopy = [];

            foreach (
                [
                    $_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/',
                    $_SERVER['DOCUMENT_ROOT'].'/local/templates/',
                ] as $path
            ) {
                if (is_dir($path)) {
                    if ($arDirs = glob($path.'{.,}*', GLOB_ONLYDIR | GLOB_BRACE)) {
                        $arExclude = [
                            $path.'.',
                            $path.'..',
                        ];
                        foreach ($arDirs as $dir) {
                            if (!in_array($dir, $arExclude)) {
                                $fileCheck = $dir.'/vendor/php/solution.php';
                                if (file_exists($fileCheck)) {
                                    $content = file_get_contents($fileCheck);
                                    if (strpos($content, MODULE_NAME) !== false) {
                                        if (strpos($content, 'ExtensionsMobile') === false) {
                                            $arTemplates[] = $fileCheck;
                                        } else {
                                            $arMobileTemplates[] = $fileCheck;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($arTemplates) {
                if (defined('TEMPLATE_NAME')) {
                    $fileSolution = __DIR__.'/install/wizards/'.PARTNER_NAME.'/'.MODULE_NAME_SHORT.'/site/templates/'.TEMPLATE_NAME.'/vendor/php/solution.php';
                    if (file_exists($fileSolution)) {
                        $arCopy[$fileSolution] = $arTemplates;
                    }
                }
            }

            if ($arMobileTemplates) {
                if (defined('TEMPLATE_MOBILE_NAME')) {
                    $fileSolutionMobile = __DIR__.'/install/wizards/'.PARTNER_NAME.'/'.MODULE_NAME_SHORT.'/site/templates/'.TEMPLATE_MOBILE_NAME.'/vendor/php/solution.php';
                    if (file_exists($fileSolutionMobile)) {
                        $arCopy[$fileSolutionMobile] = $arMobileTemplates;
                    }
                }
            }

            foreach ($arCopy as $fileFrom => $arFileTo) {
                if (file_exists($fileFrom)) {
                    foreach ($arFileTo as $fileTo) {
                        if ($fileFrom != $fileTo) {
                            if (file_exists($fileTo)) {
                                CreateBakFile($fileTo);
                            }

                            @copy($fileFrom, $fileTo);

                            UpdaterLog('Update '.$fileTo);
                        }
                    }
                }
            }
        }
    }
}

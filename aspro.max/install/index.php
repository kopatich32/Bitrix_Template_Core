<?php

global $MESS;
$strPath2Lang = str_replace('\\', '/', __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang) - strlen('/install/index.php'));
include GetLangFileName($strPath2Lang.'/lang/', '/install/index.php');

class aspro_max extends CModule
{
    public const solutionName = 'max';
    public const partnerName = 'aspro';
    public const moduleClass = 'CMax';
    public const moduleClassEvents = 'CMaxEvents';
    public const moduleClassCache = 'CMaxCache';
    public const moduleClassPreset = 'Aspro\Max\Preset';

    public $MODULE_ID = 'aspro.max';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_CSS;
    public $MODULE_GROUP_RIGHTS = 'Y';

    public function __construct()
    {
        $arModuleVersion = [];

        $path = str_replace('\\', '/', __FILE__);
        $path = substr($path, 0, strlen($path) - strlen('/index.php'));
        include $path.'/version.php';

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = GetMessage('ASPRO_MAX_SCOM_INSTALL_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('ASPRO_MAX_SCOM_INSTALL_DESCRIPTION');
        $this->PARTNER_NAME = GetMessage('ASPRO_MAX_SPER_PARTNER');
        $this->PARTNER_URI = GetMessage('ASPRO_MAX_PARTNER_URI');
    }

    public function checkValid()
    {
        return true;
    }

    public function InstallDB($install_wizard = true)
    {
        global $DB, $DBType, $APPLICATION;

        if (preg_match('/.bitrixlabs.ru/', $_SERVER['HTTP_HOST'])) {
            RegisterModuleDependences('main', 'OnBeforeProlog', $this->MODULE_ID, self::moduleClassEvents, 'correctInstall');
        }

        RegisterModule($this->MODULE_ID);
        // RegisterModuleDependences("main", "OnBeforeProlog", $this->MODULE_ID, self::moduleClassEvents, "ShowPanel");

        // autoload classes
        require_once realpath(__DIR__.'/../include.php');

        if (!Aspro\Max\ShareBasketTable::getEntity()->getConnection()->isTableExists(Aspro\Max\ShareBasketTable::getTableName())) {
            Aspro\Max\ShareBasketTable::getEntity()->createDbTable();
        }

        if (!Aspro\Max\ShareBasketItemTable::getEntity()->getConnection()->isTableExists(Aspro\Max\ShareBasketItemTable::getTableName())) {
            Aspro\Max\ShareBasketItemTable::getEntity()->createDbTable();
        }

        return true;
    }

    public function UnInstallDB($arParams = [])
    {
        global $DB, $DBType, $APPLICATION;

        // autoload classes
        require_once realpath(__DIR__.'/../include.php');

        Aspro\Max\ShareBasketTable::getEntity()->getConnection()->queryExecute('drop table if exists '.Aspro\Max\ShareBasketTable::getTableName());
        Aspro\Max\ShareBasketItemTable::getEntity()->getConnection()->queryExecute('drop table if exists '.Aspro\Max\ShareBasketItemTable::getTableName());

        UnRegisterModule($this->MODULE_ID);

        return true;
    }

    public function InstallEvents()
    {
        RegisterModuleDependences('iblock', 'OnAfterIBlockAdd', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlock');
        RegisterModuleDependences('iblock', 'OnAfterIBlockUpdate', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlock');
        RegisterModuleDependences('iblock', 'OnBeforeIBlockDelete', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlockBeforeDelete');
        RegisterModuleDependences('iblock', 'OnAfterIBlockElementAdd', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlockElement');
        RegisterModuleDependences('iblock', 'OnAfterIBlockElementUpdate', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlockElement');
        RegisterModuleDependences('iblock', 'OnAfterIBlockElementUpdate', $this->MODULE_ID, self::moduleClassEvents, 'OnRegionUpdateHandler');
        RegisterModuleDependences('iblock', 'OnAfterIBlockSectionAdd', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlockSection');
        RegisterModuleDependences('iblock', 'OnAfterIBlockSectionUpdate', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlockSection');

        RegisterModuleDependences('iblock', 'OnAfterIBlockPropertyUpdate', $this->MODULE_ID, self::moduleClassCache, 'ClearTagByProperty');
        RegisterModuleDependences('iblock', 'OnAfterIBlockPropertyAdd', $this->MODULE_ID, self::moduleClassCache, 'ClearTagByProperty');
        RegisterModuleDependences('iblock', 'OnAfterIBlockPropertyDelete', $this->MODULE_ID, self::moduleClassCache, 'ClearTagByProperty');

        RegisterModuleDependences('iblock', 'OnBeforeIBlockSectionDelete', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlockSectionBeforeDelete');

        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\ListStores', 'OnIBlockPropertyBuildList');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\ListPrices', 'OnIBlockPropertyBuildList');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\RegionLocation', 'OnIBlockPropertyBuildList');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\CustomFilter', 'OnIBlockPropertyBuildList');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\Service', 'OnIBlockPropertyBuildList');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\YaDirectQuery', 'OnIBlockPropertyBuildList');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\IBInherited', 'OnIBlockPropertyBuildList');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\ListUsersGroups', 'OnIBlockPropertyBuildList');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\ListWebForms', 'OnIBlockPropertyBuildList');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\RegionPhone', 'OnIBlockPropertyBuildList');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\ModalConditions', 'OnIBlockPropertyBuildList');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\ConditionType', 'OnIBlockPropertyBuildList');
        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\TextWithLink', 'OnIBlockPropertyBuildList');

        RegisterModuleDependences('main', 'OnAfterUserUpdate', $this->MODULE_ID, self::moduleClassCache, 'ClearTagByUser');
        RegisterModuleDependences('main', 'OnAfterAjaxResponse', $this->MODULE_ID, self::moduleClassEvents, 'onAfterAjaxResponseHandler');

        RegisterModuleDependences('main', 'OnPageStart', $this->MODULE_ID, self::moduleClassEvents, 'OnPageStartHandler');
        RegisterModuleDependences('main', 'OnBeforeUserRegister', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeUserUpdateHandler');
        RegisterModuleDependences('main', 'OnBeforeUserAdd', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeUserUpdateHandler');
        RegisterModuleDependences('main', 'OnBeforeUserUpdate', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeUserUpdateHandler');
        RegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', $this->MODULE_ID, self::moduleClassEvents, 'clearBasketCacheHandler');
        RegisterModuleDependences('sale', 'OnBasketAdd', $this->MODULE_ID, self::moduleClassEvents, 'clearBasketCacheHandler');
        RegisterModuleDependences('sale', 'OnBeforeBasketUpdate', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeBasketUpdateHandler');
        RegisterModuleDependences('sale', 'OnSaleComponentOrderProperties', $this->MODULE_ID, self::moduleClassEvents, 'OnSaleComponentOrderPropertiesHandler');
        RegisterModuleDependences('sale', 'OnSaleComponentOrderProperties', $this->MODULE_ID, self::moduleClassEvents, 'OnSaleComponentOrderProperties');
        RegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', $this->MODULE_ID, self::moduleClassEvents, 'OnSaleComponentOrderOneStepComplete');
        RegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepProcess', $this->MODULE_ID, self::moduleClassEvents, 'OnSaleComponentOrderOneStepProcess');
        RegisterModuleDependences('sale', 'OnSaleComponentOrderJsData', $this->MODULE_ID, self::moduleClassEvents, 'OnSaleComponentOrderJsDataHandler');

        RegisterModuleDependences('iblock', 'OnAfterIBlockElementUpdate', $this->MODULE_ID, self::moduleClassEvents, 'DoIBlockAfterSave');
        RegisterModuleDependences('iblock', 'OnAfterIBlockElementAdd', $this->MODULE_ID, self::moduleClassEvents, 'DoIBlockAfterSave');
        RegisterModuleDependences('catalog', 'OnPriceAdd', $this->MODULE_ID, self::moduleClassEvents, 'DoIBlockAfterSave');
        RegisterModuleDependences('catalog', 'OnPriceUpdate', $this->MODULE_ID, self::moduleClassEvents, 'DoIBlockAfterSave');
        RegisterModuleDependences('catalog', 'OnStoreProductAdd', $this->MODULE_ID, self::moduleClassEvents, 'setStoreProductHandler');
        RegisterModuleDependences('catalog', 'OnStoreProductUpdate', $this->MODULE_ID, self::moduleClassEvents, 'setStoreProductHandler');
        RegisterModuleDependences('catalog', 'OnGetOptimalPrice', $this->MODULE_ID, self::moduleClassEvents, 'OnGetOptimalPriceHandler');
        RegisterModuleDependences('form', 'onAfterResultAdd', $this->MODULE_ID, self::moduleClassEvents, 'onAfterResultAddHandler');

        RegisterModuleDependences('sender', 'onPresetTemplateList', $this->MODULE_ID, "\Aspro\Solution\CAsproMarketingMax", 'senderTemplateList');

        RegisterModuleDependences('socialservices', 'OnAfterSocServUserAdd', $this->MODULE_ID, self::moduleClassEvents, 'OnAfterSocServUserAddHandler');
        RegisterModuleDependences('socialservices', 'OnFindSocialservicesUser', $this->MODULE_ID, self::moduleClassEvents, 'OnFindSocialservicesUserHandler');

        RegisterModuleDependences('main', 'OnEndBufferContent', $this->MODULE_ID, self::moduleClassEvents, 'OnEndBufferContentHandler');
        RegisterModuleDependences('main', 'OnBeforeEventAdd', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeEventAddHandler');
        RegisterModuleDependences('main', 'OnEpilog', $this->MODULE_ID, self::moduleClassEvents, 'OnEpilogHandler');

        RegisterModuleDependences('form', 'onBeforeResultAdd', $this->MODULE_ID, self::moduleClassEvents, 'onBeforeResultAddHandler');
        RegisterModuleDependences('subscribe', 'OnBeforeSubscriptionAdd', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeSubscriptionAddHandler');

        RegisterModuleDependences('main', 'OnBeforeChangeFile', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeChangeFileHandler');
        RegisterModuleDependences('main', 'OnChangeFile', $this->MODULE_ID, self::moduleClassEvents, 'OnChangeFileHandler', 999);
        RegisterModuleDependences('main', 'OnAdminContextMenuShow', $this->MODULE_ID, self::moduleClassEvents, 'OnAdminContextMenuShowHandler');

        RegisterModuleDependences('main', 'OnBeforeUserLogin', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeUserLoginHandler');
        RegisterModuleDependences('main', 'OnAfterUserLogin', $this->MODULE_ID, self::moduleClassEvents, 'OnAfterUserLoginHandler');

        RegisterModuleDependences('search', 'OnSearchGetURL', $this->MODULE_ID, self::moduleClassEvents, 'OnSearchGetURL');

        RegisterModuleDependences('seo', "\Bitrix\Seo\Sitemap::OnAfterUpdate", $this->MODULE_ID, self::moduleClassEvents, 'OnAfterUpdateSitemapHandler');

        RegisterModuleDependences('blog', 'OnBeforeCommentAdd', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeCommentAddHandler');
        RegisterModuleDependences('blog', 'OnCommentAdd', $this->MODULE_ID, self::moduleClassEvents, 'OnCommentAddHandler');
        RegisterModuleDependences('blog', 'OnBeforeCommentUpdate', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeCommentUpdateHandler');
        RegisterModuleDependences('blog', 'OnCommentUpdate', $this->MODULE_ID, self::moduleClassEvents, 'OnCommentUpdateHandler');
        RegisterModuleDependences('blog', 'OnCommentDelete', $this->MODULE_ID, self::moduleClassEvents, 'OnCommentDeleteHandler');

        RegisterModuleDependences('main', 'OnAdminTabControlBegin', $this->MODULE_ID, 'Aspro\Max\PropertyGroups', 'eventHandler');
        RegisterModuleDependences('iblock', 'OnBeforeIBlockUpdate', $this->MODULE_ID, 'Aspro\Max\PropertyGroups', 'iblockUpdateEventHandler');

        if (class_exists('\Bitrix\Main\EventManager')) {
            $eventManager = Bitrix\Main\EventManager::getInstance();
            $eventManager->registerEventHandler('sale', 'OnSaleOrderSaved', $this->MODULE_ID, self::moduleClassEvents, 'BeforeSendEvent', 10);

            $eventManager->registerEventHandler('catalog', 'OnCatalogStoreAdd', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnCatalogStoreAdd');
            $eventManager->registerEventHandler('catalog', 'OnCatalogStoreUpdate', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnCatalogStoreUpdate');
            $eventManager->registerEventHandler('catalog', 'OnCatalogStoreDelete', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnCatalogStoreDelete');
            $eventManager->registerEventHandler('catalog', 'OnStoreProductAdd', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnStoreProductAdd');
            $eventManager->registerEventHandler('catalog', 'OnBeforeStoreProductUpdate', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnBeforeStoreProductUpdate');
            $eventManager->registerEventHandler('catalog', 'OnStoreProductUpdate', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnStoreProductUpdate');
            $eventManager->registerEventHandler('catalog', 'OnProductSetAdd', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnProductSetAdd');
            $eventManager->registerEventHandler('catalog', 'OnProductSetUpdate', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnProductSetUpdate');
            $eventManager->registerEventHandler('iblock', 'OnAfterIBlockElementUpdate', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnAfterIBlockElementUpdate');
            $eventManager->registerEventHandler('catalog', 'OnBeforeStoreProductDelete', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnBeforeStoreProductDelete');
            $eventManager->registerEventHandler('catalog', 'OnStoreProductDelete', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnStoreProductDelete');

            $eventManager->registerEventHandler('catalog', 'Bitrix\Catalog\Model\Product::OnAfterAdd', $this->MODULE_ID, self::moduleClassEvents, 'setStockProduct');
            $eventManager->registerEventHandler('catalog', 'Bitrix\Catalog\Model\Product::OnAfterUpdate', $this->MODULE_ID, self::moduleClassEvents, 'setStockProduct');

            $eventManager->registerEventHandler('main', 'Bitrix\Main\Controller\LoadExt::onBeforeAction', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeAction');
        }

        RegisterModuleDependences($this->MODULE_ID, 'OnCatalogDeliveryComponentInitUserResult', $this->MODULE_ID, self::moduleClassEvents, 'OnCatalogDeliveryComponentInitUserResult');
        RegisterModuleDependences($this->MODULE_ID, 'OnAsproParameters', $this->MODULE_ID, self::moduleClassEvents, 'onAsproParametersHandler');

        return true;
    }

    public function UnInstallEvents()
    {
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockAdd', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlock');
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockUpdate', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlock');
        UnRegisterModuleDependences('iblock', 'OnBeforeIBlockDelete', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlockBeforeDelete');
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockElementAdd', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlockElement');
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockElementUpdate', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlockElement');
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockElementUpdate', $this->MODULE_ID, self::moduleClassEvents, 'OnRegionUpdateHandler');
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockSectionAdd', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlockSection');
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockSectionUpdate', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlockSection');
        UnRegisterModuleDependences('iblock', 'OnBeforeIBlockSectionDelete', $this->MODULE_ID, self::moduleClassCache, 'ClearTagIBlockSectionBeforeDelete');

        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\ListStores', 'OnIBlockPropertyBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\ListPrices', 'OnIBlockPropertyBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\RegionLocation', 'OnIBlockPropertyBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\CustomFilter', 'OnIBlockPropertyBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\Service', 'OnIBlockPropertyBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\YaDirectQuery', 'OnIBlockPropertyBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\IBInherited', 'OnIBlockPropertyBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\ListUsersGroups', 'OnIBlockPropertyBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\ListWebForms', 'OnIBlockPropertyBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\RegionPhone', 'OnIBlockPropertyBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\ModalConditions', 'OnIBlockPropertyBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\ConditionType', 'OnIBlockPropertyBuildList');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'Aspro\Max\Property\TextWithLink', 'OnIBlockPropertyBuildList');

        UnRegisterModuleDependences('main', 'OnAfterUserUpdate', $this->MODULE_ID, self::moduleClassCache, 'ClearTagByUser');
        UnRegisterModuleDependences('main', 'OnAfterAjaxResponse', $this->MODULE_ID, self::moduleClassEvents, 'onAfterAjaxResponseHandler');

        UnRegisterModuleDependences('iblock', 'OnAfterIBlockPropertyUpdate', $this->MODULE_ID, self::moduleClassCache, 'ClearTagByProperty');
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockPropertyAdd', $this->MODULE_ID, self::moduleClassCache, 'ClearTagByProperty');
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockPropertyDelete', $this->MODULE_ID, self::moduleClassCache, 'ClearTagByProperty');

        UnRegisterModuleDependences('main', 'OnPageStart', $this->MODULE_ID, self::moduleClassEvents, 'OnPageStartHandler');
        UnRegisterModuleDependences('main', 'OnBeforeUserRegister', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeUserUpdateHandler');
        UnRegisterModuleDependences('main', 'OnBeforeUserAdd', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeUserUpdateHandler');
        UnRegisterModuleDependences('main', 'OnBeforeUserUpdate', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeUserUpdateHandler');
        UnRegisterModuleDependences('main', 'OnBeforeProlog', $this->MODULE_ID, self::moduleClassEvents, 'ShowPanel');
        UnRegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', $this->MODULE_ID, self::moduleClassEvents, 'clearBasketCacheHandler');
        UnRegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', $this->MODULE_ID, self::moduleClassEvents, 'OnSaleComponentOrderOneStepComplete');
        UnRegisterModuleDependences('sale', 'OnBasketAdd', $this->MODULE_ID, self::moduleClassEvents, 'clearBasketCacheHandler');
        UnRegisterModuleDependences('sale', 'OnBeforeBasketUpdate', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeBasketUpdateHandler');
        UnRegisterModuleDependences('sale', 'OnSaleComponentOrderProperties', $this->MODULE_ID, self::moduleClassEvents, 'OnSaleComponentOrderPropertiesHandler');
        UnRegisterModuleDependences('sale', 'OnSaleComponentOrderProperties', $this->MODULE_ID, self::moduleClassEvents, 'OnSaleComponentOrderProperties');
        UnRegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepProcess', $this->MODULE_ID, self::moduleClassEvents, 'OnSaleComponentOrderOneStepProcess');
        UnRegisterModuleDependences('sale', 'OnSaleComponentOrderJsData', $this->MODULE_ID, self::moduleClassEvents, 'OnSaleComponentOrderJsDataHandler');
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockElementUpdate', $this->MODULE_ID, self::moduleClassEvents, 'DoIBlockAfterSave');
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockElementAdd', $this->MODULE_ID, self::moduleClassEvents, 'DoIBlockAfterSave');
        UnRegisterModuleDependences('catalog', 'OnPriceAdd', $this->MODULE_ID, self::moduleClassEvents, 'DoIBlockAfterSave');
        UnRegisterModuleDependences('catalog', 'OnPriceUpdate', $this->MODULE_ID, self::moduleClassEvents, 'DoIBlockAfterSave');
        UnRegisterModuleDependences('catalog', 'OnGetOptimalPrice', $this->MODULE_ID, self::moduleClassEvents, 'OnGetOptimalPriceHandler');
        UnRegisterModuleDependences('catalog', 'OnStoreProductAdd', $this->MODULE_ID, self::moduleClassEvents, 'setStoreProductHandler');
        UnRegisterModuleDependences('catalog', 'OnStoreProductUpdate', $this->MODULE_ID, self::moduleClassEvents, 'setStoreProductHandler');
        UnRegisterModuleDependences('form', 'onAfterResultAdd', $this->MODULE_ID, self::moduleClassEvents, 'onAfterResultAddHandler');

        UnRegisterModuleDependences('sender', 'onPresetTemplateList', $this->MODULE_ID, "\Aspro\Solution\CAsproMarketingMax", 'senderTemplateList');

        UnRegisterModuleDependences('main', 'OnEndBufferContent', $this->MODULE_ID, self::moduleClassEvents, 'OnEndBufferContentHandler');
        UnRegisterModuleDependences('main', 'OnBeforeEventAdd', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeEventAddHandler');
        UnRegisterModuleDependences('main', 'OnEpilog', $this->MODULE_ID, self::moduleClassEvents, 'OnEpilogHandler');

        UnRegisterModuleDependences('socialservices', 'OnAfterSocServUserAdd', $this->MODULE_ID, self::moduleClassEvents, 'OnAfterSocServUserAddHandler');
        UnRegisterModuleDependences('socialservices', 'OnFindSocialservicesUser', $this->MODULE_ID, self::moduleClassEvents, 'OnFindSocialservicesUserHandler');

        UnRegisterModuleDependences('form', 'onBeforeResultAdd', $this->MODULE_ID, self::moduleClassEvents, 'onBeforeResultAddHandler');
        UnRegisterModuleDependences('subscribe', 'OnBeforeSubscriptionAdd', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeSubscriptionAddHandler');

        UnRegisterModuleDependences('main', 'OnBeforeChangeFile', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeChangeFileHandler');
        UnRegisterModuleDependences('main', 'OnChangeFile', $this->MODULE_ID, self::moduleClassEvents, 'OnChangeFileHandler');
        UnRegisterModuleDependences('main', 'OnAdminContextMenuShow', $this->MODULE_ID, self::moduleClassEvents, 'OnAdminContextMenuShowHandler');

        UnRegisterModuleDependences('main', 'OnBeforeUserLogin', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeUserLoginHandler');
        UnRegisterModuleDependences('main', 'OnAfterUserLogin', $this->MODULE_ID, self::moduleClassEvents, 'OnAfterUserLoginHandler');

        UnRegisterModuleDependences('search', 'OnSearchGetURL', $this->MODULE_ID, self::moduleClassEvents, 'OnSearchGetURL');

        UnRegisterModuleDependences('seo', "\Bitrix\Seo\Sitemap::OnAfterUpdate", $this->MODULE_ID, self::moduleClassEvents, 'OnAfterUpdateSitemapHandler');

        UnRegisterModuleDependences('blog', 'OnBeforeCommentAdd', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeCommentAddHandler');
        UnRegisterModuleDependences('blog', 'OnCommentAdd', $this->MODULE_ID, self::moduleClassEvents, 'OnCommentAddHandler');
        UnRegisterModuleDependences('blog', 'OnBeforeCommentUpdate', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeCommentUpdateHandler');
        UnRegisterModuleDependences('blog', 'OnCommentUpdate', $this->MODULE_ID, self::moduleClassEvents, 'OnCommentUpdateHandler');
        UnRegisterModuleDependences('blog', 'OnCommentDelete', $this->MODULE_ID, self::moduleClassEvents, 'OnCommentDeleteHandler');

        UnRegisterModuleDependences('main', 'OnAdminTabControlBegin', $this->MODULE_ID, 'Aspro\Max\PropertyGroups', 'eventHandler');
        UnRegisterModuleDependences('iblock', 'OnBeforeIBlockUpdate', $this->MODULE_ID, 'Aspro\Max\PropertyGroups', 'iblockUpdateEventHandler');

        if (class_exists('\Bitrix\Main\EventManager')) {
            $eventManager = Bitrix\Main\EventManager::getInstance();
            $eventManager->unRegisterEventHandler('sale', 'OnSaleOrderSaved', $this->MODULE_ID, self::moduleClassEvents, 'BeforeSendEvent');

            $eventManager->unRegisterEventHandler('catalog', 'OnCatalogStoreAdd', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnCatalogStoreAdd');
            $eventManager->unRegisterEventHandler('catalog', 'OnCatalogStoreUpdate', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnCatalogStoreUpdate');
            $eventManager->unRegisterEventHandler('catalog', 'OnCatalogStoreDelete', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnCatalogStoreDelete');
            $eventManager->unRegisterEventHandler('catalog', 'OnStoreProductAdd', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnStoreProductAdd');
            $eventManager->unRegisterEventHandler('catalog', 'OnBeforeStoreProductUpdate', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnBeforeStoreProductUpdate');
            $eventManager->unRegisterEventHandler('catalog', 'OnStoreProductUpdate', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnStoreProductUpdate');
            $eventManager->unRegisterEventHandler('catalog', 'OnProductSetAdd', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnProductSetAdd');
            $eventManager->unRegisterEventHandler('catalog', 'OnProductSetUpdate', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnProductSetUpdate');
            $eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockElementUpdate', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnAfterIBlockElementUpdate');
            $eventManager->unRegisterEventHandler('catalog', 'OnBeforeStoreProductDelete', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnBeforeStoreProductDelete');
            $eventManager->unRegisterEventHandler('catalog', 'OnStoreProductDelete', $this->MODULE_ID, 'Aspro\Max\Stores\EventHandler', 'OnStoreProductDelete');

            $eventManager->unRegisterEventHandler('catalog', 'Bitrix\Catalog\Model\Product::OnAfterAdd', $this->MODULE_ID, self::moduleClassEvents, 'setStockProduct');
            $eventManager->unRegisterEventHandler('catalog', 'Bitrix\Catalog\Model\Product::OnAfterUpdate', $this->MODULE_ID, self::moduleClassEvents, 'setStockProduct');

            $eventManager->unRegisterEventHandler('main', 'Bitrix\Main\Controller\LoadExt::onBeforeAction', $this->MODULE_ID, self::moduleClassEvents, 'OnBeforeAction');
        }

        UnRegisterModuleDependences($this->MODULE_ID, 'OnCatalogDeliveryComponentInitUserResult', $this->MODULE_ID, self::moduleClassEvents, 'OnCatalogDeliveryComponentInitUserResult');
        UnRegisterModuleDependences($this->MODULE_ID, 'OnAsproParameters', $this->MODULE_ID, self::moduleClassEvents, 'onAsproParametersHandler');

        return true;
    }

    public function removeDirectory($dir)
    {
        if ($objs = glob($dir.'/*')) {
            foreach ($objs as $obj) {
                if (is_dir($obj)) {
                    CMax::removeDirectory($obj);
                } else {
                    if (!unlink($obj)) {
                        if (chmod($obj, 0777)) {
                            unlink($obj);
                        }
                    }
                }
            }
        }
        if (!rmdir($dir)) {
            if (chmod($dir, 0777)) {
                rmdir($dir);
            }
        }
    }

    public function InstallFiles()
    {
        CopyDirFiles(__DIR__.'/admin/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true);
        CopyDirFiles(__DIR__.'/css/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/css/'.self::partnerName.'.'.self::solutionName, true, true);
        CopyDirFiles(__DIR__.'/js/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.self::partnerName.'.'.self::solutionName, true, true);
        CopyDirFiles(__DIR__.'/tools/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.self::partnerName.'.'.self::solutionName, true, true);
        CopyDirFiles(__DIR__.'/images/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/images/'.self::partnerName.'.'.self::solutionName, true, true);
        CopyDirFiles(__DIR__.'/components/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components', true, true);
        CopyDirFiles(__DIR__.'/wizards/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards', true, true);

        $this->InstallGadget();

        /*if(preg_match('/.bitrixlabs.ru/', $_SERVER["HTTP_HOST"])){
            @set_time_limit(0);
            require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
            CFileMan::DeleteEx(array('s1', '/bitrix/modules/'.$this->MODULE_ID.'/install/wizards'));
            CFileMan::DeleteEx(array('s1', '/bitrix/modules/'.$this->MODULE_ID.'/install/gadgets'));
        }*/

        return true;
    }

    public function InstallPublic()
    {
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(__DIR__.'/admin/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
        DeleteDirFilesEx('/bitrix/css/'.self::partnerName.'.'.self::solutionName.'/');
        DeleteDirFilesEx('/bitrix/js/'.self::partnerName.'.'.self::solutionName.'/');
        DeleteDirFilesEx('/bitrix/tools/'.self::partnerName.'.'.self::solutionName.'/');
        DeleteDirFilesEx('/bitrix/images/'.self::partnerName.'.'.self::solutionName.'/');
        DeleteDirFilesEx('/bitrix/wizards/'.self::partnerName.'/'.self::solutionName.'/');

        $this->UnInstallGadget();

        return true;
    }

    public function InstallGadget()
    {
        CopyDirFiles(__DIR__.'/gadgets/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/gadgets/', true, true);

        $gadget_id = strtoupper(self::solutionName);
        $gdid = $gadget_id.'@'.rand();
        if (class_exists('CUserOptions')) {
            $arUserOptions = CUserOptions::GetOption('intranet', '~gadgets_admin_index', false, false);
            if (is_array($arUserOptions) && isset($arUserOptions[0])) {
                foreach ($arUserOptions[0]['GADGETS'] as $tempid => $tempgadget) {
                    $p = strpos($tempid, '@');
                    $gadget_id_tmp = ($p === false ? $tempid : substr($tempid, 0, $p));

                    if ($gadget_id_tmp == $gadget_id) {
                        return false;
                    }
                    if ($tempgadget['COLUMN'] == 0) {
                        ++$arUserOptions[0]['GADGETS'][$tempid]['ROW'];
                    }
                }
                $arUserOptions[0]['GADGETS'][$gdid] = ['COLUMN' => 0, 'ROW' => 0];
                CUserOptions::SetOption('intranet', '~gadgets_admin_index', $arUserOptions, false, false);
            }
        }

        return true;
    }

    public function UnInstallGadget()
    {
        $gadget_id = strtoupper(self::solutionName);
        if (class_exists('CUserOptions')) {
            $arUserOptions = CUserOptions::GetOption('intranet', '~gadgets_admin_index', false, false);
            if (is_array($arUserOptions) && isset($arUserOptions[0])) {
                foreach ($arUserOptions[0]['GADGETS'] as $tempid => $tempgadget) {
                    $p = strpos($tempid, '@');
                    $gadget_id_tmp = ($p === false ? $tempid : substr($tempid, 0, $p));

                    if ($gadget_id_tmp == $gadget_id) {
                        unset($arUserOptions[0]['GADGETS'][$tempid]);
                    }
                }
                CUserOptions::SetOption('intranet', '~gadgets_admin_index', $arUserOptions, false, false);
            }
        }

        DeleteDirFilesEx('/bitrix/gadgets/'.self::partnerName.'/'.self::solutionName.'/');

        return true;
    }

    public function DoInstall()
    {
        global $APPLICATION, $step;

        // autoload classes
        require_once realpath(__DIR__.'/../include.php');
        $this->InstallFiles();
        $this->InstallDB(false);
        $this->InstallEvents();
        $this->InstallPublic();

        $APPLICATION->IncludeAdminFile(GetMessage('ASPRO_MAX_SCOM_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/aspro.max/install/step.php');
    }

    public function DoUninstall()
    {
        global $APPLICATION, $step;

        // autoload classes
        require_once realpath(__DIR__.'/../include.php');

        $this->UnInstallDB();
        $this->UnInstallFiles();
        $this->UnInstallEvents();

        $APPLICATION->IncludeAdminFile(GetMessage('ASPRO_MAX_SCOM_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/aspro.max/install/unstep.php');
    }
}

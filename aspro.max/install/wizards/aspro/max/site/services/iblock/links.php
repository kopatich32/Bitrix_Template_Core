<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) return;
if(!CModule::IncludeModule("aspro.max")) return;

if(!defined("WIZARD_SITE_ID")) return;
if(!defined("WIZARD_SITE_DIR")) return;
if(!defined("WIZARD_SITE_PATH")) return;
if(!defined("WIZARD_TEMPLATE_ID")) return;
if(!defined("WIZARD_TEMPLATE_ABSOLUTE_PATH")) return;
if(!defined("WIZARD_THEME_ID")) return;

$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."/";


// iblocks ids
$add_reviewIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_add_review"][0];
$partnersIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_partners"][0];
$brandsIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_brands"][0];
$newsIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_news"][0];
$stockIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_stock"][0];
$servicesIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_services"][0];
$catalogIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_catalog"][0];
$projectsIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_projects"][0];
$staffIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_staff"][0];
$regionsIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_regionality"]["aspro_max_regions"][0];
$articlesIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_articles"][0];
$landingIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_landing"][0];
$searchIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_search"][0];
$cross_salesIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_cross_sales"][0];
$megamenuIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_megamenu"][0];
$tizersIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_tizers"][0];
$banners_innerIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_adv"]["aspro_max_banners_inner"][0];
$banners_catalogIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_adv"]["aspro_max_banners_catalog"][0];


// elements ids
$arCatalog = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($catalogIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $catalogIBlockID), false, false, array("ID", "XML_ID"));
$arStock = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($stockIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $stockIBlockID), false, false, array("ID", "XML_ID"));
$arBrands = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($brandsIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $brandsIBlockID), false, false, array("ID", "XML_ID"));
$arServices = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($servicesIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $servicesIBlockID), false, false, array("ID", "XML_ID"));
$arProjects = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($projectsIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $projectsIBlockID), false, false, array("ID", "XML_ID"));
$arNews = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($newsIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $newsIBlockID), false, false, array("ID", "XML_ID"));
$arArticles = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($articlesIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $articlesIBlockID), false, false, array("ID", "XML_ID"));
$arAdd_review = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($add_reviewIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $add_reviewIBlockID), false, false, array("ID", "XML_ID"));
$arStaff = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($staffIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $staffIBlockID), false, false, array("ID", "XML_ID"));
$arLanding = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($landingIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $landingIBlockID), false, false, array("ID", "XML_ID"));
$arSearch = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($searchIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $searchIBlockID), false, false, array("ID", "XML_ID"));
$arRegions = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($regionsIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $regionsIBlockID), false, false, array("ID", "XML_ID"));
$arCross_sales = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($cross_salesIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $cross_salesIBlockID), false, false, array("ID", "XML_ID"));
$arStock = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($stockIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $stockIBlockID), false, false, array("ID", "XML_ID"));


//update brand property in catalog
$propertiesCat = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$catalogIBlockID, "CODE"=>"BRAND"));
if($prop_brand = $propertiesCat ->GetNext())
{
	$ibpBrand = new CIBlockProperty();
	$ibpBrand ->Update($prop_brand ["ID"], array("LINK_IBLOCK_ID" => $brandsIBlockID));
}


// update links in aspro_max_stock
CIBlockElement::SetPropertyValuesEx($arStock["49174"], $stockIBlockID, array("LINK_GOODS" => array($arCatalog["49741"], $arCatalog["49744"], $arCatalog["49595"], $arCatalog["49738"])));
CIBlockElement::SetPropertyValuesEx($arStock["49859"], $stockIBlockID, array("LINK_GOODS" => array($arCatalog["10696"], $arCatalog["3457"], $arCatalog["10695"], $arCatalog["3444"], $arCatalog["10694"], $arCatalog["3456"], $arCatalog["3476"], $arCatalog["3477"], $arCatalog["3469"], $arCatalog["3463"], $arCatalog["3571"], $arCatalog["3317"])));
CIBlockElement::SetPropertyValuesEx($arStock["280"], $stockIBlockID, array("LINK_GOODS" => array($arCatalog["49803"], $arCatalog["49805"], $arCatalog["49695"], $arCatalog["49807"])));
CIBlockElement::SetPropertyValuesEx($arStock["3423"], $stockIBlockID, array("LINK_GOODS" => array($arCatalog["49580"], $arCatalog["49738"], $arCatalog["49729"], $arCatalog["49872"]), "LINK_BRANDS" => array($arBrands["49588"], $arBrands["49581"], $arBrands["3307"], $arBrands["49591"])));
CIBlockElement::SetPropertyValuesEx($arStock["3252"], $stockIBlockID, array("LINK_GOODS" => array($arCatalog["3640"], $arCatalog["3512"], $arCatalog["3548"], $arCatalog["3492"], $arCatalog["3458"], $arCatalog["3342"], $arCatalog["5504c08a-a427-11de-af6f-0017317e89c2"], $arCatalog["38fa886b-2b3f-11df-ae0b-00e05013051a"], $arCatalog["10695"], $arCatalog["fdf38118-41bc-11db-bff8-00030d2b3726"], $arCatalog["10694"], $arCatalog["382"], $arCatalog["3434"], $arCatalog["299"], $arCatalog["3528"], $arCatalog["294"])));

// update links in aspro_max_services
CIBlockElement::SetPropertyValuesEx($arServices["10630"], $servicesIBlockID, array("LINK_BRANDS" => array($arBrands["49591"], $arBrands["49588"], $arBrands["49581"], $arBrands["3307"])));
CIBlockElement::SetPropertyValuesEx($arServices["10636"], $servicesIBlockID, array("LINK_PROJECTS" => array($arProjects["895"]), "LINK_GOODS" => array($arCatalog["3547"], $arCatalog["3543"], $arCatalog["3545"], $arCatalog["3546"], $arCatalog["3544"], $arCatalog["3542"])));
CIBlockElement::SetPropertyValuesEx($arServices["10639"], $servicesIBlockID, array("LINK_PROJECTS" => array($arProjects["893"]), "LINK_GOODS" => array($arCatalog["49837"], $arCatalog["49805"])));
CIBlockElement::SetPropertyValuesEx($arServices["10629"], $servicesIBlockID, array("LINK_GOODS" => array($arCatalog["37eb861e-cb06-11e1-91a6-001cf08b4a3b"], $arCatalog["c9b5f5b9-0a80-11e1-90ff-001cf08b4a3b"])));
CIBlockElement::SetPropertyValuesEx($arServices["10638"], $servicesIBlockID, array("LINK_GOODS" => array($arCatalog["49872"], $arCatalog["49732"])));

// update links in aspro_max_catalog
CIBlockElement::SetPropertyValuesEx($arCatalog["49725"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "BRAND" => array($arBrands["3307"]), "EXPANDABLES" => array($arCatalog["49732"], $arCatalog["49734"], $arCatalog["49877"], $arCatalog["49599"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49771"], $catalogIBlockID, array("BRAND" => array($arBrands["49588"]), "EXPANDABLES" => array($arCatalog["49877"], $arCatalog["49732"], $arCatalog["49734"], $arCatalog["49599"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49580"], $catalogIBlockID, array("BRAND" => array($arBrands["3307"]), "EXPANDABLES" => array($arCatalog["49732"], $arCatalog["49599"], $arCatalog["49877"], $arCatalog["49734"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49767"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["49588"]), "EXPANDABLES" => array($arCatalog["49599"], $arCatalog["49732"], $arCatalog["49877"], $arCatalog["49734"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49729"], $catalogIBlockID, array("BRAND" => array($arBrands["3307"]), "EXPANDABLES" => array($arCatalog["49734"], $arCatalog["3425"], $arCatalog["3426"], $arCatalog["49732"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49732"], $catalogIBlockID, array("BRAND" => array($arBrands["3307"]), "EXPANDABLES" => array($arCatalog["49729"], $arCatalog["49734"], $arCatalog["49736"], $arCatalog["49725"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49734"], $catalogIBlockID, array("BRAND" => array($arBrands["49581"]), "EXPANDABLES" => array($arCatalog["49729"], $arCatalog["49767"], $arCatalog["49725"], $arCatalog["49732"], $arCatalog["49736"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49736"], $catalogIBlockID, array("BRAND" => array($arBrands["49581"]), "EXPANDABLES" => array($arCatalog["49734"], $arCatalog["49729"], $arCatalog["3426"], $arCatalog["49732"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49738"], $catalogIBlockID, array("BRAND" => array($arBrands["3307"]), "EXPANDABLES" => array($arCatalog["49734"], $arCatalog["49732"], $arCatalog["49729"], $arCatalog["49875"]), "ASSOCIATED" => array($arCatalog["3425"], $arCatalog["49595"], $arCatalog["49744"], $arCatalog["49741"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49744"], $catalogIBlockID, array("BRAND" => array($arBrands["3307"]), "EXPANDABLES" => array($arCatalog["49729"], $arCatalog["49732"], $arCatalog["49875"], $arCatalog["49734"]), "ASSOCIATED" => array($arCatalog["49741"], $arCatalog["3425"], $arCatalog["49738"], $arCatalog["49595"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49595"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49902"]), "LINK_BLOG" => array($arArticles["49914"]), "BRAND" => array($arBrands["3307"]), "EXPANDABLES" => array($arCatalog["49734"], $arCatalog["49875"], $arCatalog["49732"], $arCatalog["49729"]), "ASSOCIATED" => array($arCatalog["3426"], $arCatalog["49741"], $arCatalog["49744"], $arCatalog["49738"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49741"], $catalogIBlockID, array("BRAND" => array($arBrands["3307"]), "EXPANDABLES" => array($arCatalog["49734"], $arCatalog["49875"], $arCatalog["49729"], $arCatalog["49732"]), "ASSOCIATED" => array($arCatalog["3426"], $arCatalog["49595"], $arCatalog["49744"], $arCatalog["49738"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3425"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["49914"]), "BRAND" => array($arBrands["49581"]), "EXPANDABLES" => array($arCatalog["49734"], $arCatalog["49732"], $arCatalog["49875"]), "ASSOCIATED" => array($arCatalog["49738"], $arCatalog["3426"], $arCatalog["49595"], $arCatalog["49741"], $arCatalog["49744"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3426"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["49581"]), "EXPANDABLES" => array($arCatalog["49734"], $arCatalog["49875"], $arCatalog["49732"], $arCatalog["49729"]), "ASSOCIATED" => array($arCatalog["49741"], $arCatalog["49738"], $arCatalog["3425"], $arCatalog["49744"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49747"], $catalogIBlockID, array("BRAND" => array($arBrands["49612"]), "EXPANDABLES" => array($arCatalog["49858"], $arCatalog["3308"], $arCatalog["3475"], $arCatalog["3482"]), "ASSOCIATED" => array($arCatalog["3448"], $arCatalog["49750"], $arCatalog["49745"], $arCatalog["3449"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49750"], $catalogIBlockID, array("BRAND" => array($arBrands["49612"]), "EXPANDABLES" => array($arCatalog["49856"], $arCatalog["49633"], $arCatalog["49858"], $arCatalog["49860"]), "ASSOCIATED" => array($arCatalog["3449"], $arCatalog["3448"], $arCatalog["49747"], $arCatalog["49745"], $arCatalog["49606"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49606"], $catalogIBlockID, array("BRAND" => array($arBrands["49612"]), "EXPANDABLES" => array($arCatalog["3474"], $arCatalog["3475"], $arCatalog["49856"], $arCatalog["49858"]), "ASSOCIATED" => array($arCatalog["3449"], $arCatalog["49747"], $arCatalog["49750"], $arCatalog["49745"], $arCatalog["3448"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49745"], $catalogIBlockID, array("BRAND" => array($arBrands["49882"]), "EXPANDABLES" => array($arCatalog["49858"], $arCatalog["3475"], $arCatalog["3308"], $arCatalog["3474"]), "ASSOCIATED" => array($arCatalog["3449"], $arCatalog["3448"], $arCatalog["49606"], $arCatalog["49750"], $arCatalog["49747"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3448"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49908"]), "LINK_BLOG" => array($arArticles["280"]), "BRAND" => array($arBrands["49882"]), "EXPANDABLES" => array($arCatalog["49621"], $arCatalog["49858"], $arCatalog["3474"], $arCatalog["3475"]), "ASSOCIATED" => array($arCatalog["49747"], $arCatalog["3449"], $arCatalog["49606"], $arCatalog["49745"], $arCatalog["49750"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3449"], $catalogIBlockID, array("BRAND" => array($arBrands["49882"]), "EXPANDABLES" => array($arCatalog["3474"], $arCatalog["3475"], $arCatalog["49621"], $arCatalog["49860"]), "ASSOCIATED" => array($arCatalog["49745"], $arCatalog["49747"], $arCatalog["49750"], $arCatalog["49606"], $arCatalog["3448"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49895"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["49608"]), "EXPANDABLES" => array($arCatalog["514"], $arCatalog["49863"], $arCatalog["49635"], $arCatalog["49867"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49899"], $catalogIBlockID, array("BRAND" => array($arBrands["49608"]), "EXPANDABLES" => array($arCatalog["3482"], $arCatalog["3480"], $arCatalog["514"], $arCatalog["49635"]), "ASSOCIATED" => array($arCatalog["49895"], $arCatalog["3475"], $arCatalog["49625"], $arCatalog["49899"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49625"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["49608"]), "EXPANDABLES" => array($arCatalog["514"], $arCatalog["49863"], $arCatalog["49635"], $arCatalog["49867"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3475"], $catalogIBlockID, array("BRAND" => array($arBrands["49608"]), "EXPANDABLES" => array($arCatalog["49867"], $arCatalog["49635"], $arCatalog["49863"], $arCatalog["514"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49621"], $catalogIBlockID, array("BRAND" => array($arBrands["49853"]), "EXPANDABLES" => array($arCatalog["514"], $arCatalog["49863"], $arCatalog["49635"], $arCatalog["49867"]), "ASSOCIATED" => array($arCatalog["3474"], $arCatalog["3473"], $arCatalog["3308"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3308"], $catalogIBlockID, array("BRAND" => array($arBrands["49853"]), "EXPANDABLES" => array($arCatalog["514"], $arCatalog["49863"], $arCatalog["49635"], $arCatalog["49867"]), "ASSOCIATED" => array($arCatalog["3474"], $arCatalog["3473"], $arCatalog["49621"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3473"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["49853"]), "EXPANDABLES" => array($arCatalog["49863"], $arCatalog["514"], $arCatalog["49635"], $arCatalog["49867"]), "ASSOCIATED" => array($arCatalog["3474"], $arCatalog["3308"], $arCatalog["49621"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3474"], $catalogIBlockID, array("BRAND" => array($arBrands["49853"]), "EXPANDABLES" => array($arCatalog["514"], $arCatalog["49863"], $arCatalog["49635"], $arCatalog["49867"]), "ASSOCIATED" => array($arCatalog["3473"], $arCatalog["3308"], $arCatalog["49621"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49780"], $catalogIBlockID, array("BRAND" => array($arBrands["282"]), "ASSOCIATED" => array($arCatalog["49778"], $arCatalog["3483"], $arCatalog["49776"], $arCatalog["49669"], $arCatalog["49782"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49782"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["331"]), "ASSOCIATED" => array($arCatalog["49778"], $arCatalog["3483"], $arCatalog["49776"], $arCatalog["49669"], $arCatalog["49780"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49669"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["331"]), "ASSOCIATED" => array($arCatalog["3483"], $arCatalog["49778"], $arCatalog["49776"], $arCatalog["49782"], $arCatalog["49780"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49776"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["288"]), "ASSOCIATED" => array($arCatalog["49669"], $arCatalog["3483"], $arCatalog["49782"], $arCatalog["49778"], $arCatalog["49780"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49778"], $catalogIBlockID, array("BRAND" => array($arBrands["282"]), "ASSOCIATED" => array($arCatalog["49776"], $arCatalog["3483"], $arCatalog["49669"], $arCatalog["49782"], $arCatalog["49780"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3483"], $catalogIBlockID, array("BRAND" => array($arBrands["288"]), "ASSOCIATED" => array($arCatalog["49778"], $arCatalog["49776"], $arCatalog["49669"], $arCatalog["49782"], $arCatalog["49780"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49672"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["10633"]), "BRAND" => array($arBrands["288"]), "ASSOCIATED" => array($arCatalog["3507"], $arCatalog["3506"], $arCatalog["49784"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3506"], $catalogIBlockID, array("BRAND" => array($arBrands["331"]), "ASSOCIATED" => array($arCatalog["3507"], $arCatalog["49784"], $arCatalog["49672"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49784"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["282"]), "ASSOCIATED" => array($arCatalog["3507"], $arCatalog["3506"], $arCatalog["49672"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3507"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["331"]), "ASSOCIATED" => array($arCatalog["3506"], $arCatalog["49784"], $arCatalog["49672"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49675"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "BRAND" => array($arBrands["282"]), "ASSOCIATED" => array($arCatalog["3509"], $arCatalog["3508"], $arCatalog["49786"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49786"], $catalogIBlockID, array("BRAND" => array($arBrands["282"]), "ASSOCIATED" => array($arCatalog["3509"], $arCatalog["3508"], $arCatalog["49675"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3508"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["288"]), "ASSOCIATED" => array($arCatalog["3509"], $arCatalog["49786"], $arCatalog["49675"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3509"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["331"]), "ASSOCIATED" => array($arCatalog["3508"], $arCatalog["49786"], $arCatalog["49675"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["8951"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["832"]), "ASSOCIATED" => array($arCatalog["3537"], $arCatalog["5659"], $arCatalog["448"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3537"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["10633"]), "BRAND" => array($arBrands["832"]), "ASSOCIATED" => array($arCatalog["448"], $arCatalog["5659"], $arCatalog["8951"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["448"], $catalogIBlockID, array("BRAND" => array($arBrands["49597"]), "ASSOCIATED" => array($arCatalog["3537"], $arCatalog["5659"], $arCatalog["8951"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["5659"], $catalogIBlockID, array("BRAND" => array($arBrands["3301"]), "ASSOCIATED" => array($arCatalog["3537"], $arCatalog["8951"], $arCatalog["448"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["5644"], $catalogIBlockID, array("BRAND" => array($arBrands["832"]), "ASSOCIATED" => array($arCatalog["99e8b3e5-8bab-11de-8a16-001bfc2f194f"], $arCatalog["5698"], $arCatalog["857bf915-9d4a-11e0-908c-001cf08b4a3b"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["99e8b3e5-8bab-11de-8a16-001bfc2f194f"], $catalogIBlockID, array("BRAND" => array($arBrands["3301"]), "ASSOCIATED" => array($arCatalog["5644"], $arCatalog["5698"], $arCatalog["857bf915-9d4a-11e0-908c-001cf08b4a3b"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["857bf915-9d4a-11e0-908c-001cf08b4a3b"], $catalogIBlockID, array("BRAND" => array($arBrands["49597"]), "ASSOCIATED" => array($arCatalog["99e8b3e5-8bab-11de-8a16-001bfc2f194f"], $arCatalog["5644"], $arCatalog["5698"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["5698"], $catalogIBlockID, array("BRAND" => array($arBrands["832"]), "ASSOCIATED" => array($arCatalog["99e8b3e5-8bab-11de-8a16-001bfc2f194f"], $arCatalog["5644"], $arCatalog["857bf915-9d4a-11e0-908c-001cf08b4a3b"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49809"], $catalogIBlockID, array("BRAND" => array($arBrands["3312"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49811"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["3312"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49699"], $catalogIBlockID, array("BRAND" => array($arBrands["3312"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49701"], $catalogIBlockID, array("BRAND" => array($arBrands["3316"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49799"], $catalogIBlockID, array("BRAND" => array($arBrands["3313"]), "EXPANDABLES" => array($arCatalog["49809"], $arCatalog["49699"], $arCatalog["49701"], $arCatalog["49811"]), "ASSOCIATED" => array($arCatalog["49809"], $arCatalog["49699"], $arCatalog["49811"], $arCatalog["49701"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3431"], $catalogIBlockID, array("BRAND" => array($arBrands["3316"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49803"], $catalogIBlockID, array("BRAND" => array($arBrands["3312"]), "ASSOCIATED" => array($arCatalog["49695"], $arCatalog["49805"], $arCatalog["49807"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49807"], $catalogIBlockID, array("BRAND" => array($arBrands["3313"]), "ASSOCIATED" => array($arCatalog["49803"], $arCatalog["49805"], $arCatalog["49695"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49695"], $catalogIBlockID, array("BRAND" => array($arBrands["3315"]), "ASSOCIATED" => array($arCatalog["49803"], $arCatalog["49807"], $arCatalog["49805"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49805"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["10635"]), "BRAND" => array($arBrands["3316"]), "ASSOCIATED" => array($arCatalog["49695"], $arCatalog["49803"], $arCatalog["49807"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49835"], $catalogIBlockID, array("BRAND" => array($arBrands["3312"]), "ASSOCIATED" => array($arCatalog["49839"], $arCatalog["49714"], $arCatalog["49837"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49839"], $catalogIBlockID, array("BRAND" => array($arBrands["3316"]), "ASSOCIATED" => array($arCatalog["49714"], $arCatalog["49835"], $arCatalog["49837"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49714"], $catalogIBlockID, array("BRAND" => array($arBrands["3315"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49837"], $catalogIBlockID, array("BRAND" => array($arBrands["3312"]), "ASSOCIATED" => array($arCatalog["49835"], $arCatalog["49839"], $arCatalog["49714"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49823"], $catalogIBlockID, array("BRAND" => array($arBrands["3315"]), "ASSOCIATED" => array($arCatalog["49705"], $arCatalog["49827"], $arCatalog["49821"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49827"], $catalogIBlockID, array("BRAND" => array($arBrands["3315"]), "ASSOCIATED" => array($arCatalog["49823"], $arCatalog["49821"], $arCatalog["49705"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49705"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "BRAND" => array($arBrands["3316"]), "ASSOCIATED" => array($arCatalog["49827"], $arCatalog["49823"], $arCatalog["49821"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49821"], $catalogIBlockID, array("BRAND" => array($arBrands["3312"]), "ASSOCIATED" => array($arCatalog["49705"], $arCatalog["49827"], $arCatalog["49823"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49650"], $catalogIBlockID, array("BRAND" => array($arBrands["3303"]), "ASSOCIATED" => array($arCatalog["3638"], $arCatalog["3636"], $arCatalog["3637"], $arCatalog["49759"], $arCatalog["49644"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3636"], $catalogIBlockID, array("BRAND" => array($arBrands["3300"]), "ASSOCIATED" => array($arCatalog["3638"], $arCatalog["3637"], $arCatalog["49644"], $arCatalog["49759"], $arCatalog["49650"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49759"], $catalogIBlockID, array("BRAND" => array($arBrands["49871"]), "ASSOCIATED" => array($arCatalog["3636"], $arCatalog["3638"], $arCatalog["3637"], $arCatalog["49644"], $arCatalog["49650"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3637"], $catalogIBlockID, array("BRAND" => array($arBrands["3300"]), "ASSOCIATED" => array($arCatalog["3638"], $arCatalog["3636"], $arCatalog["49644"], $arCatalog["49759"], $arCatalog["49650"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49644"], $catalogIBlockID, array("BRAND" => array($arBrands["49871"]), "ASSOCIATED" => array($arCatalog["3638"], $arCatalog["3637"], $arCatalog["3636"], $arCatalog["49759"], $arCatalog["49650"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3638"], $catalogIBlockID, array("BRAND" => array($arBrands["49871"]), "ASSOCIATED" => array($arCatalog["3637"], $arCatalog["3636"], $arCatalog["49644"], $arCatalog["49759"], $arCatalog["49650"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49761"], $catalogIBlockID, array("BRAND" => array($arBrands["3300"]), "EXPANDABLES" => array($arCatalog["49759"], $arCatalog["49650"], $arCatalog["3636"], $arCatalog["49644"], $arCatalog["3638"], $arCatalog["3637"]), "ASSOCIATED" => array($arCatalog["49765"], $arCatalog["49763"], $arCatalog["49774"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49774"], $catalogIBlockID, array("BRAND" => array($arBrands["49871"]), "EXPANDABLES" => array($arCatalog["3637"], $arCatalog["49650"], $arCatalog["3638"], $arCatalog["49759"], $arCatalog["3636"], $arCatalog["49644"]), "ASSOCIATED" => array($arCatalog["49765"], $arCatalog["49763"], $arCatalog["49761"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49763"], $catalogIBlockID, array("BRAND" => array($arBrands["3300"]), "ASSOCIATED" => array($arCatalog["49765"], $arCatalog["49774"], $arCatalog["49761"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49765"], $catalogIBlockID, array("BRAND" => array($arBrands["49871"]), "EXPANDABLES" => array($arCatalog["3637"], $arCatalog["49759"], $arCatalog["49644"], $arCatalog["3636"], $arCatalog["49650"], $arCatalog["3638"]), "ASSOCIATED" => array($arCatalog["49763"], $arCatalog["49774"], $arCatalog["49761"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49854"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["3333"]), "EXPANDABLES" => array($arCatalog["49635"], $arCatalog["514"], $arCatalog["49867"], $arCatalog["49863"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49856"], $catalogIBlockID, array("BRAND" => array($arBrands["3333"]), "EXPANDABLES" => array($arCatalog["514"], $arCatalog["49635"], $arCatalog["49867"], $arCatalog["49863"]), "ASSOCIATED" => array($arCatalog["49851"], $arCatalog["49860"], $arCatalog["49858"], $arCatalog["49633"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49633"], $catalogIBlockID, array("BRAND" => array($arBrands["3333"]), "EXPANDABLES" => array($arCatalog["49635"], $arCatalog["514"], $arCatalog["49863"], $arCatalog["49867"]), "ASSOCIATED" => array($arCatalog["49860"], $arCatalog["49858"], $arCatalog["49856"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49851"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["3333"]), "EXPANDABLES" => array($arCatalog["49863"], $arCatalog["514"], $arCatalog["49867"], $arCatalog["49635"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49858"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["3333"]), "EXPANDABLES" => array($arCatalog["49867"], $arCatalog["49635"], $arCatalog["514"], $arCatalog["49863"]), "ASSOCIATED" => array($arCatalog["49851"], $arCatalog["49633"], $arCatalog["49860"], $arCatalog["49856"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49860"], $catalogIBlockID, array("BRAND" => array($arBrands["3333"]), "EXPANDABLES" => array($arCatalog["49635"], $arCatalog["49867"], $arCatalog["49863"], $arCatalog["514"]), "ASSOCIATED" => array($arCatalog["49858"], $arCatalog["49633"], $arCatalog["49856"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49887"], $catalogIBlockID, array("BRAND" => array($arBrands["3302"]), "ASSOCIATED" => array($arCatalog["3631"], $arCatalog["49639"], $arCatalog["49889"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49889"], $catalogIBlockID, array("BRAND" => array($arBrands["3302"]), "ASSOCIATED" => array($arCatalog["3631"], $arCatalog["49639"], $arCatalog["49887"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49639"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["3302"]), "ASSOCIATED" => array($arCatalog["3631"], $arCatalog["49889"], $arCatalog["49887"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3631"], $catalogIBlockID, array("BRAND" => array($arBrands["3302"]), "ASSOCIATED" => array($arCatalog["49639"], $arCatalog["49889"], $arCatalog["49887"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49867"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["3303"]), "ASSOCIATED" => array($arCatalog["514"], $arCatalog["49863"], $arCatalog["49635"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49635"], $catalogIBlockID, array("BRAND" => array($arBrands["3303"]), "ASSOCIATED" => array($arCatalog["514"], $arCatalog["49863"], $arCatalog["49867"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49863"], $catalogIBlockID, array("BRAND" => array($arBrands["3303"]), "ASSOCIATED" => array($arCatalog["514"], $arCatalog["49635"], $arCatalog["49867"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["514"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["10633"]), "BRAND" => array($arBrands["3303"]), "ASSOCIATED" => array($arCatalog["49863"], $arCatalog["49635"], $arCatalog["49867"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["10701"], $catalogIBlockID, array("BRAND" => array($arBrands["49896"]), "ASSOCIATED" => array($arCatalog["3610"], $arCatalog["3605"], $arCatalog["3601"], $arCatalog["3597"], $arCatalog["396"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["396"], $catalogIBlockID, array("BRAND" => array($arBrands["49605"]), "ASSOCIATED" => array($arCatalog["3610"], $arCatalog["3605"], $arCatalog["3601"], $arCatalog["3597"], $arCatalog["10701"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3597"], $catalogIBlockID, array("BRAND" => array($arBrands["49605"]), "ASSOCIATED" => array($arCatalog["3610"], $arCatalog["3605"], $arCatalog["3601"], $arCatalog["396"], $arCatalog["10701"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3601"], $catalogIBlockID, array("BRAND" => array($arBrands["49896"]), "ASSOCIATED" => array($arCatalog["3610"], $arCatalog["3605"], $arCatalog["3601"], $arCatalog["3597"], $arCatalog["396"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3605"], $catalogIBlockID, array("BRAND" => array($arBrands["49896"]), "ASSOCIATED" => array($arCatalog["3610"], $arCatalog["3601"], $arCatalog["3597"], $arCatalog["396"], $arCatalog["10701"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3610"], $catalogIBlockID, array("BRAND" => array($arBrands["49896"]), "ASSOCIATED" => array($arCatalog["3605"], $arCatalog["3601"], $arCatalog["3597"], $arCatalog["396"], $arCatalog["10701"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["10689"], $catalogIBlockID, array("BRAND" => array($arBrands["49896"]), "ASSOCIATED" => array($arCatalog["10690"], $arCatalog["10693"], $arCatalog["10691"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["10690"], $catalogIBlockID, array("BRAND" => array($arBrands["49605"]), "ASSOCIATED" => array($arCatalog["10689"], $arCatalog["10693"], $arCatalog["10691"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["10691"], $catalogIBlockID, array("BRAND" => array($arBrands["49896"]), "EXPANDABLES" => array($arCatalog["3597"], $arCatalog["10701"], $arCatalog["3605"], $arCatalog["396"], $arCatalog["3610"], $arCatalog["3601"]), "ASSOCIATED" => array($arCatalog["10690"], $arCatalog["10689"], $arCatalog["10693"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["10693"], $catalogIBlockID, array("BRAND" => array($arBrands["49605"]), "EXPANDABLES" => array($arCatalog["3601"], $arCatalog["3597"], $arCatalog["10701"], $arCatalog["3605"], $arCatalog["396"], $arCatalog["3610"]), "ASSOCIATED" => array($arCatalog["10690"], $arCatalog["10689"], $arCatalog["10691"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["294"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["3422"]), "BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3294"], $arCatalog["3459"], $arCatalog["3351"], $arCatalog["3342"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["313"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["309"], $arCatalog["299"], $arCatalog["3347"], $arCatalog["3334"]), "ASSOCIATED" => array($arCatalog["299"], $arCatalog["294"], $arCatalog["309"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["299"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3334"], $arCatalog["3342"], $arCatalog["313"], $arCatalog["309"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["309"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49905"]), "BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3342"], $arCatalog["3351"], $arCatalog["3334"], $arCatalog["299"], $arCatalog["313"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["321"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["3422"]), "BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["10690"], $arCatalog["10689"], $arCatalog["8951"], $arCatalog["3324"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["346"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["3422"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["3537"], $arCatalog["333"], $arCatalog["3434"], $arCatalog["3317"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["325"], $catalogIBlockID, array("BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["448"], $arCatalog["333"], $arCatalog["3434"], $arCatalog["3329"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["333"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["3422"]), "BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["3329"], $arCatalog["3501"], $arCatalog["3317"], $arCatalog["325"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3434"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["3537"], $arCatalog["3317"], $arCatalog["3444"], $arCatalog["325"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3444"], $catalogIBlockID, array("BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["3476"], $arCatalog["3434"], $arCatalog["3317"], $arCatalog["3463"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["382"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49905"]), "BRAND" => array($arBrands["320"]), "EXPANDABLES" => array($arCatalog["3538"], $arCatalog["395"], $arCatalog["3512"], $arCatalog["3518"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["395"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["3422"]), "BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["3539"], $arCatalog["3518"], $arCatalog["3524"], $arCatalog["382"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["400"], $catalogIBlockID, array("BRAND" => array($arBrands["320"]), "EXPANDABLES" => array($arCatalog["3539"], $arCatalog["382"], $arCatalog["3571"], $arCatalog["3548"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["408"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["320"]), "ASSOCIATED" => array($arCatalog["382"], $arCatalog["395"], $arCatalog["400"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["355"], $catalogIBlockID, array("BRAND" => array($arBrands["320"]), "EXPANDABLES" => array($arCatalog["3644"], $arCatalog["3643"], $arCatalog["3641"], $arCatalog["3642"]), "ASSOCIATED" => array($arCatalog["360"], $arCatalog["350"], $arCatalog["355"], $arCatalog["364"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["360"], $catalogIBlockID, array("BRAND" => array($arBrands["320"]), "EXPANDABLES" => array($arCatalog["3641"], $arCatalog["3642"], $arCatalog["355"], $arCatalog["364"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["350"], $catalogIBlockID, array("BRAND" => array($arBrands["320"]), "EXPANDABLES" => array($arCatalog["3121"], $arCatalog["3634"], $arCatalog["3122"], $arCatalog["3633"], $arCatalog["3632"], $arCatalog["3635"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["364"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["3422"]), "BRAND" => array($arBrands["320"]), "EXPANDABLES" => array($arCatalog["3644"], $arCatalog["3643"], $arCatalog["3641"], $arCatalog["3642"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["10695"], $catalogIBlockID, array("BRAND" => array($arBrands["49632"]), "ASSOCIATED" => array($arCatalog["3458"], $arCatalog["3457"], $arCatalog["3456"], $arCatalog["10694"], $arCatalog["10696"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["10696"], $catalogIBlockID, array("BRAND" => array($arBrands["3305"]), "EXPANDABLES" => array($arCatalog["299"], $arCatalog["3434"], $arCatalog["10700"], $arCatalog["10699"]), "ASSOCIATED" => array($arCatalog["3458"], $arCatalog["3457"], $arCatalog["3456"], $arCatalog["10694"], $arCatalog["10695"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["10694"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["3306"]), "ASSOCIATED" => array($arCatalog["3458"], $arCatalog["3457"], $arCatalog["3456"], $arCatalog["10696"], $arCatalog["10695"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3456"], $catalogIBlockID, array("BRAND" => array($arBrands["3306"]), "ASSOCIATED" => array($arCatalog["3458"], $arCatalog["3457"], $arCatalog["10694"], $arCatalog["10696"], $arCatalog["10695"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3457"], $catalogIBlockID, array("BRAND" => array($arBrands["3305"]), "ASSOCIATED" => array($arCatalog["3458"], $arCatalog["3456"], $arCatalog["10694"], $arCatalog["10696"], $arCatalog["10695"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3458"], $catalogIBlockID, array("BRAND" => array($arBrands["49632"]), "EXPANDABLES" => array($arCatalog["3632"], $arCatalog["3633"], $arCatalog["3122"], $arCatalog["3634"], $arCatalog["3121"], $arCatalog["3635"]), "ASSOCIATED" => array($arCatalog["3457"], $arCatalog["3456"], $arCatalog["10694"], $arCatalog["10696"], $arCatalog["10695"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["10699"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "ASSOCIATED" => array($arCatalog["10698"], $arCatalog["10700"], $arCatalog["10697"], $arCatalog["10696"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["10700"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "ASSOCIATED" => array($arCatalog["10699"], $arCatalog["10697"], $arCatalog["10698"], $arCatalog["10695"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["10698"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "ASSOCIATED" => array($arCatalog["10697"], $arCatalog["10699"], $arCatalog["10700"], $arCatalog["10695"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["10697"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "ASSOCIATED" => array($arCatalog["10700"], $arCatalog["10699"], $arCatalog["10698"], $arCatalog["10696"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["fdf38118-41bc-11db-bff8-00030d2b3726"], $catalogIBlockID, array("BRAND" => array($arBrands["3356"]), "ASSOCIATED" => array($arCatalog["3640"], $arCatalog["3639"], $arCatalog["2bdcd9b4-f480-11e0-90e5-001cf08b4a3b"], $arCatalog["37eb861e-cb06-11e1-91a6-001cf08b4a3b"], $arCatalog["fdf3810c-41bc-11db-bff8-00030d2b3726"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3640"], $catalogIBlockID, array("BRAND" => array($arBrands["3356"]), "ASSOCIATED" => array($arCatalog["3639"], $arCatalog["2bdcd9b4-f480-11e0-90e5-001cf08b4a3b"], $arCatalog["37eb861e-cb06-11e1-91a6-001cf08b4a3b"], $arCatalog["fdf3810c-41bc-11db-bff8-00030d2b3726"], $arCatalog["fdf38118-41bc-11db-bff8-00030d2b3726"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["fdf3810c-41bc-11db-bff8-00030d2b3726"], $catalogIBlockID, array("BRAND" => array($arBrands["454"]), "ASSOCIATED" => array($arCatalog["3640"], $arCatalog["3639"], $arCatalog["2bdcd9b4-f480-11e0-90e5-001cf08b4a3b"], $arCatalog["37eb861e-cb06-11e1-91a6-001cf08b4a3b"], $arCatalog["fdf38118-41bc-11db-bff8-00030d2b3726"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["37eb861e-cb06-11e1-91a6-001cf08b4a3b"], $catalogIBlockID, array("BRAND" => array($arBrands["454"]), "ASSOCIATED" => array($arCatalog["2bdcd9b4-f480-11e0-90e5-001cf08b4a3b"], $arCatalog["fdf3810c-41bc-11db-bff8-00030d2b3726"], $arCatalog["fdf38118-41bc-11db-bff8-00030d2b3726"], $arCatalog["c9b5f5b9-0a80-11e1-90ff-001cf08b4a3b"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3639"], $catalogIBlockID, array("BRAND" => array($arBrands["3356"]), "ASSOCIATED" => array($arCatalog["fdf38118-41bc-11db-bff8-00030d2b3726"], $arCatalog["3640"], $arCatalog["fdf3810c-41bc-11db-bff8-00030d2b3726"], $arCatalog["37eb861e-cb06-11e1-91a6-001cf08b4a3b"], $arCatalog["2bdcd9b4-f480-11e0-90e5-001cf08b4a3b"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["2bdcd9b4-f480-11e0-90e5-001cf08b4a3b"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["10633"]), "BRAND" => array($arBrands["454"]), "ASSOCIATED" => array($arCatalog["3639"], $arCatalog["37eb861e-cb06-11e1-91a6-001cf08b4a3b"], $arCatalog["fdf38118-41bc-11db-bff8-00030d2b3726"], $arCatalog["fdf3810c-41bc-11db-bff8-00030d2b3726"], $arCatalog["3640"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["5504c08a-a427-11de-af6f-0017317e89c2"], $catalogIBlockID, array("BRAND" => array($arBrands["454"]), "ASSOCIATED" => array($arCatalog["c9b5f5b9-0a80-11e1-90ff-001cf08b4a3b"], $arCatalog["38fa886b-2b3f-11df-ae0b-00e05013051a"], $arCatalog["3357a620-051d-11e0-a0bd-001d601faca5"], $arCatalog["fdf38118-41bc-11db-bff8-00030d2b3726"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["38fa886b-2b3f-11df-ae0b-00e05013051a"], $catalogIBlockID, array("BRAND" => array($arBrands["454"]), "ASSOCIATED" => array($arCatalog["c9b5f5b9-0a80-11e1-90ff-001cf08b4a3b"], $arCatalog["5504c08a-a427-11de-af6f-0017317e89c2"], $arCatalog["3357a620-051d-11e0-a0bd-001d601faca5"], $arCatalog["fdf3810c-41bc-11db-bff8-00030d2b3726"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3357a620-051d-11e0-a0bd-001d601faca5"], $catalogIBlockID, array("BRAND" => array($arBrands["454"]), "ASSOCIATED" => array($arCatalog["c9b5f5b9-0a80-11e1-90ff-001cf08b4a3b"], $arCatalog["5504c08a-a427-11de-af6f-0017317e89c2"], $arCatalog["38fa886b-2b3f-11df-ae0b-00e05013051a"], $arCatalog["2bdcd9b4-f480-11e0-90e5-001cf08b4a3b"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["c9b5f5b9-0a80-11e1-90ff-001cf08b4a3b"], $catalogIBlockID, array("BRAND" => array($arBrands["454"]), "ASSOCIATED" => array($arCatalog["5504c08a-a427-11de-af6f-0017317e89c2"], $arCatalog["38fa886b-2b3f-11df-ae0b-00e05013051a"], $arCatalog["3357a620-051d-11e0-a0bd-001d601faca5"], $arCatalog["fdf3810c-41bc-11db-bff8-00030d2b3726"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49875"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["49591"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49877"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"], $arNews["10633"]), "BRAND" => array($arBrands["49591"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49599"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["10633"]), "BRAND" => array($arBrands["49591"]), "EXPANDABLES" => array($arCatalog["49732"], $arCatalog["49734"], $arCatalog["49877"], $arCatalog["49872"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["49872"], $catalogIBlockID, array("BRAND" => array($arBrands["49591"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3632"], $catalogIBlockID, array("BRAND" => array($arBrands["49638"]), "EXPANDABLES" => array($arCatalog["3641"], $arCatalog["3643"], $arCatalog["3644"], $arCatalog["3642"]), "ASSOCIATED" => array($arCatalog["3635"], $arCatalog["3122"], $arCatalog["3634"], $arCatalog["3121"], $arCatalog["3633"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3633"], $catalogIBlockID, array("BRAND" => array($arBrands["49638"]), "EXPANDABLES" => array($arCatalog["3643"], $arCatalog["3644"], $arCatalog["3642"], $arCatalog["3641"]), "ASSOCIATED" => array($arCatalog["3632"], $arCatalog["3122"], $arCatalog["3634"], $arCatalog["3121"], $arCatalog["3635"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3634"], $catalogIBlockID, array("BRAND" => array($arBrands["49638"]), "EXPANDABLES" => array($arCatalog["3643"], $arCatalog["3644"], $arCatalog["3641"], $arCatalog["3642"]), "ASSOCIATED" => array($arCatalog["3632"], $arCatalog["3633"], $arCatalog["3122"], $arCatalog["3121"], $arCatalog["3635"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3635"], $catalogIBlockID, array("BRAND" => array($arBrands["49638"]), "EXPANDABLES" => array($arCatalog["3641"], $arCatalog["3643"], $arCatalog["3644"], $arCatalog["3642"]), "ASSOCIATED" => array($arCatalog["3632"], $arCatalog["3633"], $arCatalog["3122"], $arCatalog["3634"], $arCatalog["3121"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3121"], $catalogIBlockID, array("BRAND" => array($arBrands["49638"]), "EXPANDABLES" => array($arCatalog["3641"], $arCatalog["3643"], $arCatalog["3644"], $arCatalog["3642"]), "ASSOCIATED" => array($arCatalog["3635"], $arCatalog["3633"], $arCatalog["3122"], $arCatalog["3634"], $arCatalog["3632"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3122"], $catalogIBlockID, array("BRAND" => array($arBrands["49638"]), "EXPANDABLES" => array($arCatalog["3643"], $arCatalog["3644"], $arCatalog["3642"], $arCatalog["3641"]), "ASSOCIATED" => array($arCatalog["3635"], $arCatalog["3634"], $arCatalog["3121"], $arCatalog["3632"], $arCatalog["3633"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3288"], $catalogIBlockID, array("BRAND" => array($arBrands["3304"]), "EXPANDABLES" => array($arCatalog["3462"], $arCatalog["3461"], $arCatalog["3460"], $arCatalog["3459"], $arCatalog["3452"], $arCatalog["3451"], $arCatalog["3294"], $arCatalog["3293"]), "ASSOCIATED" => array($arCatalog["3292"], $arCatalog["3291"], $arCatalog["3290"], $arCatalog["3289"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3289"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["3304"]), "EXPANDABLES" => array($arCatalog["3452"], $arCatalog["3451"], $arCatalog["3294"], $arCatalog["3293"]), "ASSOCIATED" => array($arCatalog["3292"], $arCatalog["3291"], $arCatalog["3290"], $arCatalog["3288"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3290"], $catalogIBlockID, array("BRAND" => array($arBrands["3304"]), "EXPANDABLES" => array($arCatalog["3288"], $arCatalog["3292"], $arCatalog["3290"], $arCatalog["3289"], $arCatalog["3291"]), "ASSOCIATED" => array($arCatalog["3292"], $arCatalog["3289"], $arCatalog["3294"], $arCatalog["3293"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3291"], $catalogIBlockID, array("BRAND" => array($arBrands["3304"]), "EXPANDABLES" => array($arCatalog["3288"], $arCatalog["3293"], $arCatalog["3294"], $arCatalog["3291"]), "ASSOCIATED" => array($arCatalog["3291"], $arCatalog["3289"], $arCatalog["3288"], $arCatalog["3290"], $arCatalog["3292"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3292"], $catalogIBlockID, array("BRAND" => array($arBrands["3304"]), "EXPANDABLES" => array($arCatalog["3288"], $arCatalog["3293"], $arCatalog["3294"], $arCatalog["3292"]), "ASSOCIATED" => array($arCatalog["3291"], $arCatalog["3289"], $arCatalog["3288"], $arCatalog["3292"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3293"], $catalogIBlockID, array("BRAND" => array($arBrands["49670"]), "EXPANDABLES" => array($arCatalog["3288"], $arCatalog["3290"], $arCatalog["3292"], $arCatalog["3291"]), "ASSOCIATED" => array($arCatalog["3294"], $arCatalog["3291"], $arCatalog["3288"], $arCatalog["3292"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3294"], $catalogIBlockID, array("BRAND" => array($arBrands["397"]), "EXPANDABLES" => array($arCatalog["3288"], $arCatalog["3291"], $arCatalog["3289"], $arCatalog["3290"]), "ASSOCIATED" => array($arCatalog["3288"], $arCatalog["3292"], $arCatalog["3291"], $arCatalog["3293"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3451"], $catalogIBlockID, array("BRAND" => array($arBrands["49670"]), "EXPANDABLES" => array($arCatalog["3288"], $arCatalog["3290"], $arCatalog["3292"], $arCatalog["3291"]), "ASSOCIATED" => array($arCatalog["3294"], $arCatalog["3291"], $arCatalog["3288"], $arCatalog["3292"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3452"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["397"]), "EXPANDABLES" => array($arCatalog["3288"], $arCatalog["3290"], $arCatalog["3292"], $arCatalog["3291"]), "ASSOCIATED" => array($arCatalog["3294"], $arCatalog["3291"], $arCatalog["3288"], $arCatalog["3292"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3317"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["346"], $arCatalog["3444"], $arCatalog["3434"], $arCatalog["333"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3324"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["448"], $arCatalog["8951"], $arCatalog["10690"], $arCatalog["321"]), "ASSOCIATED" => array($arCatalog["3329"], $arCatalog["3501"], $arCatalog["3487"], $arCatalog["3317"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3329"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["346"], $arCatalog["3434"], $arCatalog["333"], $arCatalog["321"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3487"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["448"], $arCatalog["321"], $arCatalog["346"], $arCatalog["333"]), "ASSOCIATED" => array($arCatalog["3324"], $arCatalog["3329"], $arCatalog["3501"], $arCatalog["3317"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3492"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["8951"], $arCatalog["3434"], $arCatalog["325"], $arCatalog["3537"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3501"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["333"], $arCatalog["346"], $arCatalog["5659"]), "ASSOCIATED" => array($arCatalog["3329"], $arCatalog["3324"], $arCatalog["3487"], $arCatalog["3317"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3334"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["10633"]), "BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["294"], $arCatalog["309"], $arCatalog["299"], $arCatalog["313"]), "ASSOCIATED" => array($arCatalog["3351"], $arCatalog["3347"], $arCatalog["3342"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3342"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["313"], $arCatalog["309"], $arCatalog["299"], $arCatalog["294"]), "ASSOCIATED" => array($arCatalog["3351"], $arCatalog["3347"], $arCatalog["3334"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3347"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["294"], $arCatalog["299"], $arCatalog["309"], $arCatalog["313"]), "ASSOCIATED" => array($arCatalog["3351"], $arCatalog["3342"], $arCatalog["3334"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3351"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["309"], $arCatalog["299"], $arCatalog["313"], $arCatalog["294"]), "ASSOCIATED" => array($arCatalog["3347"], $arCatalog["3342"], $arCatalog["3334"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3478"], $catalogIBlockID, array("BRAND" => array($arBrands["49612"]), "EXPANDABLES" => array($arCatalog["49863"], $arCatalog["514"], $arCatalog["49867"], $arCatalog["49895"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3479"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["10633"]), "BRAND" => array($arBrands["49608"]), "ASSOCIATED" => array($arCatalog["3482"], $arCatalog["3478"], $arCatalog["3480"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3480"], $catalogIBlockID, array("BRAND" => array($arBrands["49612"]), "ASSOCIATED" => array($arCatalog["3479"], $arCatalog["3482"], $arCatalog["3478"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3482"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["49612"]), "ASSOCIATED" => array($arCatalog["3480"], $arCatalog["3479"], $arCatalog["3478"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3512"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3540"], $arCatalog["3541"], $arCatalog["395"], $arCatalog["3539"], $arCatalog["3538"]), "ASSOCIATED" => array($arCatalog["3518"], $arCatalog["3524"], $arCatalog["3528"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3518"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["395"], $arCatalog["3540"], $arCatalog["3541"], $arCatalog["3539"], $arCatalog["3538"]), "ASSOCIATED" => array($arCatalog["3528"], $arCatalog["3524"], $arCatalog["3512"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3524"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["3540"], $arCatalog["3539"], $arCatalog["3538"], $arCatalog["3541"]), "ASSOCIATED" => array($arCatalog["3518"], $arCatalog["3512"], $arCatalog["3528"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3528"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3541"], $arCatalog["3540"], $arCatalog["3539"], $arCatalog["3538"]), "ASSOCIATED" => array($arCatalog["3512"], $arCatalog["3518"], $arCatalog["3524"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3548"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["382"], $arCatalog["400"], $arCatalog["3538"], $arCatalog["3540"]), "ASSOCIATED" => array($arCatalog["3554"], $arCatalog["3571"], $arCatalog["3565"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3554"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["382"], $arCatalog["3540"], $arCatalog["3539"], $arCatalog["400"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3565"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["10633"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["382"], $arCatalog["3538"], $arCatalog["400"], $arCatalog["3539"]), "ASSOCIATED" => array($arCatalog["3571"], $arCatalog["3554"], $arCatalog["3548"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3571"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["382"], $arCatalog["400"], $arCatalog["3540"], $arCatalog["3541"]), "ASSOCIATED" => array($arCatalog["3554"], $arCatalog["3565"], $arCatalog["3548"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3463"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["3306"]), "ASSOCIATED" => array($arCatalog["3469"], $arCatalog["3476"], $arCatalog["3477"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3469"], $catalogIBlockID, array("BRAND" => array($arBrands["3305"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3476"], $catalogIBlockID, array("BRAND" => array($arBrands["49632"]), "ASSOCIATED" => array($arCatalog["3477"], $arCatalog["3469"], $arCatalog["3463"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3477"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["3305"]), "ASSOCIATED" => array($arCatalog["3463"], $arCatalog["3476"], $arCatalog["3469"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3459"], $catalogIBlockID, array("BRAND" => array($arBrands["397"]), "ASSOCIATED" => array($arCatalog["3460"], $arCatalog["3462"], $arCatalog["3459"], $arCatalog["3461"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3460"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["49670"]), "EXPANDABLES" => array($arCatalog["10693"], $arCatalog["10689"], $arCatalog["10690"], $arCatalog["10691"]), "ASSOCIATED" => array($arCatalog["3460"], $arCatalog["3462"], $arCatalog["3461"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3461"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["49670"]), "ASSOCIATED" => array($arCatalog["3460"], $arCatalog["3459"], $arCatalog["3462"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3462"], $catalogIBlockID, array("BRAND" => array($arBrands["397"]), "EXPANDABLES" => array($arCatalog["3610"], $arCatalog["3601"], $arCatalog["3605"], $arCatalog["10701"], $arCatalog["3597"], $arCatalog["396"]), "ASSOCIATED" => array($arCatalog["3461"], $arCatalog["3459"], $arCatalog["3460"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3541"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "BRAND" => array($arBrands["3301"]), "ASSOCIATED" => array($arCatalog["3538"], $arCatalog["3539"], $arCatalog["3540"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3538"], $catalogIBlockID, array("BRAND" => array($arBrands["49597"]), "ASSOCIATED" => array($arCatalog["3541"], $arCatalog["3540"], $arCatalog["3539"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3539"], $catalogIBlockID, array("BRAND" => array($arBrands["832"]), "ASSOCIATED" => array($arCatalog["3538"], $arCatalog["3540"], $arCatalog["3541"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3540"], $catalogIBlockID, array("BRAND" => array($arBrands["3301"]), "ASSOCIATED" => array($arCatalog["3541"], $arCatalog["3539"], $arCatalog["3538"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3544"], $catalogIBlockID, array("BRAND" => array($arBrands["3314"]), "EXPANDABLES" => array($arCatalog["3611"], $arCatalog["3612"], $arCatalog["3588"], $arCatalog["3589"]), "ASSOCIATED" => array($arCatalog["3542"], $arCatalog["3546"], $arCatalog["3545"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3542"], $catalogIBlockID, array("BRAND" => array($arBrands["3314"]), "EXPANDABLES" => array($arCatalog["3589"], $arCatalog["3588"], $arCatalog["3612"], $arCatalog["3611"]), "ASSOCIATED" => array($arCatalog["3544"], $arCatalog["3543"], $arCatalog["3546"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3543"], $catalogIBlockID, array("BRAND" => array($arBrands["3314"]), "EXPANDABLES" => array($arCatalog["3589"], $arCatalog["3611"], $arCatalog["3612"], $arCatalog["3588"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3545"], $catalogIBlockID, array("BRAND" => array($arBrands["3314"]), "EXPANDABLES" => array($arCatalog["3589"], $arCatalog["3611"], $arCatalog["3612"], $arCatalog["3588"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3546"], $catalogIBlockID, array("BRAND" => array($arBrands["3314"]), "EXPANDABLES" => array($arCatalog["3612"], $arCatalog["3588"], $arCatalog["3589"], $arCatalog["3611"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3547"], $catalogIBlockID, array("ASSOCIATED" => array($arCatalog["3543"], $arCatalog["3544"], $arCatalog["3542"], $arCatalog["3545"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3588"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "BRAND" => array($arBrands["3314"]), "ASSOCIATED" => array($arCatalog["3611"], $arCatalog["3589"], $arCatalog["3612"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3589"], $catalogIBlockID, array("BRAND" => array($arBrands["3314"]), "ASSOCIATED" => array($arCatalog["3612"], $arCatalog["3588"], $arCatalog["3611"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3611"], $catalogIBlockID, array("BRAND" => array($arBrands["3314"]), "ASSOCIATED" => array($arCatalog["3588"], $arCatalog["3589"], $arCatalog["3612"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3612"], $catalogIBlockID, array("BRAND" => array($arBrands["3314"]), "ASSOCIATED" => array($arCatalog["3588"], $arCatalog["3589"], $arCatalog["3611"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3644"], $catalogIBlockID, array("BRAND" => array($arBrands["49634"]), "EXPANDABLES" => array($arCatalog["3121"], $arCatalog["3633"], $arCatalog["3632"], $arCatalog["3634"], $arCatalog["3122"]), "ASSOCIATED" => array($arCatalog["3642"], $arCatalog["3641"], $arCatalog["3643"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3641"], $catalogIBlockID, array("BRAND" => array($arBrands["49634"]), "EXPANDABLES" => array($arCatalog["3635"], $arCatalog["3121"], $arCatalog["3634"], $arCatalog["3122"], $arCatalog["3633"], $arCatalog["3632"]), "ASSOCIATED" => array($arCatalog["3644"], $arCatalog["3643"], $arCatalog["3642"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3642"], $catalogIBlockID, array("BRAND" => array($arBrands["49634"]), "EXPANDABLES" => array($arCatalog["3122"], $arCatalog["3633"], $arCatalog["3632"], $arCatalog["3634"]), "ASSOCIATED" => array($arCatalog["3643"], $arCatalog["3641"], $arCatalog["3644"])));

// update links in aspro_max_projects
CIBlockElement::SetPropertyValuesEx($arProjects["152"], $projectsIBlockID, array("LINK_REVIEWS" => array($arAdd_review["3652"]), "LINK_GOODS" => array($arCatalog["3589"], $arCatalog["3588"], $arCatalog["3611"], $arCatalog["3612"])));
CIBlockElement::SetPropertyValuesEx($arProjects["895"], $projectsIBlockID, array("LINK_SERVICES" => array($arServices["10636"]), "LINK_GOODS" => array($arCatalog["3547"], $arCatalog["3543"], $arCatalog["3544"], $arCatalog["3542"], $arCatalog["3546"], $arCatalog["3545"])));
CIBlockElement::SetPropertyValuesEx($arProjects["901"], $projectsIBlockID, array("LINK_SERVICES" => array($arServices["10637"])));
CIBlockElement::SetPropertyValuesEx($arProjects["893"], $projectsIBlockID, array("LINK_PROJECTS" => array($arProjects["215"]), "LINK_SERVICES" => array($arServices["10639"]), "LINK_GOODS" => array($arCatalog["49705"], $arCatalog["49837"], $arCatalog["49695"], $arCatalog["49809"])));

// update links in aspro_max_staff
CIBlockElement::SetPropertyValuesEx($arStaff["74"], $staffIBlockID, array("LINK_PROJECTS" => array($arProjects["154"]), "LINK_SERVICES" => array($arServices["10636"])));

// update links in aspro_max_articles
CIBlockElement::SetPropertyValuesEx($arArticles["49914"], $articlesIBlockID, array("LINK_GOODS" => array($arCatalog["49741"], $arCatalog["49744"])));

// update links in aspro_max_landing
CIBlockElement::SetPropertyValuesEx($arLanding["3383"], $landingIBlockID, array("LINK_REVIEWS" => array($arAdd_review["3653"])));

// update links in aspro_max_partners
CIBlockElement::SetPropertyValuesEx($arPartners["1762"], $partnersIBlockID, array("LINK_REGION" => array($arRegions["3213"], $arRegions["3212"], $arRegions["3215"], $arRegions["3214"])));
CIBlockElement::SetPropertyValuesEx($arPartners["28"], $partnersIBlockID, array("LINK_REGION" => array($arRegions["3213"], $arRegions["3212"], $arRegions["3215"])));
CIBlockElement::SetPropertyValuesEx($arPartners["27"], $partnersIBlockID, array("LINK_REGION" => array($arRegions["3213"], $arRegions["3214"])));
CIBlockElement::SetPropertyValuesEx($arPartners["67"], $partnersIBlockID, array("LINK_REGION" => array($arRegions["3213"], $arRegions["3212"], $arRegions["3215"])));
CIBlockElement::SetPropertyValuesEx($arPartners["69"], $partnersIBlockID, array("LINK_REGION" => array($arRegions["3213"], $arRegions["3212"], $arRegions["3215"], $arRegions["3214"])));
CIBlockElement::SetPropertyValuesEx($arPartners["70"], $partnersIBlockID, array("LINK_REGION" => array($arRegions["3212"], $arRegions["3215"], $arRegions["3214"])));


// get sections
$newPropSectionsXML = array (
  235 => '64',
  225 => '559',
  202 => '544',
  201 => '543',
  213 => '583',
  210 => '7f632c15-69c4-11dd-a899-0018f3099a8f',
  214 => '585',
  196 => '537',
  240 => '2534',
  217 => '589',
);
$newPropSectionsRes = CIBlockSection::GetList(array(), array("XML_ID" => $newPropSectionsXML, "IBLOCK_ID" => $catalogIBlockID), false, array("ID", "XML_ID"));
while($newPropSection = $newPropSectionsRes->Fetch()) {
	$arNewPropSections[$newPropSection["XML_ID"]] = $newPropSection["ID"];
}

// get props
$newPropPropsXML = array (
  896 => '896',
);
foreach($newPropPropsXML as $xml) {
	$resNewProps = CIBlockProperty::GetList(
		array(),
		array("XML_ID" => $xml, "IBLOCK_ID" => $catalogIBlockID)
	);
	$newProp = $resNewProps->Fetch();
	$arNewPropProps[$newProp["XML_ID"]] = $newProp["ID"];
}

// update values custom filter
CIBlockElement::SetPropertyValuesEx($arNews["49905"], $newsIBlockID, array("LINK_GOODS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":{"0":{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["64"].'"}},"2":{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBProp:'.$catalogIBlockID.':'.$arNewPropProps["896"].'","DATA":{"logic":"Equal","value":"'.iconv(LANG_CHARSET, "UTF-8", "Осень").'"}},{"CLASS_ID":"CondIBProp:'.$catalogIBlockID.':'.$arNewPropProps["896"].'","DATA":{"logic":"Equal","value":"'.iconv(LANG_CHARSET, "UTF-8", "Зима").'"}}]}}}'));
CIBlockElement::SetPropertyValuesEx($arNews["49907"], $newsIBlockID, array("LINK_GOODS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["559"].'"}}]}'));
CIBlockElement::SetPropertyValuesEx($arStock["3253"], $stockIBlockID, array("LINK_GOODS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["559"].'"}}]}'));
CIBlockElement::SetPropertyValuesEx($arProjects["154"], $projectsIBlockID, array("LINK_GOODS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["544"].'"}},{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["543"].'"}}]}'));
CIBlockElement::SetPropertyValuesEx($arProjects["215"], $projectsIBlockID, array("LINK_GOODS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"OR","True":"True"},"CHILDREN":{"1":{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["583"].'"}}}}'));
CIBlockElement::SetPropertyValuesEx($arArticles["280"], $articlesIBlockID, array("LINK_GOODS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"OR","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["544"].'"}}]}'));
CIBlockElement::SetPropertyValuesEx($arSearch["3364"], $searchIBlockID, array("CUSTOM_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["7f632c15-69c4-11dd-a899-0018f3099a8f"].'"}}]}'));
CIBlockElement::SetPropertyValuesEx($arCross_sales["3662"], $cross_salesIBlockID, array("PRODUCTS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["585"].'"}}]}', "EXT_PRODUCTS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["585"].'"}}]}'));
CIBlockElement::SetPropertyValuesEx($arCross_sales["3660"], $cross_salesIBlockID, array("PRODUCTS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["537"].'"}}]}', "EXT_PRODUCTS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["537"].'"}}]}'));
CIBlockElement::SetPropertyValuesEx($arCross_sales["3659"], $cross_salesIBlockID, array("PRODUCTS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["2534"].'"}}]}', "EXT_PRODUCTS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["2534"].'"}}]}'));
CIBlockElement::SetPropertyValuesEx($arCross_sales["3592"], $cross_salesIBlockID, array("PRODUCTS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":{"1":{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["585"].'"}}}}', "EXT_PRODUCTS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":{"1":{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["589"].'"}}}}'));
CIBlockElement::SetPropertyValuesEx($arNews["49908"], $newsIBlockID, array("LINK_GOODS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["544"].'"}}]}'));

if($_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])
{
	$mxResult = CCatalogSKU::GetInfoByProductIBlock($_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"]);
	if(is_array($mxResult)){
		$skuIblockId = $mxResult['IBLOCK_ID'];
	}

	if($_SESSION["WIZARD_MAXIMUM_STOCK_IBLOCK_ID"])
	{
		$ibp = new CIBlockProperty;
		$arStockProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_STOCK_IBLOCK_ID"], "CODE" => "LINK_GOODS_FILTER"))->Fetch();
		if($arStockProps["ID"])
		{
			$ibp->Update($arStockProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp, $_SESSION["WIZARD_MAXIMUM_STOCK_IBLOCK_ID"]);
		}
	}

	if($_SESSION["WIZARD_MAXIMUM_ARTICLES_IBLOCK_ID"])
	{
		$ibp = new CIBlockProperty;
		$arArticlesProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_ARTICLES_IBLOCK_ID"], "CODE" => "LINK_GOODS_FILTER"))->Fetch();
		if($arArticlesProps["ID"])
		{
			$ibp->Update($arArticlesProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp, $_SESSION["WIZARD_MAXIMUM_ARTICLES_IBLOCK_ID"]);
		}
	}

	if($_SESSION["WIZARD_MAXIMUM_NEWS_IBLOCK_ID"])
	{
		$ibp = new CIBlockProperty;
		$arNewsProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_NEWS_IBLOCK_ID"], "CODE" => "LINK_GOODS_FILTER"))->Fetch();
		if($arNewsProps["ID"])
		{
			$ibp->Update($arNewsProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp, $_SESSION["WIZARD_MAXIMUM_NEWS_IBLOCK_ID"]);
		}
	}

	if($_SESSION["WIZARD_MAXIMUM_PROJECTS_IBLOCK_ID"])
	{
		$ibp = new CIBlockProperty;
		$arProjectsProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_PROJECTS_IBLOCK_ID"], "CODE" => "LINK_GOODS_FILTER"))->Fetch();
		if($arProjectsProps["ID"])
		{
			$ibp->Update($arProjectsProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp, $_SESSION["WIZARD_MAXIMUM_PROJECTS_IBLOCK_ID"]);
		}
	}

	if($_SESSION["WIZARD_MAXIMUM_CROSS_SALE_IBLOCK_ID"])
	{
		$ibp = new CIBlockProperty;
		$arCrossProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_CROSS_SALE_IBLOCK_ID"], "CODE" => "PRODUCTS_FILTER"))->Fetch();
		if($arCrossProps["ID"])
		{
			$ibp->Update($arCrossProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp);
		}

		$ibp = new CIBlockProperty;
		$arCrossProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_CROSS_SALE_IBLOCK_ID"], "CODE" => "EXT_PRODUCTS_FILTER"))->Fetch();
		if($arCrossProps["ID"])
		{
			$ibp->Update($arCrossProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp, $_SESSION["WIZARD_MAXIMUM_CROSS_SALE_IBLOCK_ID"]);
		}
	}

	if($_SESSION["WIZARD_MAXIMUM_SEARCH_IBLOCK_ID"])
	{
		$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_SEARCH_IBLOCK_ID"], "CODE" => "CUSTOM_FILTER"))->Fetch();
		if($arSearchProps["ID"])
		{
			$ibp = new CIBlockProperty;
			$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp);
		}

		$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_SEARCH_IBLOCK_ID"], "CODE" => "I_ELEMENT_PAGE_TITLE"))->Fetch();
		if($arSearchProps["ID"])
		{
			$ibp = new CIBlockProperty;
			$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp);
		}

		$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_SEARCH_IBLOCK_ID"], "CODE" => "I_ELEMENT_PREVIEW_PICTURE_FILE_TITLE"))->Fetch();
		if($arSearchProps["ID"])
		{
			$ibp = new CIBlockProperty;
			$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp);
		}

		$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_SEARCH_IBLOCK_ID"], "CODE" => "I_ELEMENT_PREVIEW_PICTURE_FILE_ALT"))->Fetch();
		if($arSearchProps["ID"])
		{
			$ibp = new CIBlockProperty;
			$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp);
		}

		if($skuIblockId){
			$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_SEARCH_IBLOCK_ID"], "CODE" => "I_SKU_PAGE_TITLE"))->Fetch();
			if($arSearchProps["ID"])
			{
				$ibp = new CIBlockProperty;
				$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $skuIblockId)));
				unset($ibp);
			}

			$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_SEARCH_IBLOCK_ID"], "CODE" => "I_SKU_PREVIEW_PICTURE_FILE_TITLE"))->Fetch();
			if($arSearchProps["ID"])
			{
				$ibp = new CIBlockProperty;
				$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $skuIblockId)));
				unset($ibp);
			}

			$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_SEARCH_IBLOCK_ID"], "CODE" => "I_SKU_PREVIEW_PICTURE_FILE_ALT"))->Fetch();
			if($arSearchProps["ID"])
			{
				$ibp = new CIBlockProperty;
				$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $skuIblockId)));
				unset($ibp);
			}
		}

		unset($_SESSION["WIZARD_MAXIMUM_SEARCH_IBLOCK_ID"]);
	}

	if($_SESSION["WIZARD_MAXIMUM_CATALOG_INFO_IBLOCK_ID"])
	{
		$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_CATALOG_INFO_IBLOCK_ID"], "CODE" => "I_ELEMENT_PAGE_TITLE"))->Fetch();
		if($arSearchProps["ID"])
		{
			$ibp = new CIBlockProperty;
			$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp);
		}

		$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_CATALOG_INFO_IBLOCK_ID"], "CODE" => "I_ELEMENT_PREVIEW_PICTURE_FILE_TITLE"))->Fetch();
		if($arSearchProps["ID"])
		{
			$ibp = new CIBlockProperty;
			$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp);
		}

		$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_CATALOG_INFO_IBLOCK_ID"], "CODE" => "I_ELEMENT_PREVIEW_PICTURE_FILE_ALT"))->Fetch();
		if($arSearchProps["ID"])
		{
			$ibp = new CIBlockProperty;
			$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp);
		}

		if($skuIblockId){
			$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_CATALOG_INFO_IBLOCK_ID"], "CODE" => "I_SKU_PAGE_TITLE"))->Fetch();
			if($arSearchProps["ID"])
			{
				$ibp = new CIBlockProperty;
				$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $skuIblockId)));
				unset($ibp);
			}

			$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_CATALOG_INFO_IBLOCK_ID"], "CODE" => "I_SKU_PREVIEW_PICTURE_FILE_TITLE"))->Fetch();
			if($arSearchProps["ID"])
			{
				$ibp = new CIBlockProperty;
				$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $skuIblockId)));
				unset($ibp);
			}

			$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_CATALOG_INFO_IBLOCK_ID"], "CODE" => "I_SKU_PREVIEW_PICTURE_FILE_ALT"))->Fetch();
			if($arSearchProps["ID"])
			{
				$ibp = new CIBlockProperty;
				$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $skuIblockId)));
				unset($ibp);
			}
		}

		unset($_SESSION["WIZARD_MAXIMUM_CATALOG_INFO_IBLOCK_ID"]);
	}

	if($_SESSION["WIZARD_MAXIMUM_LANDING_IBLOCK_ID"])
	{
		$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_LANDING_IBLOCK_ID"], "CODE" => "FILTER_NEW"))->Fetch();
		if($arSearchProps["ID"])
		{
			$ibp = new CIBlockProperty;
			$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp);
		}

		$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_LANDING_IBLOCK_ID"], "CODE" => "I_ELEMENT_PAGE_TITLE"))->Fetch();
		if($arSearchProps["ID"])
		{
			$ibp = new CIBlockProperty;
			$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp);
		}

		$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_LANDING_IBLOCK_ID"], "CODE" => "I_ELEMENT_PREVIEW_PICTURE_FILE_TITLE"))->Fetch();
		if($arSearchProps["ID"])
		{
			$ibp = new CIBlockProperty;
			$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp);
		}

		$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_LANDING_IBLOCK_ID"], "CODE" => "I_ELEMENT_PREVIEW_PICTURE_FILE_ALT"))->Fetch();
		if($arSearchProps["ID"])
		{
			$ibp = new CIBlockProperty;
			$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"])));
			unset($ibp);
		}

		if($skuIblockId){
			$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_LANDING_IBLOCK_ID"], "CODE" => "I_SKU_PAGE_TITLE"))->Fetch();
			if($arSearchProps["ID"])
			{
				$ibp = new CIBlockProperty;
				$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $skuIblockId)));
				unset($ibp);
			}

			$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_LANDING_IBLOCK_ID"], "CODE" => "I_SKU_PREVIEW_PICTURE_FILE_TITLE"))->Fetch();
			if($arSearchProps["ID"])
			{
				$ibp = new CIBlockProperty;
				$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $skuIblockId)));
				unset($ibp);
			}

			$arSearchProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_LANDING_IBLOCK_ID"], "CODE" => "I_SKU_PREVIEW_PICTURE_FILE_ALT"))->Fetch();
			if($arSearchProps["ID"])
			{
				$ibp = new CIBlockProperty;
				$ibp->Update($arSearchProps["ID"], array('USER_TYPE' => 'SAsproMaxIBInherited','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $skuIblockId)));
				unset($ibp);
			}
		}

		unset($_SESSION["WIZARD_MAXIMUM_LANDING_IBLOCK_ID"]);
	}

	unset($_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"]);
}

$UserFields = array(
	$catalogIBlockID => array(
		'UF_SECTION_DESCR',
		'UF_SECTION_TEMPLATE',
		'UF_TIZERS',
		'UF_POPULAR',
		'UF_CATALOG_ICON',
		'UF_OFFERS_TYPE',
		'UF_TABLE_SIZES',
		'UF_ELEMENT_DETAIL',
		'UF_SECTION_BG_IMG',
		'UF_SECTION_BG_DARK',
		'UF_SECTION_TIZERS' => array(
			'LINK_IBLOCK' => $tizersIBlockID,
		),
		'UF_HELP_TEXT',
		'UF_MENU_BANNER' => array(
			'LINK_IBLOCK' => $banners_innerIBlockID,
		),
		'UF_REGION' => array(
			'LINK_IBLOCK' => $regionsIBlockID,
		),
		'UF_PICTURE_RATIO',
		'UF_LINE_ELEMENT_CNT',
		'UF_LINKED_BLOG' => array(
			'LINK_IBLOCK' => $articlesIBlockID,
		),
		'UF_BLOG_WIDE',
		'UF_BLOG_BOTTOM',
		'UF_BLOG_MOBILE',
		'UF_MENU_BRANDS' => array(
			'LINK_IBLOCK' => $brandsIBlockID,
		),
		'UF_LINKED_BANNERS' => array(
			'LINK_IBLOCK' => $banners_catalogIBlockID,
		),
		'UF_BANNERS_WIDE',
		'UF_BANNERS_BOTTOM',
		'UF_BANNERS_MOBILE',
	),
	$megamenuIBlockID => array(
		"UF_MENU_LINK",
		"UF_MEGA_MENU_LINK",
		"UF_CATALOG_ICON",
	),
);

// iblock user fields
$langFile = $_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards/aspro/max/site/services/iblock/lang/ru/links.php';
include($langFile);

foreach($UserFields as $iblockId => $fields) {
	foreach ($fields as $fieldKey => $fieldInfo) {

		$fieldCode = is_array($fieldInfo) ? $fieldKey : $fieldInfo;

		$arLangs = array(
			"EDIT_FORM_LABEL"   => array(
		        "ru"    => $MESS[$fieldCode],
		        "en"    => $fieldCode,
		    ),
		    "LIST_COLUMN_LABEL" => array(
		        "ru"    => $MESS[$fieldCode],
		        "en"    => $fieldCode,
		    )
		);

		if( isset($fieldInfo['LINK_IBLOCK']) ) {
			$arLangs['SETTINGS'] = array(
				'DISPLAY' => 'LIST',
				'LIST_HEIGHT' => '5',
				'IBLOCK_ID' => $fieldInfo['LINK_IBLOCK'],
			);
		}

		$arUserField = CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "IBLOCK_".$iblockId."_SECTION", "FIELD_NAME" => $fieldCode))->Fetch();

		if($arUserField) {
			$ob = new CUserTypeEntity();
			$ob->Update($arUserField["ID"], $arLangs);
		}

	}
}
?>
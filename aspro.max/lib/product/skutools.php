<?php

namespace Aspro\Max\Product;

use CMax as Solution,
    CMaxCache as SolutionCache,
    CMaxRegionality as SolutionRegionality,
    \Aspro\Functions\CAsproMax as SolutionFunctions,
    \Aspro\Functions\CAsproMaxItem as SolutionItem,
    \Aspro\Max\Property\IBInherited,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Loader;

class SkuTools {

    const signer_solt		= 'select_offer';

    public static function getSignedParams(array $arParams = []) :string {
        $signer = new \Bitrix\Main\Security\Sign\Signer;
        $signedParams = $signer->sign(base64_encode(serialize($arParams)), self::signer_solt);

        return $signedParams;
    }

    public static function getUnSignedParams(string $signedParams = '') :array {
        $signer = new \Bitrix\Main\Security\Sign\Signer;
        try{
            $params = $signer->unsign($signedParams, self::signer_solt);
            $params = Solution::unserialize(base64_decode($params));
        }
        catch(\Bitrix\Main\Security\Sign\BadSignatureException $e){
            die();
        }

        return $params;
    }

    public static function getOfferTreeJson($offers):string
    {
        $obOffersTree = [];
        foreach ($offers as $key => $value) {
            $obOffersTree[] = [
                'ID' => $value['ID'],
                'TREE' => $value['TREE'],
                'CAN_BUY' => (int)$value['CAN_BUY']
            ];
        }
        return \Bitrix\Main\Web\Json::encode($obOffersTree);
    }

    public static function checkOfferIblock(string $iblockId):bool
    {
        static $arOfferStatus = [];
        if(!isset($arOfferStatus[$iblockId])){
            Loader::IncludeModule('iblock');
            Loader::IncludeModule('catalog');
            $arOfferStatus[$iblockId] = false;
            $skuInfo = \CCatalogSKU::GetInfoByOfferIBlock($iblockId);
            if($skuInfo){
                $rsSites = \CIBlock::GetSite($skuInfo["PRODUCT_IBLOCK_ID"]);
                while($arSite = $rsSites->Fetch()){
                    $siteId = $arSite["SITE_ID"];
                    $catalogIblockId = Option::get(Solution::moduleID, 'CATALOG_IBLOCK_ID', SolutionCache::$arIBlocks[$siteId]['aspro_'.Solution::solutionName.'_catalog']['aspro_'.Solution::solutionName.'_catalog'][0], $siteId);
                    
                    if ($catalogIblockId == $skuInfo["PRODUCT_IBLOCK_ID"]) {
                        $arOfferStatus[$iblockId] = true;
                        break;
                    }
                }
            }
        }
        
        return $arOfferStatus[$iblockId];
    }

    public static function getTemplateWithJsonOffers(array $offers) {
        ?>
        <!--noindex-->
            <template class="offers-template-json">
                    <div data-json='<?=self::getOfferTreeJson($offers)?>'></div>
            </template>
        <!--/noindex-->
        <?
    }
}

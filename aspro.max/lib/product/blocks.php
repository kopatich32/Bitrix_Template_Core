<?php

namespace Aspro\Max\Product;
use CMax as Solution;

class Blocks
{

    public static $detailBlocksPath = 'include/blocks/catalog/detail_blocks/';
    public string $baseBlockPath = '';
    public array $customBlocks = [];
    public array $customBlocksInfo = [];
    public array $items = [];
    public int $countTabs = 0;

    function __construct(string $siteId = SITE_ID, string $blockParams){

        $this->baseBlockPath = static::$detailBlocksPath;
        $this->customBlocks = $this->getDetailCustom($siteId);
        $this->customBlocksInfo = $this->getCustomBlocksInfo($blockParams, $this->customBlocks);
        $this->items = $this->getAllActive();
    }

    public function getAllActive(): ?array
    {
        return array_filter($this->customBlocksInfo, fn($blockInfo) => isset($blockInfo['active']) && $blockInfo['active']);
    }

    public function getTabs(): ?array
    {
        return array_filter($this->items, fn($blockInfo) => isset($blockInfo['is_tab']) && $blockInfo['is_tab']);
    }

    public function getBlocks(): ?array
    {
        return array_filter($this->items, fn($blockInfo) => !isset($blockInfo['is_tab']) || !$blockInfo['is_tab']);
    }

    public function getDetailCustom($siteId = SITE_ID): ?array
    {
        $arCustomBlocks = [];

        $arSite = \CSite::GetByID($siteId)->Fetch();
        $siteDir = str_replace('//', '/', $arSite['DIR']).'/';
        $curBlocksDir = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].$siteDir.$this->baseBlockPath);

        if (file_exists($curBlocksDir)) {
            $arBlockFiles = glob($curBlocksDir.'*.php');

            foreach ($arBlockFiles as $customFile) {
                $blockCode = str_replace('.php', '', basename($customFile));
                $arCustomBlocks[$blockCode] = $customFile;
            }
        }

        return $arCustomBlocks;
    }

    public static function getCustomBlocksInfo($blockData, $arCustomBlocks)
    {
        $arCurCustomBlocks = $arNewCustomBlocks = [];
        try {
            $arCurCustomBlocks = \Bitrix\Main\Web\Json::decode(
                isset($_REQUEST['component_params_manager'])
                    ? $blockData
                    : iconv(SITE_CHARSET, 'utf-8', $blockData)
            );
        } catch (\Exception $e) {
            $arCurCustomBlocks = [];
        }

        if (!empty($arCustomBlocks)) {
            $allCurBlocksByCode = array_column($arCurCustomBlocks, NULL, 'id');

            foreach ($arCustomBlocks as $blockCode => $blockPath) {
                if (!isset($allCurBlocksByCode[$blockCode])) {
                    $arNewCustomBlocks[$blockCode] = [
                        'id' => $blockCode,
                    ];
                } else {
                    $arNewCustomBlocks[$blockCode] = $allCurBlocksByCode[$blockCode];
                }
                $arNewCustomBlocks[$blockCode]['file'] = $blockPath;
            }
        }

        return $arNewCustomBlocks;
    }

    public static function formatForOrder(array $arCustomBlocks = []): array
    {
        return array_combine(array_keys($arCustomBlocks), array_keys($arCustomBlocks));
    }

    public static function getPropertiesByParams($arPropsParams, $arProperties): array
    {
        $customPropertyData = [];
        if(!empty($arPropsParams) && is_array($arPropsParams)){
            foreach ($arPropsParams as $propCode){
                if($propCode && isset($arProperties[$propCode])){
                    $customPropertyData[$propCode] = $arProperties[$propCode];
                }
            }
        }

        return $customPropertyData;
    }

    public static function checkContent($allBlocks, $code): bool
    {
        return isset($allBlocks[$code]) && !empty($allBlocks[$code]['HTML']);
    }

    public function prepareForShow($arParams = [], $arResult = [], $templateData = [])
    {
        $allCustomBlocks = $this->items;

        $countCustomTabs = 0;
        foreach ($allCustomBlocks as $blockCode => $blockInfo) {
            ob_start();
            if (file_exists($blockInfo['file'])) {
                include $blockInfo['file'];
            }
            $blockHtml = trim(ob_get_clean());
            $allCustomBlocks[$blockCode]['HTML'] = $blockHtml;
            if ($blockHtml && $blockInfo['is_tab']) {
                ++$countCustomTabs;
            }
        }

        $this->$countTabs = $countCustomTabs;
        $this->items = $allCustomBlocks;
    }

    public static function getViewDir()
    {
        return $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.Solution::moduleID.'/view/epilog_blocks';
    }

}

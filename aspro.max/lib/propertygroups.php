<?php

namespace Aspro\Max;
use Bitrix\Main\Localization\Loc,
	CMax as Solution,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\SystemException,
    \Bitrix\Main\Loader;
class PropertyGroups
{
    //const moduleID = ASPRO_MAX_MODULE_ID;
    const partnerName	= 'aspro';
    const solutionName	= 'max';
    static protected $properties = [];
    static protected $propsInfo = [];
    static protected $iblockId = null;
    static protected $bModified = false;


    static public function eventHandler(&$form)
    {
        if ($GLOBALS["APPLICATION"]->GetCurPage() == "/bitrix/admin/iblock_edit.php") {
            $iblockId = $_REQUEST['ID'];
            if($iblockId){
                self::$iblockId = $iblockId;
                if(self::checkIblockId($iblockId)){
                    $groupsContent = self::getTabContent();
                    $moduleID = Solution::moduleID;
                    
                    $GLOBALS['APPLICATION']->SetAdditionalCss('/bitrix/css/'.$moduleID.'/props-group.css');
                    $GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/'.$moduleID.'/sort/Sortable.js');

                    $form->tabs[] = array(
                        "DIV" => "aspro_group_props_".self::solutionName, 
                        "TAB" => Loc::getMessage("ASPRO_PROPS_GROUP_TAB"), 
                        "ICON" => "main_user_edit", 
                        "TITLE" => Loc::getMessage("ASPRO_PROPS_GROUP_TITLE"), 
                        "CONTENT" => $groupsContent
                    ); 
                }
                               
            }
            
        }
    }

    static public function checkIblockId($iblockId)
    {
        $showTab = false;
        if(Loader::includeModule('iblock')){
            $arSites = [];
            $rsSites = \CIBlock::GetSite($iblockId);
            while($arSite = $rsSites->Fetch()){
                $arSites[] = $arSite["SITE_ID"];
                $strIblockGroup = Option::get(Solution::moduleID, 'ASPRO_PROPS_GROUP_IBLOCK', '', $arSite["SITE_ID"]);
                if(is_string($strIblockGroup) && strlen($strIblockGroup)){
                    $arIblockGroup = explode(',', $strIblockGroup);
                    if(in_array($iblockId, $arIblockGroup)){
                        $showTab = true;
                        break;
                    }
                }
                
            }
        }
        return $showTab;
    }

    static public function getTabContent()
    {
        $properties = self::getProperties();
        ob_start();
        include __DIR__ . '/../admin/propertygroups/view/iblock_tab.php';
        $html=ob_get_clean();
        return $html;
    }

    static protected function getPropsFromFile()
    {
        $filePath = self::getFilePath();
        if( file_exists($filePath) ){
            $propsFromFile = file_get_contents($filePath);
            try{
                $propsFromFile = \Bitrix\Main\Web\Json::decode($propsFromFile);
            } catch(SystemException $e) {
                $propsFromFile = [];
            }
            
            $arAllProps = [];
            foreach($propsFromFile as $keyGroup => $arGroup){
                $arAllProps = array_merge($arAllProps, $arGroup["PROPS"]);
            }
            $arNewProps = self::getPropsFromIblock();
            //$arNewPropsCode = array_column($arNewProps, "CODE");
            $arNewPropsCode = array_keys($arNewProps);

            $arAddProps = array_diff($arNewPropsCode, $arAllProps);
            
            if(!empty($arAddProps)){
                if(is_array($propsFromFile[0]["PROPS"]) && $propsFromFile[0]["NAME"] === "NO_GROUP"){
                    $propsFromFile[0]["PROPS"] = array_merge($arAddProps, $propsFromFile[0]["PROPS"]);
                } else {
                    array_unshift($propsFromFile, ["NAME" => "NO_GROUP", "PROPS" => $arAddProps]);
                }
                
                self::$bModified = true;
            }

            $arDeleteProps = array_diff($arAllProps, $arNewPropsCode);

            if( !empty($arDeleteProps) ){
                self::$bModified = true;
            }

            return $propsFromFile;
        }
    }

    static protected function getPropsFromIblock()
    {
        if (self::$propsInfo) {
            return self::$propsInfo;
        }
        $arProps = [];

        if(Loader::includeModule('iblock')){
            $serviceProps = self::getServiceProps();
            $res = \CIBlock::GetProperties(self::$iblockId, ["sort" => "asc"]);

            while($res_arr = $res->Fetch()){
                if(!in_array($res_arr['CODE'], $serviceProps)){
                    //$arProps[$res_arr['ID']] = [
                    $arProps[$res_arr['CODE']] = [
                        'NAME' => $res_arr['NAME'] . ' [' . $res_arr['CODE'] . ']',
                        'CODE' => $res_arr['CODE'],
                        'ID' => $res_arr['ID'],
                    ];
                }                
            }
        }
        self::$propsInfo = $arProps;
        return $arProps;
    }

    static protected function getProperties()
    {
        if (self::$properties) {
            return self::$properties;
        }
        $arProps = [];
        $filePath = self::getFilePath();

        if( file_exists($filePath) ){
            $arProps = self::getPropsFromFile();
        } else {
            $arTmpProps = self::getPropsFromIblock();
            $arProps[] = ["NAME" => "NO_GROUP", "PROPS" => array_keys($arTmpProps)];
        }

        self::$properties = $arProps;

        return self::$properties;
    }

    static protected function getServiceProps(){
        return array('BIG_BLOCK_PICTURE', 'OUT_OF_PRODUCTION', 'PRODUCT_ANALOG_FILTER', 'PRODUCT_ANALOG', 'BIG_BLOCK', 'SUB_TITLE', 'FAVORIT_ITEM', 'PODBORKI', 'PHOTO_GALLERY', 'SALE_TEXT', 'MORE_PHOTO', 'rating', 'vote_sum', 'vote_count', 'PRODUCT_SET_GROUP', 'PRODUCT_SET_FILTER', 'PRODUCT_SET', 'YM_ELEMENT_ID', 'IN_STOCK', 'MAXIMUM_PRICE', 'MINIMUM_PRICE', 'PERIOD', 'TITLE_BUTTON', 'LINK_BUTTON', 'REDIRECT', 'LINK_PROJECTS', 'LINK_REVIEWS', 'DOCUMENTS', 'FORM_ORDER', 'FORM_QUESTION', 'PHOTOPOS', 'TASK_PROJECT', 'PHOTOS', 'LINK_COMPANY', 'LINK_VACANCY', 'LINK_BLOG', 'LINK_LANDING', 'GALLEY_BIG', 'LINK_SERVICES', 'LINK_GOODS', 'LINK_STAFF', 'LINK_SALE', 'SERVICES', 'HIT', 'RECOMMEND', 'NEW', 'STOCK', 'VIDEO', 'VIDEO_YOUTUBE', 'CML2_ARTICLE', 'LINK_TIZERS', 'LINK_BRANDS', 'BRAND', 'POPUP_VIDEO','LINK_NEWS', 'SALE_NUMBER', 'SIDE_IMAGE_TYPE', 'SIDE_IMAGE', 'LINK_LANDINGS', 'EXPANDABLES', 'EXPANDABLES_FILTER', 'ASSOCIATED_FILTER', 'ASSOCIATED', 'LINK_PARTNERS', 'BLOG_POST_ID', 'BLOG_COMMENTS_CNT', 'HELP_TEXT', 'FORUM_TOPIC_ID', 'FORUM_MESSAGE_CNT', 'EXTENDED_REVIEWS_COUNT', 'EXTENDED_REVIEWS_RAITING');
    }

    static public function iblockUpdateEventHandler(&$arFields)
    {
        $context = \Bitrix\Main\Application::getInstance()->getContext();
        $request = $context->getRequest();
        $arJsonProp = $request->get("props-group-json");
        $iblockId = (int)$request->get("props-group-iblock-id");

        if(isset($arJsonProp) && !empty($arJsonProp) && $iblockId === $arFields["ID"]){
            self::$iblockId = $iblockId;
            $arNewPropGrops = [];
            $arParamsTranslit = array("replace_space"=>"-","replace_other"=>"-");
            foreach ($arJsonProp as $keyGroup => $propsGroup){
                try{
                    $arNewPropGrops[$keyGroup] = \Bitrix\Main\Web\Json::decode(urldecode($propsGroup));
                    $arNewPropGrops[$keyGroup]["CODE"] = \Cutil::translit(trim($arNewPropGrops[$keyGroup]["NAME"]), "ru", $arParamsTranslit);
                } catch(SystemException $e){
                }
            }
            
            self::savePropGroups($arNewPropGrops);
        }
    }

    /**
     * dir
     *
     * @return string
     */
    static protected function getDir()
    {
        return __DIR__ . '/../admin/propertygroups/json/';
    }

    /**
     * file
     *
     * @return string
     */
    static protected function getFileName()
    {
        return "prop_groups_iblock_".self::$iblockId.".json";
    }

    /**
     * path to file
     *
     * @return string
     */
    static protected function getFilePath()
    {
        return self::getDir() . self::getFileName();
    }

    /**
     * save
     */
    static public function savePropGroups($dataToSave)
    {
        file_put_contents(self::getFilePath(), \Bitrix\Main\Web\Json::encode($dataToSave));
    }
}
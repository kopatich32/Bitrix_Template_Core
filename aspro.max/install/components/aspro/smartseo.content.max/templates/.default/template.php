<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

foreach($arParams['FIELDS'] as $code => $fieldCode){
    $_code = mb_strtolower($fieldCode);

    if($arParams['SHOW_VIEW_CONTENT'] === 'Y'){
        $this->SetViewTarget($arParams['CODE_VIEW_CONTENT'].'_'.$_code);
    }

    if($arResult['VALUES'][$fieldCode]){
        echo htmlspecialchars_decode($arResult['VALUES'][$fieldCode]);
    }

    if($arParams['SHOW_VIEW_CONTENT'] === 'Y'){
        $this->EndViewTarget();
    }
}

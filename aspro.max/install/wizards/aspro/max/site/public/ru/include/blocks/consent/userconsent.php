<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? if(TSolution::GetFrontParametrValue('LICENCE_TYPE') === "BITRIX"): ?>
    <?include 'bxconsent.php';?>
<? else: ?>
    <?include 'solution.php';?>
<?endif;?>

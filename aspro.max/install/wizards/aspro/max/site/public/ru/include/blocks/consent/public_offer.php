<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//options from \Aspro\Functions\CAsproPriority2::showBlockHtml
$arOptions = $arConfig['PARAMS'];
?>

<div class="offer_block filter label_block onoff">
    <?if(isset($arOptions['HIDDEN_ERROR']) && $arOptions['HIDDEN_ERROR'] === 'Y'):?>
        <label data-for="<?=$arOptions['INPUT_ID'];?>" class="hidden error"><?=GetMessage("ERROR_FORM_LICENSE");?></label>
    <?endif;?>
    <input type="checkbox" id="<?=$arOptions['INPUT_ID'];?>" <?=TSolution::getFrontParametrValue('OFFER_CHECKED') === 'Y' ? 'checked' : '';?> name="<?=$arOptions['INPUT_NAME'];?>" required value="Y">
    <label for="<?=$arOptions['INPUT_ID'];?>" class="offer_pub">
        <?include(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR."include/offer_text.php"));?>
    </label>
</div>

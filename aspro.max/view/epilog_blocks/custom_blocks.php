<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="ordered-block <?=$code?>">
    <?if(!empty($allCustomBlocks[$code]['name'])):?>
        <div class="ordered-block__title option-font-bold font_lg">
            <?=$allCustomBlocks[$code]['name'];?>
        </div>
    <?endif;?>
    <?=$allCustomBlocks[$code]['HTML'];?>
</div>

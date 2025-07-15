<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="tab-pane <?=$value;?> <?=(!($iTab++) ? 'active' : '')?>" id="<?=$value?>">
    <?if($i == 1 && !empty($allCustomBlocks[$value]['name'])):?>
        <div class="ordered-block__title option-font-bold font_lg">
            <?=$allCustomBlocks[$value]['name'];?>
        </div>
    <?endif;?>
    <?=$allCustomBlocks[$value]['HTML'];?>
</div>

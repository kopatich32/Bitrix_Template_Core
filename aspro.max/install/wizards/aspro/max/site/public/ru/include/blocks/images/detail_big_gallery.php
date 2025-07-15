<?
$arOptions = $arConfig['PARAMS'];
$isActive = !empty($arOptions['CONFIG']['IS_ACTIVE']);
$items = $arOptions['ITEMS'] ?? [];
?>
<?if (!empty($items)):?>
    <div class="big-gallery-block"<?=($isActive ? '' : ' style="display:none;"');?> >
        <div class="owl-carousel owl-theme owl-bg-nav short-nav"
             data-plugin-options='{"items":1, "autoplay":false, "autoplayTimeout":3000, "smartSpeed":1000, "dots":true, "nav":true, "loop":false, "margin":5}'>

            <?foreach ($items as $arPhoto):?>
                <?if(!empty($arPhoto['DETAIL']['SRC']) && !empty($arPhoto['PREVIEW']['src'])):?>
                    <div class="item">
                        <a href="<?=$arPhoto['DETAIL']['SRC']?>"
                           class="fancy"
                           data-fancybox="big-gallery"
                           title="<?=$arPhoto['TITLE']?>">
                            <img src="<?=$arPhoto['PREVIEW']['src']?>"
                                 class="img-responsive inline"
                                 title="<?=$arPhoto['TITLE']?>"
                                 alt="<?=$arPhoto['ALT']?>" />
                        </a>
                    </div>
                <?endif;?>
            <?endforeach;?>

        </div>
    </div>
<?endif;?>

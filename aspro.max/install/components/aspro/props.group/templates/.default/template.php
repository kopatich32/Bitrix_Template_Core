<?$bFirst = true;?>
<?
$bGroups = is_array($arResult["GROUPS"]) && !empty($arResult["GROUPS"]);
$bOffersMode = $arParams["OFFERS_MODE"] === "Y";
?>
<?if($bGroups):?>
    <div class="properties-group js-offers-group-wrap">
        <?foreach($arResult["GROUPS"] as $arGroup):?>
            <?$bOfferGroup = $bOffersMode || ( isset($arGroup["OFFER_GROUP"]) && $arGroup["OFFER_GROUP"] );?>
            <div class="properties-group__group <?= $bOfferGroup ? 'js-offers-group' : ''?>" data-group-code="<?=$arGroup["CODE"] ?? "no-group"?>">
                <?if($arGroup["NAME"] !== "NO_GROUP"):?>
                    <div class="properties-group__group-name <?=$bFirst ? 'properties-group__group-name--first' : ''?>">
                        <?=$arGroup["NAME"]?>
                    </div>
                <?endif;?>
                <div class="properties-group__items js-offers-group__items-wrap">
                    <?foreach($arGroup["DISPLAY_PROPERTIES"] as $arProp):?>
                        <?$bHint = $arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y";?>
                        <div class="properties-group__item <?=$bOffersMode || $arProp["IS_OFFER"] ? 'js-offers-group__item' : ''?>" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
                            <div class="properties-group__name-wrap <?=$bHint ? 'whint' : ''?>">
                                <?if($bHint):?>
                                    <div class="properties-group__name-whint-wrap">
                                <?endif;?>
                                <span itemprop="name" class="properties-group__name"><?=$arProp["NAME"]?></span>
                                <?if($bHint):?>
                                    <div class="properties-group__hint"><span class="icon"><i>?</i></span><div class="tooltip"><?=$arProp["HINT"]?></div></div>
                                    </div>
                                <?endif;?>
                            </div>
                            <div class="properties-group__value-wrap">
                                <div class="properties-group__value" itemprop="value">
                                    <?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
                                        <?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
                                    <?else:?>
                                        <?=$arProp["DISPLAY_VALUE"];?>
                                    <?endif;?>
                                </div>
                            </div>
                        </div>
                    <?endforeach;?>
                </div>
            </div>
            <?$bFirst = false;?>
        <?endforeach;?>
    </div>
<?endif;?>

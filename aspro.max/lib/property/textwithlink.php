<?php

namespace Aspro\Max\Property;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use CMax as Solution;

Loc::loadMessages(__FILE__);

class TextWithLink
{
    public static function OnIBlockPropertyBuildList()
    {
        // self::ajaxAction();

        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'SAsproMaxTextWithLink',
            'DESCRIPTION' => Loc::getMessage('TEXTWITHLINK_PROP_MAX_TITLE'),
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            // 'GetPropertyFieldHtmlMulty' => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
            'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
            // 'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
            'PrepareSettings' => [__CLASS__, 'PrepareSettings'],
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
            // 'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
            // 'CheckFields' => [__CLASS__, 'CheckFields'],
            'GetLength' => [__CLASS__, 'GetLength'],
        ];
    }

    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        ob_start(); ?>

            <?extract(self::getValues(self::ConvertFromDB($arProperty, $value))); ?>
            <?if ($text):?>
                <div class="aspro_property_regionphone_item aspro_property_regionphone_item--admlistview">
                    <div class="wrapper">
                        <div class="inner_wrapper">
                            <div class="inner">
                                <div class="value_wrapper"><?=Loc::getMessage('TEXTWITHLINK_PROP_VALUE'); ?>: <?=$text; ?></div>
                            </div><br/>
                            <?if ($href):?>
                                <div class="inner" style="margin-top: -7px;">
                                    <div class="value_wrapper"><?=Loc::getMessage('TEXTWITHLINK_PROP_LINK'); ?>: <?=$href; ?></div>
                                </div>
                            <?endif; ?>
                        </div>
                    </div>
                </div>
            <?endif; ?>

        <?return ob_get_clean();
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        static $cache;

        $bAdminList = $strHTMLControlName['MODE'] === 'iblock_element_admin';
        $bEditProperty = $strHTMLControlName['MODE'] === 'EDIT_FORM';
        $bDetailPage = $strHTMLControlName['MODE'] === 'FORM_FILL';
        $bMultiple = $arProperty['MULTIPLE'] === 'Y';

        if(!isset($cache)) {
            $cache = [];
            $GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/'.Solution::moduleID.'/sort/Sortable.js');

            \CJSCore::RegisterExt('regionphone', [
                'js' => '/bitrix/js/'.Solution::moduleID.'/property/regionphone.js',
                'css' => '/bitrix/css/'.Solution::moduleID.'/property/regionphone.css',
                'lang' => '/bitrix/modules/'.Solution::moduleID.'/lang/'.LANGUAGE_ID.'/lib/property/regionphone.php',
            ]);

            \CJSCore::Init(['regionphone']);
        }

        if($bAdminList) {
            preg_match('/FIELDS\[([\D]+)(\d+)\]\[PROPERTY_'.$arProperty['ID'].'\]\[([^\]]*)\]\[VALUE\]/', $strHTMLControlName['VALUE'], $arMatch);
            $elementType = $arMatch[1];
            $elementId = $arMatch[2];
            $valueId = $arMatch[3];
            $tableId = 'tb'.md5($elementType.$elementId.':'.$arProperty['ID']);

            if(!$valueId) {
                return '';
            }
        } else {
            preg_match('/PROP\['.$arProperty['ID'].'\]\[([^\]]*)\]\[VALUE\]/', $strHTMLControlName['VALUE'], $arMatch);
            $valueId = $arMatch[1];
            if($bEditProperty) {
                $tableId = 'form_content';
            } else {
                $tableId = 'tb'.md5(htmlspecialcharsbx('PROP['.$arProperty['ID'].']'));
            }
        }

        ob_start();
        ?>
        <?if($bAdminList ? !in_array($elementId, $cache) : !in_array($arProperty['ID'], $cache)):?>
            <?php
            if($bAdminList) {
                $cache[] = $elementId;
            } else {
                $cache[] = $arProperty['ID'];
            }

        $GLOBALS['APPLICATION']->AddHeadString('<script>new JRegionPhone(\''.$tableId.'\');</script>');
        ?>
            <?if($bEditProperty):?>
                <table><tbody><tr><td>
            <?endif; ?>
        <?endif; ?>

        <?extract(self::getValues(value: self::ConvertFromDB($arProperty, $value), control: $strHTMLControlName)); ?>

        <?php
        $itemClass = match(true) {
            $bAdminList => 'aspro_property_regionphone_item--admlistedit',
            !$bMultiple => 'aspro_property_regionphone_item--admlistview',
            default => '',
        };
        ?>
        <div class="aspro_property_regionphone_item <?=$itemClass; ?>">
            <div class="wrapper">
                <div class="inner_wrapper">
                    <div class="inner">
                        <div class="value_wrapper">
                            <input type="text" name="<?=$textName; ?>" value="<?=$text; ?>" maxlength="255" placeholder="<?=Loc::getMessage('TEXTWITHLINK_PROP_VALUE'); ?>" title="<?=Loc::getMessage('TEXTWITHLINK_PROP_VALUE'); ?>" size="30" />
                        </div>
                    </div>
                    <div class="inner">
                        <div class="value_wrapper">
                            <input type="text" name="<?=$hrefName; ?>" value="<?=$href; ?>" maxlength="255" placeholder="<?=Loc::getMessage('TEXTWITHLINK_PROP_LINK'); ?>" title="<?=Loc::getMessage('TEXTWITHLINK_PROP_LINK'); ?>" size="30" />
                        </div>
                    </div>
                    <?if(!$bEditProperty && $bMultiple):?>
                        <div class="remove" title="<?=Loc::getMessage('ASPRO_REGION_PHONE_DELETE_TITLE'); ?>"></div>
                        <div class="drag" title="<?=Loc::getMessage('ASPRO_REGION_PHONE_DRAG_TITLE'); ?>"></div>
                        <?endif; ?>
                </div>
                <div style="margin-bottom: 5px;">
                    <input type="checkbox" name="<?=$targetName; ?>" id="<?=$targetName; ?>" value="Y" <?= $target === 'Y' ? 'checked' : ''; ?> />
                    <label for="<?=$targetName; ?>"><?=Loc::getMessage('TEXTWITHLINK_PROP_TARGET'); ?></label>
                </div>
                <div>
                    <input type="checkbox" name="<?=$nofollowName; ?>" id="<?=$nofollowName; ?>" value="Y" <?= $nofollow === 'Y' ? 'checked' : ''; ?> />
                    <label for="<?=$nofollowName; ?>"><?=Loc::getMessage('TEXTWITHLINK_PROP_NOFOLLOW'); ?></label>
                </div>
            </div>
        </div>

        <?if($bEditProperty):?>
            </td></tr></tbody></table>
        <?endif; ?>
        <?php
        return ob_get_clean();
    }

    public static function getValues(array $value = [], array $control = [])
    {
        $text = htmlspecialcharsbx(is_array($value['VALUE']) ? $value['VALUE']['TEXT'] : '');
        $textName = $control['VALUE'].'[TEXT]';
        $href = htmlspecialcharsbx(is_array($value['VALUE']) ? $value['VALUE']['HREF'] : '');
        $hrefName = $control['VALUE'].'[HREF]';
        $target = htmlspecialcharsbx(is_array($value['VALUE']) ? $value['VALUE']['TARGET_BLANK'] : '');
        $targetName = $control['VALUE'].'[TARGET_BLANK]';
        $nofollow = htmlspecialcharsbx(is_array($value['VALUE']) ? $value['VALUE']['NOFOLLOW'] : '');
        $nofollowName = $control['VALUE'].'[NOFOLLOW]';

        return compact('text', 'textName', 'href', 'hrefName', 'target', 'targetName', 'nofollow', 'nofollowName');
    }

    public static function PrepareSettings($arFields)
    {
        $arFields['FILTRABLE'] = $arFields['SMART_FILTER'] = $arFields['SEARCHABLE'] = $arFields['WITH_DESCRIPTION'] = 'N';
        // $arFields['MULTIPLE'] = 'Y';
        $arFields['MULTIPLE_CNT'] = 1;

        return $arFields;
    }

    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        $arPropertyFields = [
            'HIDE' => [
                'SMART_FILTER',
                'MULTIPLE_CNT',
                'COL_COUNT',
                // 'MULTIPLE',
                'WITH_DESCRIPTION',
                'FILTER_HINT',
                'DEFAULT_VALUE',
                'SEARCHABLE',
                'FILTRABLE',
            ],
            'SET' => [
                'SMART_FILTER' => 'N',
                'MULTIPLE_CNT' => '1',
                // 'MULTIPLE' => 'Y',
                'WITH_DESCRIPTION' => 'N',
            ],
        ];

        return '';
    }

    public static function ConvertToDB($arProperty, $value)
    {
        if (
            is_string($value['VALUE'])
            && $value['VALUE']
        ) {
            try {
                $value['VALUE'] = Json::decode($value['VALUE']);
            } catch (\Exception $e) {
            }
        }

        if(
            !is_array($value['VALUE'])
            || !strlen($value['VALUE']['TEXT'])
        ) {
            return [
                'VALUE' => '',
                'DESCRIPTION' => '',
            ];
        }

        $value['VALUE'] = [
            'TEXT' => strlen($value['VALUE']['TEXT']) ? trim($value['VALUE']['TEXT']) : '',
            'HREF' => strlen($value['VALUE']['HREF']) ? trim($value['VALUE']['HREF']) : '',
            'TARGET_BLANK' => strlen($value['VALUE']['TARGET_BLANK']) ? $value['VALUE']['TARGET_BLANK'] : 'N',
            'NOFOLLOW' => strlen($value['VALUE']['NOFOLLOW']) ? $value['VALUE']['NOFOLLOW'] : 'N',
        ];
        $value['VALUE'] = Json::encode($value['VALUE']);

        return $value;
    }

    public static function ConvertFromDB($arProperty, $value)
    {
        if(!is_array($value['VALUE'])) {
            $value['VALUE'] = strlen($value['VALUE']) ? $value['VALUE'] : '[]';

            try {
                $value['VALUE'] = Json::decode($value['VALUE']);
            } catch(\Exception $e) {
                $value['VALUE'] = [];
            }
        }

        if(
            !$value['VALUE']
            || !is_array($value['VALUE'])
            || !strlen($value['VALUE']['TEXT'])
        ) {
            $value['VALUE'] = [
                'TEXT' => '',
                'HREF' => '',
                'TARGET_BLANK' => 'N',
                'NOFOLLOW' => 'N',
            ];
        }

        return $value;
    }

    public static function GetPublicViewHTML($arProperty)
    {
        return '';
    }

    public static function getValuesForPublic($arProperty)
    {
        $value = $displayValue = [];

        try {
            $dbValues = (array) $arProperty['~VALUE'] ?: [];
            $allValues = array_map('\Bitrix\Main\Web\Json::decode', $dbValues);
        } catch(\Exception $e) {
            $allValues = [];
        }

        foreach ($allValues as $values) {
            $value[] = $values['TEXT'];

            $href = $values['HREF'];
            if (str_starts_with($href, '/')) {
                $href = preg_replace('/[\/]{2,}/', SITE_DIR, SITE_DIR.$href);
            }
            if ($href) {
                $attributes = [];
                if ($values['TARGET_BLANK'] === 'Y') {
                    $attributes[] = 'target=\'_blank\'';
                }
                if ($values['NOFOLLOW'] === 'Y') {
                    $attributes[] = 'rel=\'nofollow\'';
                }

                $displayValue[] = "<a href='{$href}' class='light-opacity-hover' ".(implode(' ', $attributes)).">{$values['TEXT']}</a>";
            } else {
                $displayValue[] = $values['TEXT'];
            }
        }

        return compact('value', 'displayValue');
    }

    public static function GetLength($arProperty, $value)
    {
        if(is_array($value['VALUE'])) {
            return !empty($value['VALUE']['TEXT']);
        }
    }
}

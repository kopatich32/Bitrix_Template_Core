<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main;
use Bitrix\Main\Localization\Loc as Loc;

Loc::loadMessages(__FILE__);

try {
    $arComponentParameters = [
        'GROUPS' => [
            'BASE' => [
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_GROUP_BASE'),
            ],
            'REVIEW' => [
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_GROUP_REVIEW'),
            ],
            'URL_TEMPLATES' => [
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SEF_URL_TEMPLATES'),
            ],
            'ADDITIONAL_SETTINGS' => [
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_GROUP_ADDITIONAL_SETTINGS'),
            ],
        ],
        'PARAMETERS' => [
            'MODE' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SEF_MODE'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y',
            ],
            'FOLDER' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SEF_FOLDER'),
                'TYPE' => 'STRING',
            ],
            'IBLOCK_ID' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_IBLOCK_ID'),
                'TYPE' => 'STRING',
            ],
            'SECTION_ID' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SECTION_ID'),
                'TYPE' => 'STRING',
            ],
            'CACHE_TIME' => [
                'DEFAULT' => 36000000,
            ],
            'URL_TEMPLATES' => [
                'PARENT' => 'URL_TEMPLATES',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SEF_URL_TEMPLATES'),
                'TYPE' => 'STRING',
                'DEFAULT' => '={$arParams["SEF_URL_TEMPLATES"]}',
            ],
            'SHOW_VIEW_CONTENT' => [
                'PARENT' => 'ADDITIONAL_SETTINGS',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SHOW_VIEW_CONTENT'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N',
            ],
            'CODE_VIEW_CONTENT' => [
                'PARENT' => 'ADDITIONAL_SETTINGS',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_CODE_VIEW_CONTENT'),
                'TYPE' => 'STRING',
                'DEFAULT' => 'aspro_smartseo_tags',
            ],
            'VIEW_TYPE' => [
                'PARENT' => 'ADDITIONAL_SETTINGS',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_VIEW_TYPE'),
                'TYPE' => 'LIST',
                'VALUES' => [
                    'normal' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_VIEW_TYPE_NORMAL'),
                    'slider' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_VIEW_TYPE_SLIDER'),
                    'filter' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_VIEW_TYPE_FILTER'),
                ],
                'DEFAULT' => 'normal',
            ],
            'BG_FILLED' => [
                'PARENT' => 'ADDITIONAL_SETTINGS',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_BG_FILLED'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y',
            ],
            'SHOW_COUNT' => [
                'PARENT' => 'ADDITIONAL_SETTINGS',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SHOW_COUNT'),
                'TYPE' => 'STRING',
                'DEFAULT' => '10',
            ],
            'SHOW_COUNT_MOBILE' => [
                'PARENT' => 'ADDITIONAL_SETTINGS',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SHOW_COUNT_MOBILE'),
                'TYPE' => 'STRING',
                'DEFAULT' => '3',
            ],
            'SHOW_BY_GROUPS' => [
                'PARENT' => 'ADDITIONAL_SETTINGS',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SHOW_BY_GROUPS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N',
            ],
            'SHOW_DEACTIVATED' => [
                'PARENT' => 'ADDITIONAL_SETTINGS',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SHOW_DEACTIVATED'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N',
            ],
            'SORT' => [
                'PARENT' => 'ADDITIONAL_SETTINGS',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SORT'),
                'TYPE' => 'LIST',
                'VALUES' => [
                    'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SORT_NAME'),
                    'SORT' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_SORT_SORT'),
                ],
                'DEFAULT' => 'SORT',
            ],
            'TITLE_BLOCK' => [
                'PARENT' => 'ADDITIONAL_SETTINGS',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_TAGS_TITLE_BLOCK'),
                'TYPE' => 'STRING',
                'DEFAULT' => '',
            ],
        ]
    ];
} catch (Main\LoaderException $e) {
    ShowError($e->getMessage());
}
?>
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
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_CONTENT_GROUP_BASE'),
            ],
            'ADDITIONAL_SETTINGS' => [
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_CONTENT_GROUP_ADDITIONAL_SETTINGS'),
            ],
        ],
        'PARAMETERS' => [
            'FIELDS' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_CONTENT_FILEDS'),
                'TYPE' => 'LIST',
                'MULTIPLE' => 'Y',
                'VALUES' => [
                    'TOP_DESCRIPTION' => Loc::getMessage('ASPRO_SMARTSEO_CONTENT_TOP_DESCRIPTION'),
                    'BOTTOM_DESCRIPTION' => Loc::getMessage('ASPRO_SMARTSEO_CONTENT_BOTTOM_DESCRIPTION'),
                    'ADDITIONAL_DESCRIPTION' => Loc::getMessage('ASPRO_SMARTSEO_CONTENT_ADDITIONAL_DESCRIPTION')
                ]
            ],
            'SHOW_VIEW_CONTENT' => [
                'PARENT' => 'ADDITIONAL_SETTINGS',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_CONTENT_SHOW_VIEW_CONTENT'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N',
            ],
            'CODE_VIEW_CONTENT' => [
                'PARENT' => 'ADDITIONAL_SETTINGS',
                'NAME' => Loc::getMessage('ASPRO_SMARTSEO_CONTENT_CODE_VIEW_CONTENT'),
                'TYPE' => 'STRING',
                'DEFAULT' => 'aspro_smartseo_content',
            ],
        ]
    ];
} catch (Main\LoaderException $e) {
    ShowError($e->getMessage());
}
?>
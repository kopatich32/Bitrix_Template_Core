<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc as Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = [
    'NAME' => Loc::getMessage('ASPRO_SMARTSEO_CONTENT_COMPONENT'),
    'DESCRIPTION' => Loc::getMessage('ASPRO_SMARTSEO_CONTENT_DESCRIPTION'),
    'ICON' => '/images/icon.gif',
    'SORT' => 10,
    'PATH' => [
        'ID' => 'aspro',
        'NAME' => Loc::getMessage('ASPRO_SMARTSEO_CONTENT_NAME'),
        'SORT' => 10,
    ],
];
?>
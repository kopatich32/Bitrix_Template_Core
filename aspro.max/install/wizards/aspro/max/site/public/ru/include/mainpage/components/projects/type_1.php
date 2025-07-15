<?php

use Bitrix\Main\SystemException;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    include_once '../../../../ajax/const.php';
    require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
}

if (!include_once ($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/vendor/php/solution.php')) {
    throw new SystemException('Error include solution constants');
}
?>

<?$APPLICATION->IncludeComponent(
    'bitrix:news.list',
    'front_news',
    [
        'ACTIVE_DATE_FORMAT' => 'j F Y',
        'ADD_SECTIONS_CHAIN' => 'N',
        'AJAX_MODE' => 'N',
        'AJAX_OPTION_ADDITIONAL' => '',
        'AJAX_OPTION_HISTORY' => 'N',
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_STYLE' => 'Y',
        'ALL_BLOCK_BG' => 'Y',
        'ALL_URL' => 'projects/',
        'BG_POSITION' => 'center',
        'CACHE_FILTER' => 'Y',
        'CACHE_GROUPS' => 'N',
        'CACHE_TIME' => '36000000',
        'CACHE_TYPE' => 'A',
        'CHECK_DATES' => 'Y',
        'CHECK_REQUEST_BLOCK' => TSolution::checkRequestBlock('projects'),
        'COMPONENT_TEMPLATE' => 'front_news',
        'DISPLAY_BOTTOM_PAGER' => 'Y',
        'DISPLAY_DATE' => 'Y',
        'DISPLAY_NAME' => 'Y',
        'DISPLAY_PICTURE' => 'N',
        'DISPLAY_PREVIEW_TEXT' => 'N',
        'DISPLAY_TOP_PAGER' => 'N',
        'FIELD_CODE' => ['NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE'],
        'FILTER_NAME' => 'arRegionLinkFront',
        'FON_BLOCK_2_COLS' => 'N', // "Y",
        'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
        'IBLOCK_ID' => '#IBLOCK_PROJECTS_ID#',
        'IBLOCK_TYPE' => '#IBLOCK_PROJECTS_TYPE#',
        'IMG_POSITION' => 'right',
        'INCLUDE_FILE' => '',
        'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
        'INCLUDE_SUBSECTIONS' => 'Y',
        'IS_AJAX' => TSolution::checkAjaxRequest(),
        'MESSAGE_404' => '',
        'NEWS_COUNT' => '3',
        'PAGER_BASE_LINK_ENABLE' => 'N',
        'PAGER_DESC_NUMBERING_CACHE_TIME' => '3600',
        'PAGER_DESC_NUMBERING' => 'N',
        'PAGER_SHOW_ALL' => 'N',
        'PAGER_SHOW_ALWAYS' => 'N',
        'PAGER_TEMPLATE' => 'ajax',
        'PAGER_TITLE' => '',
        'PREVIEW_TRUNCATE_LEN' => '',
        'PROPERTY_CODE' => [],
        'SET_META_DESCRIPTION' => 'N',
        'SET_META_KEYWORDS' => 'N',
        'SET_STATUS_404' => 'N',
        'SET_TITLE' => 'N',
        'SHOW_404' => 'N',
        'SHOW_SECTION_NAME' => 'Y',
        'SIZE_IN_ROW' => '3',
        'SORT_BY1' => 'SORT',
        'SORT_BY2' => 'ID',
        'SORT_ORDER1' => 'ASC',
        'SORT_ORDER2' => 'DESC',
        'STRICT_SECTION_CHECK' => 'N',
        'TITLE_BLOCK_ALL' => 'Все проекты',
        'TITLE_BLOCK' => 'Проекты',
        'TITLE_SHOW_FON' => 'N',
        'TYPE_IMG' => 'lg',
        'USE_BG_IMAGE_ALTERNATE' => 'N',
    ],
    false
);?>

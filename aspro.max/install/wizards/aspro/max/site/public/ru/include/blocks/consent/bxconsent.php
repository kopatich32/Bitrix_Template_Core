<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//options from \Aspro\Functions\CAsproMax::showBlockHtml
$arOptions = $arConfig['PARAMS'];
?>
<?
$APPLICATION->IncludeComponent(
    "bitrix:main.userconsent.request",
    "main",
    Array(
        "AUTO_SAVE" => "Y",
        "COMPOSITE_FRAME_MODE" => "A",
        "COMPOSITE_FRAME_TYPE" => "AUTO",
        "ID" => TSolution\Utils::getAgreementIdByOption($arOptions['OPTION_CODE']),
        "IS_CHECKED" => TSolution::GetFrontParametrValue('LICENCE_CHECKED'),
        "IS_LOADED" => "N",
        "INPUT_NAME" => $arOptions['INPUT_NAME'] ?? 'licence',
        'SUBMIT_EVENT_NAME' => $arOptions['SUBMIT_EVENT_NAME'] ?? '',
        "REPLACE" => array(
            'button_caption' => $arOptions['SUBMIT_TEXT'] ?? 'Send',
            'fields' => $arOptions['REPLACE_FIELDS'] ?? []
        ),
        "HIDDEN_ERROR" => $arOptions['HIDDEN_ERROR'] ?? "N"
    ),
    $arOptions['PARENT_COMPONENT'] ?? false,
    ['HIDE_ICONS' => 'Y']
);
?>

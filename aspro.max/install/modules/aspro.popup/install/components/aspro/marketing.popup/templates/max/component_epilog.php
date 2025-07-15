<?
$arScripts = [];
if($templateData["USE_COUNTDOWN"]){
    $arScripts[] = 'countdown';
}
if (count($arScripts)) {
    \Aspro\Max\Functions\Extensions::initInPopup($arScripts);
}
?>
<?if($templateData["USE_COUNTDOWN"]):?>
    <script>typeof useCountdown === 'function' && useCountdown()</script>
<?endif;?>
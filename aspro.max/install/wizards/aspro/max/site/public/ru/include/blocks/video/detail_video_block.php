<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();

$arOptions = (array)$arConfig['PARAMS'];
$skeletonClass = 'ui-card__image skeleton skeleton-item';
$totalCountElements = array_reduce($arOptions['VIDEO'], function($carry, $item) {
    return $carry + count($item);
}, 0);
?>

<div class="hidden_print">
    <div class="video_block appear-block grid-list grid-list--items <?=$totalCountElements > 1 ? 'grid-list--items-2-from-601' : ''?>">
        <?if (!empty($arOptions['VIDEO']['VIDEO_YOUTUBE'])):?>
            <?require 'video_youtube.php';?>
        <?endif;?>
        <?if (!empty($arOptions['VIDEO']['VIDEO_FILE'])):?>
            <?require 'video_file.php';?>
        <?endif;?>
    </div>
</div>    
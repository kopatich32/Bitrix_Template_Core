<?
use Aspro\Max\Preset;

global $APPLICATION;

if(Bitrix\Main\Config\Option::get('aspro.max', 'USE_BIG_BANNERS', 'N', SITE_ID) === 'Y'):?>
    <?    
    $bannerIndexStyle = '<style>
    .main-slider:not(.swiper-initialized) .swiper-slide:not([data-slide_index="' . $templateData['CURRENT_BANNER_INDEX'] . '"]) {
        display: none;
    }
    </style>';
    $APPLICATION->AddHeadString($bannerIndexStyle);
    ?>
    <script data-skip-moving="true">
    window[solutionName]['CURRENT_BANNER_INDEX'] = "<?=($templateData['CURRENT_BANNER_INDEX'] + 1)?>";
    </script>
<?endif;?>
<?php

namespace Aspro\Max\Functions;

use CMax as Solution;

class Extensions
{
    public static function register()
    {
        $arConfig = [
            'animation_ext' => [
                'css' => SITE_TEMPLATE_PATH.'/css/animation/animation_ext.css',
            ],
            'banners' => [
                'css' => SITE_TEMPLATE_PATH.'/css/banners.min.css',
            ],
            'bigdata' => [
                'js' => SITE_TEMPLATE_PATH.'/js/bigdata.js',
            ],
            'bonus_system' => [
                'css' => SITE_TEMPLATE_PATH.'/css/bonus-system.min.css',
            ],
            'bootstrap' => [
                'js' => SITE_TEMPLATE_PATH.'/vendor/js/bootstrap.js',
                'css' => [
                    SITE_TEMPLATE_PATH.'/vendor/css/bootstrap.css',
                ],
            ],
            'buy_services' => [
                'css' => SITE_TEMPLATE_PATH.'/css/buy_services.min.css',
                'js' => SITE_TEMPLATE_PATH.'/js/buy_services.min.js',
            ],
            'catalog_element' => [
                'js' => SITE_TEMPLATE_PATH.'/js/catalog_element.min.js',
            ],
            'chip' => [
                'css' => SITE_TEMPLATE_PATH.'/css/chip.css',
            ],
            'countdown' => [
                'js' => [
                    SITE_TEMPLATE_PATH.'/js/countdown.js',
                ],
            ],
            'cross' => [
                'css' => SITE_TEMPLATE_PATH.'/css/cross.min.css',
            ],
            'detail_gallery' => [
                'css' => SITE_TEMPLATE_PATH.'/css/detail-gallery.css',
            ],
            'drop' => [
                'css' => SITE_TEMPLATE_PATH.'/css/drop.css',
                'js' => SITE_TEMPLATE_PATH.'/js/drop.js',
                'lang' => '/bitrix/modules/'.Solution::moduleID.'/lang/'.LANGUAGE_ID.'/lib/drop.php',
            ],
            'fancybox' => [
                'css' => SITE_TEMPLATE_PATH.'/css/jquery.fancybox.min.css',
                'js' => SITE_TEMPLATE_PATH.'/js/jquery.fancybox.min.js',
            ],
            'font_awesome' => [
                'css' => SITE_TEMPLATE_PATH.'/vendor/fonts/font-awesome/css/font-awesome.min.css',
            ],
            'gallery_small' => [
                'css' => SITE_TEMPLATE_PATH.'/css/gallery_small.css',
                'js' => SITE_TEMPLATE_PATH.'/js/gallery_small.js',
            ],
            'grid-list' => [
                'css' => SITE_TEMPLATE_PATH.'/css/blocks/grid-list.min.css',
            ],
            'gutters' => [
                'css' => SITE_TEMPLATE_PATH.'/css/blocks/gutters.min.css',
            ],
            'hash_location' => [
                'js' => SITE_TEMPLATE_PATH.'/js/hash_location.js',
            ],
            'ikSelect' => [
                'js' => SITE_TEMPLATE_PATH.'/js/jquery.ikSelect.min.js',
            ],
            'intl_phone_input' => [
                'js' => [
                    SITE_TEMPLATE_PATH.'/vendor/js/intl.phone/intlTelInput.js',
                ],
                'css' => [
                    SITE_TEMPLATE_PATH.'/vendor/css/intl.phone/intlTelInput.css',
                    SITE_TEMPLATE_PATH.'/css/phone/intlTelCustom.css',
                ],
            ],
            'jquery.uniform' => [
                'js' => SITE_TEMPLATE_PATH.'/js/jquery.uniform.min.js',
            ],
            'jquery.validate' => [
                'js' => SITE_TEMPLATE_PATH.'/js/jquery.validate.js',
            ],
            'left_menu_aim' => [
                'js' => SITE_TEMPLATE_PATH.'/js/leftMenuAim.js',
            ],
            'line_block' => [
                'css' => SITE_TEMPLATE_PATH.'/css/blocks/line-block.min.css',
            ],
            'logo' => [
                'js' => SITE_TEMPLATE_PATH.'/js/logo.min.js',
            ],
            'mobile-scrolled' => [
                'css' => SITE_TEMPLATE_PATH.'/css/blocks/mobile-scrolled.min.css',
            ],
            'notice' => [
                'js' => '/bitrix/js/'.Solution::moduleID.'/notice.js',
                'css' => '/bitrix/css/'.Solution::moduleID.'/notice.css',
                'lang' => '/bitrix/modules/'.Solution::moduleID.'/lang/'.LANGUAGE_ID.'/lib/notice.php',
            ],
            'order_actions' => [
                'js' => SITE_TEMPLATE_PATH.'/js/order_actions.js',
            ],
            'out_of_production' => [
                'js' => SITE_TEMPLATE_PATH.'/js/out_of_production.js',
                'css' => SITE_TEMPLATE_PATH.'/css/out_of_production.css',
            ],
            'owl_carousel' => [
                'js' => SITE_TEMPLATE_PATH.'/vendor/js/carousel/owl/owl.carousel.min.js',
                'css' => [
                    SITE_TEMPLATE_PATH.'/vendor/css/carousel/owl/owl.carousel.min.css',
                    SITE_TEMPLATE_PATH.'/vendor/css/carousel/owl/owl.theme.default.min.css',
                ],
            ],
            'phone_input' => [
                'js' => [
                    SITE_TEMPLATE_PATH.'/js/phone/phone_input.js',
                ],
            ],
            'phone_mask' => [
                'js' => [
                    SITE_TEMPLATE_PATH.'/js/jquery.inputmask.bundle.min.js',
                ],
            ],
            'section_filter' => [
                'css' => SITE_TEMPLATE_PATH.'/css/section_filter.css',
                'js' => SITE_TEMPLATE_PATH.'/js/section_filter.js',
            ],
            'set_cookie_on_domains' => [
                'js' => [
                    SITE_TEMPLATE_PATH.'/js/setCookieOnDomains.js',
                ],
            ],
            'propertygroups' => [
                'js' => SITE_TEMPLATE_PATH.'/js/propertygroups.js',
                'css' => SITE_TEMPLATE_PATH.'/css/propertygroups.css',
            ],
            'hint' => [
                'js' => SITE_TEMPLATE_PATH.'/js/hint.js',
            ],
            'scroll_active_tab' => [
                'js' => SITE_TEMPLATE_PATH.'/js/scroll_active_tab.js',
            ],
            'searchtitle' => [
                'css' => SITE_TEMPLATE_PATH.'/css/searchtitle.css',
                'js' => SITE_TEMPLATE_PATH.'/js/searchtitle.js',
                'lang' => '/bitrix/modules/'.Solution::moduleID.'/lang/'.LANGUAGE_ID.'/lib/searchtitle.php',
            ],
            'select_offer' => [
                'js' => SITE_TEMPLATE_PATH.'/js/select_offer.js',
                'rel' => [
                    Solution::partnerName.'_select_offer_func'
                ],
            ],
            'select_offer_func' => [
                'js' => SITE_TEMPLATE_PATH.'/js/select_offer_func.js',
            ],
            'skeleton' => [
                'css' => SITE_TEMPLATE_PATH.'/css/skeleton.css',
            ],
            'smart_position_dropdown' => [
                'js' => SITE_TEMPLATE_PATH.'/js/smartPositionDropdown.js',
            ],
            'swiper' => [
                'js' => SITE_TEMPLATE_PATH.'/vendor/js/carousel/swiper/swiper-bundle.min.js',
                'css' => [
                    SITE_TEMPLATE_PATH.'/vendor/css/carousel/swiper/swiper-bundle.min.css',
                    SITE_TEMPLATE_PATH.'/css/slider.swiper.min.css',
                ],
                'rel' => [
                    Solution::partnerName.'_swiper_init'
                ],
            ],
            'swiper_events' => [
                'js' => SITE_TEMPLATE_PATH.'/js/slider.swiper.galleryEvents.min.js',
            ],
            'swiper_main_styles' => [
                'css' => SITE_TEMPLATE_PATH.'/css/main_slider.min.css',
            ],
            'swiper_init' => [
                'js' => SITE_TEMPLATE_PATH.'/js/slider.swiper.min.js',
                'rel' => [
                    Solution::partnerName.'_swiper'
                ],
            ],
            'tabs' => [
                'css' => SITE_TEMPLATE_PATH.'/css/tabs.css',
            ],
            'tabs_history' => [
                'js' => SITE_TEMPLATE_PATH.'/js/tabs_history.js',
            ],
            'top_banner' => [
                'js' => '/bitrix/components/aspro/com.banners.max/common_files/js/script.min.js',
            ],
            'mega_menu' => [
                'js' => SITE_TEMPLATE_PATH.'/js/mega_menu.js',
                'css' => SITE_TEMPLATE_PATH.'/css/mega_menu.css',
            ],
            'top_tabs' => [
                'css' => SITE_TEMPLATE_PATH.'/css/top_tabs.min.css',
            ],
            'ui-card' => [
                'css' => SITE_TEMPLATE_PATH.'/css/conditional/ui-card.min.css',
            ],
            'ui-card.ratio' => [
                'css' => SITE_TEMPLATE_PATH.'/css/conditional/ui-card.ratio.min.css',
            ],
            'validate' => [
                'js' => [
                    SITE_TEMPLATE_PATH.'/js/conditional/validation.js',
                ],
                'rel' => [
                    Solution::partnerName.'_jquery.validate',
                ],
            ],
            'video' => [
                'css' => SITE_TEMPLATE_PATH.'/css/conditional/video-block.min.css',
            ],
            'video_banner' => [
                'js' => SITE_TEMPLATE_PATH.'/js/video_banner.min.js',
            ],
            'xzoom' => [
                'css' => SITE_TEMPLATE_PATH.'/css/xzoom.min.css',
                'js' => SITE_TEMPLATE_PATH.'/js/xzoom.min.js',
            ],
            'video_inline_appear' => [
                'js' => SITE_TEMPLATE_PATH.'/js/video_inline_appear.min.js',
            ],
            'video_block' => [
                'css' => SITE_TEMPLATE_PATH.'/css/video_block.min.css',
            ],
        ];

        foreach ($arConfig as $ext => $arExt) {
            \CJSCore::RegisterExt(Solution::partnerName.'_'.$ext, array_merge($arExt, ['skip_core' => true]));
        }
    }

    public static function init($arExtensions)
    {
        $arExtensions = is_array($arExtensions) ? $arExtensions : (array) $arExtensions;

        if ($arExtensions) {
            $arExtensions = array_map(function ($ext) {
                return strpos($ext, Solution::partnerName) !== false ? $ext : Solution::partnerName.'_'.$ext;
            }, $arExtensions);

            \CJSCore::Init($arExtensions);
        }
    }

    public static function initInPopup($arExtensions)
    {
        static::register();
        static::init($arExtensions);
    }
}

<?
namespace Aspro\Max\Traits;

use \Bitrix\Main\Config\Option;

use CMax as Solution;

trait Js {
    public static function SetJSOptions()
    {
        global $APPLICATION, $STARTTIME, $arSite, $arTheme;

		$MESS['MIN_ORDER_PRICE_TEXT']=Option::get(Solution::moduleID, 'MIN_ORDER_PRICE_TEXT', GetMessage('MIN_ORDER_PRICE_TEXT_EXAMPLE'), SITE_ID);

		list($bPhoneAuthSupported, $bPhoneAuthShow, $bPhoneAuthRequired, $bPhoneAuthUse) = \Aspro\Max\PhoneAuth::getOptions();

		$arFrontParametrs = Solution::GetFrontParametrsValues(SITE_ID);
		?>
		<?\Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('basketitems-component-block');?>
			<?if(Solution::getShowBasket()):?>
				<?if($arFrontParametrs['USE_REGIONALITY'] == 'Y')
					\CSaleBasket::UpdateBasketPrices(\CSaleBasket::GetBasketUserID(), SITE_ID);
				?>
				<?$APPLICATION->IncludeComponent( "bitrix:sale.basket.basket.line", "actual", Array(
					"PATH_TO_BASKET" => SITE_DIR."basket/",
					"PATH_TO_ORDER" => SITE_DIR."order/",
					"SHOW_DELAY" => "Y",
					"SHOW_PRODUCTS"=>"Y",
					"SHOW_EMPTY_VALUES" => "Y",
					"SHOW_NOTAVAIL" => "N",
					"SHOW_SUBSCRIBE" => "N",
					"SHOW_IMAGE" => "Y",
					"SHOW_PRICE" => "Y",
					"SHOW_SUMMARY" => "Y",
					"SHOW_NUM_PRODUCTS" => "Y",
					"SHOW_TOTAL_PRICE" => "Y",
					"HIDE_ON_BASKET_PAGES" => "N"
				) );?>
			<?endif;?>
		<?\Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('basketitems-component-block', '');?>
		<?if($arFrontParametrs['SHOW_LICENCE'] == 'Y')
		{
			if(function_exists('file_get_contents'))
			{
				$license_text = file_get_contents(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/licenses_text.php'));
			}
			else
			{
				ob_start();
					include_once(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/licenses_text.php'));
				$license_text = ob_get_contents();
				ob_end_clean();
			}
			$MESS['LICENSES_TEXT'] = $license_text;
		}?>
		<?if($arFrontParametrs['SHOW_OFFER'] == 'Y')
		{
			if(function_exists('file_get_contents'))
			{
				$license_text = file_get_contents(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/offer_text.php'));
			}
			else
			{
				ob_start();
					include_once(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/offer_text.php'));
				$license_text = ob_get_contents();
				ob_end_clean();
			}
			$MESS['OFFER_TEXT'] = $license_text;
		}?>
		<?
		if (Solution::IsOrderPage()) {
			if (function_exists('file_get_contents')) {
				$required_text = file_get_contents(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/required_message.php'));
			} else {
				ob_start();
					include_once(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/required_message.php'));
				$required_text = ob_get_contents();
				ob_end_clean();
			}
			$MESS['REQUIRED_TEXT'] = $required_text;
		}
		?>
		<div class="cd-modal-bg"></div>
		<script data-skip-moving="true">var solutionName = 'arMaxOptions';</script>
		<script src="<?=SITE_TEMPLATE_PATH.'/js/setTheme.php?site_id='.SITE_ID.'&site_dir='.SITE_DIR?>" data-skip-moving="true"></script>
		<script type="text/javascript">window.onload=function(){window.basketJSParams = window.basketJSParams || [];<?if($arFrontParametrs['YANDEX_ECOMERCE'] == 'Y' || $arFrontParametrs['GOOGLE_ECOMERCE'] == 'Y'):?>window.dataLayer = window.dataLayer || [];<?endif;?>}
		BX.message(<?=\CUtil::PhpToJSObject( $MESS, false )?>);
		arAsproOptions.PAGES.FRONT_PAGE = window[solutionName].PAGES.FRONT_PAGE = "<?=Solution::IsMainPage()?>";arAsproOptions.PAGES.BASKET_PAGE = window[solutionName].PAGES.BASKET_PAGE = "<?=Solution::IsBasketPage()?>";arAsproOptions.PAGES.ORDER_PAGE = window[solutionName].PAGES.ORDER_PAGE = "<?=Solution::IsOrderPage()?>";arAsproOptions.PAGES.PERSONAL_PAGE = window[solutionName].PAGES.PERSONAL_PAGE = "<?=Solution::IsPersonalPage()?>";arAsproOptions.PAGES.CATALOG_PAGE = window[solutionName].PAGES.CATALOG_PAGE = "<?=Solution::IsCatalogPage()?>";</script>
		<?$APPLICATION->ShowViewContent('themeScriptJS');?>
		<?/*fix reset POST*/
		if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['color_theme']){
			LocalRedirect($_SERVER['HTTP_REFERER']);
		}?><?
    }

    public static function autoloadJs()
    {
		$arBlocks = glob($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/js/autoload/*.js');

		foreach ($arBlocks as $blockPath) {
			if (strpos($blockPath, '.min.js') === false) {
				$currentPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $blockPath);
                $minFile = str_replace('.js', '.min.js', $currentPath);
                if (file_exists($_SERVER['DOCUMENT_ROOT'].$minFile)) {
                    // $currentPath = $minFile;
                }
				$GLOBALS['APPLICATION']->AddHeadScript($currentPath);
			}
		}
	}
}
?>

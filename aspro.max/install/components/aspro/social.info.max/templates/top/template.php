<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<div class="social-icons">
	<?if(
		$arParams["SOCIAL_TITLE"] && (
			!empty($arResult["SOCIAL_VK"]) ||
			!empty($arResult["SOCIAL_ODNOKLASSNIKI"]) ||
			!empty($arResult["SOCIAL_FACEBOOK"]) ||
			!empty($arResult["SOCIAL_TWITTER"]) ||
			!empty($arResult["SOCIAL_INSTAGRAM"]) ||
			!empty($arResult["SOCIAL_TELEGRAM"]) ||
			!empty($arResult["SOCIAL_MAIL"]) ||
			!empty($arResult["SOCIAL_YOUTUBE"]) ||
			//!empty($arResult["SOCIAL_GOOGLEPLUS"]) ||
			!empty($arResult["SOCIAL_VIBER"]) ||
			!empty($arResult["SOCIAL_WHATS"]) ||
			!empty($arResult["SOCIAL_WHATS_CUSTOM"]) ||
			!empty($arResult["SOCIAL_VIBER_CUSTOM_DESKTOP"]) ||
			!empty($arResult["SOCIAL_VIBER_CUSTOM_MOBILE"]) ||
			!empty($arResult["SOCIAL_ZEN"]) ||
			!empty($arResult["SOCIAL_PINTEREST"]) ||
			!empty($arResult["SOCIAL_SNAPCHAT"]) ||
			!empty($arResult["SOCIAL_TIKTOK"]) ||
			!empty($arResult["SOCIAL_LINKEDIN"]) ||
            !empty($arResult["SOCIAL_RUTUBE"])
		)
	):?>
		<div class="small_title"><?=$arParams["SOCIAL_TITLE"];?></div>
	<?endif;?>
	<!-- noindex -->
	<ul>
		<?if(!empty($arResult['SOCIAL_VK'])):?>
			<li class="vk">
				<a href="<?=$arResult['SOCIAL_VK']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_VK')?>">
					<?=CMax::showIconSvg("vk", SITE_TEMPLATE_PATH.'/images/svg/social/social_vk.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_FACEBOOK'])):?>
			<li class="facebook">
				<a href="<?=$arResult['SOCIAL_FACEBOOK']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_FACEBOOK')?>">
					<?=CMax::showIconSvg("fb", SITE_TEMPLATE_PATH.'/images/svg/social/Facebook.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_ODNOKLASSNIKI'])):?>
			<li class="odn">
				<a href="<?=$arResult['SOCIAL_ODNOKLASSNIKI']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_ODNOKLASSNIKI')?>">
					<?=CMax::showIconSvg("odn", SITE_TEMPLATE_PATH.'/images/svg/social/Odnoklassniki.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_TWITTER'])):?>
			<li class="twitter">
				<a href="<?=$arResult['SOCIAL_TWITTER']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_TWITTER')?>">
					<?=CMax::showIconSvg("tw", SITE_TEMPLATE_PATH.'/images/svg/social/social_twitter.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_INSTAGRAM'])):?>
			<li class="instagram">
				<a href="<?=$arResult['SOCIAL_INSTAGRAM']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_INSTAGRAM')?>">
					<?=CMax::showIconSvg("inst", SITE_TEMPLATE_PATH.'/images/svg/social/Instagram.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_TELEGRAM'])):?>
			<li class="telegram">
				<a href="<?=$arResult['SOCIAL_TELEGRAM']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_TELEGRAM')?>">
					<?=CMax::showIconSvg("tlg", SITE_TEMPLATE_PATH.'/images/svg/social/social_telegram.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_YOUTUBE'])):?>
			<li class="ytb">
				<a href="<?=$arResult['SOCIAL_YOUTUBE']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_YOUTUBE')?>">
					<?=CMax::showIconSvg("ytb", SITE_TEMPLATE_PATH.'/images/svg/social/Youtube.svg');?>
				</a>
			</li>
		<?endif;?>
		<?/*if(!empty($arResult['SOCIAL_GOOGLEPLUS'])):?>
			<li class="gplus">
				<a href="<?=$arResult['SOCIAL_GOOGLEPLUS']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_GOOGLEPLUS')?>">
					<?=CMax::showIconSvg("gplus", SITE_TEMPLATE_PATH.'/images/svg/social/Googleplus.svg');?>
				</a>
			</li>
		<?endif;*/?>
		<?if(!empty($arResult['SOCIAL_MAIL'])):?>
			<li class="mail">
				<a href="<?=$arResult['SOCIAL_MAIL']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_MAILRU')?>">
					<?=CMax::showIconSvg("mail", SITE_TEMPLATE_PATH.'/images/svg/social/Mailru.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_VIBER']) || !empty($arResult["SOCIAL_VIBER_CUSTOM_MOBILE"]) ):?>
			<?$hrefDesktop = strlen(trim($arResult["SOCIAL_VIBER_CUSTOM_DESKTOP"])) ? $arResult["SOCIAL_VIBER_CUSTOM_DESKTOP"] : 'viber://chat?number=+'.$arResult['SOCIAL_VIBER'];?>
			<li class="viber">
				<a href="<?=$hrefDesktop?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_VIBER')?>">
					<?=CMax::showIconSvg("vi", SITE_TEMPLATE_PATH."/images/svg/social/Viber.svg");?>
					<?=GetMessage('TEMPL_SOCIAL_VIBER')?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_WHATS']) || !empty($arResult["SOCIAL_WHATS_CUSTOM"])):?>
			<?
			if( strlen(trim($arResult["SOCIAL_WHATS_CUSTOM"])) ){
				$whatsHref = $arResult["SOCIAL_WHATS_CUSTOM"];
			} else {
				if(defined('LANG_CHARSET') && strtolower(LANG_CHARSET) == 'windows-1251'){
					$text = iconv("windows-1251","utf-8", $arResult['SOCIAL_WHATS_TEXT']);
				} else {
					$text = $arResult['SOCIAL_WHATS_TEXT'];
				}
				$bWhatsText = !empty($arResult['SOCIAL_WHATS_TEXT']);
				$whatsText = $bWhatsText ? '?text='.rawurlencode($text) : '';
				$whatsHref = 'https://wa.me/'.$arResult['SOCIAL_WHATS'].$whatsText;
			}
			?>
			<li class="whats">
				<a href="<?=$whatsHref?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_WHATS')?>">
					<?=CMax::showIconSvg("wh", SITE_TEMPLATE_PATH."/images/svg/social/Whatsapp.svg");?>
					<?=GetMessage('TEMPL_SOCIAL_WHATS')?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_ZEN'])):?>
			<li class="zen">
				<a href="<?=$arResult['SOCIAL_ZEN']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_ZEN')?>">
					<?=CMax::showIconSvg("zen", SITE_TEMPLATE_PATH."/images/svg/social/Zen.svg");?>
					<?=GetMessage('TEMPL_SOCIAL_ZEN')?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_TIKTOK'])):?>
			<li class="tiktok">
				<a href="<?=$arResult['SOCIAL_TIKTOK']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_TIKTOK')?>">
					<?=CMax::showIconSvg("tt", SITE_TEMPLATE_PATH."/images/svg/social/Tiktok.svg");?>
					<?=GetMessage('TEMPL_SOCIAL_TIKTOK')?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_PINTEREST'])):?>
			<li class="pinterest">
				<a href="<?=$arResult['SOCIAL_PINTEREST']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_PINTEREST')?>">
					<?=CMax::showIconSvg("pt", SITE_TEMPLATE_PATH."/images/svg/social/Pinterest.svg");?>
					<?=GetMessage('TEMPL_SOCIAL_PINTEREST')?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_SNAPCHAT'])):?>
			<li class="snapchat">
				<a href="<?=$arResult['SOCIAL_SNAPCHAT']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_SNAPCHAT')?>">
					<?=CMax::showIconSvg("sc", SITE_TEMPLATE_PATH."/images/svg/social/Snapchat.svg");?>
					<?=GetMessage('TEMPL_SOCIAL_SNAPCHAT')?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_LINKEDIN'])):?>
			<li class="linkedin">
				<a href="<?=$arResult['SOCIAL_LINKEDIN']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_LINKEDIN')?>">
					<?=CMax::showIconSvg("linkedin", SITE_TEMPLATE_PATH."/images/svg/social/Linkedin.svg");?>
					<?=GetMessage('TEMPL_SOCIAL_LINKEDIN')?>
				</a>
			</li>
		<?endif;?>
        <?if(!empty($arResult['SOCIAL_RUTUBE'])):?>
			<li class="rutube">
				<a href="<?=$arResult['SOCIAL_RUTUBE']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_RUTUBE')?>">
					<?=CMax::showIconSvg("linkedin", SITE_TEMPLATE_PATH."/images/svg/social/Rutube.svg");?>
					<?=GetMessage('TEMPL_SOCIAL_RUTUBE')?>
				</a>
			</li>
		<?endif;?>
	</ul>
	<!-- /noindex -->
</div>

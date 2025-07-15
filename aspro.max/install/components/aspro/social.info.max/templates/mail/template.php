<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<div class="mail_soc_wrapper" style="text-align:center;padding-top:20px;font-size:0px;">
	<?if(!empty($arResult['SOCIAL_VK'])):?>
		<a href="<?=$arResult['SOCIAL_VK']?>" target="_blank" class="mail_soc" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/vk.png" alt="<?=GetMessage("VKONTAKTE")?>" title="<?=GetMessage("VKONTAKTE")?>" />
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_ODNOKLASSNIKI'])):?>
		<a href="<?=$arResult['SOCIAL_ODNOKLASSNIKI']?>" target="_blank"  class="mail_soc" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/odn.png" alt="<?=GetMessage("ODN")?>" title="<?=GetMessage("ODN")?>" />
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_FACEBOOK'])):?>
		<a href="<?=$arResult['SOCIAL_FACEBOOK']?>" target="_blank" class="mail_soc" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/facebook.png" alt="<?=GetMessage("FACEBOOK")?>" title="<?=GetMessage("FACEBOOK")?>" />
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_TWITTER'])):?>
		<a href="<?=$arResult["SOCIAL_TWITTER"]?>" target="_blank" class="mail_soc" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/twitter.png" alt="<?=GetMessage("TWITTER")?>" title="<?=GetMessage("TWITTER")?>" /> 
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_INSTAGRAM'])):?>
		<a href="<?=$arResult["SOCIAL_INSTAGRAM"]?>" target="_blank" class="mail_soc" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/inst.png" alt="<?=GetMessage("INST")?>" title="<?=GetMessage("INST")?>" />
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_TELEGRAM'])):?>
		<a href="<?=$arResult["SOCIAL_TELEGRAM"]?>" target="_blank" class="mail_soc" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/telegram.png" alt="<?=GetMessage("TELEGRAM")?>" title="<?=GetMessage("TELEGRAM")?>" />
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_MAIL'])):?>
		<a href="<?=$arResult["SOCIAL_MAIL"]?>" target="_blank" class="mail_soc" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/mail.png" alt="<?=GetMessage("MAIL")?>" title="<?=GetMessage("MAIL")?>" />
		</a>
	<?endif;?>
	<?/*if(!empty($arResult['SOCIAL_GOOGLEPLUS'])):?>
		<a href="<?=$arResult["SOCIAL_GOOGLEPLUS"]?>" target="_blank" class="mail_soc" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/gplus.png" alt="<?=GetMessage("GOOGLEPLUS")?>" title="<?=GetMessage("GOOGLEPLUS")?>" /> 
		</a>
	<?endif;*/?>
	<?if(!empty($arResult['SOCIAL_YOUTUBE'])):?>
		<a href="<?=$arResult["SOCIAL_YOUTUBE"]?>" target="_blank" class="mail_soc" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/youtube.png" alt="<?=GetMessage("YOUTUBE")?>" title="<?=GetMessage("YOUTUBE")?>" /> 
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_VIBER']) || !empty($arResult["SOCIAL_VIBER_CUSTOM_DESKTOP"])):?>
		<?$hrefDesktop = strlen(trim($arResult["SOCIAL_VIBER_CUSTOM_DESKTOP"])) ? $arResult["SOCIAL_VIBER_CUSTOM_DESKTOP"] : 'viber://chat?number=+'.$arResult['SOCIAL_VIBER'];?>
		<a href="<?=$hrefDesktop?>" target="_blank" class="mail_soc" title="<?=GetMessage('VIBER')?>" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/viber.png" alt="<?=GetMessage("VIBER")?>" title="<?=GetMessage("VIBER")?>" />
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_WHATS']) || !empty($arResult["SOCIAL_WHATS_CUSTOM"]) ):?>
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
		<a href="<?=$whatsHref?>" target="_blank" class="mail_soc" title="<?=GetMessage('WHATS')?>" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/whats.png" alt="<?=GetMessage("WHATS")?>" title="<?=GetMessage("WHATS")?>" />
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_ZEN'])):?>
		<a href="<?=$arResult['SOCIAL_ZEN']?>" target="_blank" class="mail_soc" title="<?=GetMessage('ZEN')?>" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/zen.png" alt="<?=GetMessage("ZEN")?>" title="<?=GetMessage("ZEN")?>" />
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_TIKTOK'])):?>
		<a href="<?=$arResult['SOCIAL_TIKTOK']?>" target="_blank" class="mail_soc" title="<?=GetMessage('TIKTOK')?>" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/tiktok.png" alt="<?=GetMessage("TIKTOK")?>" title="<?=GetMessage("TIKTOK")?>" />
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_PINTEREST'])):?>
		<a href="<?=$arResult['SOCIAL_PINTEREST']?>" target="_blank" class="mail_soc" title="<?=GetMessage('PINTEREST')?>" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/pinterest.png" alt="<?=GetMessage("PINTEREST")?>" title="<?=GetMessage("PINTEREST")?>" />
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_SNAPCHAT'])):?>
		<a href="<?=$arResult['SOCIAL_SNAPCHAT']?>" target="_blank" class="mail_soc" title="<?=GetMessage('SNAPCHAT')?>" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/snapchat.png" alt="<?=GetMessage("SNAPCHAT")?>" title="<?=GetMessage("SNAPCHAT")?>" />
		</a>
	<?endif;?>
	<?if(!empty($arResult['SOCIAL_LINKEDIN'])):?>
		<a href="<?=$arResult['SOCIAL_LINKEDIN']?>" target="_blank" class="mail_soc" title="<?=GetMessage('LINKEDIN')?>" style="display:inline-block;font-size:0px;padding:5px;">
			<img src="/bitrix/components/aspro/social.info.max/images/linkedin.png" alt="<?=GetMessage("LINKEDIN")?>" title="<?=GetMessage("LINKEDIN")?>" />
		</a>
	<?endif;?>
</div>
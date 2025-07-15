<?
use Bitrix\Main\Loader;

$GLOBALS['APPLICATION']->RestartBuffer();
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/install/wizard_sol/wizard.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards/'.ASPRO_PARTNER_NAME.'/'.ASPRO_MODULE_NAME_SHORT.'/css/styles.css');

interface ISkipableWizardStep {
	static function getId();
	static function canSkip($wizard);
	static function definePrevStep($wizard);
	static function defineNextStep($wizard);
}

class SelectSiteStep extends CSelectSiteWizardStep{
	function setFromRequest(){
		if(!isset($_REQUEST['CurrentStepID'])){
			if(strlen($siteId = isset($_REQUEST['siteId']) ? strval($_REQUEST['siteId']) : false)){
				$this->wizard->setVar('siteID', $siteId);
			}

			if($bCreateSite = isset($_REQUEST['createSite']) ? $_REQUEST['createSite'] === 'Y' : false){
				$this->wizard->setVar('createSite', $bCreateSite ? 'Y' : 'N');
				$this->wizard->setVar('siteNewID', $siteId);
			}

			if(strlen($siteDir = isset($_REQUEST['siteDir']) ? strval($_REQUEST['siteDir']) : false)){
				$this->wizard->setVar('siteFolder', $siteDir);
			}

			if($siteId){
				$_REQUEST[$this->wizard->nextButtonID] = $_REQUEST[$this->wizard->nextStepHiddenID] = 1;
				$this->OnPostForm();
				unset($_REQUEST[$this->wizard->nextButtonID], $_REQUEST[$this->wizard->nextStepHiddenID]);
				if(!count($this->stepErrors)){
					if(!$this->wizard->firstStepID || $this->wizard->firstStepID == $this->stepID){
						$this->wizard->firstStepID = $this->nextStepID;
					}
				}
			}
		}
	}

	function InitStep(){
		parent::InitStep();

		$wizard =& $this->GetWizard();
		$wizard->solutionName = ASPRO_MODULE_NAME_SHORT;

		$this->setFromRequest();
	}
}

class SelectTemplateStep extends CWizardStep{
	function setFromRequest(){
		if(!isset($_REQUEST['CurrentStepID'])){
			if(strlen($templateID = isset($_REQUEST['templateID']) ? strval($_REQUEST['templateID']) : false)){
				$this->wizard->setVar('templateID', $templateID);
			}

			if($templateID){
				$_REQUEST[$this->wizard->nextButtonID] = $_REQUEST[$this->wizard->nextStepHiddenID] = 1;
				$this->OnPostForm();
				unset($_REQUEST[$this->wizard->nextButtonID], $_REQUEST[$this->wizard->nextStepHiddenID]);
				if(!count($this->stepErrors)){
					if(!$this->wizard->firstStepID || $this->wizard->firstStepID == $this->stepID){
						$this->wizard->firstStepID = $this->nextStepID;
					}
				}
			}
		}
	}

	function InitStep(){
		$wizard =& $this->GetWizard();
		$wizard->solutionName = ASPRO_MODULE_NAME_SHORT;

		$this->SetStepID('select_template');
		$this->SetTitle(GetMessage('SELECT_TEMPLATE_TITLE'));
		$this->SetSubTitle(GetMessage('SELECT_TEMPLATE_SUBTITLE'));

		if(!defined('WIZARD_DEFAULT_SITE_ID')){
			$this->SetPrevStep('select_site');
			$this->SetPrevCaption(GetMessage('PREVIOUS_BUTTON'));
		}
		else{
			$wizard->SetVar('siteID', WIZARD_DEFAULT_SITE_ID);
		}

		$this->SetNextStep('select_thematic');
		$this->SetNextCaption(GetMessage('NEXT_BUTTON'));
		$wizard->SetDefaultVars(array('templateID' => ASPRO_MODULE_NAME_SHORT));
		$this->setFromRequest();
	}

	function ShowStep(){
		if(!Loader::includeModule(ASPRO_MODULE_NAME)){
			$this->content .= '<p style="color:red">'.GetMessage('WIZ_NO_MODULE_').'</p>';
			?>
			<script type="text/javascript">
			$(document).ready(function() {
				$('.wizard-next-button').remove();
			});
			</script>
			<?
		}
		else{
			$wizard =& $this->GetWizard();
			$siteID = $wizard->GetVar('siteID');
			$templatesPath = WizardServices::GetTemplatesPath($wizard->GetPath().'/site');
			$arTemplates = WizardServices::GetTemplates($templatesPath);
			if(empty($arTemplates)){
				return;
			}

			foreach($arTemplates as $templateID => $arTemplate){
				if(strpos($templateID, ASPRO_PARTNER_NAME.'_') === false || (isset($arTemplate['TYPE']) && $arTemplate['TYPE'] === 'mail')){
					unset($arTemplates[$templateID]);
				}
			}

			$templateID = $wizard->GetVar('templateID');
			if(isset($templateID) && array_key_exists($templateID, $arTemplates)){
				$defaultTemplateID = $templateID;
				$wizard->SetDefaultVar('templateID', $templateID);
			}
			else{
				$defaultTemplateID = COption::GetOptionString('main', 'wizard_template_id', '', $siteID);
				if(!strlen($defaultTemplateID) || !array_key_exists($defaultTemplateID, $arTemplates)){
					$defaultTemplateID = '';
				}
				else{
					$wizard->SetDefaultVar('templateID', $defaultTemplateID);
				}
			}

			global $SHOWIMAGEFIRST;
			$SHOWIMAGEFIRST = true;
			$this->content .= '<div id="solutions-container" class="inst-template-list-block">';

			foreach($arTemplates as $templateID => $arTemplate){
				if(!strlen($defaultTemplateID)){
					$defaultTemplateID = $templateID;
					$wizard->SetDefaultVar('templateID', $defaultTemplateID);
				}
				elseif($defaultTemplateID == $templateID){
					$wizard->SetDefaultVar('templateID', $defaultTemplateID);
				}

				$this->content .= '<div class="inst-template-description">';
				$this->content .= $this->ShowRadioField('templateID', $templateID, array('id' => $templateID, 'class' => 'inst-template-list-inp'));
				if($arTemplate['SCREENSHOT'] && $arTemplate['PREVIEW']){
					$this->content .= CFile::Show2Images($arTemplate['PREVIEW'], $arTemplate['SCREENSHOT'], 150, 150, ' class="inst-template-list-img"');
				}
				else{
					$this->content .= CFile::ShowImage($arTemplate['SCREENSHOT'], 150, 150, ' class="inst-template-list-img"', '', true);
				}
				$this->content .= '<label for="'.$templateID.'" class="inst-template-list-label">';
				$this->content .= $arTemplate['NAME'].'<p>'.$arTemplate['DESCRIPTION'].'</p>';
				$this->content .= '</label>';
				$this->content .= '</div>';
			}

			$this->content .= '</div>';
		}
	}

	function OnPostForm(){
		$wizard =& $this->GetWizard();

		$proactive = COption::GetOptionString('statistic', 'DEFENCE_ON', 'N');
		if($proactive == "Y"){
			COption::SetOptionString('statistic', 'DEFENCE_ON', 'N');
			$wizard->SetVar('proactive', 'Y');
		}
		else{
			$wizard->SetVar('proactive', 'N');
		}

		if($wizard->IsNextButtonClick()){
			$templatesPath = WizardServices::GetTemplatesPath($wizard->GetPath().'/site');
			$arTemplates = WizardServices::GetTemplates($templatesPath);
			$templateID = $wizard->GetVar('templateID');

			if(!array_key_exists($templateID, $arTemplates)){
				$this->SetError(GetMessage('wiz_template'));
			}
		}
	}
}

class SelectThematicStep extends CWizardStep{
	function setFromRequest(){
		if(!isset($_REQUEST['CurrentStepID'])){
			if(strlen($thematic = isset($_REQUEST['thematic']) ? strval($_REQUEST['thematic']) : false)){
				$templateID = $this->wizard->GetVar('templateID');
				$thematicVarName = $templateID.'_thematicCODE';
				$this->wizard->setVar($thematicVarName, $thematic);
			}

			if($thematic){
				$_REQUEST[$this->wizard->nextButtonID] = $_REQUEST[$this->wizard->nextStepHiddenID] = 1;
				$this->OnPostForm();
				unset($_REQUEST[$this->wizard->nextButtonID], $_REQUEST[$this->wizard->nextStepHiddenID]);
				if(!count($this->stepErrors)){
					if(!$this->wizard->firstStepID || $this->wizard->firstStepID == $this->stepID){
						$this->wizard->firstStepID = $this->nextStepID;
					}
				}
			}
		}
	}

	static function getThematics(){
		static $arThematics;

		if(!isset($arThematics)){
			$arThematics = array();

			if(Loader::includeModule(ASPRO_MODULE_NAME)){
				if($obModule = CModule::CreateModuleObject(ASPRO_MODULE_NAME)){
					$moduleClassPreset = $obModule::moduleClassPreset;
					$arThematics = $moduleClassPreset::$arThematicsList;
				}
			}
		}

		return $arThematics;
	}

	function InitStep(){
		$this->SetStepID('select_thematic');
		$this->SetTitle(GetMessage('SELECT_THEMATIC_TITLE'));
		$this->SetSubTitle(GetMessage('SELECT_THEMATIC_SUBTITLE'));
		$this->SetPrevStep('select_template');
		$this->SetPrevCaption(GetMessage('PREVIOUS_BUTTON'));
		$this->SetNextStep('select_preset');
		$this->SetNextCaption(GetMessage('NEXT_BUTTON'));
		$this->setFromRequest();
	}

	function ShowStep(){
		if(!Loader::includeModule(ASPRO_MODULE_NAME)){
			$this->content .= '<p style="color:red">'.GetMessage('WIZ_NO_MODULE_').'</p>';
			?>
			<script type="text/javascript">
			$(document).ready(function() {
				$('.wizard-next-button').remove();
			});
			</script>
			<?
		}
		else{
			$wizard =& $this->GetWizard();
			$templateID = $wizard->GetVar('templateID');
			$siteID = $wizard->GetVar('siteID');
			if(empty($arThematics = self::getThematics())){
				return;
			}

			$thematicVarName = $templateID.'_thematicCODE';
			$defaultThematic = 'UNIVERSAL';
			$wizard->SetDefaultVar($thematicVarName, $defaultThematic);
			$thematic = COption::GetOptionString(ASPRO_MODULE_NAME, 'THEMATIC', $defaultThematic, $siteID);
			if(!strlen($thematic)){
				$thematic = $defaultThematic;
			}
			if(strlen($thematic) && array_key_exists($thematic, $arThematics)){
				$defaultThematic = $thematic;
				$wizard->SetDefaultVar($thematicVarName, $thematic);
			}

			$this->content .= '<div id="solutions-container" class="inst-template-list-block">';
			foreach($arThematics as $thematicCODE => $arThematic){
				$this->content .= '<div class="inst-template-description">';
				$this->content .= $this->ShowRadioField($thematicVarName, $thematicCODE, array('id' => $thematicCODE, 'class' => 'inst-template-list-inp'));
				//$this->content .= CFile::Show2Images($arThematic['PREVIEW_PICTURE'], $arThematic['DETAIL_PICTURE'], 150, 150, ' class="inst-template-list-img"');
				$this->content .= '<label for="'.$thematicCODE.'" class="inst-template-list-label">';
				$this->content .= CFile::ShowImage($arThematic['PREVIEW_PICTURE'], 150, 150, ' class="inst-template-list-img"');
				$this->content .= $arThematic['TITLE'].'<p>'.$arThematic['DESCRIPTION'].'</p>';
				$this->content .= '</label>';
				$this->content .= '</div>';
			}
			$this->content .= '</div>';
		}
	}

	function OnPostForm(){
		$wizard =& $this->GetWizard();
		if($wizard->IsNextButtonClick()){
			$templateID = $wizard->GetVar('templateID');
			$thematicVarName = $templateID.'_thematicCODE';
			$thematic = $wizard->GetVar($thematicVarName);
			$arThematics = self::getThematics();
			if(!array_key_exists($thematic, $arThematics)){
				$this->SetError(GetMessage('wiz_error_need_select_thematic'));
			}
		}
	}
}

class SelectPresetStep extends CWizardStep implements ISkipableWizardStep{
	static function getId() {
		return 'select_preset';
	}

	static function canSkip($wizard) {
		return false;
	}

	static function definePrevStep($wizard) {
		return 'select_thematic';
	}

	static function defineNextStep($wizard) {
		if (ExtModulesStep::canSkip($wizard)) {
			return ExtModulesStep::defineNextStep($wizard);
		}
		else {
			return ExtModulesStep::getId();
		}
	}

	function setFromRequest(){
		if(!isset($_REQUEST['CurrentStepID'])){
			if(strlen($preset = isset($_REQUEST['preset']) ? strval($_REQUEST['preset']) : false)){
				$templateID = $this->wizard->GetVar('templateID');
				$presetVarName = $templateID.'_presetID';
				$this->wizard->setVar($presetVarName, $preset);
			}

			if($preset){
				$_REQUEST[$this->wizard->nextButtonID] = $_REQUEST[$this->wizard->nextStepHiddenID] = 1;
				$this->OnPostForm();
				unset($_REQUEST[$this->wizard->nextButtonID], $_REQUEST[$this->wizard->nextStepHiddenID]);
				if(!count($this->stepErrors)){
					if(!$this->wizard->firstStepID || $this->wizard->firstStepID == $this->stepID){
						$this->wizard->firstStepID = $this->nextStepID;
					}
				}
			}
		}
	}

	static function getPresets(){
		static $arPresets;

		if(!isset($arPresets)){
			$arPresets = array();

			if(Loader::includeModule(ASPRO_MODULE_NAME)){
				if($obModule = CModule::CreateModuleObject(ASPRO_MODULE_NAME)){
					$moduleClassPreset = $obModule::moduleClassPreset;
					$arPresets = $moduleClassPreset::$arPresetsList;
				}
			}
		}

		return $arPresets;
	}

	function InitStep(){
		$wizard =& $this->GetWizard();

		$this->SetStepID('select_preset');
		$this->SetTitle(GetMessage('SELECT_PRESET_TITLE'));
		$this->SetSubTitle(GetMessage('SELECT_PRESET_SUBTITLE'));

		$this->SetPrevCaption(GetMessage('PREVIOUS_BUTTON'));
		if ($prevStep = static::definePrevStep($wizard)) {
			$this->SetPrevStep($prevStep);
		}

		$this->SetNextCaption(GetMessage('NEXT_BUTTON'));
		if ($nextStep = static::defineNextStep($wizard)) {
			$this->SetNextStep($nextStep);
		}

		$this->setFromRequest();
	}

	function ShowStep(){
		if(!Loader::includeModule(ASPRO_MODULE_NAME)){
			$this->content .= '<p style="color:red">'.GetMessage('WIZ_NO_MODULE_').'</p>';
			?>
			<script type="text/javascript">
			$(document).ready(function() {
				$('.wizard-next-button').remove();
			});
			</script>
			<?
		}
		else{
			$wizard =& $this->GetWizard();
			$templateID = $wizard->GetVar('templateID');
			$siteID = $wizard->GetVar('siteID');
			$thematicVarName = $templateID.'_thematicCODE';
			$thematic = strtoupper($wizard->GetVar($thematicVarName));
			$arThematics = SelectThematicStep::getThematics();
			$arPresets = self::getPresets();
			if(empty($arThematics) || empty($arPresets) || empty($arThematics[$thematic]['PRESETS']['LIST'])){
				return;
			}

			$presetVarName = $templateID.'_presetID';
			$defaultPreset = $arThematics[$thematic]['PRESETS']['DEFAULT'];
			$wizard->SetDefaultVar($presetVarName, $defaultPreset);
			$preset = $defaultPreset;

			$this->content .= '<div id="solutions-container" class="inst-template-list-block">';
			foreach($arThematics[$thematic]['PRESETS']['LIST'] as $presetID){
				if($arPreset = $arPresets[$presetID]){
					$this->content .= '<div class="inst-template-description">';
					$this->content .= $this->ShowRadioField($presetVarName, $presetID, array('id' => $presetID, 'class' => 'inst-template-list-inp'));
					$this->content .= CFile::Show2Images($arPreset['PREVIEW_PICTURE'], $arPreset['DETAIL_PICTURE'], 150, 150, ' class="inst-template-list-img"');
					//$this->content .= CFile::ShowImage($arPreset['PREVIEW_PICTURE'], 150, 150, ' class="inst-template-list-img"');
					$this->content .= '<label for="'.$presetID.'" class="inst-template-list-label">'.$arPreset['TITLE'].'<p>'.$arPreset['DESCRIPTION'].'</p></label>';
					$this->content .= '</div>';
				}
			}
			$this->content .= '</div>';
		}
	}

	function OnPostForm(){
		$wizard =& $this->GetWizard();
		if($wizard->IsNextButtonClick()){
			$templateID = $wizard->GetVar('templateID');
			$thematicVarName = $templateID.'_thematicCODE';
			$thematic = strtoupper($wizard->GetVar($thematicVarName));
			$arThematics = SelectThematicStep::getThematics();
			$presetVarName = $templateID.'_presetID';
			$preset = $wizard->GetVar($presetVarName);
			$arPresets = self::getPresets();

			if(!array_key_exists($preset, $arPresets) || !in_array($preset, $arThematics[$thematic]['PRESETS']['LIST'])){
				$this->SetError(GetMessage('wiz_error_need_select_preset'));
			}
		}
	}
}


class ExtModulesStep extends CWizardStep implements ISkipableWizardStep {
	static function getId() {
		return 'ext_modules';
	}

	static function canSkip($wizard) {
		$arExtModules = [
			'aspro.smartseo',
			'aspro.popup',
		];
		foreach ($arExtModules as $moduleId) {
			if (
				!Loader::includeModule($moduleId)
			) {
				return false;
			}
		}

		return true;
	}

	static function definePrevStep($wizard) {
		if (SelectPresetStep::canSkip($wizard)) {
			return SelectPresetStep::definePrevStep($wizard);
		}
		else {
			return SelectPresetStep::getId();
		}
	}

	static function defineNextStep($wizard) {
		return 'site_settings';
	}

	function InitStep() {
		$wizard =& $this->GetWizard();

		$this->SetStepID(static::getId());
		$this->SetTitle(GetMessage('WIZ_STEP_EXT_MODULES'));

		$this->SetPrevCaption(GetMessage('PREVIOUS_BUTTON'));
		if ($prevStep = static::definePrevStep($wizard)) {
			$this->SetPrevStep($prevStep);
		}

		$this->SetNextCaption(GetMessage('NEXT_BUTTON'));
		if ($nextStep = static::defineNextStep($wizard)) {
			$this->SetNextStep($nextStep);
		}
	}

	function ShowStep() {
		if (!Loader::includeModule(ASPRO_MODULE_NAME)) {
			$this->content .= '<p style="color:red">'.GetMessage('WIZ_NO_MODULE_').'</p>';
			?>
			<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
				let btns = document.querySelectorAll('.wizard-next-button');
				if (btns.length) {
					btns.forEach (function(btn) {
						btn.remove();
					});
				}
			});
			</script>
			<?
		}
		else {
			$wizard =& $this->GetWizard();
			$templateID = $wizard->GetVar('templateID');
			$themeVarName = $templateID.'_thematicCODE';
			$thematic = $wizard->GetVar($themeVarName);
			$presetVarName = $templateID.'_presetID';
			$preset = $wizard->GetVar($presetVarName);

			$bSmartSeoInstalled = Loader::includeModule('aspro.smartseo');
			$bPopupInstalled = Loader::includeModule('aspro.popup');

			$wizard->SetDefaultVars(
				array(
					'installSmartSeo' => 'Y',
					'installPopup' => 'Y',
				)
			);

			// step bg
			$this->content .='<script type="text/javascript">document.addEventListener(\'DOMContentLoaded\', function() {setWizardBackgroundColor("'.$preset.'");});</script>';

			$this->content .= '<div class="wizard-input-form">';

			if (!$bSmartSeoInstalled) {
				$this->content .= '
					<div class="wizard-input-form-block">
						<div class="wizard-input-form-field wizard-input-form-field-checkbox">
							<div class="wizard-catalog-form-item">
								'.$this->ShowCheckboxField('installSmartSeo', 'Y', (array('id' => 'installSmartSeoID'))).'
								<label for="installSmartSeoID">'.GetMessage('WIZ_EXT_MODULE_SMART_SEO_INSTALL').'</label>
							</div>';
							$this->content .=
						'</div>
						<div class="wizard-catalog-form-item" style="font-size: 14px;">'.GetMessage('WIZ_EXT_MODULE_SMARTSEO_DESC').'</div>
					</div>';
			}

			if (!$bPopupInstalled) {
				$this->content .= '
					<div class="wizard-input-form-block">
						<div class="wizard-input-form-field wizard-input-form-field-checkbox">
							<div class="wizard-catalog-form-item">
								'.$this->ShowCheckboxField('installPopup', 'Y', (array('id' => 'installPopupID'))).'
								<label for="installPopupID">'.GetMessage('WIZ_EXT_MODULE_POPUP_INSTALL').'</label>
							</div>';
							$this->content .=
						'</div>
						<div class="wizard-catalog-form-item" style="font-size: 14px;">'.GetMessage('WIZ_EXT_MODULE_POPUP_DESC').'</div>
					</div>';
			}

			$this->content .= '</div>';
		}
	}

	function OnPostForm() {
	}
}

class SiteSettingsStep extends CSiteSettingsWizardStep implements ISkipableWizardStep{
	static function getId() {
		return 'site_settings';
	}

	static function canSkip($wizard) {
		return !Loader::includeModule("sale");
	}

	static function definePrevStep($wizard) {
		if (ExtModulesStep::canSkip($wizard)) {
			return ExtModulesStep::definePrevStep($wizard);
		}
		else {
			return ExtModulesStep::getId();
		}
	}

	static function defineNextStep($wizard) {
		return LANGUAGE_ID !== 'ru' ? 'pay_system' : 'person_type';
	}

	function InitStep(){
		if(Loader::includeModule(ASPRO_MODULE_NAME)){
			parent::InitStep();

			$wizard =& $this->GetWizard();
			$wizard->solutionName = ASPRO_MODULE_NAME_SHORT;

			$this->SetTitle(GetMessage('WIZ_STEP_SITE_SET'));

			$this->SetPrevCaption(GetMessage('PREVIOUS_BUTTON'));
			if ($prevStep = static::definePrevStep($wizard)) {
				$this->SetPrevStep($prevStep);
			}

			$this->SetNextStep(LANGUAGE_ID !== 'ru' ? 'pay_system' : 'person_type');
			$this->SetNextCaption(GetMessage('NEXT_BUTTON'));
		}
	}

	function ShowStep(){
		if(!Loader::includeModule(ASPRO_MODULE_NAME)){
			$this->content .= '<p style="color:red">'.GetMessage('WIZ_NO_MODULE_').'</p>';
			?>
			<script type="text/javascript">
			$(document).ready(function() {
				$('.wizard-next-button').remove();
			});
			</script>
			<?
		}
		else{
			$wizard =& $this->GetWizard();
			$templateID = $wizard->GetVar('templateID');
			$themeVarName = $templateID.'_thematicCODE';
			$thematic = $wizard->GetVar($themeVarName);
			$presetVarName = $templateID.'_presetID';
			$preset = $wizard->GetVar($presetVarName);

			$siteID = $wizard->GetVar('siteID');
			$obSite = new CSite;
			$arSite = $obSite->GetByID($siteID)->Fetch();
			$arCurSiteTheme = CMax::GetBackParametrsValues($siteID);

			$phone = '';
			if($arCurSiteTheme['HEADER_PHONES']){
				$arPhone = array();
				for($i = 0; $i < $arCurSiteTheme['HEADER_PHONES']; ++$i){
					$arPhone[] =  $arCurSiteTheme['HEADER_PHONES_array_PHONE_VALUE_'.$i];
				}
				$phone = implode(',', $arPhone);
			}

			$shopVk = $arCurSiteTheme['SOCIAL_VK'];
			$shopFacebook = $arCurSiteTheme['SOCIAL_FACEBOOK'];
			$shopTwitter = $arCurSiteTheme['SOCIAL_TWITTER'];
			$shopInstagram = $arCurSiteTheme['SOCIAL_INSTAGRAM'];
			$shopTelegram = $arCurSiteTheme['SOCIAL_TELEGRAM'];
			$shopYoutube = $arCurSiteTheme['SOCIAL_YOUTUBE'];
			$shopRutube = $arCurSiteTheme["SOCIAL_RUTUBE"];
			$shopOdnoklassniki = $arCurSiteTheme['SOCIAL_ODNOKLASSNIKI'];
			//$shopGooglePlus = $arCurSiteTheme['SOCIAL_GOOGLEPLUS'];
			$shopMailRu = $arCurSiteTheme['SOCIAL_MAIL'];

			$shopViber = $arCurSiteTheme["SOCIAL_VIBER"];
			$shopWhats = $arCurSiteTheme["SOCIAL_WHATS"];
			$shopTikTok = $arCurSiteTheme["SOCIAL_TIKTOK"];
			$shopZen = $arCurSiteTheme["SOCIAL_ZEN"];
			$shopPinterest = $arCurSiteTheme["SOCIAL_PINTEREST"];
			$shopSnapchat = $arCurSiteTheme["SOCIAL_SNAPCHAT"];
			$shopLinkedin = $arCurSiteTheme["SOCIAL_LINKEDIN"];

			$wizard->SetDefaultVars(
				array(
					'siteLogoSet' => false,
					'siteNameSet' => true,
					'siteName' => (strlen($arSite['SITE_NAME']) ? $arSite['SITE_NAME'] : (strlen($arSite['NAME']) ? $arSite['NAME'] : GetMessage('WIZ_COMPANY_NAME_DEF'))),
					'siteTelephone' => ($phone ? $phone : GetMessage('WIZ_COMPANY_TELEPHONE_DEF')),
					'siteCopy' => GetMessage('WIZ_COMPANY_COPY_DEF'),
					'siteEmail' => strip_tags($this->GetFileContent(WIZARD_SITE_PATH.'include/footer/site-email.php', GetMessage('WIZ_COMPANY_EMAIL_DEF'))),
					'siteAddress' => $this->GetFileContent(WIZARD_SITE_PATH.'include/top_page/site-address.php', GetMessage('WIZ_COMPANY_ADDRESS_DEF')),
					'siteSchedule' => $this-> GetFileContent(WIZARD_SITE_PATH.'include/contacts-site-schedule.php', GetMessage('WIZ_COMPANY_SCHEDULE_DEF')),
					'shopVk' => (strlen($shopVk) ? $shopVk : GetMessage('WIZ_SHOP_VK_DEF')),
					'shopTwitter' => (strlen($shopTwitter) ? $shopTwitter : GetMessage('WIZ_SHOP_TWITTER_DEF')),
					'shopFacebook' => (strlen($shopFacebook) ? $shopFacebook : GetMessage('WIZ_SHOP_FACEBOOK_DEF')),
					'shopInstagram' => (strlen($shopInstagram) ? $shopInstagram : GetMessage('WIZ_SHOP_INSTAGRAM_DEF')),
					'shopTelegram' => (strlen($shopTelegram) ? $shopTelegram : GetMessage('WIZ_SHOP_TELEGRAM_DEF')),
					'shopYoutube' => (strlen($shopYoutube) ? $shopYoutube : GetMessage('WIZ_SHOP_YOUTUBE_DEF')),
					'shopRutube' => (strlen($shopRutube) ? $shopRutube : GetMessage('WIZ_SHOP_RUTUBE_DEF')),
					'shopOdnoklassniki' => (strlen($shopOdnoklassniki) ? $shopOdnoklassniki : GetMessage('WIZ_SHOP_ODNOKLASSNIKI_DEF')),
					//'shopGooglePlus' => (strlen($shopGooglePlus) ? $shopGooglePlus : GetMessage('WIZ_SHOP_GOOGLE_DEF')),
					//'shopLiveJournal' => (strlen($shopLiveJournal) ? $shopLiveJournal : GetMessage('WIZ_SHOP_LIVEJOURNAL_DEF')),
					'shopMailRu' => (strlen($shopMailRu) ? $shopMailRu : GetMessage('WIZ_SHOP_MAILRU_DEF')),
					"shopViber" => (strlen($shopViber) ? $shopViber : GetMessage("WIZ_SHOP_VIBER_DEF")),
					"shopWhats" => (strlen($shopWhats) ? $shopWhats : GetMessage("WIZ_SHOP_WHATS_DEF")),
					"shopTikTok" => (strlen($shopTikTok) ? $shopTikTok : GetMessage("WIZ_SHOP_TIKTOK_DEF")),
					"shopZen" => (strlen($shopZen) ? $shopZen : GetMessage("WIZ_SHOP_ZEN_DEF")),
					"shopPinterest" => (strlen($shopPinterest) ? $shopPinterest : GetMessage("WIZ_SHOP_PINTEREST_DEF")),
					"shopSnapchat" => (strlen($shopSnapchat) ? $shopSnapchat : GetMessage("WIZ_SHOP_SNAPCHAT_DEF")),
					"shopLinkedin" => (strlen($shopLinkedin) ? $shopLinkedin : GetMessage("WIZ_SHOP_LINKEDIN_DEF")),
					'siteMetaDescription' => GetMessage('wiz_site_desc'),
					'siteMetaKeywords' => GetMessage('wiz_keywords'),
					'shopLocalization' => COption::GetOptionString(ASPRO_MODULE_NAME, 'shopLocalization', 'ru', $siteID),
				)
			);

			// step bg
			$this->content .='<script type="text/javascript">$(document).ready(function(){setWizardBackgroundColor("'.$preset.'");});</script>';

			$this->content .= '<div class="wizard-input-form">';

			// site name
			if($wizard->GetVar('siteNameSet', true)){
				$this->content .= '
				<div class="wizard-input-form-block">
					<label for="siteName" class="wizard-input-title">'.GetMessage('WIZ_COMPANY_NAME').'</label><br />'
					.$this->ShowInputField('text', 'siteName', array('class' => 'wizard-field', 'id' => 'siteName')).'
				</div>';
			}

			// logo
			if($wizard->GetVar('siteLogoSet', true)){
				$siteLogo = $wizard->GetVar('siteLogo', true);
				$this->content .= '
				<div class="wizard-input-form-block">
					<label for="siteLogo" class="wizard-input-title">'.GetMessage('WIZ_COMPANY_LOGO').'</label><br />'
					.CFile::ShowImage($siteLogo, 193, 43, 'border=0 vspace=15').'<br>'.
					$this->ShowFileField('siteLogo', array('show_file_info' => 'N', 'id' => 'siteLogo')).'
				</div>';
			}

			// copyright
			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="siteCopy" class="wizard-input-title">'.GetMessage('WIZ_COMPANY_COPY').'</label><br />'
				.$this->ShowInputField('textarea', 'siteCopy', array('class' => 'wizard-field', 'rows' => '3', 'id' => 'siteCopy')).'
				<span style="display:inline-block;font-size:12px;margin-top:5px;vertical-align:top;">'.GetMessage('WIZ_COMPANY_COPY_NOTE').'</span>
			</div>';

			// phone
			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="siteTelephone" class="wizard-input-title">'.GetMessage('WIZ_COMPANY_TELEPHONE').'</label><br />'
				.$this->ShowInputField('text', 'siteTelephone', array('class' => 'wizard-field', 'id' => 'siteTelephone')).'
				<span style="display:inline-block;font-size:12px;margin-top:5px;vertical-align:top;">'.GetMessage('WIZ_COMPANY_PHONE_NOTE').'</span>
			</div>';

			// email
			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="siteEmail" class="wizard-input-title">'.GetMessage('WIZ_COMPANY_EMAIL').'</label><br />'
				.$this->ShowInputField('textarea', 'siteEmail', array('class' => 'wizard-field', 'id' => 'siteEmail')).'
			</div>';

			// skype
			/*$this->content .= '
			<div class="wizard-input-form-block">
				<label for="siteSkype" class="wizard-input-title">'.GetMessage('WIZ_COMPANY_SKYPE').'</label><br />'
				.$this->ShowInputField('textarea', 'siteSkype', array('class' => 'wizard-field', 'id' => 'siteSkype')).'
			</div>';*/

			// address
			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="siteAddress" class="wizard-input-title">'.GetMessage('WIZ_COMPANY_ADDRESS').'</label><br />'
				.$this->ShowInputField('textarea', 'siteAddress', array('class' => 'wizard-field', 'id' => 'siteAddress')).'
			</div>';

			// schedule
			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="siteSchedule" class="wizard-input-title">'.GetMessage('WIZ_COMPANY_SCHEDULE').'</label><br />'
				.$this->ShowInputField('textarea', 'siteSchedule', array('class' => 'wizard-field', 'id' => 'siteSchedule')).'
			</div>';

			// vk
			if(LANGUAGE_ID === 'ru'){
				$this->content .= '
				<div class="wizard-input-form-block">
					<label for="shopVk" class="wizard-input-title">'.GetMessage('WIZ_SHOP_VK').'</label><br />'
					.$this->ShowInputField('text', 'shopVk', array('class' => 'wizard-field', 'id' => 'shopVk')).'
				</div>';
			}

			// facebook
			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopFacebook" class="wizard-input-title">'.GetMessage('WIZ_SHOP_FACEBOOK').'</label><br />'
				.$this->ShowInputField('text', 'shopFacebook', array('class' => 'wizard-field', 'id' => 'shopFacebook')).'
			</div>';

			// twitter
			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopTwitter" class="wizard-input-title">'.GetMessage('WIZ_SHOP_TWITTER').'</label><br />'
				.$this->ShowInputField('text', 'shopTwitter', array('class' => 'wizard-field', 'id' => 'shopTwitter')).'
			</div>';

			// instagram
			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopInstagram" class="wizard-input-title">'.GetMessage('WIZ_SHOP_INSTAGRAM').'</label><br />'
				.$this->ShowInputField('text', 'shopInstagram', array('class' => 'wizard-field', 'id' => 'shopInstagram')).'
			</div>';

			// telegram
			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopTelegram" class="wizard-input-title">'.GetMessage('WIZ_SHOP_TELEGRAM').'</label><br />'
				.$this->ShowInputField('text', 'shopTelegram', array('class' => 'wizard-field', 'id' => 'shopTelegram')).'
			</div>';

			// youtube
			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopYouTube" class="wizard-input-title">'.GetMessage('WIZ_SHOP_YOUTUBE').'</label><br />'
				.$this->ShowInputField('text', 'shopYoutube', array('class' => 'wizard-field', 'id' => 'shopYoutube')).'
			</div>';

			// rutube
			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopRutube" class="wizard-input-title">'.GetMessage('WIZ_SHOP_RUTUBE').'</label><br />'
				.$this->ShowInputField('text', 'shopRutube', array('class' => 'wizard-field', 'id' => 'shopRutube')).'
			</div>';

			// google+
			// $this->content .= '
			// <div class="wizard-input-form-block">
			// 	<label for="shopGooglePlus" class="wizard-input-title">'.GetMessage('WIZ_SHOP_GOOGLE').'</label><br />'
			// 	.$this->ShowInputField('text', 'shopGooglePlus', array('class' => 'wizard-field', 'id' => 'shopGooglePlus')).'
			// </div>';

			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopViber" class="wizard-input-title">'.GetMessage("WIZ_SHOP_VIBER").'</label><br />'
				.$this->ShowInputField('text', 'shopViber', array("class"=>"wizard-field", "id" => "shopViber")).'
			</div>';

			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopWhats" class="wizard-input-title">'.GetMessage("WIZ_SHOP_WHATS").'</label><br />'
				.$this->ShowInputField('text', 'shopWhats', array("class"=>"wizard-field", "id" => "shopWhats")).'
			</div>';

			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopTikTok" class="wizard-input-title">'.GetMessage("WIZ_SHOP_TIKTOK").'</label><br />'
				.$this->ShowInputField('text', 'shopTikTok', array("class"=>"wizard-field", "id" => "shopTikTok")).'
			</div>';

			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopZen" class="wizard-input-title">'.GetMessage("WIZ_SHOP_ZEN").'</label><br />'
				.$this->ShowInputField('text', 'shopZen', array("class"=>"wizard-field", "id" => "shopZen")).'
			</div>';

			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopPinterest" class="wizard-input-title">'.GetMessage("WIZ_SHOP_PINTEREST").'</label><br />'
				.$this->ShowInputField('text', 'shopPinterest', array("class"=>"wizard-field", "id" => "shopPinterest")).'
			</div>';

			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopSnapchat" class="wizard-input-title">'.GetMessage("WIZ_SHOP_SNAPCHAT").'</label><br />'
				.$this->ShowInputField('text', 'shopSnapchat', array("class"=>"wizard-field", "id" => "shopSnapchat")).'
			</div>';

			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopLinkedin" class="wizard-input-title">'.GetMessage("WIZ_SHOP_LINKEDIN").'</label><br />'
				.$this->ShowInputField('text', 'shopLinkedin', array("class"=>"wizard-field", "id" => "shopLinkedin")).'
			</div>';

			// live journal
			// $this->content .= '
			// <div class="wizard-input-form-block">
			// 	<label for="shopLiveJournal" class="wizard-input-title">'.GetMessage('WIZ_SHOP_LIVEJOURNAL').'</label><br />'
			// 	.$this->ShowInputField('text', 'shopLiveJournal', array('class' => 'wizard-field', 'id' => 'shopLiveJournal')).'
			// </div>';

			if(LANGUAGE_ID === 'ru'){
				// ok
				$this->content .= '
				<div class="wizard-input-form-block">
					<label for="shopOdnoklassniki" class="wizard-input-title">'.GetMessage('WIZ_SHOP_ODNOKLASSNIKI').'</label><br />'
					.$this->ShowInputField('text', 'shopOdnoklassniki', array('class' => 'wizard-field', 'id' => 'shopOdnoklassniki')).'
				</div>';

				// mailru
				$this->content .= '
				<div class="wizard-input-form-block">
					<label for="shopMailRu" class="wizard-input-title">'.GetMessage('WIZ_SHOP_MAILRU').'</label><br />'
					.$this->ShowInputField('text', 'shopMailRu', array('class' => 'wizard-field', 'id' => 'shopMailRu')).'
				</div>';
			}

			// meta
			$this->content .= '
				<div  id="bx_metadata" '.$styleMeta.'>
					<div class="wizard-input-form-block">
						<div class="wizard-metadata-title">'.GetMessage('wiz_meta_data').'</div>
						<label for="siteMetaDescription" class="wizard-input-title">'.GetMessage('wiz_meta_description').'</label>
						'.$this->ShowInputField('textarea', 'siteMetaDescription', array('class' => 'wizard-field', 'id' => 'siteMetaDescription', 'style' => 'width:100%', 'rows' => '3')).'
					</div>
					<div class="wizard-input-form-block">
						<label for="siteMetaKeywords" class="wizard-input-title">'.GetMessage('wiz_meta_keywords').'</label><br>
						'.$this->ShowInputField('text', 'siteMetaKeywords', array('class' => 'wizard-field', 'id' => 'siteMetaKeywords')).'
					</div>
				</div>';

			if(LANGUAGE_ID !== 'ru'){
				if(Loader::includeModule('catalog')){
					$db_res = CCatalogGroup::GetGroupsList(array('CATALOG_GROUP_ID' => '1', 'BUY' =>'Y', 'GROUP_ID' => 2));
					if(!$db_res->Fetch()){
						$this->content .= '
						<div class="wizard-input-form-block">
							<label for="shopAdr">'.GetMessage('WIZ_SHOP_PRICE_BASE_TITLE').'</label>
							<div class="wizard-input-form-block-content">
								'. GetMessage('WIZ_SHOP_PRICE_BASE_TEXT1') .'<br><br>
								'. $this->ShowCheckboxField('installPriceBASE', 'Y', (array('id' => 'install-price_base')))
								. ' <label for="install-price_base">'.GetMessage('WIZ_SHOP_PRICE_BASE_TEXT2').'</label><br />
							</div>
						</div>';
					}
				}
			}

			$this->content .= $this->ShowHiddenField('installDemoData', 'Y');

			$this->content .= '</div>'; // div class="wizard-input-form"
		}
	}

	function OnPostForm(){
		$wizard =& $this->GetWizard();
		if($wizard->IsNextButtonClick()){
			$res = $this->SaveFile('siteLogo', array('extensions' => 'gif,jpg,jpeg,png', 'max_height' => 43, 'max_width' => 193, 'make_preview' => 'Y'));
			if(file_exists(WIZARD_SITE_PATH.'include/logo.jpg')){
				$wizard->SetVar('siteLogoSet', true);
			}
		}
	}
}

class ShopSettings extends CWizardStep{
	function InitStep(){
		$this->SetStepID("shop_settings");
		$this->SetTitle(GetMessage("WIZ_STEP_SS"));
		$this->SetNextStep("person_type");
		$this->SetPrevStep("site_settings");
		$this->SetNextCaption(GetMessage("NEXT_BUTTON"));
		$this->SetPrevCaption(GetMessage("PREVIOUS_BUTTON"));

		$wizard =& $this->GetWizard();
		$siteStamp =$wizard->GetPath()."/site/templates/minimal/images/pechat.gif";
		$siteID = $wizard->GetVar("siteID");

		$wizard->SetDefaultVars(
			Array(
				"shopLocalization" => COption::GetOptionString("aspro.max", "shopLocalization", "ru", $siteID),
				"shopEmail" => COption::GetOptionString("aspro.max", "shopEmail", $wizard->GetVar("siteEmail"), $siteID),
				"shopOfName" => COption::GetOptionString("aspro.max", "shopOfName", GetMessage("WIZ_SHOP_OF_NAME_DEF"), $siteID),
				"shopLocation" => COption::GetOptionString("aspro.max", "shopLocation", GetMessage("WIZ_SHOP_LOCATION_DEF"), $siteID),
				//"shopZip" => 101000,
				"shopAdr" => COption::GetOptionString("aspro.max", "shopAdr", GetMessage("WIZ_SHOP_ADR_DEF"), $siteID),
				"shopINN" => COption::GetOptionString("aspro.max", "shopINN", "1234567890", $siteID),
				"shopKPP" => COption::GetOptionString("aspro.max", "shopKPP", "123456789", $siteID),
				"shopNS" => COption::GetOptionString("aspro.max", "shopNS", "0000 0000 0000 0000 0000", $siteID),
				"shopBANK" => COption::GetOptionString("aspro.max", "shopBANK", GetMessage("WIZ_SHOP_BANK_DEF"), $siteID),
				"shopBANKREKV" => COption::GetOptionString("aspro.max", "shopBANKREKV", GetMessage("WIZ_SHOP_BANKREKV_DEF"), $siteID),
				"shopKS" => COption::GetOptionString("aspro.max", "shopKS", "30101 810 4 0000 0000225", $siteID),
				"siteStamp" => COption::GetOptionString("aspro.max", "siteStamp", $siteStamp, $siteID),

				//"shopCompany_ua" => COption::GetOptionString("aspro.max", "shopCompany_ua", "", $siteID),
				"shopOfName_ua" => COption::GetOptionString("aspro.max", "shopOfName_ua", GetMessage("WIZ_SHOP_OF_NAME_DEF_UA"), $siteID),
				"shopLocation_ua" => COption::GetOptionString("aspro.max", "shopLocation_ua", GetMessage("WIZ_SHOP_LOCATION_DEF_UA"), $siteID),
				"shopAdr_ua" => COption::GetOptionString("aspro.max", "shopAdr_ua", GetMessage("WIZ_SHOP_ADR_DEF_UA"), $siteID),
				"shopEGRPU_ua" =>  COption::GetOptionString("aspro.max", "shopCompany_ua", "", $siteID),
				"shopINN_ua" =>  COption::GetOptionString("aspro.max", "shopINN_ua", "", $siteID),
				"shopNDS_ua" =>  COption::GetOptionString("aspro.max", "shopNDS_ua", "", $siteID),
				"shopNS_ua" =>  COption::GetOptionString("aspro.max", "shopNS_ua", "", $siteID),
				"shopBank_ua" =>  COption::GetOptionString("aspro.max", "shopBank_ua", "", $siteID),
				"shopMFO_ua" =>  COption::GetOptionString("aspro.max", "shopMFO_ua", "", $siteID),
				"shopPlace_ua" =>  COption::GetOptionString("aspro.max", "shopPlace_ua", "", $siteID),
				"shopFIO_ua" =>  COption::GetOptionString("aspro.max", "shopFIO_ua", "", $siteID),
				"shopTax_ua" =>  COption::GetOptionString("aspro.max", "shopTax_ua", "", $siteID),

				"installPriceBASE" => COption::GetOptionString("aspro.max", "installPriceBASE", "Y", $siteID),
			)
		);
	}

	function ShowStep(){
		$wizard =& $this->GetWizard();
		$siteStamp = $wizard->GetVar("siteStamp", true);
		$templateID = $wizard->GetVar("templateID");
		$ThemeID = $wizard->GetVar($templateID."_themeID");
		$this->content .='<script type="text/javascript">$(document).ready(function(){setWizardBackgroundColor("'.$ThemeID.'");});</script>';

		if(!Loader::includeModule("catalog")){
			$this->content .= "<p style='color:red'>".GetMessage("WIZ_NO_MODULE_CATALOG")."</p>";
			$this->SetNextStep("shop_settings");
		}
		else{
			/*$this->content .=
				$this->ShowSelectField("shopLocalization", array("ru" => GetMessage("WIZ_SHOP_LOCALIZATION_RUSSIA"), "ua" => GetMessage("WIZ_SHOP_LOCALIZATION_UKRAINE")), array("onchange" => "langReload()", "id" => "localization_select"))
				.' <label for="shopLocalization">'.GetMessage("WIZ_SHOP_LOCALIZATION").'</label><br />';*/

			$this->content .= '<div class="wizard-input-form">';

			$this->content .= '<div class="wizard-input-form-block">
				<label for="shopOfName" class="wizard-input-title">'.GetMessage("WIZ_SHOP_OF_NAME").'</label><br />
				'.$this->ShowInputField('text', 'shopOfName', array("class" => "wizard-field", "id" => "shopOfName")).'
			</div>';

			$this->content .= '<div class="wizard-input-form-block">
				<label for="shopEmail" class="wizard-input-title">'.GetMessage("WIZ_SHOP_EMAIL").'</label><br />
				'.$this->ShowInputField('text', 'shopEmail', array("class" => "wizard-field", "id" => "shopEmail")).'
			</div>';

			$this->content .= '<div class="wizard-input-form-block">
				<label for="shopLocation" class="wizard-input-title">'.GetMessage("WIZ_SHOP_LOCATION").'</label><br />';

			$this->content .= $this->ShowInputField('text', 'shopLocation', array("class" => "wizard-field", "id" => "shopLocation"));
			$this->content .= '</div>';

			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="shopAdr" class="wizard-input-title">'.GetMessage("WIZ_SHOP_ADR").'</label><br />
				'.$this->ShowInputField('textarea', 'shopAdr', array("class" => "wizard-field", "rows"=>"3", "id" => "shopAdr")).'
			</div>';


			$currentLocalization = $wizard->GetVar("shopLocalization");
			if (empty($currentLocalization))
				$currentLocalization = $wizard->GetDefaultVar("shopLocalization");
	 //ru
			/*$this->content .= '
			<div id="ru_bank_details" class="wizard-input-form-block" >
				<div class="wizard-catalog-title">'.GetMessage("WIZ_SHOP_BANK_TITLE").'</div>
				<table  class="wizard-input-table" >
					<tr>
						<td class="wizard-input-table-left">'.GetMessage("WIZ_SHOP_INN").':</td>
						<td class="wizard-input-table-right">'.$this->ShowInputField('text', 'shopINN', array('class' => 'wizard-field')).'</td>
					</tr>
					<tr>
						<td class="wizard-input-table-left">'.GetMessage("WIZ_SHOP_KPP").':</td>
						<td class="wizard-input-table-right">'.$this->ShowInputField('text', 'shopKPP', array('class' => 'wizard-field')).'</td>
					</tr>
					<tr>
						<td class="wizard-input-table-left">'.GetMessage("WIZ_SHOP_NS").':</td>
						<td class="wizard-input-table-right">'.$this->ShowInputField('text', 'shopNS', array('class' => 'wizard-field')).'</td>
					</tr>
					<tr>
						<td class="wizard-input-table-left">'.GetMessage("WIZ_SHOP_BANK").':</td>
						<td class="wizard-input-table-right">'.$this->ShowInputField('text', 'shopBANK', array('class' => 'wizard-field')).'</td>
					</tr>
					<tr>
						<td class="wizard-input-table-left">'.GetMessage("WIZ_SHOP_BANKREKV").':</td>
						<td class="wizard-input-table-right">'.$this->ShowInputField('text', 'shopBANKREKV', array('class' => 'wizard-field')).'</td>
					</tr>
					<tr>
						<td class="wizard-input-table-left">'.GetMessage("WIZ_SHOP_KS").':</td>
						<td class="wizard-input-table-right">'.$this->ShowInputField('text', 'shopKS', array('class' => 'wizard-field')).'</td>
					</tr>
					<tr>
						<td class="wizard-input-table-left">'.GetMessage("WIZ_SHOP_STAMP").':</td>
						<td class="wizard-input-table-right">'.$this->ShowFileField("siteStamp", Array("show_file_info"=> "N", "id" => "siteStamp")).'<br />'.CFile::ShowImage($siteStamp, 75, 75, "border=0 vspace=5", false, false).'</td>
						</tr>
				</table>
			</div>
			';*/

			if (Loader::includeModule("catalog")){
				$db_res = CCatalogGroup::GetGroupsList(array("CATALOG_GROUP_ID"=>'1', "BUY"=>"Y", "GROUP_ID"=>2));
				if (!$db_res->Fetch()){
					$this->content .= '
					<div class="wizard-input-form-block">
						<label for="shopAdr">'.GetMessage("WIZ_SHOP_PRICE_BASE_TITLE").'</label>
						<div class="wizard-input-form-block-content">
							'. GetMessage("WIZ_SHOP_PRICE_BASE_TEXT1") .'<br><br>
							'. $this->ShowCheckboxField("installPriceBASE", "Y",
							(array("id" => "install-demo-data")))
							. ' <label for="install-demo-data">'.GetMessage("WIZ_SHOP_PRICE_BASE_TEXT2").'</label><br />

						</div>
					</div>';
				}
			}

			$this->content .= '</div>';
		}
	}

	function OnPostForm(){
		$wizard =& $this->GetWizard();
		$res = $this->SaveFile("siteStamp", Array("extensions" => "gif,jpg,jpeg,png", "max_height" => 70, "max_width" => 190, "make_preview" => "Y"));
	}

}

class PersonType extends CWizardStep{
	function InitStep(){
		$this->SetStepID('person_type');
		$this->SetTitle(GetMessage('WIZ_STEP_PT'));
		$this->SetPrevStep('site_settings');
		$this->SetPrevCaption(GetMessage('PREVIOUS_BUTTON'));
		$this->SetNextStep('pay_system');
		$this->SetNextCaption(GetMessage('NEXT_BUTTON'));
	}

	function ShowStep(){
		if(!Loader::includeModule(ASPRO_MODULE_NAME)){
			$this->content .= '<p style="color:red">'.GetMessage('WIZ_NO_MODULE_').'</p>';
			?>
			<script type="text/javascript">
			$(document).ready(function() {
				$('.wizard-next-button').remove();
			});
			</script>
			<?
		}
		else{
			$wizard =& $this->GetWizard();
			$templateID = $wizard->GetVar('templateID');
			$themeVarName = $templateID.'_thematicCODE';
			$thematic = $wizard->GetVar($themeVarName);
			$presetVarName = $templateID.'_presetID';
			$preset = $wizard->GetVar($presetVarName);

			$shopLocalization = $wizard->GetVar('shopLocalization', true);
			if($shopLocalization === 'ua'){
				$wizard->SetDefaultVars(
					array(
						'personType' => array(
							'fiz' => 'Y',
							'fiz_ua' => 'Y',
							'ur' => 'Y',
						)
					)
				);
			}
			else{
				$wizard->SetDefaultVars(
					array(
						'personType' => array(
							'fiz' => 'Y',
							'ur' => 'Y',
						)
					)
				);
			}

			// step bg
			$this->content .='<script type="text/javascript">$(document).ready(function(){setWizardBackgroundColor("'.$preset.'");});</script>';

			// personTtype
			$this->content .= '
			<div class="wizard-input-form">
				<div class="wizard-input-form-block">
					<div style="padding-top:15px">
						<div class="wizard-input-form-field wizard-input-form-field-checkbox">
							<div class="wizard-catalog-form-item">
								'.$this->ShowCheckboxField('personType[fiz]', 'Y', (array('id' => 'personTypeF'))).'
								<label for="personTypeF">'.GetMessage('WIZ_PERSON_TYPE_FIZ').'</label>
							</div>
							<div class="wizard-catalog-form-item">
								'.$this->ShowCheckboxField('personType[ur]', 'Y', (array('id' => 'personTypeU'))).'
								<label for="personTypeU">'.GetMessage('WIZ_PERSON_TYPE_UR').'</label>
							</div>';
							$this->content .=
						'</div>
						<div class="wizard-catalog-form-item" style="font-size: 14px;">'.GetMessage('WIZ_PERSON_TYPE').'</div>
					</div>
				</div>
			</div>';
		}
	}

	function OnPostForm(){
		$wizard = &$this->GetWizard();
		if($wizard->IsNextButtonClick()){
			$personType = $wizard->GetVar('personType');
			if(empty($personType['fiz']) && empty($personType['ur'])){
				$this->SetError(GetMessage('WIZ_NO_PT'));
			}
		}
	}
}

class PaySystem extends CWizardStep{
	function InitStep(){
		$this->SetStepID('pay_system');
		$this->SetTitle(GetMessage('WIZ_STEP_PS'));
		$this->SetPrevStep(LANGUAGE_ID !== 'ru' ? 'site_settings' : 'person_type');
		$this->SetPrevCaption(GetMessage('PREVIOUS_BUTTON'));
		$this->SetNextStep('data_install');
		$this->SetNextCaption(GetMessage('NEXT_BUTTON'));
	}

	function ShowStep(){
		$wizard =& $this->GetWizard();
		$siteID = $wizard->GetVar('siteID');
		$templateID = $wizard->GetVar('templateID');
		$themeVarName = $templateID.'_thematicCODE';
		$thematic = $wizard->GetVar($themeVarName);
		$presetVarName = $templateID.'_presetID';
		$preset = $wizard->GetVar($presetVarName);

		$shopLocalization = $wizard->GetVar('shopLocalization', true);
		$shopLocalization = 'ru';
		if(LANGUAGE_ID === 'ru'){
			$wizard->SetDefaultVars(
				array(
					'paysystem' => array(
						'cash' => 'Y',
						'sber' => 'Y',
						'bill' => 'Y',
						'collect' => 'Y'  //cash on delivery
					),
					'delivery' => array(
						'courier' => 'Y',
						'self' => 'Y',
						'russianpost' => 'N',
					)
				)
			);
		}
		else{
			$wizard->SetDefaultVars(
				array(
					'paysystem' => array(
						'cash' => 'Y',
						'paypal' => 'Y',
					),
					'delivery' => array(
						'courier' => 'Y',
						'self' => 'Y',
						'dhl' => 'Y',
						'ups' => 'Y',
					)
				)
			);
		}

		// step bg
		$this->content .='<script type="text/javascript">$(document).ready(function(){setWizardBackgroundColor("'.$preset.'");});</script>';

		$arAutoDeliveries = array();
		if(Loader::includeModule('sale')){
			$dbRes = \Bitrix\Sale\Delivery\Services\Table::getList(array(
				'filter' => array(
					'=CLASS_NAME' => array(
						'\Sale\Handlers\Delivery\SpsrHandler',
						'\Bitrix\Sale\Delivery\Services\Automatic',
						'\Sale\Handlers\Delivery\AdditionalHandler'
					)
				),
				'select' => array('ID', 'CODE', 'ACTIVE', 'CLASS_NAME')
			));

			while($dlv = $dbRes->fetch()){
				if($dlv['CLASS_NAME'] == '\Sale\Handlers\Delivery\SpsrHandler'){
					$arAutoDeliveries['spsr'] = $dlv['ACTIVE'];
				}
				elseif($dlv['CLASS_NAME'] == '\Sale\Handlers\Delivery\AdditionalHandler' && $dlv['CONFIG']['MAIN']['SERVICE_TYPE'] == 'RUSPOST'){
					$arAutoDeliveries['ruspost'] = $dlv['ACTIVE'];
				}
				elseif(!empty($dlv['CODE'])){
					$arAutoDeliveries[$dlv['CODE']] = $dlv['ACTIVE'];
				}
			}
		}

		// paysystem
		$this->content .= '<div class="wizard-input-form">';
		$this->content .= '
		<div class="wizard-input-form-block">
			<div class="wizard-catalog-title">'.GetMessage('WIZ_PAY_SYSTEM_TITLE').'</div>
			<div>
				<div class="wizard-input-form-field wizard-input-form-field-checkbox">
					<div class="wizard-catalog-form-item">
						'.$this->ShowCheckboxField('paysystem[cash]', 'Y', (array('id' => 'paysystemC'))).' <label for="paysystemC">'.GetMessage('WIZ_PAY_SYSTEM_C').'</label></div>';

					if(LANGUAGE_ID == "ru")
					{
						if($shopLocalization == "ua" && ($personType["fiz"] == "Y" || $personType["fiz_ua"] == "Y"))
							$this->content .=
								'<div class="wizard-catalog-form-item">'.
									$this->ShowCheckboxField('paysystem[oshad]', 'Y', (array("id" => "paysystemO"))).
									' <label for="paysystemS">'.GetMessage("WIZ_PAY_SYSTEM_O").'</label>
								</div>';
						if ($shopLocalization == "ru")
						{
							if ($personType["fiz"] == "Y")
								$this->content .=
									'<div class="wizard-catalog-form-item">'.
										$this->ShowCheckboxField('paysystem[sber]', 'Y', (array("id" => "paysystemS"))).
										' <label for="paysystemS">'.GetMessage("WIZ_PAY_SYSTEM_S").'</label>
									</div>';
							if ($personType["fiz"] == "Y" || $personType["ur"] == "Y")
								$this->content .=
									'<div class="wizard-catalog-form-item">'.
										$this->ShowCheckboxField('paysystem[collect]', 'Y', (array("id" => "paysystemCOL"))).
										' <label for="paysystemCOL">'.GetMessage("WIZ_PAY_SYSTEM_COL").'</label>
									</div>';
						}
						if($personType["ur"] == "Y")
						{
							$this->content .=
								'<div class="wizard-catalog-form-item">'.
									$this->ShowCheckboxField('paysystem[bill]', 'Y', (array("id" => "paysystemB"))).
									' <label for="paysystemB">';
							if ($shopLocalization == "ua")
								$this->content .= GetMessage("WIZ_PAY_SYSTEM_B_UA");
							else
								$this->content .= GetMessage("WIZ_PAY_SYSTEM_B");
							$this->content .= '</label>
								</div>';
						}
					}
					else
					{
						$this->content .=
							'<div class="wizard-catalog-form-item">'.
								$this->ShowCheckboxField('paysystem[paypal]', 'Y', (array("id" => "paysystemP"))).
								' <label for="paysystemP">PayPal</label>
							</div>';
					}
				$this->content .= '</div>
			</div>
			<div class="wizard-catalog-form-item">'.GetMessage('WIZ_PAY_SYSTEM').'</div>
		</div>';

		// delivery
		if(
			LANGUAGE_ID !== 'ru' ||
			LANGUAGE_ID === 'ru' &&
			(
				COption::GetOptionString(ASPRO_MODULE_NAME, 'wizard_installed', 'N', $siteID) !== 'Y'
				|| $shopLocalization === 'ru' && ($arAutoDeliveries['rus_post'] !== 'Y')
				|| $shopLocalization === 'ua' && ($arAutoDeliveries['ua_post'] !== 'Y')
				|| $shopLocalization === 'kz' && ($arAutoDeliveries['kaz_post'] !== 'Y')
			)
		){
			$deliveryNotes = array();
			$deliveryContent = '<div class="wizard-input-form-field wizard-input-form-field-checkbox">';

			if(COption::GetOptionString(ASPRO_MODULE_NAME, 'wizard_installed', 'N', $siteID) !== 'Y'){
				$deliveryContent .= '<div class="wizard-catalog-form-item">'.$this->ShowCheckboxField('delivery[courier]', 'Y', (array('id' => 'deliveryC'))).' <label for="deliveryC">'.GetMessage('WIZ_DELIVERY_C').'</label></div>';
				$deliveryContent .= '<div class="wizard-catalog-form-item">'.$this->ShowCheckboxField('delivery[self]', 'Y', (array('id' => 'deliveryS'))).' <label for="deliveryS">'.GetMessage('WIZ_DELIVERY_S').'</label></div>';
			}

			if(LANGUAGE_ID === 'ru'){
				if($shopLocalization === 'ru'){
					if($arAutoDeliveries['ruspost'] !== 'Y'){
						\Bitrix\Sale\Delivery\Services\Manager::getHandlersList();
						$res = \Sale\Handlers\Delivery\AdditionalHandler::getSupportedServicesList();

						if(!empty($res['NOTES']) && is_array($res['NOTES'])){
							$deliveryNotes = $res['NOTES'];
						}
						else{
							$deliveryContent .= '<div class="wizard-catalog-form-item">'.$this->ShowCheckboxField('delivery[ruspost]', 'Y', (array('id' => 'deliveryR'))).' <label for="deliveryR">'.GetMessage('WIZ_DELIVERY_R').'</label></div>';
						}
					}

					if($arAutoDeliveries['rus_post'] !== 'Y'){
						$deliveryContent .= '<div class="wizard-catalog-form-item">'.$this->ShowCheckboxField('delivery[rus_post]', 'Y', (array('id' => 'deliveryR2'))).' <label for="deliveryR2">'.GetMessage('WIZ_DELIVERY_R2').'</label></div>';
					}

					if($arAutoDeliveries['rus_post_first'] !== "Y"){
						$deliveryContent .= '<div class="wizard-catalog-form-item">'.$this->ShowCheckboxField('delivery[rus_post_first]', 'Y', (array('id' => 'deliveryRF'))).' <label for="deliveryRF">'.GetMessage('WIZ_DELIVERY_RF').'</label></div>';
					}
				}
				elseif($shopLocalization === 'ua'){
					if($arAutoDeliveries['ua_post'] !== 'Y'){
						$deliveryContent .= '<div class="wizard-catalog-form-item">'.$this->ShowCheckboxField('delivery[ua_post]', 'Y', (array('id' => 'deliveryU'))).' <label for="deliveryU">'.GetMessage('WIZ_DELIVERY_UA').'</label></div>';
					}
				}
				elseif($shopLocalization === 'kz'){
					if($arAutoDeliveries['kaz_post'] !== 'Y'){
						$deliveryContent .= '<div class="wizard-catalog-form-item">'.$this->ShowCheckboxField('delivery[kaz_post]', 'Y', (array('id' => 'deliveryK'))).' <label for="deliveryK">'.GetMessage('WIZ_DELIVERY_KZ').'</label></div>';
					}
				}
			}
			else{
				$deliveryContent .= '<div class="wizard-catalog-form-item">'.$this->ShowCheckboxField('delivery[dhl]', 'Y', (array('id' => 'deliveryD'))).' <label for="deliveryD">DHL</label></div>';
				$deliveryContent .= '<div class="wizard-catalog-form-item">'.$this->ShowCheckboxField('delivery[ups]', 'Y', (array('id' => 'deliveryU'))).' <label for="deliveryU">UPS</label></div></div>';
			}

			if(!empty($deliveryNotes)){
				$deliveryContent = '<link rel="stylesheet" type="text/css" href="/bitrix/wizards/bitrix/eshop/css/style.css">
					<div class="eshop-wizard-info-note-wrap"><div class="eshop-wizard-info-note">'.implode("<br>\n", $deliveryNotes).'</div></div>'.$deliveryContent;
			}

			$this->content .= '<div class="wizard-input-form-block"><div class="wizard-catalog-title">'.GetMessage('WIZ_DELIVERY_TITLE').'</div><div>'.$deliveryContent.'</div><div class="wizard-catalog-form-item">'.GetMessage('WIZ_DELIVERY').'</div></div>';						;
		}

		// location
		$this->content .= '
		<div class="wizard-input-form-block">
			<div class="wizard-catalog-title">'.GetMessage('WIZ_LOCATION_TITLE').'</div>
			<div>
				<div class="wizard-input-form-field wizard-input-form-field-checkbox">';
					if(in_array(LANGUAGE_ID, array('ru', 'ua', 'kz'))){
						$this->content .= '<div class="wizard-catalog-form-item">'.$this->ShowRadioField('locations_csv', 'loc_ussr.csv', array('id' => 'loc_ussr', 'checked' => 'checked')).' <label for="loc_ussr">'.GetMessage('WSL_STEP2_GFILE_USSR').'</label></div>';
						$this->content .= '<div class="wizard-catalog-form-item">'.$this->ShowRadioField('locations_csv', 'loc_ua.csv', array('id' => 'loc_ua')).' <label for="loc_ua">'.GetMessage('WSL_STEP2_GFILE_UA').'</label></div>';
						$this->content .= '<div class="wizard-catalog-form-item">'.$this->ShowRadioField('locations_csv', 'loc_kz.csv', array('id' => 'loc_kz')).' <label for="loc_kz">'.GetMessage('WSL_STEP2_GFILE_KZ').'</label></div>';
					}
					$this->content .= '<div class="wizard-catalog-form-item">'.$this->ShowRadioField('locations_csv', 'loc_usa.csv', array('id' => 'loc_usa')).' <label for="loc_usa">'.GetMessage('WSL_STEP2_GFILE_USA').'</label></div>';
					$this->content .= '<div class="wizard-catalog-form-item">'.$this->ShowRadioField('locations_csv', 'loc_cntr.csv', array('id' => 'loc_cntr')).' <label for="loc_cntr">'.GetMessage('WSL_STEP2_GFILE_CNTR').'</label></div>';
					$this->content .= '<div class="wizard-catalog-form-item">'.$this->ShowRadioField('locations_csv', '', array('id' => 'none')).' <label for="none">'.GetMessage('WSL_STEP2_GFILE_NONE').'</label></div>';
					$this->content .= '<div class="wizard-catalog-form-item" style="font-size: 14px;">'.GetMessage('WIZ_DELIVERY_HINT').'</div>
				</div>
			</div>
		</div>';
	}

	function OnPostForm(){
		$wizard = &$this->GetWizard();
		if($wizard->IsNextButtonClick()){
			$paysystem = $wizard->GetVar('paysystem');
			if(empty($paysystem['cash']) && empty($paysystem['sber']) && empty($paysystem['bill']) && empty($paysystem['paypal']) && empty($paysystem['collect'])){
				$this->SetError(GetMessage('WIZ_NO_PS'));
			}
		}
	}
}

class DataInstallStep extends CDataInstallWizardStep{
	static function setLastWritedIblockParams($id = false, $type = false, $code = false){
		$_SESSION['WIZARD_LAST_WRITTED_IBLOCK']['ID'] = ($id && intVal($id)) ? intVal($id) : false;
		$_SESSION['WIZARD_LAST_WRITTED_IBLOCK']['CODE'] = ($code && trim($code)) ? trim($code) : false;
		$_SESSION['WIZARD_LAST_WRITTED_IBLOCK']['TYPE'] = ($type && trim($type)) ? trim($type) : false;

		return
			intVal($_SESSION['WIZARD_LAST_WRITTED_IBLOCK']['ID']) ||
			trim($_SESSION['WIZARD_LAST_WRITTED_IBLOCK']['TYPE']) ||
			trim($_SESSION['WIZARD_LAST_WRITTED_IBLOCK']['CODE']);
	}

	static function getLastWritedIblockParams(){
		$arResult = array(
			'ID' => ($_SESSION['WIZARD_LAST_WRITTED_IBLOCK']['ID'] ? intVal($_SESSION['WIZARD_LAST_WRITTED_IBLOCK']['ID']) : false),
			'TYPE' => ($_SESSION['WIZARD_LAST_WRITTED_IBLOCK']['TYPE'] ? trim($_SESSION['WIZARD_LAST_WRITTED_IBLOCK']['TYPE']) : false),
			'CODE' => ($_SESSION['WIZARD_LAST_WRITTED_IBLOCK']['CODE'] ? trim($_SESSION['WIZARD_LAST_WRITTED_IBLOCK']['CODE']) : false),
		);

		foreach($arResult as $key => $value){
			if(!$value){
				unset($arResult[$key]);
			}
		}

		return count($arResult) ? $arResult : false;
	}

	static function clearLastWritedIblockParams(){
		unset($_SESSION['WIZARD_LAST_WRITTED_IBLOCK']);

		return true;
	}

	function InitStep(){
		$wizard =& $this->GetWizard();
		$this->SetStepID('data_install');
		$this->SetTitle(GetMessage('wiz_install_data'));
		$this->SetSubTitle(GetMessage('wiz_install_data'));

		$templateID = $wizard->GetVar('templateID');
		$themeVarName = $templateID.'_thematicCODE';
		$thematic = $wizard->GetVar($themeVarName);
		$presetVarName = $templateID.'_presetID';
		$preset = $wizard->GetVar($presetVarName);

		// step bg
		$this->content .='<script type="text/javascript">$(document).ready(function(){setWizardBackgroundColor("'.$preset.'");});</script>';
	}

	function InstallService($serviceID, $serviceStage){
		unset($_SESSION['BX_next_LOCATION']);

		$wizard =& $this->GetWizard();

		$siteID = WizardServices::GetCurrentSiteID($wizard->GetVar('siteID'));
		define('WIZARD_SITE_ID', $siteID);
		$WIZARD_SITE_ROOT_PATH = $_SERVER['DOCUMENT_ROOT'];

		$rsSites = CSite::GetByID($siteID);
		if($arSite = $rsSites->Fetch()){
			if($arSite['DOC_ROOT'] <> ''){
				$WIZARD_SITE_ROOT_PATH = $arSite['DOC_ROOT'];
			}
			define('WIZARD_SITE_DIR', $arSite['DIR']);
		}
		else{
			define('WIZARD_SITE_DIR', '/');
		}

		define('WIZARD_SITE_ROOT_PATH', $WIZARD_SITE_ROOT_PATH);
		define('WIZARD_SITE_PATH', str_replace('//', '/', WIZARD_SITE_ROOT_PATH.'/'.WIZARD_SITE_DIR.'/'));

		$wizardPath = $wizard->GetPath();
		define('WIZARD_RELATIVE_PATH', $wizardPath);
		define('WIZARD_ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT'].$wizardPath);

		$templatesPath = WizardServices::GetTemplatesPath(WIZARD_RELATIVE_PATH.'/site');
		$templateID = $wizard->GetVar('templateID');

		define('WIZARD_TEMPLATE_ID', $templateID);
		define('WIZARD_TEMPLATE_RELATIVE_PATH', $templatesPath.'/'.WIZARD_TEMPLATE_ID);
		define('WIZARD_TEMPLATE_ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT'].WIZARD_TEMPLATE_RELATIVE_PATH);

		// TODO: remove it from scripts of services
		define('WIZARD_THEME_ID', 1);
		define('WIZARD_THEME_RELATIVE_PATH', WIZARD_TEMPLATE_RELATIVE_PATH.'/themes/'.WIZARD_THEME_ID);
		define('WIZARD_THEME_ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT'].WIZARD_THEME_RELATIVE_PATH);

		$themeVarName = $templateID.'_thematicCODE';
		$thematic = $wizard->GetVar($themeVarName);
		$lower_thematic = strtolower($thematic);
		define('WIZARD_THEMATIC', $thematic);
		define('WIZARD_THEMATIC_LOWER', $lower_thematic);
		define('WIZARD_THEMATIC_FILES_RELATIVE_PATH', WIZARD_RELATIVE_PATH.'/site/services/thematics/files/'.WIZARD_THEMATIC_LOWER);
		define('WIZARD_THEMATIC_FILES_ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT'].WIZARD_THEMATIC_FILES_RELATIVE_PATH);
		define('WIZARD_THEMATIC_PUBLIC_RELATIVE_PATH', WIZARD_RELATIVE_PATH.'/site/public/'.LANGUAGE_ID.'/');
		define('WIZARD_THEMATIC_PUBLIC_ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT'].WIZARD_THEMATIC_PUBLIC_RELATIVE_PATH);
		define('WIZARD_THEMATIC_IBLOCK_XML_RELATIVE_PATH', WIZARD_RELATIVE_PATH.'/site/services/iblock/xml/'.LANGUAGE_ID.'/');
		define('WIZARD_THEMATIC_IBLOCK_XML_ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT'].WIZARD_THEMATIC_IBLOCK_XML_RELATIVE_PATH);

		$presetVarName = $templateID.'_presetID';
		$preset = $wizard->GetVar($presetVarName);
		define('WIZARD_PRESET', $preset);

		$servicePath = WIZARD_RELATIVE_PATH.'/site/services/'.$serviceID;
		define('WIZARD_SERVICE_RELATIVE_PATH', $servicePath);
		define('WIZARD_SERVICE_ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT'].$servicePath);
		define('WIZARD_IS_RERUN', $_SERVER['PHP_SELF'] !== '/index.php');

		define('WIZARD_SITE_LOGO', intval($wizard->GetVar('siteLogo')));
		define('WIZARD_INSTALL_DEMO_DATA', $wizard->GetVar('installDemoData') === 'Y');
		define('WIZARD_REINSTALL_DATA', false);
		define('WIZARD_FIRST_INSTAL', COption::GetOptionString('main', 'wizard_first'.substr($wizard->GetID(), 7).'_'.$siteID, false, $siteID));

		$dbUsers = CGroup::GetList($by = 'id', $order = 'asc', Array('ACTIVE' => 'Y'));
		while($arUser = $dbUsers->Fetch()){
			define('WIZARD_'.$arUser['STRING_ID'].'_GROUP', $arUser['ID']);
		}

		if(!file_exists(WIZARD_SERVICE_ABSOLUTE_PATH.'/'.$serviceStage)){
			return false;
		}

		$langSubst = LangSubst(LANGUAGE_ID);
		if($langSubst <> LANGUAGE_ID){
			if(file_exists(($fname = WIZARD_SERVICE_ABSOLUTE_PATH.'/lang/'.$langSubst.'/'.$serviceStage))){
				__IncludeLang($fname, false, true);
			}
		}

		if(file_exists(($fname = WIZARD_SERVICE_ABSOLUTE_PATH.'/lang/'.LANGUAGE_ID.'/'.$serviceStage))){
			__IncludeLang($fname, false, true);
		}

		@set_time_limit(3600);
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $DB, $DBType, $APPLICATION, $USER, $CACHE_MANAGER;
		include(WIZARD_SERVICE_ABSOLUTE_PATH.'/'.$serviceStage);

		return true;
	}

	function CorrectServices(&$arServices){
		if($_SESSION['BX_next_LOCATION'] === 'Y'){
			$this->repeatCurrentService = true;
		}
		else{
			$this->repeatCurrentService = false;
		}

		// $iblockParams = self::getLastWritedIblockParams();
		// if($iblockParams && intVal($iblockParams['ID']) && trim($iblockParams['CODE'])){
		// 	switch ($iblockParams['CODE']){
		// 		//perform any manipulations with last installed infoblock
		// 		default:
		// 		break;
		// 	}
		// }

		//cuz correct need only once
		self::clearLastWritedIblockParams();

		// $wizard =& $this->GetWizard();
		// if($wizard->GetVar('installDemoData') !== 'Y'){}
	}
}

class FinishStep extends CFinishWizardStep{
	function InitStep(){
		$this->SetStepID('finish');
		$this->SetTitle(GetMessage('FINISH_STEP_TITLE'));
		$this->SetNextStep('finish');
		$this->SetNextCaption(GetMessage('wiz_go'));
	}

	function checkValid(){
		return true;
	}

	function ShowStep(){
		$wizard =& $this->GetWizard();
		$templateID = $wizard->GetVar('templateID');
		$themeVarName = $templateID.'_thematicCODE';
		$thematic = $wizard->GetVar($themeVarName);
		$presetVarName = $templateID.'_presetID';
		$preset = $wizard->GetVar($presetVarName);

		// step bg
		$this->content .='<script type="text/javascript">$(document).ready(function(){setWizardBackgroundColor("'.$preset.'");});</script>';

		if($wizard->GetVar('installDemoData') === 'Y'){
			if(!Loader::includeModule('iblock')){
				return;
			}
		}

		if($wizard->GetVar('proactive') === 'Y'){
			COption::SetOptionString('statistic', 'DEFENCE_ON', 'Y');
		}

		$siteDir = '/';
		$siteID = WizardServices::GetCurrentSiteID($wizard->GetVar('siteID'));
		$rsSite = CSite::GetByID($siteID);
		if($arSite = $rsSite->Fetch()){
			$siteDir = $arSite['DIR'];
		}
		$wizard->SetFormActionScript(str_replace('//', '/', $siteDir.'/?finish'));


		$this->CreateNewIndex();
        $this->updateRegions();

		COption::SetOptionString('main', 'wizard_solution', $wizard->solutionName, false, $siteID);

		$this->content .= GetMessage('FINISH_STEP_CONTENT');

		if($wizard->GetVar('installDemoData') === 'Y'){
			$this->content .= GetMessage('FINISH_STEP_REINDEX');
		}

		if(Loader::includeModule(ASPRO_MODULE_NAME)){
			if($obModule = CModule::CreateModuleObject(ASPRO_MODULE_NAME)){
				$moduleClassPreset = $obModule::moduleClassPreset;
				$moduleClassPreset::setBackParametrsOfPreset($preset, $siteID);
			}
		}

		COption::SetOptionString(ASPRO_MODULE_NAME, 'WIZARD_DEMO_INSTALLED', 'Y');
	}

    function updateRegions(){
		Bitrix\Main\Loader::includeModule('iblock');

		$bSale = Loader::includeModule('sale');
		$iblockID = $_SESSION["WIZARD_MAX_REGIONS_IBLOCK_ID"];

		if (
			$iblockID &&
			$bSale
		) {
			$rsItems = CIBlockElement::GetList(
				[],
				[
					'IBLOCK_ID' => $iblockID
				],
				false,
				false,
				[
					'ID',
					'NAME',
					'IBLOCK_ID',
				]
			);
			while ($arItem = $rsItems->Fetch()) {
				$locationId = NULL;

				$code = ucfirst(CUtil::translit(
					$arItem['NAME'],
					'ru',
					[
						'replace_space' => "-",
						'replace_other' => '-'
					]
				));

				if (strlen($code)) {
					$rsLoc = \CSaleLocation::GetList(
						[],
						[
							'CITY_NAME' => $arItem['NAME'],
							'CITY_LID' => 'ru',
						]
					);
					while ($arLoc = $rsLoc->GetNext()){
						if (
							(!$arLoc['REGION_LID'] || $arLoc['REGION_LID'] == 'ru') &&
							(!$arLoc['COUNTRY_LID'] || $arLoc['COUNTRY_LID'] == 'ru')
						) {
							$locationId = $arLoc['ID'];

							if (LANGUAGE_ID !== 'ru')  {
								$rsLoc2 = \CSaleLocation::GetList(
									[],
									[
											'CITY_ID' => $arLoc['CITY_ID'],
											'CITY_LID' => LANGUAGE_ID,
									]
								);
								while ($arLoc2 = $rsLoc2->GetNext()){
									if (
										(!$arLoc2['REGION_LID'] || $arLoc2['REGION_LID'] == LANGUAGE_ID) &&
										(!$arLoc2['COUNTRY_LID'] || $arLoc2['COUNTRY_LID'] == LANGUAGE_ID)
									) {
										$locationId = $arLoc2['ID'];
										break;
									}
								}
							}

							break;
						}
					}
				}

				CIBlockElement::SetPropertyValuesEx(
					$arItem['ID'],
					$arItem['IBLOCK_ID'],
					[
						'LOCATION_LINK' => $locationId,
					]
				);
			}
		}
	}
}
?>
<script>
	<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards/'.ASPRO_PARTNER_NAME.'/'.ASPRO_MODULE_NAME_SHORT.'/js/jquery-1.8.3.min.js');?>
	<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards/'.ASPRO_PARTNER_NAME.'/'.ASPRO_MODULE_NAME_SHORT.'/js/jquery.keyboard.js');?>
	function setWizardBackgroundColor(preset){
		window.console&&console.log(preset);
		switch(preset){
			case '221':
				$('.instal-bg').css('backgroundColor', '#365edc');
				break;
			default:
				$('.instal-bg').css('backgroundColor', '#365edc');
				break;
		}
	}

	$(document).ready(function(){
		$('body').keyboard(
			'ctrl+shift+f',
			{
				preventDefault: true
			},
			function(){
				document.location.href = document.location.href + '&fast=y';
			}
		);
	});

<?if(isset($_REQUEST['fast']) && (strtolower($_REQUEST['fast']) === 'y')):?>
	$(document).ready(function(){
		if($('input#installDemoData').length){
			$('input#installDemoData').attr('checked', 'checked');
		}

		if($('.wizard-next-button').length){
			if($('.wizard-next-button').attr('value') != 'Перейти на сайт'){
				$('.wizard-next-button').click();
			}
		}
	});
<?endif;?>
</script>

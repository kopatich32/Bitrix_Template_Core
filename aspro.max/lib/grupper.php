<?
namespace Aspro\Max;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,
	Bitrix\Main\SystemException,
	Bitrix\Main\Web\Json,	
	CMax as Solution,
	CMaxCache as Cache;

Loc::loadMessages(__FILE__);

class Grupper {
	protected $iblockId;
	protected $modified;
	protected $properties;
	protected $iblockProperties;

	static public function OnAdminTabControlBegin(&$form) {
		if ($GLOBALS['APPLICATION']->GetCurPage() == '/bitrix/admin/iblock_edit.php') {
			$iblockId = $_REQUEST['ID'] ?? false;

			if (
				$iblockId &&
				static::checkIblockId($iblockId)
			) {
				$arIBlock = Cache::$arIBlocksInfo[$iblockId] ?? [];
				if ($arIBlock) {
					foreach ($arIBlock['LID'] as $siteId) {
						if (static::isAspro($siteId)) {
							$GLOBALS['APPLICATION']->SetAdditionalCss('/bitrix/css/'.Solution::moduleID.'/grupper.css');
							$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/'.Solution::moduleID.'/sort/Sortable.js');
			
							$grupper = new static($iblockId);
							$form->tabs[] = [
								'TAB' => Loc::getMessage('ASPRO_GRUPPER_TAB'),
								'TITLE' => Loc::getMessage('ASPRO_GRUPPER_TITLE'),
								'DIV' => 'aspro_group_props_'.Solution::solutionName,
								'ICON' => 'main_user_edit',
								'CONTENT' => $grupper->getTabContent(),
							];

							return;
						}
					}
				}
			}
		}
	}

	static public function OnBeforeIBlockUpdate(&$arFields) {
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$request = $context->getRequest();
		$iblockId = (int)$request->get('props-group-iblock-id');
		
		if (
			$iblockId &&
			$iblockId === $arFields['ID'] &&
			static::checkIblockId($iblockId)
		) {
			$arIBlock = Cache::$arIBlocksInfo[$iblockId] ?? [];
			if ($arIBlock) {
				foreach ($arIBlock['LID'] as $siteId) {
					if (static::isAspro($siteId)) {
						$grupper = new static($iblockId);
						$grupper->saveConfig();

						return;
					}
				}
			}
		}
	}

	static public function get($siteId = '') {
		$siteId = $siteId ?: SITE_ID;
		
		return Option::get(Solution::moduleID, 'GRUPPER_PROPS', 'NOT', $siteId);
	}

	static public function isAspro($siteId = '') {
		$siteId = $siteId ?: SITE_ID;

		return static::get($siteId) === 'ASPRO_PROPS_GROUP';
	}

	static public function checkIblockId($iblockId) {
		if ($iblockId) {
			$arIBlock = Cache::$arIBlocksInfo[$iblockId] ?? [];
			if ($arIBlock) {

				foreach ($arIBlock['LID'] as $siteId) {
					$strIblockGroup = Option::get(Solution::moduleID, 'ASPRO_PROPS_GROUP_IBLOCK', '', $siteId);	
					if (is_string($strIblockGroup) && strlen($strIblockGroup)) {
						$arIblockGroup = explode(',', $strIblockGroup);
						if (in_array($iblockId, $arIblockGroup)) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	static protected function getServiceProperties() {
		return [
			'BIG_BLOCK_PICTURE', 'OUT_OF_PRODUCTION', 'PRODUCT_ANALOG_FILTER', 'PRODUCT_ANALOG', 'BIG_BLOCK', 'SUB_TITLE', 'FAVORIT_ITEM', 'PODBORKI', 'PHOTO_GALLERY', 'SALE_TEXT', 'MORE_PHOTO', 'rating', 'vote_sum', 'vote_count', 'PRODUCT_SET_GROUP', 'PRODUCT_SET_FILTER', 'PRODUCT_SET', 'YM_ELEMENT_ID', 'IN_STOCK', 'MAXIMUM_PRICE', 'MINIMUM_PRICE', 'PERIOD', 'TITLE_BUTTON', 'LINK_BUTTON', 'REDIRECT', 'LINK_PROJECTS', 'LINK_REVIEWS', 'DOCUMENTS', 'FORM_ORDER', 'FORM_QUESTION', 'PHOTOPOS', 'TASK_PROJECT', 'PHOTOS', 'LINK_COMPANY', 'LINK_VACANCY', 'LINK_BLOG', 'LINK_LANDING', 'GALLEY_BIG', 'LINK_SERVICES', 'LINK_GOODS', 'LINK_STAFF', 'LINK_SALE', 'SERVICES', 'HIT', 'RECOMMEND', 'NEW', 'STOCK', 'VIDEO', 'VIDEO_YOUTUBE', 'CML2_ARTICLE', 'LINK_TIZERS', 'LINK_BRANDS', 'BRAND', 'POPUP_VIDEO','LINK_NEWS', 'SALE_NUMBER', 'SIDE_IMAGE_TYPE', 'SIDE_IMAGE', 'LINK_LANDINGS', 'EXPANDABLES', 'EXPANDABLES_FILTER', 'ASSOCIATED_FILTER', 'ASSOCIATED', 'LINK_PARTNERS', 'BLOG_POST_ID', 'BLOG_COMMENTS_CNT', 'HELP_TEXT', 'FORUM_TOPIC_ID', 'FORUM_MESSAGE_CNT', 'EXTENDED_REVIEWS_COUNT', 'EXTENDED_REVIEWS_RAITING', 
			'BNR_TOP_UNDER_HEADER', 'BNR_TOP', 'BNR_TOP_IMG', 'BNR_TOP_BG', 'BNR_TOP_COLOR', 'BUTTON1TEXT', 'BUTTON1LINK', 'BUTTON1TARGET', 'BUTTON1CLASS', 'BUTTON1COLOR', 'BUTTON2TEXT', 'BUTTON2LINK', 'BUTTON2TARGET', 'BUTTON2CLASS', 'BUTTON2COLOR', 'LINK_FAQ', 'CML2_TAXES', 'LINK_ARTICLES', 'PRICE_CURRENCY', 'PRICE', 'PRICEOLD', 'ECONOMY', 'FILTER_PRICE', 'STATUS', 'INSTRUCTIONS', 'WB_STATUS', 'WB_ERROR_TEXT', 'WB_IMT_ID', 'WB_NM_ID', 'WB_ID', 'WB_BARCODE', 'WB_CHRT_ID'
		];
	}

	public function __construct($iblockId) {
		$this->iblockId = $iblockId;
		$this->modified = false;
		$this->properties = [];
		$this->iblockProperties = [];
	}

	public function getTabContent() {
		$this->getProperties();

		ob_start();
		include __DIR__.'/../admin/propertygroups/view/iblock_tab.php';
		$html = ob_get_clean();

		return $html;
	}

	protected function getPropertiesFromConfig() {
		$configPath = $this->getConfigPath();

		if (file_exists($configPath)) {
			try {
				$content = file_get_contents($configPath);
				if ($content) {
					$arProperties = Json::decode($content);
				}
			}
			catch (SystemException $e) {
				$arProperties = [];
			}
			
			$arAllProprties = [];
			foreach ($arProperties as $keyGroup => $arGroup) {
				$arAllProprties = array_merge($arAllProprties, $arGroup['PROPS']);
			}
			
			$arNewProperties = $this->getPropertiesFromIblock();
			$arNewPropertiesCode = array_keys($arNewProperties);

			$arAddProperties = array_diff($arNewPropertiesCode, $arAllProprties);
			if (!empty($arAddProperties)) {
				if (is_array($arProperties[0]['PROPS']) && $arProperties[0]['NAME'] === 'NO_GROUP') {
					$arProperties[0]['PROPS'] = array_merge($arAddProperties, $arProperties[0]['PROPS']);
				}
				else {
					array_unshift(
						$arProperties,
						[
							'NAME' => 'NO_GROUP',
							'PROPS' => $arAddProperties,
						]
					);
				}
				
				$this->modified = true;
			}

			$arDeleteProps = array_diff($arAllProprties, $arNewPropertiesCode);
			if (!empty($arDeleteProps)) {
				$this->modified = true;
			}

			return $arProperties;
		}
	}

	protected function getPropertiesFromIblock(){
		if (!$this->iblockProperties) {
			$this->iblockProperties = [];

			if (Loader::includeModule('iblock')) {
				$serviceProps = static::getServiceProperties();
				$dbRes = \CIBlock::GetProperties($this->iblockId, ['sort' => 'asc']);
				while ($arProperty = $dbRes->Fetch()) {
					if (!in_array($arProperty['CODE'], $serviceProps)) {
						$this->iblockProperties[$arProperty['CODE']] = [
							'NAME' => $arProperty['NAME'].' ['.$arProperty['CODE'].']',
							'CODE' => $arProperty['CODE'],
							'ID' => $arProperty['ID'],
						];
					}			
				}
			}
		}

		return $this->iblockProperties;
	}

	protected function getProperties() {
		if (!$this->properties) {
			$this->properties = [];

			$configPath = $this->getConfigPath();
			if (file_exists($configPath)) {
				$this->properties = $this->getPropertiesFromConfig();
			}
			else {
				$this->properties[] = [
					'NAME' => 'NO_GROUP',
					'PROPS' => array_keys($this->getPropertiesFromIblock()),
				];
			}
		}

		return $this->properties;
	}

	protected function getConfigName() :string {
		return 'iblock_'.$this->iblockId.'.json';
	}

	protected function getConfigDir() :string {
		return __DIR__.'/../admin/grupper/propertygroups/json';
	}

	protected function getConfigPath() :string {
		return $this->getConfigDir().$this->getConfigName();
	}

	public function saveConfig() {
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$request = $context->getRequest();
		$arJsonProp = $request->get('props-group-json');

		if (
			isset($arJsonProp) &&
			$arJsonProp
		) {
			$arNewPropGrops = [];
			$arParamsTranslit = [
				'replace_space' => '-',
				'replace_other' => '-',
			];
			foreach ($arJsonProp as $keyGroup => $propsGroup) {
				try {
					$arNewPropGrops[$keyGroup] = Json::decode(urldecode($propsGroup));
					$arNewPropGrops[$keyGroup]['CODE'] = \Cutil::translit(trim($arNewPropGrops[$keyGroup]['NAME']), 'ru', $arParamsTranslit);
				}
				catch(SystemException $e) {
				}
			}

			file_put_contents($this->getConfigPath(), Json::encode($arNewPropGrops));
		}
	}
}
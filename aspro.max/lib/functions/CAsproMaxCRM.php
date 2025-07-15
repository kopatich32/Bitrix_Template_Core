<?
namespace Aspro\Functions;

use \Bitrix\Main\Application,
	\Bitrix\Main\Web\DOM\Document,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Web\DOM\CssParser,
	\Bitrix\Main\Text\HtmlFilter,
	\Bitrix\Main\IO\File,
	\Bitrix\Main\IO\Directory,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

if (!class_exists('CAsproMaxCRM')) {
	class CAsproMaxCRM {
		const MODULE_ID = \CMax::moduleID;
		const FLOWLU_PATH = 'https://#DOMAIN#';
		const FLOWLU_SHORT_PATH = 'https://#DOMAIN#.flowlu.ru';
		const ACLOUD_PATH = 'https://#DOMAIN#';
		const ACLOUD_SHORT_PATH = 'https://#DOMAIN#.acloud.ru';
		const AMO_CRM_PATH = 'https://#DOMAIN#.amocrm.ru';

		public static $arCrmFileds = array(
			'MAIN' => array(
				'FORM_NAME' => '',
				'FORM_SID' => '',
				'SITE_ID' => '',
				'RESULT_ID' => '',
				'FORM_ALL' => '',
				'FORM_ALL_HTML' => '',
			),
			'ORDER' => array(
				'ID' => '',
				'SITE_ID' => '',
				'PERSON_TYPE' => '',
				'SUMM' => '',
				'COMMENTS' => '',
				'GOODS' => '',
			),
			'FLOWLU' => array(
				'name' => '',
				'description' => '',
				'budget' => '',
				'contact_name' => '',
				'contact_phone' => '',
				'contact_mobile' => '',
				'contact_email' => '',
				'contact_web' => '',
				'contact_position' => '',
				'contact_company' => '',
				'start_date' => '',
				'deadline' => '',
				'active' => '',
				'closing_date' => '',
				'closing_comment' => '',
			),
			'ACLOUD' => array(
				'name' => '',
				'description' => '',
				'budget' => '',
				'contact_name' => '',
				'contact_phone' => '',
				'contact_mobile' => '',
				'contact_email' => '',
				'contact_web' => '',
				'contact_position' => '',
				'contact_company' => '',
				'start_date' => '',
				'deadline' => '',
				'active' => '',
				'closing_date' => '',
				'closing_comment' => '',
			),
			'AMO_CRM' => array(
				'leads' => array(
					'PROPS' => array(
						'name_leads' => '',
						'tags_leads' => '',
						'price_leads' => '',
						'notes_leads' => '',
					)
				),
				'contacts' => array(
					'PROPS' => array(
						'name_contacts' => '',
					)
				),
				'companies' => array(
					'PROPS' => array(
						'name_companies' => '',
					)
				)
			)
		);

		public static $arCrmMethods = array(
			'FLOWLU' => array(
				'CREATE_LEAD' => '/api/v1/module/crm/lead/create',
				'LIST_LEADS' => '/api/v1/module/crm/lead/list',
				'LIST_CUSTOM_FIELDS' => '/api/v1/module/customfields/fields/list',
			),
			'ACLOUD' => array(
				'CREATE_LEAD' => '/api/v1/module/crm/lead/create',
				'LIST_LEADS' => '/api/v1/module/crm/lead/list',
				'LIST_CUSTOMFIELDS_FIELDS' => '/api/v1/module/customfields/fields/list',
				'LIST_CUSTOMFIELDS_FIELDSETS' => '/api/v1/module/customfields/fieldsets/list',
			),
			'AMO_CRM' => array(
				'ACCESS_TOKENT'=> '/oauth2/access_token',
				'CHECK_OAUTH' => '/oauth2/account/subdomain',
				'CREATE_LEAD' => '/private/api/v2/json/leads/set/',
				'CREATE_CONTACT' => '/private/api/v2/json/contacts/set/',
				'CREATE_COMPANY' => '/private/api/v2/json/company/set/',
				'CREATE_NOTES' => '/private/api/v2/json/notes/set/',
			),
		);

		public static function addFileds($arFields = array()){
			self::$arCrmFileds = array_merge(self::$arCrmFileds, (array)$arFields);
		}

		public static function updateToken(&$arOAuth, $arConfig = array(), $tokenType = 'accessToken'){
			$arResponse = array();

			$arOAuth = is_array($arOAuth) ? $arOAuth : array();
			foreach(
				array(
					'accessToken',
					'accessTokenExpires',
					'refreshToken',
					'refreshTokenExpires',
					'authCode',
				)
				as $field
			){
				$arOAuth[$field] = array_key_exists($field, $arOAuth) ? $arOAuth[$field] : '';
				$$field =& $arOAuth[$field];
			}

			$arConfig = is_array($arConfig) ? $arConfig : array();
			$type = array_key_exists('type', $arConfig) ? $arConfig['type'] : false;

			$arPost = $arHeaders = array();
			$with_curl = false;

			if($type === 'AMO_CRM'){
				foreach(
					array(
						'domain',
						'clientSecret',
						'clientId',
						'redirectUrl',
					)
					as $field
				){
					$$field = array_key_exists($field, $arConfig) ? $arConfig[$field] : '';
				}

				$domain = strlen($domain) ? $domain : 'noname';
				$url = str_replace("#DOMAIN#", $domain, self::AMO_CRM_PATH);

				if($with_curl){
					$arHeaders = array(
						'Content-Type' => 'application/json',
					);
				}

				$arPost = array(
					'grant_type' => $tokenType === 'accessToken' ? 'refresh_token' : 'authorization_code',
					'client_id' => $clientId,
					'client_secret' => $clientSecret,
					'redirect_uri' => $redirectUrl,
				);

				if($tokenType === 'accessToken'){
					$arPost['refresh_token'] = $refreshToken;
				}
				elseif($tokenType === 'refreshToken'){
					$arPost['code'] = $authCode;
				}
			}

			if($url){
				$response = self::query(
					$url,
					self::$arCrmMethods[$type]['ACCESS_TOKENT'],
					$arPost,
					$arHeaders,
					$with_curl,
					false
				);

				if($response){
					$arResponse = Json::decode($response);
					if(
						$arResponse &&
						is_array($arResponse)
					){
						if(array_key_exists('status', $arResponse)){
							if($tokenType === 'accessToken'){
								return self::updateRefreshToken($arOAuth, $arConfig);
							}
						}
						else{
							if($arResponse['access_token']){
								$arOAuth['accessToken'] = $arResponse['access_token'];
							}
							if($arResponse['expires_in']){
								$arOAuth['accessTokenExpires'] = time() + intval($arResponse['expires_in']);
							}
							if($arResponse['refresh_token']){
								$arOAuth['refreshToken'] = $arResponse['refresh_token'];
								$arOAuth['refreshTokenExpires'] = time() + 90 * 86400;
							}
						}
					}
				}
			}

			return $arResponse;
		}

		public static function updateAccessToken(&$arOAuth, $arConfig){
			return self::updateToken($arOAuth, $arConfig, 'accessToken');
		}

		public static function updateRefreshToken(&$arOAuth, $arConfig){
			return self::updateToken($arOAuth, $arConfig, 'refreshToken');
		}

		public static function updateOAuth(&$arOAuth, $arConfig = array()){
			$arResponse = array();

			$arOAuth = is_array($arOAuth) ? $arOAuth : array();
			foreach(
				array(
					'accessToken',
					'accessTokenExpires',
					'refreshToken',
					'refreshTokenExpires',
					'authCode',
				)
				as $field
			){
				$arOAuth[$field] = array_key_exists($field, $arOAuth) ? $arOAuth[$field] : '';
				$$field =& $arOAuth[$field];
			}

			$arConfig = is_array($arConfig) ? $arConfig : array();

			if(
				!strlen($accessToken) ||
				$accessTokenExpires < time()
			){
				if(
					!strlen($refreshToken) ||
					$refreshTokenExpires < time()
				){
					$arResponse = self::updateRefreshToken($arOAuth, $arConfig);
				}
				else{
					$arResponse = self::updateAccessToken($arOAuth, $arConfig);
				}
			}

			return $arResponse;
		}

		public static function checkOAuth(&$arOAuth, $arConfig = array()){
			$arResponse = array();

			$arOAuth = is_array($arOAuth) ? $arOAuth : array();
			$arConfig = is_array($arConfig) ? $arConfig : array();
			$siteId = array_key_exists('siteId', $arConfig) ? $arConfig['siteId'] : (defined('SITE_ID') ? SITE_ID : false);
			$type = array_key_exists('type', $arConfig) ? $arConfig['type'] : false;

			$arPost = $arHeaders = array();
			$with_curl = false;

			if($type === 'AMO_CRM'){
				$domain = array_key_exists('domain', $arConfig) ? $arConfig['domain'] : '';
				$domain = strlen($domain) ? $domain : 'noname';
				$url = str_replace("#DOMAIN#", $domain, self::AMO_CRM_PATH);
			}

			if(strlen($url)){
				$arResponse = self::updateOAuth(
					$arOAuth,
					$arConfig
				);

				if(!array_key_exists('status', $arResponse)){
					if($type === 'AMO_CRM'){
						$arHeaders = array(
							'Authorization' => 'Bearer '.$arOAuth['accessToken']
						);
					}

					$response = self::query(
						$url,
						self::$arCrmMethods[$type]['CHECK_OAUTH'],
						$arPost,
						$arHeaders,
						false,
						false
					);

					if($response){
						$arResponse = Json::decode($response);
					}
				}
				else{
					$arResponse = array(
						'error_code' => $arResponse['status'],
						'error' => $arResponse['hint'] ? $arResponse['hint'] : ($arResponse['title'].'. '.$arResponse['detail']),
					);
				}
			}

			return $arResponse;
		}

		public static function restore(&$arOAuth, &$arConfig){
			$arConfig = is_array($arConfig) ? $arConfig : array();
			$siteId = array_key_exists('siteId', $arConfig) ? $arConfig['siteId'] : (defined('SITE_ID') ? SITE_ID : false);
			$type = array_key_exists('type', $arConfig) ? $arConfig['type'] : false;

			if($type === 'AMO_CRM'){
				$arOAuth = array(
					'authCode' => Option::get(self::MODULE_ID, 'AUTH_CODE_'.$type, '', $siteId),
					'accessToken' => Option::get(self::MODULE_ID, 'ACCESS_TOKEN_'.$type, '', $siteId),
					'accessTokenExpires' => Option::get(self::MODULE_ID, 'ACCESS_TOKEN_EXPIRES_'.$type, '', $siteId),
					'refreshToken' => Option::get(self::MODULE_ID, 'REFRESH_TOKEN_'.$type, '', $siteId),
					'refreshTokenExpires' => Option::get(self::MODULE_ID, 'REFRESH_TOKEN_EXPIRES_'.$type, '', $siteId),
				);

				$arConfig = array(
					'type' => $type,
					'siteId' => $siteId,
					'domain' => Option::get(self::MODULE_ID, 'DOMAIN_'.$type, '', $siteId),
					'clientSecret' => Option::get(self::MODULE_ID, 'CLIENT_SECRET_'.$type, '', $siteId),
					'clientId' => Option::get(self::MODULE_ID, 'CLIENT_ID_'.$type, '', $siteId),
					'redirectUrl' => ($GLOBALS['APPLICATION']->IsHTTPS() ? 'https://': 'http://').$_SERVER['SERVER_NAME'],
					'hash' => Option::get(self::MODULE_ID, 'HASH_'.$type, '', $siteId),
				);
			}
		}

		public static function save($arOAuth, $arConfig){
			$arConfig = is_array($arConfig) ? $arConfig : array();
			$siteId = array_key_exists('siteId', $arConfig) ? $arConfig['siteId'] : (defined('SITE_ID') ? SITE_ID : false);
			$type = array_key_exists('type', $arConfig) ? $arConfig['type'] : false;

			if($type === 'AMO_CRM'){
				Option::set(self::MODULE_ID, 'AUTH_CODE_'.$type, $authCode = $arOAuth['authCode'], $siteId);
				Option::set(self::MODULE_ID, 'ACCESS_TOKEN_'.$type, $arOAuth['accessToken'], $siteId);
				Option::set(self::MODULE_ID, 'ACCESS_TOKEN_EXPIRES_'.$type, $arOAuth['accessTokenExpires'], $siteId);
				Option::set(self::MODULE_ID, 'REFRESH_TOKEN_'.$type, $arOAuth['refreshToken'], $siteId);
				Option::set(self::MODULE_ID, 'REFRESH_TOKEN_EXPIRES_'.$type, $arOAuth['refreshTokenExpires'], $siteId);
				Option::set(self::MODULE_ID, 'DOMAIN_'.$type, $domain = $arConfig['domain'], $siteId);
				Option::set(self::MODULE_ID, 'CLIENT_SECRET_'.$type, $clientSecret = $arConfig['clientSecret'], $siteId);
				Option::set(self::MODULE_ID, 'CLIENT_ID_'.$type, $clientId = $arConfig['clientId'], $siteId);
				Option::set(self::MODULE_ID, 'HASH_'.$type, $hash = md5(serialize(array($domain, $clientSecret, $clientId, $authCode))), $siteId);
			}
		}

		public static function query(
			$url,
			$method,
			$arPost = array(),
			$arHeaders = array(),
			$with_curl = false,
			$decode = false
		){
			if($with_curl){
				$ch = \curl_init();

				if(
					$arHeaders &&
					is_array($arHeaders)
				){
					$arNewHeaders = array();
					foreach($arHeaders as $key => $value){
						$arNewHeaders[] = $key.':'.$value;
					}

					\curl_setopt($ch, CURLOPT_HTTPHEADER, $arNewHeaders);
				}

				if(!empty($arPost)){
					\curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
					\curl_setopt($ch, CURLOPT_POST, true);
					\curl_setopt($ch, CURLOPT_POSTFIELDS, Json::encode($arPost));
				}

				\curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				\curl_setopt($ch, CURLOPT_URL, $url.$method);
				\curl_setopt($ch, CURLOPT_HEADER, false);
				\curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt');
				\curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt');
				\curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
				\curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
				$result_text = \curl_exec ($ch);
			}
			else{
				$obHTTP = new \Bitrix\Main\Web\HttpClient();

				if(
					$arHeaders &&
					is_array($arHeaders)
				){
					foreach($arHeaders as $key => $value){
						$obHTTP->setHeader($key, $value, true);
					}
				}

				if(!empty($arPost)){
					$obHTTP->setCharset('UTF-8');
					$arPost = \Bitrix\Main\Text\Encoding::convertEncoding($arPost, LANG_CHARSET, 'UTF-8');
					$result_text = $obHTTP->post($url.$method, $arPost);
				}
				else{
					$result_text = $obHTTP->get($url.$method);
				}
			}

			if($decode){
				$result_text = $GLOBALS['APPLICATION']->ConvertCharset($result_text, 'UTF-8', LANG_CHARSET);
			}

			return $result_text;
		}

		public static function isEnabledIntegration(string $type, string $siteId){
			if (
				strlen($type) &&
				strlen($siteId)
			) {
				return 
					Option::get(self::MODULE_ID, 'ACTIVE_LINK_'.$type, '', $siteId) && 
					Option::get(self::MODULE_ID, 'ACTIVE_'.$type, 'N', $siteId) === 'Y';
			}

			return false;
		}

		// deprecated
		public static function authAmoCrm($site = SITE_ID){
			return array();
		}
	}
}

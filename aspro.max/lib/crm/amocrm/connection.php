<?
namespace Aspro\Max\CRM\Amocrm;

use Bitrix\Main\Config\Option,
    Bitrix\Main\Web\HttpClient,
	Bitrix\Main\Web\Json,
    Aspro\Max\CRM,
    CMax as Solution;

class Connection extends CRM\Base\Connection {
    protected static $instances = [];

    public static function getType() {
        return CRM\Type::AMOCRM;
    }

    public static function fixDomain(string $domain) {
        $domain = trim($domain);
        $domain = preg_replace('/\/*$/', '', $domain);

        if (strlen($domain)) {
            $domain = 'https://'.preg_replace('/https?:\/\//i', '', $domain);

            if (strpos($domain, '.') === false) {
                $domain .= '.amocrm.ru';
            }
        }

        return $domain;
    }

    public function loadConfig() {
        $this->config = [
            'domain' => static::fixDomain(Option::get(Solution::moduleID, 'DOMAIN_'.static::getType(), '', $this->siteId)),
            'client_secret' => Option::get(Solution::moduleID, 'CLIENT_SECRET_'.static::getType(), '', $this->siteId),
            'client_id' => Option::get(Solution::moduleID, 'CLIENT_ID_'.static::getType(), '', $this->siteId),
            'auth_code' => Option::get(Solution::moduleID, 'AUTH_CODE_'.static::getType(), '', $this->siteId),
        ];
    }

    public function __get($name) {
        switch ($name) {
            case 'tags_leads':
                return 
                    Option::get(Solution::moduleID, 'TAGS_'.$this->type.'_TITLE', '', $this->siteId);
            default:
                return parent::__get($name);
        }
    }

    public function __set($name, $value) {
        switch ($name) {
            case 'tags_leads':
                Option::set(Solution::moduleID, 'TAGS_'.$this->type.'_TITLE', $value, $this->siteId);
                
                return $value;
            default:
                return parent::__set($name, $value);            
        }
    }

    public function api(string $method, array $params = []) :array {
        $result = [];

        return $result;
    }

    public function try() :bool {
        return true;
    }
}

<?
namespace Aspro\Max\CRM\Base;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Config\Option,
    Aspro\Max\CRM,
    CMax as Solution;

Loc::loadMessages(__FILE__);

abstract class Connection {
    protected static $instances = [];

    protected $siteId;
    protected $config;

    abstract public static function getType();
    abstract public static function fixDomain(string $domain);

    public static function getInstance(string $siteId) {
        if (!isset(static::$instances[$siteId])) {
            static::$instances[$siteId] = new static($siteId);
        }

        return static::$instances[$siteId];
    }

    protected function __construct(string $siteId) {
        $this->setSiteId($siteId);
        $this->loadConfig();
    }

    protected function setSiteId(string $siteId) {
        if (!strlen($siteId)) {
            throw new \Exception('Invalid siteId '.$siteId);
        }

        $this->siteId = $siteId;
    }

    abstract public function loadConfig();

    protected function getFormsMatches() :array {
        $result = [];

        $forms = CRM\Helper::getForms($this->siteId);
        foreach ($forms as $form) {
            $result[$form['ID']] = $this->getFormMatches($form['ID']);
        }

        return $result;
    }

    protected function getFormMatches(int $formId) :array {
        $result = [];

        $value = Option::get(Solution::moduleID, $this->type.'_CRM_FIELDS_MATCH_'.$formId, '', $this->siteId);
        $result = strlen($value) ? Solution::unserialize($value) : [];
        $result = is_array($result) ? $result : [];

        return $result;
    }

    protected function setFormMatches(int $formId, $matches) {
        $matches = is_array($matches) ? $matches : [];
        Option::set(Solution::moduleID, $this->type.'_CRM_FIELDS_MATCH_'.$formId, serialize($matches), $this->siteId);
    }

    protected function setFormsMatches($matches) {
        $newMatches = is_array($matches) ? $matches : [];
        $curMatches = $this->getFormsMatches();

        // merge arrays with saving keys (int $formId)        
        foreach ($curMatches as $formId => $matches) {
            if (array_key_exists($formId, $newMatches)) {
                $curMatches[$formId] = $newMatches[$formId];
            }
        }

        foreach ($newMatches as $formId => $matches) {
            if (!array_key_exists($formId, $curMatches)) {
                $curMatches[$formId] = $matches;
            }
        }

        foreach ($curMatches as $formId => $matches) {
            $this->setFormMatches($formId, $matches);
        }
    }

    protected function getOrdersMatches() :array {
        $result = [];

        $value = Option::get(Solution::moduleID, $this->type.'_CRM_ORDER_FIELDS_MATCH', '', $this->siteId);
        $result = strlen($value) ? Solution::unserialize($value) : [];
        $result = is_array($result) ? $result : [];

        return $result;
    }

    protected function setOrdersMatches($matches) {
        $matches = is_array($matches) ? $matches : [];
        Option::set(Solution::moduleID, $this->type.'_CRM_ORDER_FIELDS_MATCH', serialize($matches), $this->siteId);
    }

    public function __get($name) {
        switch ($name) {
            case 'type':
                return static::getType();
            case 'siteId':
                return $this->siteId;
            case 'config':
                return $this->config;
            case 'tested':
                return 
                    Option::get(Solution::moduleID, 'ACTIVE_LINK_'.$this->type, 'N', $this->siteId) === 'Y';
            case 'active':
                return 
                    Option::get(Solution::moduleID, 'ACTIVE_LINK_'.$this->type, 'N', $this->siteId) === 'Y' && 
                    Option::get(Solution::moduleID, 'ACTIVE_'.$this->type, 'N', $this->siteId) === 'Y';
            case 'logging':
                return 
                    Option::get(Solution::moduleID, 'USE_LOG_'.$this->type, 'N', $this->siteId) === 'Y';
            case 'forms_autosend':
                return 
                    Option::get(Solution::moduleID, 'AUTOMATE_SEND_'.$this->type, 'N', $this->siteId) === 'Y';
            case 'forms_lead_title':
                return 
                    Option::get(Solution::moduleID, 'LEAD_NAME_'.$this->type.'_TITLE', Loc::getMessage('CRM_FORMS_LEAD_TITLE_DEFAULT'), $this->siteId);
            case 'forms_matches':
                return $this->getFormsMatches();
            case 'orders_autosend':
                return 
                    Option::get(Solution::moduleID, 'AUTOMATE_SEND_ORDER_'.$this->type, 'N', $this->siteId) === 'Y';
            case 'orders_lead_title':
                return 
                    Option::get(Solution::moduleID, 'LEAD_NAME_ORDER_'.$this->type.'_TITLE', Loc::getMessage('CRM_ORDERS_LEAD_TITLE_DEFAULT'), $this->siteId);
            case 'orders_matches':
                return $this->getOrdersMatches();
            default:
                if (array_key_exists($name, $this->config)) {
                    return $this->config[$name];
                }
                break;
        }
    }

    public function __set($name, $value) {
        switch ($name) {
            case 'tested':
                Option::set(Solution::moduleID, 'ACTIVE_LINK_'.$this->type, $value ? 'Y' : 'N', $this->siteId);

                return $value;
            case 'active':
                Option::set(Solution::moduleID, 'ACTIVE_'.$this->type, $value ? 'Y' : 'N', $this->siteId);

                return $value;
            case 'logging':
                Option::set(Solution::moduleID, 'USE_LOG_'.$this->type, $value ? 'Y' : 'N', $this->siteId);

                return $value;
            case 'forms_autosend':
                Option::set(Solution::moduleID, 'AUTOMATE_SEND_'.$this->type, $value ? 'Y' : 'N', $this->siteId);
                
                return $value;
            case 'forms_lead_title':
                Option::set(Solution::moduleID, 'LEAD_NAME_'.$this->type.'_TITLE', $value, $this->siteId);

                return $value;
            case 'forms_matches':
                $this->setFormsMatches($value);
                return $value;
            case 'orders_autosend':
                Option::set(Solution::moduleID, 'AUTOMATE_SEND_ORDER_'.$this->type, $value ? 'Y' : 'N', $this->siteId);

                return $value;
            case 'orders_lead_title':
                Option::set(Solution::moduleID, 'LEAD_NAME_ORDER_'.$this->type.'_TITLE', $value, $this->siteId);

                return $value;
            case 'orders_matches':
                $this->setOrdersMatches($value);

                return $value;
        }
    }

    public function getLogBasePath() {
        return $_SERVER['DOCUMENT_ROOT'].'/upload/logs/'.Solution::moduleID.'/crm/';
    }

    public function getLogPath() {
        return $this->getLogBasePath().date('Y-m').'/';
    }

    public function getLogFilename() {
        return strtolower(str_replace('_', '', $this->type)).'.log';
    }

    public function log($message) {
        if (!is_string($message)) {
            $message = print_r($message, 1);
        }

        $path = $this->getLogPath();
        if(!is_dir($path)){
            @mkdir($path, BX_DIR_PERMISSIONS, true);
        }

        $filename = $this->getLogFilename();
        @file_put_contents($path.$filename, date('d-m-Y H-i-s', time() + \CTimeZone::GetOffset()).': '.$message.PHP_EOL, LOCK_EX | FILE_APPEND);
    }

    abstract public function api(string $method, array $params = []) :array;
    abstract public function try() :bool;

    public function test(array $config) :bool {
        $connection = clone $this;
        $connection->config = $config;
        $connection->config['domain'] = static::fixDomain($connection->config['domain']);
        
        return $connection->try();
    }
}

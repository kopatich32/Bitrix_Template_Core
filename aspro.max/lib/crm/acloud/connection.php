<?
namespace Aspro\Max\CRM\Acloud;

use Bitrix\Main\Config\Option,
    Bitrix\Main\Web\HttpClient,
	Bitrix\Main\Web\Json,
    Aspro\Max\CRM,
    CMax as Solution;

class Connection extends CRM\Base\Connection {
    protected static $instances = [];

    public static function getType() {
        return CRM\Type::ACLOUD;
    }

    public static function fixDomain(string $domain) {
        $domain = trim($domain);
        $domain = preg_replace('/\/*$/', '', $domain);

        if (strlen($domain)) {
            $domain = 'https://'.preg_replace('/https?:\/\//i', '', $domain);

            if (strpos($domain, '.') === false) {
                $domain .= '.aspro.cloud';
            }
        }

        return $domain;
    }

    public function loadConfig() {
        $this->config = [
            'domain' => static::fixDomain(Option::get(Solution::moduleID, 'DOMAIN_'.static::getType(), '', $this->siteId)),
            'api_key' => Option::get(Solution::moduleID, 'TOKEN_'.static::getType(), '', $this->siteId),
        ];
    }

    public function api(string $method, array $params = []) :array {
        $result = [];

        $params['api_key'] = $this->api_key;

        $url = '/api/v1/'.$method;
        $url = $this->domain.preg_replace('/\/{2,}/', '/', $url);

        if ($this->logging) {
            $this->log('Request: '.print_r([
                'url' => $url,
                'params' => $params,
            ], 1));
        }

        $response = CRM\Connection::query(
            $url,
            HttpClient::HTTP_POST,
            $params
        );

        if ($this->logging) {
            $this->log('Response: '.print_r($response, 1).PHP_EOL);
        }

        if (strlen($response)) {
            $data = Json::decode($response);
            if (
                $data &&
                is_array($data)
            ) {
                if (
                    array_key_exists('error', $data) &&
                    is_array($data['error']) &&
                    array_key_exists('error_msg', $data['error'])
                ) {
                    throw new \Exception($data['error']['error_msg']);
                }

                if (array_key_exists('response', $data)) {
                    return $data['response'];
                }
            }
        } else {
            throw new \Exception('Empty api response');
        }

        return $result;
    }

    public function try() :bool {
        $response = $this->api('/module/crm/leads/list');

        return true;
    }
}

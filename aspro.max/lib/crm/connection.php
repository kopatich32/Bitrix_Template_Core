<?
namespace Aspro\Max\CRM;

use Bitrix\Main\Config\Option,
    Bitrix\Main\Web\HttpClient,
	Bitrix\Main\Web\Json,
    Aspro\Max\CRM;

class Connection {
    public static function getClass(string $type) :string {
        return CRM\Type::getClass($type, 'Connection');
    }

    public static function getInstance(string $type, string $siteId) {
        $className = static::getClass($type);
        if (strlen($className)) {
            if (class_exists($className)) {
                return $className::getInstance($siteId);
            }

            throw new \Exception('Unknown class '.$className);
        }
        
        throw new \Exception('Invalid type '.$type);
    }

    public static function query(
        string $url,
        string $method,
        array $params = [],
        array $headers = []
    ) :string {
        $response = '';

        $http = new HttpClient([
            'redirect' => true,
            'redirectMax' => 5,
            'waitResponse' => true,
            'socketTimeout' => 30,
            'streamTimeout' => 30,
            'version' => '1.0',
            'compress' => false,
            'disableSslVerification' => true,
        ]);

        if ($headers) {
            foreach ($headers as $key => $value) {
                $http->setHeader($key, $value, true);
            }
        }

        if ($params) {
            $url .= (strpos($url, '?') === false ? '?' : '').http_build_query($params);
        }

        if ($method === HttpClient::HTTP_POST) {
            if ($params) {
                $http->setCharset('UTF-8');
                $params = \Bitrix\Main\Text\Encoding::convertEncoding($params, LANG_CHARSET, 'UTF-8');
            }

            $response = $http->post($url, $params);
        } elseif ($method === HttpClient::HTTP_GET) {
            $response = $http->get($url);
        }

        return $response;
    }
}

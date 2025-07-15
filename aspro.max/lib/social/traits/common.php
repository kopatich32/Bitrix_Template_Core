<?php

namespace Aspro\Max\Social\Traits;

use Bitrix\Main\Web\Json,
	Bitrix\Main\Web\HttpClient,
	Bitrix\Main\SystemException;

trait Common
{
	protected $error;
	
	protected function getRequest(string $url): array
	{
		try {
			$http = new HttpClient();
			$http->setTimeout(30);
			$http->setStreamTimeout(30);

			$data = $http->get($url);
			$data = Json::decode($data);
		} catch(SystemException $e) {
			$data = [
				'error' => [
					'error_msg' => $e->getMessage()
				]
			];
		}

		return $data;
	}

	protected function checkApiToken()
	{
		if (!strlen($this->access_token)) {
			$arString = explode("\\", static::class);
			$service = end($arString);
			$mess_key = "NO_API_TOKEN_".strtoupper($service);

			$this->error = GetMessage($mess_key) ?: $mess_key;
		}
	}
}
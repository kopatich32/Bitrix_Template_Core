<?
if(!defined('ASPRO_MAX_MODULE_ID'))
	define('ASPRO_MAX_MODULE_ID', 'aspro.max');


class CVKMax{
	const MODULE_ID = ASPRO_MAX_MODULE_ID;
	const URL_VK_API = 'https://api.vk.com/method/';
	const API_VERSION = 5.131;

	private $access_token = 0;
	public $token_params = 0;
	public $count_post = 0;
	public $error = "";
	public $App = "";

	public function __construct($token, $count = 10){
		global $APPLICATION;
		$this->token_params = $token;
		$this->count_post = $count;
		$this->App=$APPLICATION;
	}

	public function checkApiToken(){
		if(!strlen($this->token_params)){
			$this->error="No API token VK";
		}
		$this->access_token='/?access_token='.$this->token_params;
	}

	public function getFormatResult($method, $params = [], $fields = ''){
		if ($fields) {
			$method.$this->access_token .= '&fields='.$fields;
		}
		if ($params) {
			foreach ($params as $param => $value) {
				$method.$this->access_token .= "&".$param."=".$value;
			} 
		}
		if(function_exists('curl_init'))
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, self::URL_VK_API.$method.$this->access_token."&count=".$this->count_post."&v=".self::API_VERSION);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			$out = curl_exec($curl);
			$data =  $out ? $out : curl_error($curl);
		}
		else
		{
			$data = file_get_contents(self::URL_VK_API.$method.$this->access_token."&count=".$this->count_post."&v=".self::API_VERSION);			
		}
		
		$data = json_decode($data, true);
		$data = $this->App->ConvertCharsetArray($data, 'UTF-8', LANG_CHARSET);
		
		return $data;
	}

	public function getVKPosts($owner_id = -1, $params = []){
		$this->checkApiToken();

		if($this->error){
			return [
				'error' => [
					'error_msg' => $this->error
				]
			];
		}else{
			$data = $this->getFormatResult('wall.get', array_merge([
				'owner_id' => $owner_id, 
				'extended' => 1
			], $params));

			if (isset($data['error'])) {
				return $data;
			} elseif (isset($data['response']['items']) && count($data['response']['items'])) {
				$data['response']['items'] = array_map(function($item){
					if (isset($item['copy_history'])) {
						if (strlen($item['copy_history'][0]['text']))
							$item['text'] = $item['copy_history'][0]['text'];
						if (isset($item['copy_history'][0]['attachments']))
							$item['attachments'] = $item['copy_history'][0]['attachments'];
	
						$item['copy_history'] = true;
					}
	
					if (isset($item['attachments'])) {
						foreach ($item['attachments'] as &$attachment) {
							if (!$item['text'] && $attachment[$attachment['type']]['description']) {
								$item['text'] = $attachment[$attachment['type']]['description'];
							}

							if (
								$attachment['type'] === 'link' && 
								isset($attachment['link']['photo']) &&
								count($attachment['link']['photo'])
							) {
								$attachment['type'] = 'photo';
								$attachment['photo'] = $attachment['link']['photo'];
								unset($attachment['link']);
							}
						}
					}
	
					if (
						isset($item['attachments']) && 
						isset($item['attachments'][0]['type']) === 'link' && 
						isset($item['attachments'][0]['link']['photo']) &&
						count($item['attachments'][0]['link']['photo'])
					) {
						if (!isset($item['attachments']))
							$item['attachments'] = [];
						
						$item['attachments'][] = [
							'type' => 'photo',
							'photo' => $item['attachments'][0]['link']['photo']
						];
					}
	
					return $item;
				}, $data['response']['items']);
	
				$data['response']['items'] = array_filter($data['response']['items'], function($item) {
					return strlen(trim($item['text']))
						? true
						: ( 
							isset($item['attachments'])
								? count(array_filter($item['attachments'], function($attachment){
									return in_array($attachment['type'], ['video', 'photo']);
								}))
								: false
						);
				});
	
				$countItems = count($data['response']['items']);
				if ($countItems < $this->count_post) {
					$offsetData = self::getVKPosts($owner_id, ['offset' => $this->count_post]);
	
					if (isset($offsetData['response']['items'])){
						$data['response']['items'] = array_merge($data['response']['items'], $offsetData['response']['items']);
					}
				}
			} else {
				return [
					'error' => [
						'error_msg' => "No items available"
					]
				];
			}
		}

		return $data;
	}
	
	public function getInstagramUser(){
		$this->checkApiToken();

		if($this->error){
			return $this->error;
		}else{
			$data=$this->getFormatResult('users/self');
		}

		return $data;
	}

	public function getInstagramTag($tag) {
		$this->checkApiToken();

		if($this->error){
			return $this->error;
		}else{
			$data=$this->getFormatResult('tag/'.$tag.'/media/recent');
		}

		return $data;
	}
}?>
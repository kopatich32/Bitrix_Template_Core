<?php

namespace Aspro\Max\Social;

class Instagram extends Base
{
	const API_URL = 'https://graph.instagram.com/me/';

	public function __construct($token, $count = 10)
	{
		parent::__construct($token, $count);
	}

	protected function getFormatResult(array $arOptions = [])
	{
		return $this->getRequest($this->prepareURLString($arOptions)."&limit=".$this->count_post);
	}

	public function getPosts(array $arOptions = [])
	{
		$this->checkApiToken();

		if ($this->error) {
			return [
				"ERROR" => "Y", 
				"MESSAGE" => $this->error
			];
		}
		
		return $this->getFormatResult([
			'METHOD' => 'media',
			'FIELDS' => 'id,caption,media_url,permalink,username,timestamp,thumbnail_url'
		]);
	}

	public function getUser()
	{
		$this->checkApiToken();

		if ($this->error) {
			return $this->error;
		}

		return $this->getFormatResult(['METHOD' => 'users/static']);
	}

	public function getTag($tag)
	{
		$this->checkApiToken();

		if ($this->error) {
			return $this->error;
		}

		return $this->getFormatResult(['METHOD' => 'tag/'.$tag.'/media/recent']);
	}
}

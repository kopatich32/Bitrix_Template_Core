<?php

namespace Aspro\Max\Social;

abstract class Base
{
	use Traits\Common;

	const API_VERSION = 'abstract';
	const API_URL = 'abstract';

	protected $access_token = 0;
	public $count_post = 0;

	public function __construct($token, $count = 10)
	{
		$this->access_token = $token;
		$this->count_post = $count;
	}

	protected function prepareURLString($arOptions)
	{
		$arDefaultOptions = [
			'FIELDS' => '',
			'PARAMS' => [],
			'METHOD' => '',
		];
		$arConfig = array_merge($arDefaultOptions, $arOptions);
		$fields = $arConfig['FIELDS'];
		$params = $arConfig['PARAMS'];

		$url = static::API_URL.$arConfig['METHOD'].'/?access_token='.$this->access_token;

		if ($fields) {
			$url .= '&fields='.$fields;
		}
		if ($params) {
			$url .= '&'.http_build_query($params);
		}

		return $url;
	}

	abstract protected function getFormatResult(array $arOptions = []);
	abstract public function getPosts(array $arOptions = []);
}

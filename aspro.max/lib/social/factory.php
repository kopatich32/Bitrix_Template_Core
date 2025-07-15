<?php

namespace Aspro\Max\Social;

class Factory
{
	public static function create(string $type, string $token = '', $count = 10)
	{
		switch ($type) {
			case 'instagram':
				return new Instagram($token, $count);
				break;
			case 'vk':
				return new Vk($token, $count);
				break;
		}
	}
}
<?php

namespace Aspro\Max\Social\Video;

class Factory
{
	public static function create(string $type, array $options = [])
	{
		switch ($type) {
			case 'vk':
				return new VK($options);
				break;
			case 'youtube':
				return new Youtube($options);
				break;
			case 'rutube':
				return new Rutube($options);
				break;
		}
	}
}
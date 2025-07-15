<?php

namespace Aspro\Max\Social\Video;

use \Aspro\Max\Social;

abstract class Base implements Social\Interface\Video
{
	use Social\Traits\Common;
	use Social\Traits\Video;

	const API_URL = 'abstract';

	public function __construct(array $options = [])
	{
		$this->options = array_merge([
			'channel_id' => 0,
		], $options);
	}
}

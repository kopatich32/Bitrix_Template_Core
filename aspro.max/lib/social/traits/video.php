<?php

namespace Aspro\Max\Social\Traits;

trait Video
{
	protected array $items = [];
	protected array $options;
	protected array $sort;

	/**
	 * Normalize items from different services
	 * 
	 * $elements = [
	 *	[
	 * 		'snippet' => [
	 * 			'resourceId' => [
	 * 				'videoId' => 'value',
	 * 			],
	 * 		],
	 *	],
	 *	...
	 * ]
	 * $mapping = [
	 * 	'snippet,resourceId,videoId' => 'id',
	 * 	...
	 * ]
	 * $result = [
	 * 	['id' => 'value'],
	 * 	...
	 * ]
	 */
	protected function normalizeResults($mappings)
	{
		$result = [];

		foreach ($this->items as $element) {
			$transformedElement = [];

			foreach ($mappings as $keys => $newKey) {
				$keyArray = array_map('trim', explode(',', $keys));
				$value = $this->getValueByKeys($element, $keyArray);

				if ($value !== null) {
					$transformedElement[$newKey] = $value;
				}
			}

			$result[] = $transformedElement;
		}

		$this->items = $result;
	}

	/**
	 * Deep searching value in array
	 */
	protected function getValueByKeys($element, $keys)
	{
		$value = $element;

		foreach ($keys as $key) {
			if (isset($value[$key])) {
				$value = $value[$key];
			} else {
				return null;
			}
		}

		return $value;
	}

	protected function formatDate($date)
	{
		return strtotime($date);
	}

	protected function formatDuration($duration)
	{
		$hour = gmdate('H', $duration);
		$minutesWithSeconds = gmdate('i:s', $duration);

		return ($hour !== '00' ? $hour.':' : '').$minutesWithSeconds;
	}
}
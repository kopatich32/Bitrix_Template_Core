<?php
/**
 * Aspro:Max module thematics
 * @copyright 2019 Aspro
 */

 use Aspro\Max\Preset;

 IncludeModuleLangFile(__FILE__);
 
 Preset::$arThematicsList = array(
	'ACTIVE' => array(
		'CODE' => 'ACTIVE',
		'TITLE' => GetMessage('THEMATIC_ACTIVE_TITLE'),
		'DESCRIPTION' => GetMessage('THEMATIC_ACTIVE_DESCRIPTION'),
		'PREVIEW_PICTURE' => '/bitrix/images/aspro.max/themes/thematic_preview_active.png',
		'URL' => 'https://active.max-demo.ru/',
		'OPTIONS' => array(
		),
		'PRESETS' => array(
			'LIST' => array(
				0 => 248,
				1 => 513,
				2 => 911,
			),
			'DEFAULT' => 248,
		),
	),
	'MEBEL' => array(
		'CODE' => 'MEBEL',
		'TITLE' => GetMessage('THEMATIC_MEBEL_TITLE'),
		'DESCRIPTION' => GetMessage('THEMATIC_MEBEL_DESCRIPTION'),
		'PREVIEW_PICTURE' => '/bitrix/images/aspro.max/themes/thematic_preview_mebel.png',
		'URL' => 'https://mebel.max-demo.ru/',
		'OPTIONS' => array(
		),
		'PRESETS' => array(
			'LIST' => array(
				0 => 780,
				1 => 768,
				2 => 844,
			),
			'DEFAULT' => 780,
		),
	),
	'UNIVERSAL' => array(
		'CODE' => 'UNIVERSAL',
		'TITLE' => GetMessage('THEMATIC_UNIVERSAL_TITLE'),
		'DESCRIPTION' => GetMessage('THEMATIC_UNIVERSAL_DESCRIPTION'),
		'PREVIEW_PICTURE' => '/bitrix/images/aspro.max/themes/thematic_preview_universal.png',
		'URL' => 'https://max-demo.ru/',
		'OPTIONS' => array(
		),
		'PRESETS' => array(
			'DEFAULT' => 595,
			'LIST' => array(
				0 => 595,
				1 => 931,
				2 => 461,
				3 => 850,
				4 => 400,
			),
		),
	),
	'VOLT' => array(
		'CODE' => 'VOLT',
		'TITLE' => GetMessage('THEMATIC_VOLT_TITLE'),
		'DESCRIPTION' => GetMessage('THEMATIC_VOLT_DESCRIPTION'),
		'PREVIEW_PICTURE' => '/bitrix/images/aspro.max/themes/thematic_preview_volt.png',
		'URL' => 'https://volt.max-demo.ru/',
		'OPTIONS' => array(
		),
		'PRESETS' => array(
			'DEFAULT' => 124,
			'LIST' => array(
				0 => 124,
				1 => 403,
			),
		),
	),
	'MODA' => array(
		'CODE' => 'MODA',
		'TITLE' => GetMessage('THEMATIC_MODA_TITLE'),
		'DESCRIPTION' => GetMessage('THEMATIC_MODA_DESCRIPTION'),
		'PREVIEW_PICTURE' => '/bitrix/images/aspro.max/themes/thematic_preview_moda.png',
		'URL' => 'https://moda.max-demo.ru/',
		'OPTIONS' => array(
		),
		'PRESETS' => array(
			'DEFAULT' => 340,
			'LIST' => array(
				0 => 340,
				1 => 437,
				2 => 253,
				3 => 424,
				4 => 479,
				5 => 131,
				6 => 939,
				7 => 562,
				8 => 588,
			),
		),
	),
	'HOME' => array(
		'CODE' => 'HOME',
		'TITLE' => GetMessage('THEMATIC_HOME_TITLE'),
		'DESCRIPTION' => GetMessage('THEMATIC_HOME_DESCRIPTION'),
		'PREVIEW_PICTURE' => '/bitrix/images/aspro.max/themes/thematic_preview_housegoods.png',
		'URL' => 'https://home.max-demo.ru/',
		'OPTIONS' => array(
		),
		'PRESETS' => array(
			'DEFAULT' => 695,
			'LIST' => array(
				0 => 695,
				1 => 594,
				2 => 895,
				3 => 701,
				4 => 826,
				5 => 555,
				6 => 103,
			),
		),
	),
);
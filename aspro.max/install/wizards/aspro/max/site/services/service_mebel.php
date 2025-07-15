<?$arServices = array(
	'thematics' => array(
		'NAME' => GetMessage('SERVICE_PREPARE_DATA'),
		'MODULE_ID' => 'fileman',
		'STAGES' => array(
			'check.php',
			'download.php',
			'clear.php',
			'unzip.php',
		),
	),
	'main' => array(
		'NAME' => GetMessage('SERVICE_MAIN_SETTINGS'),
		'STAGES' => array(
			'public.php',
			'template.php',
			'theme.php',
			'menu.php',
			'settings.php',
		),
	),
	'iblock' => array(
		'NAME' => GetMessage('SERVICE_IBLOCK_DEMO_DATA'),
		'STAGES' => Array(
			"types.php",
			"banner_types.php",
			"vacancy.php",
			"licenses.php",
			"regions.php",
			"faq.php",
			"megamenu.php",
			"docs.php",
			"bottom_icons.php",
			"stories.php",
			"adv_content.php",
			"banners_inner.php",
			"tizers.php",
			"shops.php",
			"company.php",
			"banners_float.php",
			"cross_sales.php",
			// "marketings.php",
			"gallery.php",
			"staff.php",
			"landing.php",
			"search.php",
			"add_review.php",
			"articles.php",
			"news.php",
			"stock.php",
			"services.php",
			"projects.php",
			"partners.php",
			"catalog_part1.php",
			"catalog_part2.php",
			"catalog_part3.php",
			"catalog_part4.php",
			"catalog_part5.php",
			"catalog_part6.php",
			"catalog_part7.php",
			"banners.php",
			"references.php",
			"references_color.php",
			"references_contact.php",
			"sku.php",
			"catalog_info.php",
			"brands.php",
			"lookbook.php",
			"favorit.php",
			"banners_catalog.php",
			"mainblocks.php",
			"links.php",
			"errors_updates.php",
		),
	),
	'form' => array(
		'NAME' => GetMessage('SERVICE_FORM_DEMO_DATA'),
		'STAGES' => array(
			'ask.php',
			'ask_staff.php',
			'callback.php',
			'cheaper.php',
			'feedback.php',
			'one_click_buy.php',
			'projects.php',
			'resume.php',
			'review.php',
			'toorder.php',
			'services.php',
			'send_gift.php',
			'sms.php',
			'errors_updates.php',
		)
	),
	'sale' => array(
		'NAME' => GetMessage('SERVICE_SALE_DEMO_DATA'),
		'STAGES' => array(
			'locations.php',
			'step1.php',
			'step2.php',
			'step3.php'
		),
	),
);

if (\Bitrix\Main\Loader::includeModule('forum')) {
	$arServices['forum'] = [
		'NAME' => GetMessage('SERVICE_FORUM'),
	];
}

// if (\Bitrix\Main\Loader::includeModule('search')) {
// 	$arServices['search'] = [
// 		'NAME' => GetMessage('SERVICE_SEARCH'),
// 		'STAGES' => array(
// 			'search.php',
// 		),
// 	];
// }


$arExtModules = [
	'aspro.smartseo',
	'aspro.popup',
];
foreach ($arExtModules as $moduleId) {
	if (!\Bitrix\Main\Loader::includeModule($moduleId) || !empty($_SESSION['aspro_ext_'.$moduleId])) {
		$moduleShortCode = str_replace('aspro.', '', $moduleId);

		$arServices[$moduleId] = [
			'NAME' => GetMessage('EXT_SERVICE_PREPARE_DATA', ['#EXT_MODULE_ID#' => $moduleId]),
			'MODULE_ID' => 'fileman',
			'STAGES' => array(
				'check.php',
				'clear.php',
				'download.php',
				'unzip.php',
				'copy.php',
				'setup.php',
				'custom.php',
			),
		];
	}
}

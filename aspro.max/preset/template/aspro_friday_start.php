<table class="mail-grid" width="600" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<table class="mail-grid-cell"   width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
				<tr>
					<td data-bx-block-editor-place="body">

						<!-- image -->
						<div data-bx-block-editor-block-type="image">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" class="bxBlockImage">
								<tbody class="bxBlockOut">
								<tr>
									<td valign="top" class="bxBlockInn bxBlockInnImage">
										<table align="left" border="0" cellpadding="0" cellspacing="0" width="100%">
											<tbody>
											<tr>
												<td valign="top" class="bxBlockContentImage" style="text-align: center">
													<a href="/">
														<img align="center" data-bx-editor-def-image="0" src="/bitrix/images/aspro.max/preset/friday_start.jpg" class="bxImage">
													</a>
												</td>
											</tr>
											</tbody>
										</table>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
						<!-- /image  -->

						<div data-bx-block-editor-block-type="text">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" class="bxBlockText">
								<tbody class="bxBlockOut">
									<tr>
										<td valign="top" class="bxBlockInn bxBlockInnText">
											<table align="left" border="0" cellpadding="0" cellspacing="0" width="100%">
												<tbody>
													<tr>
														<td valign="top" class="bxBlockPadding bxBlockContentText">
															%FRIDAY_START_TEXT%
														</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
								</tbody>
							</table>
						</div>

						<div data-bx-block-editor-block-type="component"><div class="bxBlockPadding"><?EventMessageThemeCompiler::includeComponent(	"bitrix:catalog.show.products.mail",	"aspro_goods",	Array(		"CATALOG_PAGE" => "/catalog/",		"COMPOSITE_FRAME_MODE" => "A",		"COMPOSITE_FRAME_TYPE" => "AUTO",		"CONVERT_CURRENCY" => "N",		"LIST_ITEM_ID" => array(""),		"SHOW_CATALOG" => "N",		"SITE_ADDRESS" => "%SITE_ADDRESS%",		"SITE_ID" => "%SITE_ID%",		"THEME_COLOR" => "",		"TITLE" => "%FRIDAY_GOODS_TITLE%"	));?></div></div>			

						<div data-bx-block-editor-block-type="button">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" class="bxBlockButton">
								<tbody class="bxBlockOut">
									<tr>
										<td valign="top" class="bxBlockPadding bxBlockInn bxBlockInnButton">
											<table align="center" border="0" cellpadding="0" cellspacing="0" style="background: rgb(0, 0, 0); border-radius: 4px;" class="bxBlockContentButtonEdge">
												<tbody>
													<tr>
														<td valign="top">
															<a class="bxBlockContentButton" style="background:inherit" title="%BUTTON%" href="/catalog/" target="_blank">
																%FRIDAY_START_BUTTON%
															</a>
														</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
								</tbody>
							</table>
						</div>

						<div data-bx-block-editor-block-type="component">
							<?EventMessageThemeCompiler::includeComponent(
								"bitrix:news.list.mail",
								"aspro_tizers",
								Array(
									"ACTIVE_DATE_FORMAT" => "d.m.Y",
									"CACHE_FILTER" => "N",
									"CACHE_GROUPS" => "N",
									"CACHE_TIME" => "3600",
									"CACHE_TYPE" => "A",
									"CHECK_DATES" => "Y",
									"COMPOSITE_FRAME_MODE" => "A",
									"COMPOSITE_FRAME_TYPE" => "AUTO",
									"DETAIL_URL" => "",
									"DISPLAY_DATE" => "Y",
									"DISPLAY_NAME" => "Y",
									"DISPLAY_PICTURE" => "Y",
									"DISPLAY_PREVIEW_TEXT" => "Y",
									"FIELD_CODE" => array("", ""),
									"FILTER_NAME" => "",
									"HIDE_LINK_WHEN_NO_DETAIL" => "N",
									"SITE_ADDRESS" => "%SITE_ADDRESS%",
									"IBLOCK_ID" => "%TIZER_IBLOCK_ID%",
									"IBLOCK_TYPE" => "aspro_optimus_content",
									"INCLUDE_SUBSECTIONS" => "Y",
									"NEWS_COUNT" => "3",
									"PARENT_SECTION" => "",
									"PARENT_SECTION_CODE" => "",
									"PREVENT_SEND_IF_NO_NEWS" => "N",
									"PREVIEW_TRUNCATE_LEN" => "",
									"PROPERTY_CODE" => array("", ""),
									"SENDER_CHAIN_ID" => "{#SENDER_CHAIN_ID#}",
									"SORT_BY1" => "ACTIVE_FROM",
									"SORT_BY2" => "SORT",
									"SORT_ORDER1" => "DESC",
									"SORT_ORDER2" => "ASC"
								),
								false
							);?>
						</div>
						
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?
/**
 * Aspro:Light
 * @copyright 2022 Aspro
 */

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\SystemException,
	CMax as Solution;

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$lang = isset($_REQUEST['lang']) ? trim(htmlspecialcharsbx($_REQUEST['lang'])) : LANGUAGE_ID;
Loc::setCurrentLang($lang);
Loc::loadMessages(__FILE__);

$siteId = isset($_REQUEST['siteId']) ? trim(htmlspecialcharsbx($_REQUEST['siteId'])) : false;
$siteDir = isset($_REQUEST['siteDir']) ? trim(htmlspecialcharsbx($_REQUEST['siteDir'])) : false;

try {
	$bDemo = false;
	if(
		Loader::includeModule('aspro.sharepreset') &&
		(
			strpos($_SERVER['SERVER_NAME'], Solution::solutionName.'-demo.ru') !== false ||
			strpos($_SERVER['SERVER_NAME'], 'dev.aspro.ru') !== false
		)
	){
		$bDemo = true;
	}

	if(!Loader::includeModule(Solution::moduleID)){
		throw new SystemException(Loc::getMessage('TA_T_ERROR_MODULE_REQUIRED', array(
			'#MODULE_ID#' => Solution::moduleID,
		)));
	}

	?>
	<?// uniform.js?>
	<script src="<?=(file_exists($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/js/jquery.uniform.min.js') ? SITE_TEMPLATE_PATH.'/js/jquery.uniform.min.js' : SITE_TEMPLATE_PATH.'/js/jquery.uniform.min.js')?>" type="text/javascript"></script>
	<?

	if(
		!$siteId ||
		!$siteDir
	){
		throw new SystemException(Loc::getMessage('TA_T_ERROR_BAD_SITE_PARAMS'));
	}

	$arFrontParametrs = Solution::GetFrontParametrsValues($siteId, $siteDir, false);
	if($arFrontParametrs['THEME_SWITCHER'] !== 'Y'){
		throw new SystemException(Loc::getMessage('TA_T_ERROR_SWITCHER_NOT_ACTIVE'));
	}
	?>
	<div class="scrollblock">
		<div class="form">
			<?ob_start();?>
			<div class="sharepreset-blocks" style="display:none;">
				<div class="sharepreset-blocks-inner">
					<div class="sharepreset-blocks__actions">
						<a href="" rel="nofollow" class="dark_link sharepreset-blocks__action sharepreset-blocks__action--select-all"><span class="dotted"><?=Loc::getMessage('TA_T_EXPORT_BLOCKS_SELECT_ALL')?></span></a>
						<a href="" rel="nofollow" class="dark_link sharepreset-blocks__action sharepreset-blocks__action-reset-all"><span class="dotted"><?=Loc::getMessage('TA_T_EXPORT_BLOCKS_RESET_ALL')?></span></a>
					</div>
					<div class="options">
						<?$arBlockCodes = array();?>
						<?foreach(Solution::$arParametrsList as $blockCode => $arBlock):?>
							<?if($arBlock['THEME'] === 'Y'):?>
								<?$arBlockCodes[] = $blockCode;?>
								<div class="link-item animation-boxs current" data-option-value="<?=$blockCode?>"><span class="title"><?=$arBlock['TITLE']?></span></div>
							<?endif;?>
						<?endforeach;?>
						<input type="hidden" name="blocks" id="SHARE_BLOCKS" value="<?=implode(',', $arBlockCodes)?>" maxlength="1000" />
					</div>
				</div>
			</div>
			<?$htmlBlocks = ob_get_clean();?>

			<div class="sharepreset-part sharepreset-part--export">
				<div class="content-body">
					<div class="style-switcher-line-block style-switcher-flexbox--justify-beetwen">
						<div class="style-switcher-line-block__item">
							<div class="sharepreset-title"><?=Solution::showIconSvg('share-export', dirname($_SERVER['SCRIPT_NAME']).'/images/svg/share-export.svg');?><?=Loc::getMessage('TA_T_EXPORT_TITLE')?></div>
						</div>
						<div class="style-switcher-line-block__item">
							<a href="" rel="nofollow" class="sharepreset-blocks-toggle dark_link">
								<span><?=Loc::getMessage(
									'TA_T_EXPORT_BLOCKS_TOGGLE',
									array(
										'#COUNT#' => count($arBlockCodes),
										'#ALLCOUNT#' => count($arBlockCodes),
									)
								)?></span><?=Solution::showIconSvg('share-arrow-down', dirname($_SERVER['SCRIPT_NAME']).'/images/svg/share-arrow-down.svg');?>
							</a>
						</div>
					</div>

					<form action="<?=$_SERVER['REQUEST_URI']?>" method="post">
						<input type="hidden" name="siteId" value="<?=$siteId?>" />
						<input type="hidden" name="siteDir" value="<?=$siteDir?>" />
						<input type="hidden" name="lang" value="<?=$lang?>" />
						<input type="hidden" name="moduleId" value="<?=Solution::moduleID?>" />
						<input type="hidden" name="charset" value="<?=SITE_CHARSET?>" />

						<div class="alert alert-danger sharepreset-error" role="alert"></div>

						<?=$htmlBlocks?>

						<div class="item groups-tab">
							<div class="tabs bottom-line" enctype="multipart/form-data">
								<ul class="nav nav-tabs">
									<?if($bDemo):?>
										<li class="active"><a href="#TAB_EXPORT_LINK" data-toggle="tab" class="linked colored_theme_hover_text"><?=Loc::getMessage('TA_T_LINK_TAB')?></a></li>
									<?endif;?>

									<li class="<?=($bDemo ? '' : 'active')?>"><a href="#TAB_EXPORT_FILE" data-toggle="tab" class="linked colored_theme_hover_text"><?=Loc::getMessage('TA_T_EXPORT_FILE_TAB')?></a></li>
								</ul>
							</div>
							<div class="tab-content">
								<?if($bDemo):?>
									<div class="tab-pane active" id="TAB_EXPORT_LINK">
										<div class="form-control sharepreset-control--hidden">
											<input type="text" name="link" value="" autocomplete="off" readonly />
										</div>
										<div class="sharepreset-buttons">
											<button class="btn btn-default btn-lg btn-wide sharepreset-button sharepreset-control--visible" name="action" value="exportToLink"><?=Loc::getMessage('TA_T_EXPORT_GENERATE_BUTTON')?></button>
											<div class="btn btn-default btn-lg btn-wide sharepreset-button sharepreset-button--copy sharepreset-control--hidden"><?=Loc::getMessage('TA_T_EXPORT_COPY_BUTTON')?></div>
										</div>
										<div class="sharepreset-hint"><?=Loc::getMessage('TA_T_EXPORT_HINT')?></div>
									</div>
								<?endif;?>

								<div class="tab-pane<?=($bDemo ? '' : ' active')?>" id="TAB_EXPORT_FILE">
									<div class="sharepreset-buttons">
										<button class="btn btn-default btn-lg btn-wide sharepreset-button" name="action" value="exportToFile"><?=Loc::getMessage('TA_T_EXPORT_DOWNLOAD_BUTTON')?></button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="sharepreset-part sharepreset-part--import">
				<div class="content-body">
					<div class="sharepreset-title"><?=Solution::showIconSvg('share-import', dirname($_SERVER['SCRIPT_NAME']).'/images/svg/share-import.svg');?><?=Loc::getMessage('TA_T_IMPORT_TITLE')?></div>

					<form action="<?=$_SERVER['REQUEST_URI']?>" method="post">
						<input type="hidden" name="siteId" value="<?=$siteId?>" />
						<input type="hidden" name="siteDir" value="<?=$siteDir?>" />
						<input type="hidden" name="lang" value="<?=$lang?>" />
						<input type="hidden" name="moduleId" value="<?=Solution::moduleID?>" />
						<input type="hidden" name="charset" value="<?=SITE_CHARSET?>" />

						<div class="alert alert-danger sharepreset-error" role="alert"></div>

						<div class="item groups-tab">
							<div class="tabs bottom-line">
								<ul class="nav nav-tabs">
									<li class="active"><a href="#TAB_IMPORT_LINK" data-toggle="tab" class="linked colored_theme_hover_text"><?=Loc::getMessage('TA_T_LINK_TAB')?></a></li>
									<li><a href="#TAB_IMPORT_FILE" data-toggle="tab" class="linked colored_theme_hover_text"><?=Loc::getMessage('TA_T_IMPORT_FILE_TAB')?></a></li>
								</ul>
							</div>
							<div class="tab-content">
								<div class="tab-pane active" id="TAB_IMPORT_LINK">
									<div class="form-control">
										<input type="text" name="link" value="" required autocomplete="off" placeholder="<?=Loc::getMessage('TA_T_IMPORT_LINK_PLACEHOLDER')?>" maxlength="50" />
									</div>
									<div class="sharepreset-buttons">
										<button class="btn btn-default btn-lg btn-wide sharepreset-button" name="action" value="importFromLink"><?=Loc::getMessage('TA_T_IMPORT_APPLY')?></button>
									</div>
								</div>
								<div class="tab-pane" id="TAB_IMPORT_FILE">
									<div class="form-control">
										<input type="file" name="file" value="" class="json_extension filesize_100k" required />
									</div>
									<div class="sharepreset-buttons">
										<button class="btn btn-default btn-lg btn-wide sharepreset-button" name="action" value="importFromFile"><?=Loc::getMessage('TA_T_IMPORT_APPLY')?></button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
			<script>
			BX.loadScript(
				'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.16/clipboard.min.js',
				function(){
					$(document).ready(function(){
						var $btnCopyLink = $('.sharepreset-button--copy');
						if($btnCopyLink.length){
							var copyLinkTimeout = false;
							clipboard = new Clipboard('.sharepreset-button--copy');
							clipboard.on('success', function(e){
								$btnCopyLink.html(BX.message('TA_T_EXPORT_COPYED'));

								if(copyLinkTimeout){
									clearTimeout(copyLinkTimeout);
									copyLinkTimeout = false;
								}

								copyLinkTimeout = setTimeout(function(){
									$btnCopyLink.html(BX.message('TA_T_EXPORT_COPY_BUTTON'));
								}, 2000);

								e.clearSelection();
							});

							clipboard.on('error', function(e){
								alert(BX.message('TA_T_ERROR_LINK_COPY') + $btnCopyLink.attr('data-clipboard-text'))
							});
						}
					});
				}
			);

			BX.message({
				TA_T_IMPORT_FILE_PLACEHOLDER: '<?=Loc::getMessage('TA_T_IMPORT_FILE_PLACEHOLDER')?>',
				TA_T_ERROR_VALIDATE_JSON_EXT: '<?=Loc::getMessage('TA_T_ERROR_VALIDATE_JSON_EXT')?>',
				TA_T_ERROR_VALIDATE_LARGE_FILESIZE_100K: '<?=Loc::getMessage('TA_T_ERROR_VALIDATE_LARGE_FILESIZE_100K')?>',
				TA_T_EXPORT_BLOCKS_TOGGLE: '<?=Loc::getMessage('TA_T_EXPORT_BLOCKS_TOGGLE')?>',
				TA_T_EXPORT_COPY_BUTTON: '<?=Loc::getMessage('TA_T_EXPORT_COPY_BUTTON')?>',
				TA_T_EXPORT_COPYED: '<?=Loc::getMessage('TA_T_EXPORT_COPYED')?>',
				TA_T_ERROR_LINK_COPY: '<?=Loc::getMessage('TA_T_ERROR_LINK_COPY')?>',
			});

			$(document).on('click', '.sharepreset-blocks-toggle', function(e){
				e.preventDefault();
				$(this).toggleClass('sharepreset-blocks-toggle--open');
				$('.sharepreset-blocks').slideToggle();
			});

			$(document).on('click', '.sharepreset-blocks__action--select-all', function(e){
				e.preventDefault();
				$('.sharepreset-blocks .options .link-item').addClass('current');

				var codes = [];
				$('.sharepreset-blocks .options .link-item.current').map(function(i, e){codes.push($(e).data('option-value'))});
				$('.sharepreset-blocks .options input').val(codes.join(','));

				$('.sharepreset-blocks-toggle span').text(
					BX.message('TA_T_EXPORT_BLOCKS_TOGGLE').replace('#COUNT#', codes.length).replace('#ALLCOUNT#', $('.sharepreset-blocks .options .link-item').length)
				);

				$('.sharepreset-part--export').removeClass('sharepreset-part--exported2Link');
			});

			$(document).on('click', '.sharepreset-blocks__action-reset-all', function(e){
				e.preventDefault();
				$('.sharepreset-blocks .options .link-item').removeClass('current');
				$('.sharepreset-blocks .options input').val('');

				$('.sharepreset-blocks-toggle span').text(
					BX.message('TA_T_EXPORT_BLOCKS_TOGGLE').replace('#COUNT#', 0).replace('#ALLCOUNT#', $('.sharepreset-blocks .options .link-item').length)
				);

				$('.sharepreset-part--export').removeClass('sharepreset-part--exported2Link');
			});

			$(document).on('click', '.sharepreset-blocks .options .link-item', function(e){
				e.preventDefault();
				$(this).toggleClass('current');

				var codes = [];
				$('.sharepreset-blocks .options .link-item.current').map(function(i, e){codes.push($(e).data('option-value'))});
				$('.sharepreset-blocks .options input').val(codes.join(','));

				$('.sharepreset-blocks-toggle span').text(
					BX.message('TA_T_EXPORT_BLOCKS_TOGGLE').replace('#COUNT#', codes.length).replace('#ALLCOUNT#', $('.sharepreset-blocks .options .link-item').length)
				);

				$('.sharepreset-part--export').removeClass('sharepreset-part--exported2Link');
			});

			$(document).ready(function(){
				// $('.style-switcher .contents.share').mCustomScrollbar({
				// 	mouseWheel: {
				// 		scrollAmount: 150,
				// 		preventDefault: true
				// 	}
				// });

				$('.style-switcher .sharepreset-part input[type=file]').uniform({
					fileButtonHtml: BX.message('TA_T_IMPORT_FILE_PLACEHOLDER'),
					fileDefaultHtml: BX.message('TA_T_IMPORT_FILE_PLACEHOLDER')
				});

				$('.sharepreset-part--export input[name=link]').click(function(){
					$(this).select();
				});

				$forms = $('.style-switcher .sharepreset-part form');
				if($forms.length){
					var $submitButton = false;

					$('.style-switcher .sharepreset-part form .sharepreset-button[name][value]').click(function(){
						$submitButton = $(this);
					});

                    BX.Aspro.Loader.addExt('validate').then(() => {
                        if(typeof $.validator === 'function'){
                            $.validator.addMethod(
                                'json_extension',
                                function (value, element, param) {
                                    return this.optional(element) || value.match(new RegExp('.json$', 'i'));
                                },
                                BX.message('TA_T_ERROR_VALIDATE_JSON_EXT')
                            );

                            $.validator.addMethod(
                                'filesize_100k',
                                function (value, element, param) {
                                    return this.optional(element) || element.files[0].size <= 102400;
                                },
                                BX.message('TA_T_ERROR_VALIDATE_LARGE_FILESIZE_100K')
                            );

                            $.validator.addClassRules({
                                json_extension: {
                                    json_extension: '',
                                },
                                filesize_100k: {
                                    filesize_100k: '',
                                },
                            });

                            $forms.each(function(){
                                var $form = $(this);

                                $form.validate({
                                    highlight: function (element){
                                        $(element).parent().addClass('error');
                                    },
                                    unhighlight: function (element){
                                        $(element).parent().removeClass('error');
                                    },
                                    submitHandler: function (form){
                                        if($form.valid()){
                                            if(!$form.closest('.form').hasClass('sending')){
                                                $form.closest('.form').addClass('sending');
                                                $('.sharepreset-error').hide();

                                                var action = $submitButton ? $submitButton.attr('value') : '';
                                                if(!action){
                                                    return false;
                                                }

                                                var siteId = $form.find('input[name=siteId]').val();
                                                var siteDir = $form.find('input[name=siteDir]').val();
                                                var lang = $form.find('input[name=lang]').val();
                                                var moduleId = $form.find('input[name=moduleId]').val();
                                                var charset = $form.find('input[name=charset]').val();

                                                var fd = new FormData();
                                                fd.append('siteId', siteId);
                                                fd.append('siteDir', siteDir);
                                                fd.append('moduleId', moduleId);
                                                fd.append('charset', charset);
                                                fd.append('front', 1);
                                                fd.append('sessid', BX.message('bitrix_sessid'));

                                                if(action === 'importFromLink'){
                                                    fd.append('link', $form.find('input[name=link]').val());
                                                }
                                                else if(action === 'importFromFile'){
                                                    var fileInput = $form.find('input[name=file]')[0];
                                                    var files = fileInput.files || [fileInput.value];
                                                    fd.append('file', files[0]);
                                                }
                                                else if(
                                                    action === 'exportToLink' ||
                                                    action === 'exportToFile'
                                                ){
                                                    fd.append('blocks', $form.find('input[name=blocks]').val());
                                                }

                                                var moduleName = 'aspro:sharepreset';
                                                var componentName = '<?=Solution::partnerName?>:theme.<?=Solution::solutionName?>';

                                                if(action === 'exportToLink'){
                                                    var moduleAction = action;
                                                    var moduleActionFull = moduleName + '.api.sharepreset.' + moduleAction;
                                                    var promise = BX.ajax.runAction(
                                                        moduleActionFull,
                                                        {
                                                            data: fd
                                                        }
                                                    );
                                                }
                                                else{
                                                    var componentAction = action;
                                                    var promise = BX.ajax.runComponentAction(
                                                        componentName,
                                                        componentAction,
                                                        {
                                                            mode: 'ajax',
                                                            data: fd
                                                        }
                                                    );
                                                }

                                                promise.then(
                                                    function(response){
                                                        if(action === 'exportToLink'){
                                                            $form.closest('.form').removeClass('sending');

                                                            if(response.data.link){
                                                                $form.find('input[name=link]').val(response.data.link);
                                                                $('.sharepreset-button--copy').attr('data-clipboard-text', response.data.link);
                                                                $('.sharepreset-part--export').addClass('sharepreset-part--exported2Link');
                                                            }
                                                        }
                                                        else if(action === 'exportToFile'){
                                                            $form.closest('.form').removeClass('sending');

                                                            if(response.data.code){
                                                                location.href = '/bitrix/services/main/ajax.php?mode=ajax&c=' + encodeURIComponent(componentName) +'&action=downloadFile&sessid=' + BX.message('bitrix_sessid') + '&siteId=' + siteId + '&siteDir=' + siteDir + '&charset=' + charset + '&code=' + response.data.code;
                                                            }
                                                        }
                                                        else if(action === 'importFromLink'){
                                                            if(response.data.preset){
                                                                fd.append('preset', JSON.stringify(response.data.preset));

                                                                var promise = BX.ajax.runComponentAction(
                                                                    componentName,
                                                                    'importFromPreset',
                                                                    {
                                                                        mode: 'ajax',
                                                                        data: fd
                                                                    }
                                                                ).then(
                                                                    function(response){
                                                                        location.href = location.href;
                                                                    },
                                                                    function(response){
                                                                        console.error(response);
                                                                        var error = response.errors[0];

                                                                        $form.closest('.form').removeClass('sending');
                                                                        $form.find('.sharepreset-error').html(error.message).show();
                                                                    },
                                                                );
                                                            }
                                                        }
                                                        else if(action === 'importFromFile'){
                                                            location.href = location.href;
                                                        }
                                                    },
                                                    function(response){
                                                        console.error(response);
                                                        var error = response.errors[0];

                                                        $form.closest('.form').removeClass('sending');
                                                        $form.find('.sharepreset-error').html(error.message).show();
                                                    }
                                                );
                                            }
                                        }

                                        $submitButton = false;
                                    },
                                    errorPlacement: function (error, element){
                                        error.insertAfter(element)
                                    }
                                });
                            });
                        }
                    });

				}
			});
			</script>
		</div>
	</div>
	<?
}
catch(\Bitrix\Main\SystemException $e){
	?>
	<div class="content-body">
		<div class="alert alert-danger" role="alert"><?=$e->getMessage()?></div>
	</div>
	<?
}

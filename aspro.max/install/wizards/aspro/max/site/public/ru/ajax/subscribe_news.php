<?php
use Bitrix\Main\SystemException;

if (!include_once($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/vendor/php/solution.php')) {
    throw new SystemException('Error include solution constants');
}
$GLOBALS['APPLICATION']->ShowAjaxHead();

global $arTheme;
$licenceChecked = (CMax::GetFrontParametrValue('LICENCE_CHECKED') == 'Y' ? 'checked' : '');
$subscribePage = CMax::GetFrontParametrValue('SUBSCRIBE_PAGE_URL');
$showLicence = CMax::GetFrontParametrValue('SHOW_LICENCE');
?>
<?if (!$GLOBALS['bMobileForm']):?>
    <a href="#" class="close jqmClose"><?=CMax::showIconSvg('', SITE_TEMPLATE_PATH.'/images/svg/Close.svg');?></a>
<?endif;?>

<div class="form subscribe <?=$GLOBALS['bMobileForm'] ? 'mobile' : '';?>">
    <div class="form_head">
        <h2><?=GetMessage('SUBSCRIBE_TITLE');?></h2>
    </div>
    <div class="js_form">
        <form name="short_subscribe" action="<?=$APPLICATION->GetCurPage();?>" method="post" enctype="multipart/form-data" novalidate="novalidate">
            <?=bitrix_sessid_post();?>
            <input type="hidden" name="type" value="subscribe">
            <input type="hidden" name="note" value="Y">
            <input type="hidden" name="licenses_subscribe" value="Y">

            <div class="form_body">
                <div class="row" data-sid="SUBSCRIBE">
                    <div class="col-md-12">
                        <div class="form-control form-group animated-labels">
                            <label for="POPUP_EMAIL"><span><?=GetMessage('EMAIL');?>&nbsp;<span class="required-star">*</span></span></label>
                            <div class="input">
                                <input type="email" id="POPUP_EMAIL" class="form-control inputtext" data-sid="EMAIL" required name="EMAIL" value="" aria-required="true">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form_footer">
                <?if($showLicence == 'Y'):?>
                    <?
                    TSolution\Functions::showBlockHtml([
                        'FILE' => 'consent/userconsent.php',
                        'PARAMS' => [
                            'OPTION_CODE' => 'AGREEMENT_SUBSCRIBE',
                            'SUBMIT_TEXT' => GetMessage("SUBSCRIBE_PAGE"),
                            'REPLACE_FIELDS' => [],
                            'INPUT_NAME' => "licenses_popup",
                            'INPUT_ID' => 'licenses_popup_subscribe',
                        ]
                    ]);
                    ?>
                <?endif;?>

                <div class="line-block line-block--column line-block--align-flex-start line-block--24-vertical">
                    <div class="line-block__item">
                        <?$APPLICATION->IncludeFile(SITE_DIR.'include/required_message.php', [], ['MODE' => 'html']);?>
                    </div>

                    <div class="line-block__item width100">
                        <div class="buttons clearfix">
                            <button class="btn btn-default btn-lg pull-left" type="submit" value="<?=GetMessage('SUBSCRIBE_PAGE');?>" name="web_form_submit">
                                <?=GetMessage('SUBSCRIBE_PAGE');?>
                            </button>
                            <a class="settings font_upper pull-right dark-color" href="<?=$subscribePage;?>"><?=CMax::showIconSvg('', SITE_TEMPLATE_PATH.'/images/svg/gear.svg');?><?=GetMessage('SETTINGS');?></a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    BX.Aspro.Utils.readyDOM(() => {
        BX.Aspro.Loader.addExt('validate').then(() => {
            $('form[name="short_subscribe"]').validate({
                ignore: ".ignore",
                submitHandler: (form) => {
                    if ($('form[name="short_subscribe"]').valid()) {
                        $(form).find('button[type="submit"]').attr('disabled', 'disabled');

                        $.ajax({
                            url: arMaxOptions['SITE_DIR'] + 'ajax/subscribe_user.php',
                            data: {
                                'data': $(form).serialize()
                            },
                            type: 'POST',
                            success: (html) => {
                                $('.form .js_form').html(html);
                            }
                        })
                    }
                },
                errorPlacement: (error, element) => {
                    if ($(element).hasClass('captcha')) {
                        $(element).closest('.captcha-row').append(error);
                    } else if ($(element).closest('.licence_block').length) {
                        $(element).closest('.licence_block').append(error);
                    } else if($(element).closest('[data-sid=FILE]')){
                        $(element).closest('.form-group').append(error);
                    } else {
                        if ($(element).closest('.licence_block').length) {
                            $(element).closest('.licence_block').append(error);
                        } else if ($(element).closest('[data-sid=FILE]')) {
                            $(element).closest('.form-group').append(error);
                        } else {
                            error.insertAfter(element);
                        }
                    }
                },
                messages:{
                    licenses_popup: {
                        required : BX.message('JS_REQUIRED_LICENSES')
                    }
                }
            });
        });
    });
</script>

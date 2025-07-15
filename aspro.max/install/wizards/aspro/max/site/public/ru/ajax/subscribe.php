<?
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Localization\Loc;

$itemID = (int) $_GET['id'];
?>
<a href="#" class="close jqmClose"><?=CMax::showIconSvg('', SITE_TEMPLATE_PATH.'/images/svg/Close.svg');?></a>
<form class="form subform">
    <input type="hidden" name="manyContact" value="N">
    <?=bitrix_sessid_post();?>
    <input type="hidden" name="itemId" value="<?=$itemID;?>">
    <input type="hidden" name="siteId" value="s1">
    <input type="hidden" name="contactFormSubmit" value="Y">

    <div class="form_head">
        <h2><?=Loc::getMessage('SUBSCRIBE_ITEM');?></h2>
    </div>

    <div class="form_body">
        <div class="mess"></div>
        <div class="form-control onoff">
            <label><span><?=Loc::getMessage('SUBSCRIBE_ITEM_EMAIL');?>&nbsp;<span class="star">*</span></span></label>
            <input type="text" class="inputtext email" data-sid="CLIENT_NAME" required="" name="contact[1][user]" value="" aria-required="true">
        </div>
    </div>

    <div class="form_footer">
        <button type="submit" class="btn btn-default" value="<?=Loc::getMessage('SUBSCRIBE_SEND');?>" name="web_form_submit">
            <?=Loc::getMessage('SUBSCRIBE_SEND');?>
        </button>
    </div>
</form>
<script>
    $('input[name="siteId"]').val(arMaxOptions['SITE_ID']);
    BX.Aspro.Loader.addExt('validate').then(() => {
        $('form.subform').validate({
            submitHandler: (form) => {
                if ($('form.subform').valid()) {
                    setTimeout(() => {
                        $(form).find('button[type="submit"]').attr("disabled", "disabled");
                    }, 300);

                    BX.ajax.submitAjax($('form.subform')[0], {
                        method : 'POST',
                        url: '/bitrix/components/bitrix/catalog.product.subscribe/ajax.php',
                        processData : true,
                        onsuccess: (response) => {
                            resultForm = BX.parseJSON(response, {});
                            if (resultForm.success) {
                                const email = $('form.subform input.email').val();

                                $('form.subform .form_body').html('<div class="success">'+resultForm.message+'</div>');
                                $('form.subform .form_footer').html('');

                                getActualBasket();
                                $.ajax({
                                    url: arMaxOptions['SITE_DIR'] + 'ajax/subscribe_sync.php',
                                    dataType: "json",
                                    type: "POST",
                                    data: BX.ajax.prepareData({
                                        sessid: BX.bitrix_sessid(),
                                        subscribe: 'Y',
                                        itemId: '<?=$itemID;?>',
                                        itemEmail: email,
                                        siteId: arMaxOptions['SITE_ID']
                                    }),
                                    success: (id) =>  {

                                    },
                                })

                                $('.to-subscribe[data-item=<?=$itemID;?>]').hide();
                                $('.in-subscribe[data-item=<?=$itemID;?>]').show();

                            } else if (resultForm.error) {
                                let errorMessage = resultForm.message;
                                if (resultForm.hasOwnProperty('typeName')) {
                                    errorMessage = resultForm.message.replace('USER_CONTACT', resultForm.typeName);
                                }
                                $('form.subform .form_body .mess').text(errorMessage);
                            }
                        }
                    });
                }
            },
        });
    });
</script>

<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc,
    CMax as Solution,
    Aspro\Max\Thematics;

$moduleID = Solution::moduleID;
Bitrix\Main\Loader::includeModule($moduleID);

// ajax action

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    in_array($_POST['action'], array('download', 'delete'))
) {
    $APPLICATION->RestartBuffer();

    if (
        check_bitrix_sessid() &&
        !$bReadOnly
    ) {
        // ajax result
        $arResult = [];

        $action = $_POST['action'] ?: '';
        $step = $_POST['step'] ?: '';

        try {
            if ($action === 'download') {
                if ($step === 'check') {
                    $arFiles = Thematics::check([
                        'templateID' => $downloadTemplateId,
                        'moduleID' => $downloadModuleId,
                        'bSeparateModule' => true,
                    ]);

                    $arResult = [
                        'title' => Loc::getMessage('ASPRO_MODULE_CLEAR'),
                        'nextStep' => 'clear',
                        'procent' => 10,
                    ];
                } elseif ($step === 'clear') {
                    Thematics::clear();

                    $arResult = [
                        'title' => Loc::getMessage('ASPRO_MODULE_DOWNLOAD'),
                        'nextStep' => 'download',
                        'procent' => 20,
                    ];
                } elseif ($step === 'download') {
                    $result = Thematics::download([
                        'templateID' => $downloadTemplateId,
                        'moduleID' => $downloadModuleId,
                    ]);

                    if ($result['arDownloadFile']) {
                        $status = Loc::getMessage(
                            'ASPRO_MODULE_DOWNLOAD_PART',
                            $result['arStatus']
                        );

                        $arResult = [
                            'title' => $status,
                            'nextStep' => 'download',
                            'procent' => 20,
                        ];
                    } else {
                        $arResult = [
                            'title' => Loc::getMessage('ASPRO_MODULE_UNZIP'),
                            'nextStep' => 'unzip',
                            'procent' => 50,
                        ];
                    }
                } elseif ($step === 'unzip') {
                    $result = Thematics::unzip([
                        'templateID' => $downloadTemplateId,
                        'moduleID' => $downloadModuleId,
                    ]);

                    if ($result['unZipFile']) {
                        $status = Loc::getMessage(
                            'ASPRO_MODULE_UNZIP_PART',
                            $result['arStatus']
                        );

                        $arResult = [
                            'title' => $status,
                            'nextStep' => 'unzip',
                            'procent' => 50,
                        ];
                    } else {
                        $arResult = [
                            'title' => Loc::getMessage('ASPRO_MODULE_COPY'),
                            'nextStep' => 'copy',
                            'procent' => 80,
                        ];
                    }
                } elseif ($step === 'copy') {
                    $moduleDir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $downloadModuleId;

                    Thematics::copy([
                        'targetDir' => $moduleDir,
                        'moduleID' => $downloadModuleId,
                    ]);

                    $arResult = [
                        'title' => Loc::getMessage('ASPRO_MODULE_CLEAR_FINAL'),
                        'nextStep' => 'clear_final',
                        'procent' => 85,
                    ];
                } elseif ($step === 'clear_final') {
                    Thematics::clear();

                    $arResult = [
                        'title' => Loc::getMessage('ASPRO_MODULE_SETUP'),
                        'nextStep' => 'setup',
                        'procent' => 90,
                    ];
                } elseif ($step === 'setup') {
                    // check module index file
                    $indexFile = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $downloadModuleId . '/install/index.php';

                    if (@file_exists($indexFile)) {
                        include_once($indexFile);

                        $obModuleSeo = new $downloadModuleClass;
                        if (!$obModuleSeo->IsInstalled()) {
                            $obModuleSeo->DoInstall();
                            $arResult = [
                                'title' => Loc::getMessage('ASPRO_MODULE_FINISH'),
                                'nextStep' => 'finish',
                                'procent' => 100,
                            ];
                        }
                    } else {
                        throw new \Exception(Loc::getMessage('ASPRO_MODULE_ERROR_SETUP_INDEX'));
                    }
                }
            }
        } catch (\Exception $e) {
            $arResult['errors'] = $e->getMessage();
            $arResult['title'] = Loc::getMessage('ASPRO_MODULE_ERROR');
        }

        echo \Bitrix\Main\Web\Json::encode($arResult);
        die();
    }

    die();
}
?>
<? if ($RIGHT >= 'R') : ?>
    <script>
        $(document).ready(function() {
            function sendAction(action, step) {
                if (
                    action === 'download' || action === 'delete'
                ) {
                    var $form = $('.module-download-form');
                    if ($form.length) {
                        var data = {
                            sessid: $form.find('input[name=sessid]').val(),
                            action: action,
                            step: step
                        };

                        $.ajax({
                            type: 'POST',
                            data: data,
                            dataType: 'json',
                            success: function(jsonData) {
                                if (jsonData) {
                                    if (jsonData['errors']) {
                                        console.log(jsonData['errors']);
                                        $('.download-errors .adm-info-message-title').html(jsonData['errors']);
                                        $('.download-errors').show();
                                        $('.progress-download').hide();
                                    } else {
                                        if (jsonData['nextStep'] && jsonData['nextStep'] !== 'finish') {
                                            let nextStep = jsonData['nextStep'];
                                            sendAction(action, nextStep);
                                        }
                                        if (jsonData['procent']) {
                                            $('.progress-download__bar-inner').css('width', jsonData['procent'] + '%');
                                        }
                                        if (jsonData['title']) {
                                            $('.progress-download__title').html(jsonData['title']);
                                        }
                                        if (action === "download" && jsonData['nextStep'] === 'finish') {
                                            $('.module-info-block').show();
                                            $('.download-module-wrap').hide();
                                            location.href = $('.module-settings-link').attr('href');
                                        }
                                    }
                                }
                            },
                            error: function() {

                            }
                        });
                    }
                }
            }

            $(document).on('click', '.download-module', function() {
                $('.download-module-wrap').hide();
                $('.progress-download').show();
                sendAction('download', 'check');
            });
        });
    </script>
<? endif; ?>

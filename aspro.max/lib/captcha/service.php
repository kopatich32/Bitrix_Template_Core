<?php

namespace Aspro\Max\Captcha;

use Bitrix\Main\Application;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);

abstract class Service extends Base
{
    public const VALIDATE_URL = '';

    abstract public function getPublicKey(): string;

    abstract public function getPrivateKey(): string;

    public function getDefaultOptions(): array
    {
        $options = [
            'publicKey' => $this->getPublicKey(),
            'privateKey' => $this->getPrivateKey(),
            'excludePages' => implode(';', $this->getExcludePages()),
        ];

        return array_merge(
            parent::getDefaultOptions(),
            $options
        );
    }

    public function isActive(): bool
    {
        $bActive = $this->getPublicKey() && $this->getPrivateKey() && !$this->isExcludePage();

        return $bActive;
    }

    public function getExcludePages(): array
    {
        $pages = $this->options['excludePages'] ?? static::getModuleOption('CAPTCHA_SERVICE_EXCLUDE_PAGES', '', $this->getSiteId());
        $pages = explode(';', $pages);
        $pages = array_filter(
            array_map(
                function ($page) {
                    return trim($page);
                }, $pages
            )
        );

        return $pages;
    }

    public function isExcludePage(): bool
    {
        if (defined('ADMIN_SECTION')) {
            return true;
        }

        $arExcludePages = $this->getExcludePages();

        if ($arExcludePages) {
            $request = Application::getInstance()->getContext()->getServer();
            $url = $request['REAL_FILE_PATH'] ?: $request->getScriptName();
            $reg = '#^'.implode('|', $arExcludePages).'#i';

            return preg_match($reg, $url);
        }

        return false;
    }

    protected function mkPostData()
    {
        $application = Application::getInstance();
        $request = $application->getContext()->getRequest();
        $arPostData = $request->getPostList()->toArray();

        // sale.order.ajax component sends data in the key
        $keyPostData = null;
        $sid = $arPostData['captcha_sid'] ?? $arPostData['captcha_code'] ?? null;
        if (!isset($sid) && is_array($arPostData)) {
            foreach ($arPostData as $key => $value) {
                if (is_array($value)) {
                    $sid = $value['captcha_sid'] ?? $value['captcha_code'] ?? null;
                    if ($sid) {
                        $keyPostData = $key;
                        $arPostData = $value;
                        break;
                    }
                }
            }
        }

        return [$arPostData, $keyPostData];
    }

    protected function getSid(array $arPostData): ?string
    {
        $sid = $arPostData['captcha_sid'] ?? $arPostData['captcha_code'] ?? null;

        return $sid;
    }

    abstract public function getClientResponse(array $arPostData): ?string;

    protected function cleanWord()
    {
        if (isset($_REQUEST['captcha_word'])) {
            $_REQUEST['captcha_word'] = $_POST['captcha_word'] = '';
        }
    }

    protected function setWord(string $word, ?string $keyPostData)
    {
        if (is_null($keyPostData)) {
            $_REQUEST['captcha_word'] = $_POST['captcha_word'] = $word;
        } else {
            $_REQUEST[$keyPostData]['captcha_word'] = $_POST[$keyPostData]['captcha_word'] = $word;
        }
    }

    protected function getWordBySid(string $sid): string
    {
        $connection = Application::getConnection();
        $helper = $connection->getSqlHelper();
        $result = $connection->query("SELECT CODE FROM b_captcha WHERE id='".$helper->forSql($sid)."'")->fetch();

        return $result['CODE'] ?: '';
    }

    protected function reInitContext()
    {
        $application = Application::getInstance();
        $context = $application->getContext();
        $request = $context->getRequest();
        $server = $context->getServer();

        $httpRequest = new HttpRequest(
            $server,
            $request->getQueryList()->toArray(),
            $_POST,
            $request->getFileList()->toArray(),
            $request->getCookieList()->toArray()
        );

        $context->initialize(
            $httpRequest,
            $context->getResponse(),
            $server,
            [
                'env' => $context->getEnvironment(),
            ]
        );

        $application->setContext($context);
    }

    abstract public function getJsOptions(): array;

    abstract public function verifyClientResponse($clientResponse): bool;

    protected function addAssets()
    {
        $assets = Asset::getInstance();

        // add scripts
        $scriptsDir = $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.static::getModuleId().'/captcha/';
        foreach ([
            'recaptcha.min.js',
        ] as $script) {
            $path = File::isFileExists($scriptsDir.$script) ? $scriptsDir.$script : $scriptsDir.str_replace('.min.js', '.js', $script);
            $content = '<script>'.File::getFileContents($path).'</script>';
            $assets->addString($content);
        }

        // add global object asproRecaptcha
        $params = $this->getJsOptions();
        $content = "<script>window['asproRecaptcha'] = ".json_encode($params).';</script>';
        $content .= '<script>BX.Aspro.Captcha.init('.json_encode($params).');</script>';
        $assets->addString($content);
    }

    public function onPageStart()
    {
        parent::onPageStart();

        if (!$this->isActive()) {
            return;
        }

        $this->addAssets();
        $this->verify();
    }

    public function verify(): bool
    {
        $this->cleanWord();

        [$arPostData, $keyPostData] = $this->mkPostdata();
        $sid = $this->getSid($arPostData);
        $clientResponse = $this->getClientResponse($arPostData);

        if (
            !empty($sid)
            && !empty($clientResponse)
        ) {
            if ($this->verifyClientResponse($clientResponse)) {
                $word = $this->getWordBySid($sid);
                if ($word) {
                    $this->setWord($word, $keyPostData);
                    $this->reInitContext();

                    return true;
                }
            }
        }

        return false;
    }

    public function onEndBufferContent(&$content)
    {
        parent::onEndBufferContent($content);

        if (!$this->isActive()) {
            return;
        }

        $this->replace($content);
    }

    public function replace(&$content)
    {
        $this->replaceImg($content);
        $this->replaceInput($content);
        $this->replaceStrings($content);
    }

    public function replaceImg(&$content)
    {
        // replace src and style
        $contentReplace = preg_replace_callback(
            '!(<img\s[^>]*?src[^>]*?=[^>]*?)(\/bitrix\/tools\/captcha\.php\?(captcha_code|captcha_sid)=[0-9a-z]+)([^>]*?>)!',
            'static::replaceImgCallback',
            $content,
            -1,
            $count
        );

        if (
            $count <= 0
            || !$contentReplace
        ) {
            return;
        }

        $content = $contentReplace;
    }

    public static function replaceImgCallback(array $match)
    {
        $arImage = [
            'tag' => $match[1],
            'src' => $match[2],
            'tail' => $match[4],
        ];

        // remove style before src
        $arImage['tag'] = preg_replace('!style=("|\').*?("|\')!', '', $arImage['tag'], -1);

        // replace src
        if ($arImage['src']) {
            $arImage['src'] = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
        }

        // replace style
        if ($arImage['tail']) {
            $replaceImageStyle = 'style="display: none; opacity: 0; width: 0; height: 0; margin: 0;"';
            $subCount = 0;
            $arImage['tail'] = preg_replace('!style=("|\').*?("|\')!', $replaceImageStyle, $arImage['tail'], -1, $subCount);

            if (!$subCount) {
                $arImage['tail'] = preg_replace('/(\\/)?\\>/', "$replaceImageStyle />", $arImage['tail']);
            }
        }

        return implode('', $arImage);
    }

    abstract public function replaceInput(&$content);

    public function replaceStrings(&$content)
    {
        $content = str_replace(
            [
                Loc::getMessage('CAPTCHA_FORM_TITLE1'),
                Loc::getMessage('CAPTCHA_FORM_TITLE3'),
            ],
            Loc::getMessage('CAPTCHA_FORM_TITLE'),
            $content
        );
    }
}

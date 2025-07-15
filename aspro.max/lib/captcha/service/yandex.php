<?php

namespace Aspro\Max\Captcha\Service;

use Aspro\Max\Captcha\Service;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;

Loc::loadMessages(__FILE__);

class Yandex extends Service
{
    public const VERIFY_URL = 'https://smartcaptcha.yandexcloud.net/validate';

    public function getDefaultOptions(): array
    {
        $options = [
            'size' => $this->getSize(),
        ];

        if ($this->isInvisible()) {
            $options['showShield'] = $this->getShowShield();
            $options['shieldPosition'] = $this->getShieldPosition();
        }

        return array_merge(
            parent::getDefaultOptions(),
            $options
        );
    }

    public function getPublicKey(): string
    {
        $key = $this->options['publicKey'] ?? static::getModuleOption('YANDEX_SMARTCAPTCHA_PUBLIC_KEY', '', $this->getSiteId());

        return $key;
    }

    public function getPrivateKey(): string
    {
        $key = $this->options['privateKey'] ?? static::getModuleOption('YANDEX_SMARTCAPTCHA_PRIVATE_KEY', '', $this->getSiteId());

        return $key;
    }

    public function getSize(): ?string
    {
        $value = strtolower($this->options['size'] ?? static::getModuleOption('YANDEX_SMARTCAPTCHA_SIZE', 'NORMAL', $this->getSiteId()));

        return $value;
    }

    public function isInvisible(): bool
    {
        return $this->getSize() == 'invisible';
    }

    public function getShowShield(): ?string
    {
        $value = null;

        if ($this->isInvisible()) {
            $value = strtolower($this->options['showShield'] ?? static::getModuleOption('YANDEX_SMARTCAPTCHA_SHOW_SHIELD', 'NORMAL', $this->getSiteId()));
        }

        return $value;
    }

    public function getShieldPosition(): ?string
    {
        $value = null;

        if ($this->isInvisible()) {
            $value = strtolower($this->options['shieldPosition'] ?? static::getModuleOption('YANDEX_SMARTCAPTCHA_SHIELD_POSITION', 'BOTTOM-RIGHT', $this->getSiteId()));
        }

        return $value;
    }

    public function getClientResponse(array $arPostData): ?string
    {
        $clientResponse = $arPostData['smart-token'] ?? null;

        return $clientResponse;
    }

    public function getJsOptions(): array
    {
        $params = [
            'sitekey' => $this->getPublicKey(),
            'hl' => $this->getLang(),
            'callback' => 'onPassedCaptcha',
            'invisible' => $this->isInvisible(),
        ];

        if ($this->isInvisible()) {
            $params = array_merge(
                $params,
                [
                    'hideShield' => $this->getShowShield() === 'n',
                    'shieldPosition' => $this->getShieldPosition(),
                ]
            );
        }

        return [
            'type' => 'ya.smartcaptcha',
            'key' => $this->getPublicKey(), // legacy
            'params' => $params,
            'key' => $this->getPublicKey(),
        ];
    }

    public function verifyClientResponse($clientResponse): bool
    {
        try {
            if (
                !is_string($clientResponse)
                || !strlen($clientResponse)
            ) {
                throw new SystemException('Missing input parameter "token"');
            }

            $data = [
                'secret' => $this->getPrivateKey(),
                'token' => $clientResponse,
            ];
            $httpClient = new HttpClient();
            $response = $httpClient->post(static::VERIFY_URL, $data);

            if (empty($response)) {
                throw new SystemException('Wrong response, "json" expected');
            }

            $response = json_decode($response, true);
            if (!is_array($response)) {
                throw new SystemException('Wrong response, "json" expected');
            }

            if (($response['status'] ?? '') !== 'ok') {
                throw new SystemException($response['message'] ?? 'Captcha error');
            }

            return true;
        } catch (SystemException $exception) {
            $this->addError2EventLog($exception->getMessage());
        }

        return false;
    }

    public function replaceInput(&$content)
    {
        $publicKey = $this->getPublicKey();

        $i = 0;
        do {
            $uniqueId = randString(4);
            $content = preg_replace(
                '!<input\s[^>]*?name[^>]*?=[^>]*?captcha_word[^>]*?>!',
                "<div id='recaptcha-$uniqueId' class='smart-captcha' data-sitekey='$publicKey'></div><script>if (typeof renderCaptcha !== 'undefined') {renderCaptcha('recaptcha-$uniqueId');} else {parent.renderCaptcha('recaptcha-$uniqueId');}</script>".(!$i ? "<script>if (typeof BX !== 'undefined') {BX.onCustomEvent('onRenderCaptcha');} else {parent.BX.onCustomEvent('onRenderCaptcha');}</script>" : ''),
                $content,
                1,
                $count
            );
        } while ($count > 0);
    }
}

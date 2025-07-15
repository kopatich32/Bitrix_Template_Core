<?php

namespace Aspro\Max\Captcha\Service;

use Aspro\Max\Captcha\Service;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;

Loc::loadMessages(__FILE__);

class Google extends Service
{
    public const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public function getDefaultOptions(): array
    {
        $options = [
            'version' => $this->getVersion(),
        ];

        if ($this->getVersion() == 3) {
            $options['minScore'] = $this->getMinScore();
        } else {
            $options['size'] = $this->getSize();
            $options['color'] = $this->getColor();
            $options['showLogo'] = $this->getShowLogo();
            $options['badgePosition'] = $this->getBadgePosition();
        }

        return array_merge(
            parent::getDefaultOptions(),
            $options
        );
    }

    public function getPublicKey(): string
    {
        $key = $this->options['publicKey'] ?? static::getModuleOption('GOOGLE_RECAPTCHA_PUBLIC_KEY', '', $this->getSiteId());

        return $key;
    }

    public function getPrivateKey(): string
    {
        $key = $this->options['privateKey'] ?? static::getModuleOption('GOOGLE_RECAPTCHA_PRIVATE_KEY', '', $this->getSiteId());

        return $key;
    }

    public function getVersion(): string
    {
        $version = $this->options['version'] ?? static::getModuleOption('GOOGLE_RECAPTCHA_VERSION', '2', $this->getSiteId());

        return $version;
    }

    public function getSize(): ?string
    {
        $value = null;

        if ($this->getVersion() != 3) {
            $value = strtolower($this->options['size'] ?? static::getModuleOption('GOOGLE_RECAPTCHA_SIZE', 'NORMAL', $this->getSiteId()));
        }

        return $value;
    }

    public function getColor(): ?string
    {
        $value = null;

        if ($this->getVersion() != 3) {
            $value = strtolower($this->options['color'] ?? static::getModuleOption('GOOGLE_RECAPTCHA_COLOR', 'LIGHT', $this->getSiteId()));
        }

        return $value;
    }

    public function getShowLogo(): ?string
    {
        $value = null;

        if ($this->getVersion() != 3) {
            $value = strtolower($this->options['showLogo'] ?? static::getModuleOption('GOOGLE_RECAPTCHA_SHOW_LOGO', 'Y', $this->getSiteId()));
        }

        return $value;
    }

    public function getBadgePosition(): ?string
    {
        $value = null;

        if ($this->getVersion() != 3) {
            $value = strtolower($this->options['badgePosition'] ?? static::getModuleOption('GOOGLE_RECAPTCHA_BADGE', 'BOTTOMRIGHT', $this->getSiteId()));
        }

        return $value;
    }

    public function getMinScore(): ?float
    {
        $value = null;

        if ($this->getVersion() == 3) {
            $value = $this->options['minScore'] ?? static::getModuleOption('GOOGLE_RECAPTCHA_MIN_SCORE', 0.3, $this->getSiteId());
        }

        return $value;
    }

    public function getClientResponse(array $arPostData): ?string
    {
        $clientResponse = $arPostData['g-recaptcha-response'] ?? null;

        return $clientResponse;
    }

    public function getJsOptions(): array
    {
        $params = [
            'sitekey' => $this->getPublicKey(),
            'hl' => $this->getLang(),
            'callback' => 'onPassedCaptcha',
        ];

        $version = $this->getVersion();
        if ($version != 3) {
            $params = array_merge(
                $params,
                [
                    'theme' => $this->getColor() ?? '',
                    'showLogo' => $this->getShowLogo() ?? '',
                    'size' => $this->getSize() ?? '',
                    'badge' => $this->getBadgePosition() ?? '',
                ]
            );
        }

        return [
            'type' => 'g.recaptcha',
            'key' => $this->getPublicKey(), // legacy
            'ver' => $this->getVersion(),
            'params' => $params,
        ];
    }

    public function verifyClientResponse($clientResponse): bool
    {
        try {
            if (
                !is_string($clientResponse)
                || !strlen($clientResponse)
            ) {
                throw new SystemException('Missing input parameter "response"');
            }

            $data = [
                'secret' => $this->getPrivateKey(),
                'response' => $clientResponse,
            ];
            $httpClient = new HttpClient();
            $response = $httpClient->post(static::VERIFY_URL, $data);

            if (empty($response)) {
                throw new SystemException('Wrong argument type, "json" expected');
            }

            $response = json_decode($response, true);
            if (!is_array($response)) {
                throw new SystemException('Wrong response, "json" expected');
            }

            if (!empty($response['success'])) {
                if ($this->getVersion() == '3') {
                    if ($response['score'] < $this->getMinScore()) {
                        throw new SystemException('Low score');
                    }
                }
            } else {
                if (empty($response['error-codes'])) {
                    throw new SystemException('Wrong argument type, "array" expected');
                } else {
                    throw new SystemException(implode('<br>', $response['error-codes']));
                }
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
                "<div id='recaptcha-$uniqueId' class='g-recaptcha' data-sitekey='$publicKey'></div><script>if (typeof renderCaptcha !== 'undefined') {renderCaptcha('recaptcha-$uniqueId');} else {parent.renderCaptcha('recaptcha-$uniqueId');}</script>".(!$i ? "<script>if (typeof BX !== 'undefined') {BX.onCustomEvent('onRenderCaptcha');} else {parent.BX.onCustomEvent('onRenderCaptcha');}</script>" : ''),
                $content,
                1,
                $count
            );
        } while ($count > 0);
    }
}

<?php

namespace Aspro\Max\Captcha;

use Aspro\Max\Captcha;

abstract class Base extends Captcha
{
    protected static $instances;

    public static function getInstance(string $siteId = '', array $options = []): static
    {
        $siteId = $siteId ?: SITE_ID;

        if (!isset(static::$instances[$siteId])) {
            static::$instances[$siteId] = new static($siteId, $options);
        }

        return static::$instances[$siteId];
    }

    protected string $siteId;
    protected array $options;

    protected function __clone()
    {
    }

    protected function __wakeup()
    {
    }

    protected function __construct(string $siteId = '', array $options = [])
    {
        $this->siteId = $siteId ?: SITE_ID;

        $this->setDefaultOptions();
        $this->setOptions($options);
    }

    public function getDefaultOptions(): array
    {
        return [
            'lang' => $this->getLang(),
        ];
    }

    public function setDefaultOptions()
    {
        $this->options = [];
        $this->options = $this->getDefaultOptions();
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options = [])
    {
        if (array_key_exists('lang', $options)) {
            $options['lang'] = $options['lang'] ?? LANGUAGE_ID;
        }

        // It is important to note that default options may depend on other options.

        // current default options
        $oldDefaultOptions = $this->getDefaultOptions();

        // merge current options with new options
        $this->options = array_merge($this->options, $options);

        // calc new default options with dependences of merged options
        $newDefaultOptions = $this->getDefaultOptions();

        // old default options to remove - as diff of current & new default options
        $oldDefaultOptions2Remove = array_diff_key($oldDefaultOptions, $newDefaultOptions);

        // merge new default options with new options with some removed options
        $this->options = array_merge(
            $newDefaultOptions,
            array_diff_key($this->options, $oldDefaultOptions2Remove),
        );
    }

    public function getSiteId()
    {
        return $this->siteId;
    }

    public function setSiteId(string $siteId = '')
    {
        $this->siteId = $siteId ?: SITE_ID;

        $this->setOptions($this->getOptions());
    }

    public function getLang()
    {
        return ($this->options['lang'] ?? '') ?: LANGUAGE_ID;
    }

    public function setLang(string $lang = '')
    {
        $this->setOptions(
            [
                'lang' => $lang,
            ]
        );
    }

    public function isService(): bool
    {
        return $this instanceof Service;
    }

    public function addError2EventLog(string $message)
    {
        \CEventLog::Add([
            'SEVERITY' => 'WARNING',
            'AUDIT_TYPE_ID' => 'ASPRO_'.strtoupper(static::getModuleId()).'.RECAPTCHA_ERROR',
            'MODULE_ID' => static::getModuleId(),
            'ITEM_ID' => static::getModuleId(),
            'DESCRIPTION' => $message,
        ]);
    }

    public function onPageStart()
    {
    }

    public function onEndBufferContent(&$content)
    {
    }
}

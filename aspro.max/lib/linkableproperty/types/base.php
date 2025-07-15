<?php

namespace Aspro\Max\LinkableProperty\Types;

use Bitrix\Main\Localization\Loc;
use CMax as Solution;

Loc::loadMessages(__FILE__);

abstract class Base
{
    protected $separator = 'is';

    public function __construct(array $info)
    {
        $this->info = $info;

        $this->info['VALUE'] = (array) $this->info['VALUE'];
        $this->info['DISPLAY_VALUE'] = (array) $this->info['DISPLAY_VALUE'];
    }

    public function resolve($path)
    {
        if (!$this->isLinkable()) {
            return $this->getRawDisplayValue();
        }

        return $this->makeUrlWithPath($path);
    }

    private function isLinkable()
    {
        return $this->info['SMART_FILTER'] === 'Y';
    }

    private function makeUrlWithPath($path)
    {
        return array_map(function ($filteredValue, $value) use ($path) {
            $href = preg_replace('/\/{2,}/', '/', $this->makeFilterUrl($path, $filteredValue));

            return "<a href={$href}>{$value}</a>";
        }, $this->getFilteredValue(), $this->getValue());
    }

    private function makeFilterUrl($path, $value)
    {
        return str_replace('#SMART_FILTER_PATH#', $this->mkFilterValue($value), $path);
    }

    private function mkFilterValue($value)
    {
        $smartPart = [
            $this->getCodeOrId(),
            $this->getSeparator(),
            $value,
        ];

        return implode('-', $smartPart);
    }

    private function getCodeOrId()
    {
        return mb_strtolower($this->info['CODE'] ?: $this->info['ID']);
    }

    private function getSeparator()
    {
        return $this->separator;
    }

    protected function getFilteredValue()
    {
        return $this->getValue();
    }

    protected function getValue()
    {
        return $this->getRawValue();
    }

    protected function getRawValue()
    {
        return $this->info['VALUE'];
    }

    protected function getRawDisplayValue()
    {
        return $this->info['DISPLAY_VALUE'];
    }
}

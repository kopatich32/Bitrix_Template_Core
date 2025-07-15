<?php

namespace Aspro\Max\LinkableProperty\Types;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

abstract class Textbox extends Base
{
    protected function getFilteredValue()
    {
        return $this->getEncodedValue();
    }

    private function getEncodedValue()
    {
        $utf_id = \Bitrix\Main\Text\Encoding::convertEncoding($this->transformValueToLowerCase(), LANG_CHARSET, 'utf-8');

        return array_map('rawurlencode', $utf_id);
    }

    protected function transformValueToLowerCase()
    {
        return array_map('mb_strtolower', $this->getRawValue());
    }
}

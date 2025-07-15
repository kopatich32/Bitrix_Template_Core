<?php

namespace Aspro\Max\LinkableProperty\Types;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Property_e extends Textbox
{
    protected function getValue()
    {
        if (!is_array($this->info['LINK_ELEMENT_VALUE'])) return [];

        return array_map(fn($item) => $item['NAME'], $this->info['LINK_ELEMENT_VALUE']);
    }

    protected function getRawValue()
    {
        if (!is_array($this->info['LINK_ELEMENT_VALUE'])) return [];

        return array_map(fn($item) => $item['CODE'] ?: $item['ID'], $this->info['LINK_ELEMENT_VALUE']);
    }
}

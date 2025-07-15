<?php

namespace Aspro\Max\LinkableProperty\Types;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Property_l extends Textbox
{
    protected function getValue()
    {
        return $this->getRawDisplayValue();
    }

    protected function getRawValue()
    {
        return (array) $this->info['VALUE_XML_ID'];
    }
}

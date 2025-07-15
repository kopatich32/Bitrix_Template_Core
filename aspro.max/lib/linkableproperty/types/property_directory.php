<?php

namespace Aspro\Max\LinkableProperty\Types;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Property_directory extends Textbox
{
    protected function getValue()
    {
        return $this->getRawDisplayValue();
    }
}

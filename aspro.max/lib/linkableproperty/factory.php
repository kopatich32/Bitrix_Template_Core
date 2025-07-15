<?php

namespace Aspro\Max\LinkableProperty;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Factory
{
    private function __construct()
    {
    }

    private function __wakeup()
    {
    }

    public static function create(array $property): Types\Base
    {
        $className = sprintf(__NAMESPACE__.'\Types\property_%s', ucfirst(static::getType($property)));

        if (!class_exists($className)) {
            throw new \Exception(Loc::getMessage('ASPRO_MAX_TRANSFORM_FACTORY_UNKNOWN_PROPERTY_TYPE', ['#PROPERTY_TYPE#' => $property['PROPERTY_TYPE']]));
        }

        return new $className($property);
    }

    private static function getType($property)
    {
        $type = $property['USER_TYPE'] ?: $property['PROPERTY_TYPE'];

        return strtolower($type);
    }
}

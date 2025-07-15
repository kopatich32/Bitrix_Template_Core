<?

namespace Aspro\Max\Video;

class Inline
{
    protected static $videoProperties = ['VIDEO_YOUTUBE', 'VIDEO_FILE'];

    public static function getFilesFromProperties(array $arProperties): array
    {
        $result = [];
        foreach (self::$videoProperties as $property) {
            $arProperty = self::getFileFromProperty($arProperties, $property);
            if ($arProperty) {
                $result[$property] = $arProperty;
            }
        }

        return $result;
    }

    protected static function getFileFromProperty(array $arProperties, $property): array
    {
        $result = [];

        if (!empty($arProperties[$property]["~VALUE"])) {
            $values = $arProperties[$property]["~VALUE"];
            if (is_array($values)) {
                $result = array_merge($result, $values);
            } elseif (strlen($values)) {
                $result[] = $arProperties[$property]["~VALUE"];
            }
        }

        return $result;
    }
}

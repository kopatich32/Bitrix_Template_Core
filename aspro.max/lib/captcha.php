<?
namespace Aspro\Max;

use Bitrix\Main\Config\Option,
    CMax as Solution;

abstract class Captcha
{
    private static $instances;

    /**
     * Example:
     * <code>
     * // current site captcha type
     * $captcha = Aspro\Max\Captcha::getInstance();
     *
     * // custom site captcha type
     * $captcha = Aspro\Max\Captcha::getInstance('s1');
     *
     * // custom site captcha type & custom captcha options
     * $captcha = Aspro\Max\Captcha::getInstance('s1', ['lang' => 'en', 'version' => 3]);
     * </code>
     */
    public static function getInstance(string $siteId = '', array $options = []) :Captcha\Base {
        $siteId = $siteId ?: SITE_ID;

        if (!isset(static::$instances[$siteId])) {
            $type = static::getModuleOption('CAPTCHA_TYPE', 'BITRIX', $siteId);
            $classname = static::getClassnameByType($type);
            static::$instances[$siteId] = $classname::getInstance($siteId, $options);
        }

        return static::$instances[$siteId];
    }

    private static function getClassnameByType(string $type) :string {
        $classname = __NAMESPACE__.'\Captcha\Bitrix';

        if ($type === 'GOOGLE') {
            $classname = __NAMESPACE__.'\Captcha\Service\Google';
        }
        elseif ($type === 'YANDEX') {
            $classname = __NAMESPACE__.'\Captcha\Service\Yandex';
        }

        return $classname;
    }

    public static function getModuleId() :string {
        return Solution::moduleID;
    }

    public static function getModuleOption(string $name, string $default = '', bool|string $siteId = false) :string {
        $value = Option::get(static::getModuleId(), $name, $default, $siteId ?: SITE_ID);

        return $value;
    }
}

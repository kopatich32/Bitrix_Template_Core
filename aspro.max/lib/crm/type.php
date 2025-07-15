<?
namespace Aspro\Max\CRM;

class Type {
    const ACLOUD = 'ACLOUD';
    const FLOWLU = 'FLOWLU';
    const AMOCRM = 'AMO_CRM';

    public static function getAvailable() {
        return [
            static::ACLOUD,
            static::FLOWLU,
            static::AMOCRM,
        ];
    }

    public static function isAvailable(string &$type) {
        return (in_array($type, static::getAvailable()));
    }

    public static function getClass(string &$type, string $crmClass) {
        if (strlen($crmClass)) {
            $type = strtoupper($type);

            if (in_array($type, static::getAvailable())) {
                $typeClasses = [
                    static::ACLOUD => 'Acloud',
                    static::FLOWLU => 'Flowlu',
                    static::AMOCRM => 'Amocrm',
                ];
                
                $typeClass = $typeClasses[$type];

                if (strlen($typeClass)) {            
                    return __NAMESPACE__.'\\'.$typeClass.'\\'.$crmClass;
                }
            }
        }

        return '';
    }
}
<?
namespace Aspro\Max\CRM;

use Aspro\Max\CRM;

class Lead {
    public static function getClass(string $type) :string {
        return CRM\Type::getClass($type, 'Lead');
    }

    public static function newInstance(CRM\Base\Connection $connection) {
        $type = $connection->type;
        $className = static::getClass($type);
        if (strlen($className)) {
            if (class_exists($className)) {
                return new $className($connection);
            }

            throw new \Exception('Unknown class '.$className);
        }
        
        throw new \Exception('Invalid type '.$type);
    }
}

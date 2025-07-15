<?
namespace Aspro\Max\CRM\Base;

use Bitrix\Main\Localization\Loc,
    Aspro\Max\CRM;

Loc::loadMessages(__FILE__);

abstract class Lead {
    protected $id;
    protected $connection;
    public $values;
    
    abstract public static function getFieldsMap(CRM\Base\Connection $connection) :array;
    abstract public static function getCustomFieldsMap(CRM\Base\Connection $connection) :array;
    abstract public static function getUrl(int $id) :string;

    public static function getTitleField() :string {
        return 'name';
    }

    public function __construct(CRM\Base\Connection $connection) {
        $this->connection = $connection;
        $this->values = [];
    }

    public function __get($name) {
        switch ($name) {
            case 'id':
                return $this->id;
            case 'connection':
                return $this->connection;
        }
    }

    public function get(int $id) {
        if (intval($id) <= 0) {
            throw new \Exception('Invalid lead id '.$id);
        }

        $this->id = intval($id);
        $this->load();

        return $this;
    }

    abstract public function load();
    abstract public function create() :bool;
    abstract public function update() :bool;
    abstract public function delete() :bool;

    public function save() :bool {
        if ($this->id) {
            return $this->update();
        } else {
            return $this->create();
        }

        return false;
    }
}
<?
namespace Aspro\Max\CRM\Amocrm;

use Bitrix\Main\Localization\Loc,
    Aspro\Max\CRM;

Loc::loadMessages(__FILE__);

class Lead extends CRM\Base\Lead {
    public static function getFieldsMap(CRM\Base\Connection $connection) :array {
        $result = [];
        
        foreach (
            [
                'name',
                'description',
                'budget',
                'contact_name',
                'contact_email',
                'contact_phone',
                'contact_mobile',
                'contact_company',
                'contact_position',
                'contact_web',
                'start_date',
                'deadline',
                'closing_date',
                'closing_comment',
            ] as $field
        ) {
            $result[$field] = Loc::getMessage('CRM_LEAD_FIELD_'.$field);
        }

        return $result;
    }

    public static function getCustomFieldsMap(CRM\Base\Connection $connection) :array {
        return [];
    }

    protected static function getCustomFields(CRM\Base\Connection $connection) :array {
        return [];
    }

    public static function getTitleField() :string {
        return 'name_leads';
    }

    public static function getUrl(int $id) :string {
        return $id > 0 ? '' : '';
    }

    public function load() {
        $this->values = [];

        return $this;
    }

    public function create() :bool {
        return false;
    }

    public function update() :bool {
        return false;
    }

    public function delete() :bool {
        return false;
    }
}
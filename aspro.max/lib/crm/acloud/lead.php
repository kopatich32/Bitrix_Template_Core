<?
namespace Aspro\Max\CRM\Acloud;

use Bitrix\Main\Localization\Loc,
    Aspro\Max\CRM;

Loc::loadMessages(__FILE__);

class Lead extends CRM\Base\Lead
    implements \Aspro\Max\CRM\Base\Lead\iReferential {
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
        $result = [];
        
        $customFields = static::getCustomFields($connection);
        if ($customFields) {
            foreach ($customFields as $field) {
                if (
                    $field['active'] &&
                    in_array(
                        $field['type'], 
                        [
                            'smalltext',
                            'text',
                            'int',
                            'price',
                            'date',
                            'datetime',
                            'decimal',
                            'password',
                            'phone',
                            'percent',
                        ]
                    )
                ){
                    $result['cf_'.$field['id']] = $field['name'];
                }
            }
        }

        return $result;
    }

    protected static function getCustomFields(CRM\Base\Connection $connection) :array {
        $result = [];

        $customFieldset = static::getCustomFieldset($connection);
        if ($customFieldset) {
            $params = [
                'filter' => array(
                    'fieldset_id' => $customFieldset['id'],
                ),
            ];

            $response = $connection->api('/module/customfields/fields/list', $params);
            if (
                $response &&
                is_array($response) &&
                is_array($response['items'])
            ) {
                $result = $response['items'];
            }
        }

        return $result;
    }

    protected static function getCustomFieldset(CRM\Base\Connection $connection) :array {
        $result = [];

        $params = [
            'filter' => array(
                'module' => 'crm',
                'model' => 'leads',
            ),
        ];

        $response = $connection->api('/module/customfields/fieldsets/list', $params);
        if (
            $response &&
            is_array($response) &&
            is_array($response['items'])
        ) {
            if ($response['items']) {
                $result = array_values($response['items'])[0];
            }
        }

        return $result;
    }

    public static function getRefField() :string {
        return 'ref';
    }

    public static function getRefIdField() :string {
        return 'ref_id';
    }

    public static function getUrl(int $id) :string {
        return $id > 0 ? '/_module/crm/view/lead/'.$id : '';
    }

    public function load() {
        $this->values = [];

        if ($this->id) {
            $response = $this->connection->api('/module/crm/leads/get/'.$this->id);
            if (
                $response &&
                is_array($response)
            ) {
                $this->values = $response;
            }
        }

        return $this;
    }

    public function create() :bool {
        if ($this->id) {
            return false;
        }

        $response = $this->connection->api('/module/crm/lead/create', $this->values);
        if (
            $response &&
            is_array($response) &&
            $response['id']
        ) {
            $this->id = $response['id'];
            $this->values = [];
            
            return true;
        }

        return false;
    }

    public function update() :bool {
        if ($this->id) {
            $response = $this->connection->api('/module/crm/lead/update/'.$this->id, $this->values);
            if (
                $response &&
                is_array($response)
            ) {
                $this->values = $response;
                
                return true;
            }
        }

        return false;
    }

    public function delete() :bool {
        if ($this->id) {
            $response = $this->connection->api('/module/crm/lead/delete/'.$this->id);
            if (
                $response &&
                is_array($response)
            ) {
                $this->id = $this->values = null;
                
                return true;
            }
        }

        return false;
    }
}
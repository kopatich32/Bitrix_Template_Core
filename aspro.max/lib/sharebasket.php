<?
namespace Aspro\Max;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Fields\Relations\OneToMany,
    Bitrix\Main\ORM\Query\Join,
    Bitrix\Main\ORM\Fields\Validators,
    CMax as Solution;

Loc::loadMessages(__FILE__);

class ShareBasketTable extends DataManager{
    const CODE_LENGTH = 10;

    public static function getTableName(){
        return 'b_'.(str_replace('.', '_', Solution::moduleID).'_sharebasket');
    }

    public static function getMap(){
        return array(
            new Fields\IntegerField(
                'ID',
                array(
                    'primary' => true,
                    'autocomplete' => true
                )
            ),

            new Fields\StringField(
                'CODE',
                array(
                    'required' => true,
                    'validation' => array(__CLASS__, 'validateCode'),
                )
            ),

            new Fields\StringField(
                'HASH',
                array(
                    'required' => true,
                    'validation' => array(__CLASS__, 'validateHash'),
                )
            ),

            new Fields\DatetimeField(
                'DATE_CREATE',
                array(
                    'default_value' => new \Bitrix\Main\Type\DateTime,
                )
            ),

            new Fields\IntegerField(
                'CREATED_BY',
                array(
                    'validation' => array(__CLASS__, 'validateCreatedBy'),
                )
            ),

            new Fields\BooleanField(
                'PUBLIC',
                array(
                    'values' => array('N', 'Y')
                )
            ),

            new Fields\IntegerField(
                'USER_ID',
                array(
                    'validation' => array(__CLASS__, 'validateUserId'),
                )
            ),

            new Fields\StringField(
                'SITE_ID',
                array(
                    'required' => true,
                    'validation' => array(__CLASS__, 'validateSiteId'),
                )
            ),

            new Fields\IntegerField(
                'REGION_ID',
                array(
                    'validation' => array(__CLASS__, 'validateRegionId'),
                )
            ),

            // to remind
            new Fields\StringField(
                'REGION_NAME',
                array(
                    'validation' => array(__CLASS__, 'validateRegionName'),
                )
            ),

            (new OneToMany(
                'ITEMS',
                ShareBasketItemTable::class,
                'BASKET'
            ))->configureJoinType('inner'),
        );
    }

    public static function validateHash(){
        return array(
            new Validators\LengthValidator(null, 32),
        );
    }

    public static function validateCode(){
        return array(
            new Validators\LengthValidator(null, static::CODE_LENGTH),
            function($value){
                $result = static::getList(array(
                    'filter' => array('=CODE' => $value),
                    'limit' => 1,
                    'select' => array('ID'),
                ));

                if($result->fetch()){
                    return 'The value \''.$value.'\' of field CODE is not unique.';
                }

                return true;
            }
        );
    }

    public static function validateCreatedBy(){
        return array(
            new Validators\RangeValidator(0, null, false),
        );
    }

    public static function validateUserId(){
        return array(
            new Validators\RangeValidator(0, null, false),
        );
    }

    public static function validateSiteId(){
        return array(
            new Validators\LengthValidator(null, 2),
        );
    }

    public static function validateRegionId(){
        return array(
            new Validators\RangeValidator(0, null, false),
        );
    }

    public static function validateRegionName(){
        return array(
            new Validators\LengthValidator(null, 255),
        );
    }
}
?>
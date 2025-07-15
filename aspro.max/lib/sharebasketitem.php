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

class ShareBasketItemTable extends DataManager{
    public static function getTableName(){
        return 'b_'.(str_replace('.', '_', Solution::moduleID).'_sharebasket_item');
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

            new Fields\IntegerField(
                'BASKET_ID',
                array(
                    'required' => true,
                    'validation' => array(__CLASS__, 'validateBasketId'),
                )
            ),

            (new Reference(
                'BASKET',
                ShareBasketTable::class,
                Join::on('this.BASKET_ID', 'ref.ID')
            ))->configureJoinType('inner'),

            new Fields\FloatField(
                'QUANTITY',
                array(
                    'required' => true,
                    'validation' => array(__CLASS__, 'validateQuantity'),
                )
            ),

            new Fields\BooleanField(
                'DELAY',
                array(
                    'values' => array('N', 'Y')
                )
            ),

            new Fields\IntegerField(
                'PRODUCT_ID',
                array(
                    'required' => true,
                    'validation' => array(__CLASS__, 'validateProductId'),
                )
            ),

            // to remind
            new Fields\StringField(
                'NAME',
                array(
                    'required' => true,
                    'validation' => array(__CLASS__, 'validateName'),
                )
            ),

            new Fields\IntegerField(
                'SECTION_ID',
                array(
                    'validation' => array(__CLASS__, 'validateSectionId'),
                )
            ),

            // to remind
            new Fields\StringField(
                'SECTION_NAME',
                array(
                    'validation' => array(__CLASS__, 'validateSectionName'),
                )
            ),

            // to remind
            new Fields\FloatField(
                'BASE_PRICE',
                array(
                    'required' => true,
                    'validation' => array(__CLASS__, 'validatePrice'),
                )
            ),

            // to remind
            new Fields\FloatField(
                'PRICE',
                array(
                    'required' => true,
                    'validation' => array(__CLASS__, 'validatePrice'),
                )
            ),

            // to remind
            new Fields\FloatField(
                'DISCOUNT_PRICE',
                array(
                    'required' => true,
                )
            ),

            // to remind
            new Fields\FloatField(
                'FINAL_PRICE',
                array(
                    'required' => true,
                    'validation' => array(__CLASS__, 'validatePrice'),
                )
            ),

            // to remind
            new Fields\StringField(
                'CURRENCY',
                array(
                    'required' => true,
                    'validation' => array(__CLASS__, 'validateCurrency'),
                )
            ),

            // to remind
            new Fields\StringField(
                'MEASURE_NAME',
                array(
                    'validation' => array(__CLASS__, 'validateMeasureName'),
                )
            ),

            // to remind
            new Fields\FloatField(
                'RATIO',
                array(
                    'validation' => array(__CLASS__, 'validateRatio'),
                )
            ),

            // to remind
            new Fields\StringField(
                'ARTICLE',
                array(
                    'validation' => array(__CLASS__, 'validateArticle'),
                )
            ),

            // to remind
            new Fields\TextField(
                'BASKET_PROPS',
                array(
                    'save_data_modification' => array(__CLASS__, 'saveBasketProps'),
                    'fetch_data_modification' => array(__CLASS__, 'fetchBasketProps'),
                )
            ),
        );
    }

    public static function validateBasketId(){
        return array(
            new Validators\RangeValidator(0, null, true),
        );
    }

    public static function validateQuantity(){
        return array(
            new Validators\RangeValidator(0, null, true),
        );
    }

    public static function validateProductId(){
        return array(
            new Validators\RangeValidator(0, null, true),
        );
    }

    public static function validateName(){
        return array(
            new Validators\LengthValidator(null, 255),
        );
    }

    public static function validateSectionId(){
        return array(
            new Validators\RangeValidator(0, null, false),
        );
    }

    public static function validateSectionName(){
        return array(
            new Validators\LengthValidator(null, 255),
        );
    }

    public static function validatePrice(){
        return array(
            new Validators\RangeValidator(0, null, false),
        );
    }

    public static function validateCurrency(){
        return array(
            new Validators\LengthValidator(null, 3),
        );
    }

    public static function validateMeasureName(){
        return array(
            new Validators\LengthValidator(null, 15),
        );
    }

    public static function validateRatio(){
        return array(
            new Validators\RangeValidator(0, null, true),
        );
    }

    public static function validateArticle(){
        return array(
            new Validators\LengthValidator(null, 255),
        );
    }

    public static function saveBasketProps(){
        return array(
            function($value){
                if(
                    $value &&
                    is_array($value)
                ){
                    return serialize($value);
                }

                return '';
            }
        );
    }

    public static function fetchBasketProps(){
        return array(
            function($value){
                $value = trim($value);

                return $value;
            }
        );
    }
}
?>
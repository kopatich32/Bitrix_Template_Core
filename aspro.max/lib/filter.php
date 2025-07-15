<?php

namespace Aspro\Max;

class Filter {
    public static function getAvailableByStores(array $stores) :array {
        $arStoresFilter = [];
        
        if($stores){					
            $arTmpStoresFilter = array(
                'STORE_NUMBER' => $stores,
                '>STORE_AMOUNT' => 0,
            );

            $arStoresFilter = [
                'LOGIC' => 'OR',
                array('TYPE' => array('2','3')), //complects and offers
                $arTmpStoresFilter,
                'CAN_BUY_ZERO' => "Y", //for show product if it can buy
                // for bitrix Available logic you can use code below
                /* [
                    'LOGIC' => 'AND',
                    'QUANTITY_TRACE' => "Y",
                    'CAN_BUY_ZERO' => "Y",
                ],
                'QUANTITY_TRACE' => "N", */
            ];
        }

        return $arStoresFilter;
    }
}
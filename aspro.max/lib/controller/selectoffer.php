<?php

namespace Aspro\Max\Controller;

use \Bitrix\Main\Error;

use CMax as Solution,
    \Aspro\Max\Product\Sku,
    \Aspro\Max\Product\SkuTools;

class SelectOffer extends \Bitrix\Main\Engine\Controller
{
    public function configureActions()
    {
        return [
            'select' => [
				'prefilters' => [],
			],
        ];
    }

    /**
     * Place/remove emelent in favorites
     * @param array $params transfer params
     * @return array|null
     */
    public function selectAction($params): ?array
    {
        if (!check_bitrix_sessid()) {
            $this->addError(new Error('Wrong session id'));
        }

        array_walk_recursive($params, fn(&$param) => $param = htmlspecialcharsbx($param));

        if(isset($params['PARAMS'])){
            $params['PARAMS'] = SkuTools::getUnSignedParams($params['PARAMS']);
        }

        if ($this->getErrors()) {
            return null;
        }

        $arResult = Sku::getChangeSku($params);

        return $arResult;
    }
}

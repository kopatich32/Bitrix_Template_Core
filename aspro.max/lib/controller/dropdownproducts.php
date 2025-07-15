<?php

namespace Aspro\Max\Controller;

use \Bitrix\Main\Error,
    \Aspro\Max\Product\Dropdown;

class DropdownProducts extends \Bitrix\Main\Engine\Controller
{
    protected static $iblocks = [];

    public function configureActions()
    {
        return [
            'show' => [
				'prefilters' => [],
			],
        ];
    }

    /**
     * Show favorites
     * @param array $params transfer params
     * @return array|null
     */
    public function showAction($params): ?array
    {
        if (!check_bitrix_sessid()) {
            $this->addError(new Error('Wrong session id'));
        }

        if ($this->getErrors()) {
            return null;
        }

        $arResult = [];
        $type = $params['type'] === 'compare' ? 'compare' : 'favorite';

        $arResult = Dropdown::getItems($type);

        return $arResult;
    }
}

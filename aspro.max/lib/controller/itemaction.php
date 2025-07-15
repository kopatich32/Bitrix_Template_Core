<?php

namespace Aspro\Max\Controller;

use Aspro\Max\ItemAction\Compare;
use Aspro\Max\ItemAction\Favorite;
use Bitrix\Main\Error;
use CMax as Solution;

class ItemAction extends \Bitrix\Main\Engine\Controller
{
    public function configureActions()
    {
        return [
            'favorite' => [
                'prefilters' => [],
            ],
            'compare' => [
                'prefilters' => [],
            ],
        ];
    }

    /**
     * Place/remove emelent in favorites.
     *
     * @param array $fields transfer params
     */
    public function favoriteAction(array $fields): ?array
    {
        if (!check_bitrix_sessid()) {
            $this->addError(new Error('Wrong session id'));
        }

        $arItems = $this->getItems($fields);

        if ($this->getErrors()) {
            return null;
        }

        foreach ($arItems as $id) {
            if ($fields['state']) {
                Favorite::addItem($id);
            } else {
                Favorite::removeItem($id);
            }
        }

        $arResult['items'] = Favorite::getItems();
        $arResult['count'] = Favorite::getCount();
        $arResult['title'] = Favorite::getTitle();

        // update session for basket fly
        Solution::updateBasketCounters(['FAVORITE' => ['COUNT' => $arResult['count']]]);

        return $arResult;
    }

    /**
     * Place/remove emelent in compare.
     *
     * @param array $fields transfer params
     */
    public function compareAction(array $fields): ?array
    {
        if (!check_bitrix_sessid()) {
            $this->addError(new Error('Wrong session id'));
        }

        $arItems = $this->getItems($fields);

        if ($this->getErrors()) {
            return null;
        }

        foreach ($arItems as $id) {
            if ($fields['state']) {
                Compare::addItem($id);
            } else {
                Compare::removeItem($id);
            }
        }

        $arResult['items'] = Compare::getItems();
        $arResult['count'] = Compare::getCount();
        $arResult['title'] = Compare::getTitle();

        // update session for basket fly
        Solution::updateBasketCounters(['COMPARE' => ['COUNT' => $arResult['count']]]);

        return $arResult;
    }

    private function getItems(array $fields): array
    {
        $arItems = [];
        if ($fields['type'] === 'multiple') {
            foreach ((array) $fields['items'] as $arItem) {
                $arItems[] = intval(is_array($arItem) ? $arItem['id'] : $arItem);
            }
        } else {
            if ($id = $fields['item']) {
                $arItems = [$id];
            }
        }

        if (!$arItems) {
            $this->addError(new Error('Invalid items'));
        }

        return $arItems;
    }
}

<?php

namespace Aspro\Max\Template;

use Bitrix\Main\Localization\Loc;
use CMax as Solution;

class CatalogSort
{
    private const KEY = 'POPULARITY';
    private const SORT = 'SHOWS';
    private const ORDER = 'desc';

    private const DEFAULT_SORT_BUTTONS = [
        'SORT',
        'POPULARITY',
        'NAME',
        'PRICE',
        'QUANTITY',
        'RANK',
        'CUSTOM',
    ];

    protected array $availableSort = [];
    protected array $sortButtons = [];
    protected string $sortField = '';
    protected string $orderField = '';
    protected string $template = '';

    protected ?bool $showRankSort = null;

    protected string $sort;
    protected string $order;

    public function __construct(array $params = [], string $template = '', string $sortField = '', string $orderField = '')
    {
        $this->template = $template;
        $this->sort = $this->sortField = $sortField;
        $this->order = $this->orderField = $orderField;

        if (empty($params['SORT_BUTTONS'])) {
            return;
        }

        if ($params['IS_SEARCH']) {
            $this->showRankSort = $params['SHOW_SORT_RANK_BUTTON'] !== 'N';
        }

        if ($params['SORT_BUTTONS']) {
            $this->sortButtons = (array) $params['SORT_BUTTONS'];

            if ($this->showRankSort) {
                $this->sortButtons[] = 'RANK';
            }
        }

        if ($params['AVAILABLE_SORT']) {
            $this->availableSort = $params['AVAILABLE_SORT'];
        } else {
            $this->setAvailableSort($params);
        }

        if ($params['SORT']) {
            $this->sort = $params['SORT'];
        } else {
            $this->setSort();
        }

        if ($params['ORDER']) {
            $this->order = $params['ORDER'];
        } else {
            $this->setOrder();
        }

        $this->setCurrentSort();
    }

    private function __wakeup()
    {
    }

    private function __clone()
    {
    }

    protected function setAvailableSort(array $params)
    {
        $this->avaliableSort = [];

        $this->availableSort['SORT'] = [
            'KEY' => 'SORT',
            'SORT' => 'SORT',
            'ORDER_VALUES' => [
                'desc' => Loc::getMessage('SECT_SORT_SORT').Loc::getMessage('SECT_ORDER_DESC'),
                'asc' => Loc::getMessage('SECT_SORT_SORT').Loc::getMessage('SECT_ORDER_ASC'),
            ],
        ];

        $this->availableSort[static::KEY] = [
            'KEY' => static::KEY,
            'SORT' => static::SORT,
            'ORDER_VALUES' => [
                'desc' => Loc::getMessage('SECT_SORT_'.static::KEY).Loc::getMessage('SECT_ORDER_DESC'),
                'asc' => Loc::getMessage('SECT_SORT_'.static::KEY).Loc::getMessage('SECT_ORDER_ASC'),
            ],
        ];

        $this->availableSort['NAME'] = [
            'KEY' => 'NAME',
            'SORT' => 'NAME',
            'ORDER_VALUES' => [
                'desc' => Loc::getMessage('SECT_SORT_NAME').Loc::getMessage('SECT_ORDER_DESC'),
                'asc' => Loc::getMessage('SECT_SORT_NAME').Loc::getMessage('SECT_ORDER_ASC'),
            ],
        ];

        $catalogAvaiableSort = $this->sortField === 'CATALOG_AVAILABLE' ? strtolower($this->orderField) : 'desc';
        $this->availableSort['QUANTITY'] = [
            'KEY' => 'QUANTITY',
            'SORT' => 'CATALOG_AVAILABLE',
            'ORDER_VALUES' => [
                $catalogAvaiableSort => Loc::getMessage('SECT_SORT_QUANTITY').Loc::getMessage('SECT_ORDER_'.strtoupper($catalogAvaiableSort)),
            ],
        ];

        $this->addRankSort();
        $this->addPriceSort($params);
        $this->addPropertySort($params);

        $this->availableSort = array_filter(
            $this->availableSort,
            fn ($key) => in_array($key, $this->sortButtons),
            ARRAY_FILTER_USE_KEY
        );

        $this->addCustomSort();
    }

    protected function addRankSort()
    {
        if ($this->showRankSort) {
            $application = \Bitrix\Main\Application::getInstance();
            $session = $application->getSession();

            $this->availableSort['RANK'] = [
                'KEY' => 'RANK',
                'SORT' => 'RANK',
                'ORDER_VALUES' => [
                    'desc' => Loc::getMessage('SECT_SORT_RANK'),
                ],
            ];

            if (!$session->has('rank_sort')) {
                $session->set('rank_sort', 'Y');
            }
        }
    }

    protected function addPriceSort(array $params)
    {
        if (in_array('PRICE', $this->sortButtons, true)) {
            $this->availableSort['PRICE'] = [
                'KEY' => 'PRICE',
                'SORT' => 'PRICE',
                'ORDER_VALUES' => [
                    'desc' => Loc::getMessage('SECT_SORT_PRICE').Loc::getMessage('SECT_ORDER_DESC'),
                    'asc' => Loc::getMessage('SECT_SORT_PRICE').Loc::getMessage('SECT_ORDER_ASC'),
                ],
            ];

            $arSortPrices = $params['SORT_PRICES'];
            if ($arSortPrices == 'MINIMUM_PRICE' || $arSortPrices == 'MAXIMUM_PRICE') {
                $this->availableSort['PRICE']['SORT'] = 'PROPERTY_'.$arSortPrices;
            } else {
                if ($arSortPrices == 'REGION_PRICE') {
                    global $arRegion;

                    if ($arRegion) {
                        if (!$arRegion['PROPERTY_SORT_REGION_PRICE_VALUE'] || $arRegion['PROPERTY_SORT_REGION_PRICE_VALUE'] === 'component') {
                            $price = \CCatalogGroup::GetList([], ['NAME' => $params['SORT_REGION_PRICE']], false, false, ['ID', 'NAME'])->GetNext();
                            $this->availableSort['PRICE']['SORT'] = 'CATALOG_PRICE_'.$price['ID'];
                        } else {
                            $this->availableSort['PRICE']['SORT'] = 'CATALOG_PRICE_'.$arRegion['PROPERTY_SORT_REGION_PRICE_VALUE'];
                        }
                    } else {
                        $price_name = $params['SORT_REGION_PRICE'] ?: 'BASE';
                        $price = \CCatalogGroup::GetList([], ['NAME' => $price_name], false, false, ['ID', 'NAME'])->GetNext();
                        $this->availableSort['PRICE']['SORT'] = 'CATALOG_PRICE_'.$price['ID'];
                    }
                } else {
                    $price = \CCatalogGroup::GetList([], ['NAME' => $params['SORT_PRICES']], false, false, ['ID', 'NAME'])->GetNext();
                    $this->availableSort['PRICE']['SORT'] = 'CATALOG_PRICE_'.$price['ID'];
                }
            }
        }
    }

    protected function addPropertySort(array $params)
    {
        $sortButtons = array_filter($this->sortButtons, fn ($value) => !in_array($value, static::DEFAULT_SORT_BUTTONS));
        if ($sortButtons) {
            $propsIBlockID = $params['IBLOCK_CATALOG_ID'] ?: Solution::getFrontParametrValue('CATALOG_IBLOCK_ID');

            foreach ($sortButtons as $propCode) {
                $arProp = \CIBlockProperty::GetList([], ['ACTIVE' => 'Y', 'IBLOCK_ID' => $propsIBlockID, 'CODE' => $propCode])->fetch();
                if ($arProp) {
                    $propCode = mb_strtoupper($arProp['CODE']);

                    $this->availableSort[$propCode] = [
                        'KEY' => $propCode,
                        'SORT' => 'PROPERTY_'.$propCode,
                        'ORDER_VALUES' => [
                            'desc' => $arProp['NAME'].' '.Loc::getMessage('SECT_ORDER_DESC'),
                            'asc' => $arProp['NAME'].' '.Loc::getMessage('SECT_ORDER_ASC'),
                        ],
                    ];
                }
            }
        }
    }

    protected function addCustomSort()
    {
        $customSort = $this->sortField;

        if (in_array('CUSTOM', $this->sortButtons) && !$this->getSortKey($customSort)) {
            $orderValues = [
                'desc' => Loc::getMessage('SECT_SORT_CUSTOM').Loc::getMessage('SECT_ORDER_DESC'),
                'asc' => Loc::getMessage('SECT_SORT_CUSTOM').Loc::getMessage('SECT_ORDER_ASC'),
            ];
            if ($customSort === 'CATALOG_AVAILABLE') {
                $orderValues = [strtolower($this->orderField) => Loc::getMessage('SECT_SORT_CUSTOM').Loc::getMessage('SECT_ORDER_'.strtoupper($this->orderField))];
                $customSort = 'QUANTITY';
            }

            $this->availableSort[$customSort] = [
                'KEY' => $customSort,
                'SORT' => $this->sortField,
                'ORDER_VALUES' => $orderValues,
            ];
        }
    }

    protected function setSort()
    {
        $application = \Bitrix\Main\Application::getInstance();
        $context = $application->getContext();
        $request = $context->getRequest();
        $session = $application->getSession();

        $sessionName = 'sort'.($this->template ? '_'.$this->template : '');

        $sort = static::SORT;
        if ($request->get('sort')) {
            $sort = htmlspecialcharsbx(strtoupper($request->get('sort')));

            if ($this->showRankSort !== null) {
                if ($sort === 'RANK' && $this->showRankSort) {
                    $session->set('rank_sort', 'Y');
                } else {
                    $session->set('rank_sort', 'N');
                    $session->set($sessionName, $sort);
                }
            } else {
                $session->set($sessionName, $sort);
            }
        } elseif ($session->get('rank_sort') === 'Y' && $this->showRankSort) {
            $sort = 'RANK';
        } elseif ($session->has($sessionName)) {
            $sort = strtoupper($session->get($sessionName));
        } elseif ($this->sortField) {
            $sort = strtoupper($this->sortField);
            if ($this->isSortTypePrice($sort) && !$this->availableSort[$sort]) {
                $sort = 'PRICE';
            }
        }

        $this->sort = $sort;
    }

    protected function setOrder()
    {
        $application = \Bitrix\Main\Application::getInstance();
        $context = $application->getContext();
        $request = $context->getRequest();
        $session = $application->getSession();

        $sessionName = 'order'.($this->template ? '_'.$this->template : '');

        $sort_order = static::ORDER;
        if ($request->get('order') && in_array(mb_strtolower($request->get('order')), ['asc', 'desc'])) {
            $sort_order = htmlspecialcharsbx(mb_strtolower($request->get('order')));
            $session->set($sessionName, $sort_order);
        } elseif ($session->has($sessionName) && in_array(mb_strtolower($session->get($sessionName)), ['asc', 'desc'])) {
            $sort_order = $session->get($sessionName);
        } elseif ($this->orderField) {
            $sort_order = mb_strtolower($this->orderField);
        }

        $this->order = $sort_order;
    }

    protected function setCurrentSort()
    {
        $currentSortKey = $this->getCurrentSortKey();

        if ($this->availableSort[$currentSortKey]) {
            $this->availableSort[$currentSortKey]['CURRENT'] = $this->order;
        }
    }

    protected function getSortKey(string $key): string
    {
        $sortKey = array_search($key, array_column($this->availableSort, 'SORT', 'KEY'));
        if (!$sortKey) {
            $sortKey = array_search($key, array_column($this->availableSort, 'KEY', 'KEY'));
        }

        return $sortKey;
    }

    public function getCurrentSortKey(): string
    {
        static $currentSortKey;
        if (!$currentSortKey) {
            $currentSortKey = $this->getSortKey($this->sort);
        }

        return $currentSortKey;
    }

    public function getAvailableSort(): array
    {
        return $this->availableSort ?: [];
    }

    public function getSort(): string
    {
        return $this->sort ?: static::SORT;
    }

    public function getOrder(): string
    {
        return $this->order ?: static::ORDER;
    }

    public function getCurrentSortTitle(): string
    {
        $defaultSort = Loc::getMessage('SECT_SORT_CUSTOM').Loc::getMessage('SECT_ORDER_'.strtoupper($this->order));
        if (
            $this->availableSort[$this->getCurrentSortKey()]
            && $this->availableSort[$this->getCurrentSortKey()]['ORDER_VALUES'][$this->order]
        ) {
            $defaultSort = $this->availableSort[$this->getCurrentSortKey()]['ORDER_VALUES'][$this->order];
        }

        return $defaultSort;
    }

    public function getSortForFilter(): string
    {
        $sortForFilter = $this->getSort();
        if ($sortForFilter === 'PRICE') {
            if ($this->availableSort['PRICE']) {
                $sortForFilter = $this->availableSort['PRICE']['SORT'];
            }
        }

        if ($sortForFilter === 'QUANTITY') {
            $sortForFilter = $this->availableSort['QUANTITY']['SORT'] ?? 'CATALOG_AVAILABLE';
        }

        return $sortForFilter;
    }

    protected function isSortTypePrice(string $key): bool
    {
        return strpos($key, 'SCALED_PRICE_') === 0;
    }
}

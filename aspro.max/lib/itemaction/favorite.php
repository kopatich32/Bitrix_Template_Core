<?php

namespace Aspro\Max\Itemaction;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use CMax as Solution;
use CMaxCache as SolutionCache;

class Favorite extends Base
{
    use Trait\User;
    use Trait\Site;
    use Trait\Element;

    protected static $siteId = '';
    protected static $iblockId = false;
    protected static $userId = false;
    protected static $bModified = true;

    public static function getIblockId(): int
    {
        if (!static::$iblockId) {
            $favoriteIblockId = SolutionCache::$arIBlocks[self::getSiteId()]['aspro_'.Solution::solutionName.'_catalog']['aspro_'.Solution::solutionName.'_favorit'][0];
            if ($favoriteIblockId) {
                static::$iblockId = $favoriteIblockId;
            }
        }

        return static::$iblockId;
    }

    public static function setIblockId(int $iblockId)
    {
        self::$bModified = true;
        self::$iblockId = false;

        $iblockId = intval($iblockId);
        if ($iblockId > 0) {
            self::$iblockId = $iblockId;
        }
    }

    public static function getOffersIDFromItems(&$arItems)
    {
        $arResult = [];
        if ($arItems) {
            $arElements = SolutionCache::CIBlockElement_GetList(
                [
                    'CACHE' => [
                        'GROUP' => ['ID'],
                        'TAG' => SolutionCache::GetIBlockCacheTag($arItem['IBLOCK_ID']),
                    ],
                ],
                [
                    // 'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                    'ID' => $arItems,
                ],
                false,
                false,
                [
                    'ID',
                    'IBLOCK_ID',
                    'PROPERTY_CML2_LINK',
                ]
            );

            foreach ($arElements as $key => $arItem) {
                if ($arItem['PROPERTY_CML2_LINK_VALUE']) {
                    $arResult[] = $key;
                    unset($arItems[$key]);
                    $arItems[] = $arItem['PROPERTY_CML2_LINK_VALUE'];
                }
            }
        }

        return $arResult;
    }

    public static function getItems(): array
    {
        static $result;

        if (self::$bModified) {
            $result = [];
            $userId = self::getUserId();
            $iblockId = self::getIblockId();
            if ($userId && $iblockId) {
                if (
                    $_SESSION['CATALOG_FAVORITE_LIST']
                    && is_array($_SESSION['CATALOG_FAVORITE_LIST'])
                ) {
                    if (
                        is_array($_SESSION['CATALOG_FAVORITE_LIST'][$iblockId])
                        && is_array($_SESSION['CATALOG_FAVORITE_LIST'][$iblockId]['ITEMS'])
                        && $_SESSION['CATALOG_FAVORITE_LIST'][$iblockId]['ITEMS']
                    ) {
                        foreach (array_keys($_SESSION['CATALOG_FAVORITE_LIST'][$iblockId]['ITEMS']) as $itemId) {
                            static::addItem($itemId);
                        }
                    }

                    unset($_SESSION['CATALOG_FAVORITE_LIST']);
                }

                $arCollection = self::getCollection(
                    [
                        'USER_ID' => $userId,
                        'IBLOCK_ID' => self::getIblockId(),
                    ]
                );
                if ($arCollection['PROPERTY_LINK_ELEMENTS_VALUE']) {
                    foreach ((array) $arCollection['PROPERTY_LINK_ELEMENTS_VALUE'] as $itemId) {
                        $result[$itemId] = $itemId;
                    }
                }
            } else {
                if (
                    $_SESSION['CATALOG_FAVORITE_LIST']
                    && is_array($_SESSION['CATALOG_FAVORITE_LIST'])
                ) {
                    $iblockId = self::getIblockId();
                    if (
                        is_array($_SESSION['CATALOG_FAVORITE_LIST'][$iblockId])
                        && is_array($_SESSION['CATALOG_FAVORITE_LIST'][$iblockId]['ITEMS'])
                        && $_SESSION['CATALOG_FAVORITE_LIST'][$iblockId]['ITEMS']
                    ) {
                        foreach (array_keys($_SESSION['CATALOG_FAVORITE_LIST'][$iblockId]['ITEMS']) as $itemId) {
                            $result[$itemId] = $itemId;
                        }
                    }
                }
            }

            self::$bModified = false;
        }

        return $result;
    }

    public static function getCount(): int
    {
        return count(static::getItems());
    }

    public static function addItem(int $id)
    {
        if ($id <= 0) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_ITEM_ID'));
        }

        $iblockId = self::getIblockId();
        if ($iblockId <= 0) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_IBLOCK_ID'));
        }

        // self::modifyItem($id);

        $arItem = self::getElement($id);
        if (!$arItem) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_ITEM'));
        }

        if ($userID = self::getUserId()) {
            // add root section for user
            $arRootSection = self::getRootSection(['USER_ID' => $userID, 'IBLOCK_ID' => $iblockId]);

            // get user collection
            $arListItem = self::addCollection(['USER_ID' => $userID, 'IBLOCK_ID' => $iblockId, 'PARENT_SECTION' => $arRootSection]);

            self::addItemsToCollection(['ITEMS' => $id, 'IBLOCK_ID' => $iblockId, 'COLLECTION' => $arListItem]);
        } else {
            if (!is_array($_SESSION['CATALOG_FAVORITE_LIST'])) {
                $_SESSION['CATALOG_FAVORITE_LIST'] = [];
            }

            if (!is_array($_SESSION['CATALOG_FAVORITE_LIST'][$iblockId])) {
                $_SESSION['CATALOG_FAVORITE_LIST'][$iblockId] = [
                    'ITEMS' => [],
                ];
            }

            if (!is_array($_SESSION['CATALOG_FAVORITE_LIST'][$iblockId]['ITEMS'])) {
                $_SESSION['CATALOG_FAVORITE_LIST'][$iblockId]['ITEMS'] = [];
            }

            $_SESSION['CATALOG_FAVORITE_LIST'][$iblockId]['ITEMS'][$id] = $arItem;
        }

        self::$bModified = true;
    }

    public static function removeItem(int $id)
    {
        if ($id <= 0) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_ITEM_ID'));
        }

        $iblockId = self::getIblockId();
        if ($iblockId <= 0) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_IBLOCK_ID'));
        }

        // self::modifyItem($id);

        $arItem = self::getElement($id);
        if (!$arItem) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_ITEM'));
        }

        if ($userID = self::getUserId()) {
            // add root section for user
            $arRootSection = self::getRootSection(['USER_ID' => $userID, 'IBLOCK_ID' => $iblockId]);

            // get user collection
            $arListItem = self::addCollection(['USER_ID' => $userID, 'IBLOCK_ID' => $iblockId, 'PARENT_SECTION' => $arRootSection]);

            self::removeItemsFromCollection(['ITEMS' => $id, 'IBLOCK_ID' => $iblockId, 'COLLECTION' => $arListItem]);
        } else {
            if (!is_array($_SESSION['CATALOG_FAVORITE_LIST'])) {
                $_SESSION['CATALOG_FAVORITE_LIST'] = [];
            }

            if (!is_array($_SESSION['CATALOG_FAVORITE_LIST'][$iblockId])) {
                $_SESSION['CATALOG_FAVORITE_LIST'][$iblockId] = [
                    'ITEMS' => [],
                ];
            }

            if (!is_array($_SESSION['CATALOG_FAVORITE_LIST'][$iblockId]['ITEMS'])) {
                $_SESSION['CATALOG_FAVORITE_LIST'][$iblockId]['ITEMS'] = [];
            }

            unset($_SESSION['CATALOG_FAVORITE_LIST'][$iblockId]['ITEMS'][$id]);
        }

        self::$bModified = true;
    }

    public static function getTitle()
    {
        return Loc::getMessage('ITEMACTION_FAVORITE_TITLE');
    }

    public static function getRootSection(array $options = ['USER_ID' => null, 'IBLOCK_ID' => null]): array
    {
        if (!$options['USER_ID']) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_USER_ID'));
        }

        if (!$options['IBLOCK_ID']) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_IBLOCK_ID'));
        }

        Loader::includeModule('iblock');

        $arSectionFields = [
            'ACTIVE' => 'Y',
            'XML_ID' => $options['USER_ID'],
            'IBLOCK_ID' => $options['IBLOCK_ID'],
        ];

        $arResult = SolutionCache::CIBlockSection_GetList(
            [
                'CACHE' => [
                    'MULTI' => 'N',
                    'TAG' => SolutionCache::GetIBlockCacheTag($options['IBLOCK_ID']),
                ],
            ],
            $arSectionFields
        );

        if (!$arResult) {
            $dbRes = \CUser::getByID($options['USER_ID']);
            $arUser = $dbRes->Fetch();

            $userSectionName = $arUser['LOGIN'];

            if ($userEmail = $arUser['EMAIL']) {
                $userSectionName .= ' ('.$userEmail.')';
            }

            $obSection = new \CIBlockSection();
            $arResult['ID'] = $obSection->Add(
                array_merge(
                    $arSectionFields,
                    [
                        'NAME' => $userSectionName,
                    ]
                )
            );

            if (!$arResult['ID']) {
                throw new SystemException($obSection->LAST_ERROR);
            }
        }

        return $arResult;
    }

    public static function addCollection(array $options = ['USER_ID' => null, 'IBLOCK_ID' => null, 'PARENT_SECTION' => null]): array
    {
        Loader::includeModule('iblock');

        $arResult = self::getCollection($options);

        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());

        $listName = $request->get('LIST_NAME') ?? '<NO_GROUP>';

        if (!$arResult['ID']) {
            if (
                $options['PARENT_SECTION']
                && $options['PARENT_SECTION']['ID']
            ) {
                $obElement = new \CIBlockElement();
                $arResult['ID'] = $obElement->Add(
                    array_merge(
                        $arResult['FILTER_FIELDS'],
                        [
                            'NAME' => $listName,
                            'IBLOCK_SECTION' => $options['PARENT_SECTION']['ID'],
                        ]
                    )
                );

                if (!$arResult['ID']) {
                    throw new SystemException($obElement->LAST_ERROR);
                }
            } else {
                throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_PARENT_SECTION'));
            }
        }

        return $arResult;
    }

    /**
     * @param array $options
     *                       $options = [
     *                       'USER_ID' => (string)
     *                       'IBLOCK_ID' => (string)
     *                       ]
     */
    public static function getCollection(array $options = ['USER_ID' => null, 'IBLOCK_ID' => null]): array
    {
        if (!$options['USER_ID']) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_USER_ID'));
        }

        if (!$options['IBLOCK_ID']) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_IBLOCK_ID'));
        }

        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());

        $listID = $request->get('LIST_ID') ?? $options['USER_ID'].'_common';

        $arFields = [
            'ACTIVE' => 'Y',
            'XML_ID' => $listID,
            'IBLOCK_ID' => $options['IBLOCK_ID'],
        ];

        $arResult = SolutionCache::CIBlockElement_GetList(
            [
                'CACHE' => [
                    'MULTI' => 'N',
                    'TAG' => SolutionCache::GetIBlockCacheTag($options['IBLOCK_ID']),
                ],
            ],
            $arFields,
            false,
            false,
            [
                'ID',
                'NAME',
                'PROPERTY_LINK_ELEMENTS',
            ]
        );

        $arResult['FILTER_FIELDS'] = $arFields;

        return $arResult;
    }

    public static function addItemsToCollection(array $options = ['ITEMS' => null, 'COLLECTION' => null])
    {
        if (!$options['ITEMS']) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_ITEMS'));
        }

        if (!$options['COLLECTION']) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_COLLECTION'));
        }

        Loader::includeModule('iblock');

        $arProps = (array) $options['ITEMS'];

        if ($options['COLLECTION']['PROPERTY_LINK_ELEMENTS_VALUE']) {
            $options['COLLECTION']['PROPERTY_LINK_ELEMENTS_VALUE'] = (array) $options['COLLECTION']['PROPERTY_LINK_ELEMENTS_VALUE'];
            $arProps = array_unique(array_merge($options['COLLECTION']['PROPERTY_LINK_ELEMENTS_VALUE'], $arProps));
        }

        \CIBlockElement::SetPropertyValuesEx($options['COLLECTION']['ID'], false, ['LINK_ELEMENTS' => ($arProps ?: false)]);

        if ($options['IBLOCK_ID']) {
            SolutionCache::ClearCacheByTag(SolutionCache::GetIBlockCacheTag($options['IBLOCK_ID']));
        }
    }

    public static function removeItemsFromCollection(array $options = ['ITEMS' => null, 'COLLECTION' => null])
    {
        if (!$options['ITEMS']) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_ITEMS'));
        }

        if (!$options['COLLECTION']) {
            throw new SystemException(Loc::getMessage('ITEMACTION_FAVORITE_ERROR_COLLECTION'));
        }

        $arProps = (array) $options['ITEMS'];

        if ($options['COLLECTION']['PROPERTY_LINK_ELEMENTS_VALUE']) {
            $options['COLLECTION']['PROPERTY_LINK_ELEMENTS_VALUE'] = (array) $options['COLLECTION']['PROPERTY_LINK_ELEMENTS_VALUE'];
            $arProps = array_unique(array_diff($options['COLLECTION']['PROPERTY_LINK_ELEMENTS_VALUE'], $arProps));
        }

        \CIBlockElement::SetPropertyValuesEx($options['COLLECTION']['ID'], false, ['LINK_ELEMENTS' => ($arProps ?: false)]);

        if ($options['IBLOCK_ID']) {
            SolutionCache::ClearCacheByTag(SolutionCache::GetIBlockCacheTag($options['IBLOCK_ID']));
        }
    }
}

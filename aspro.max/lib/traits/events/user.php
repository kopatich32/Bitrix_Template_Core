<?php

namespace Aspro\Max\Traits\Events;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Password;
use CMax as Solution;

Loc::loadMessages(__FILE__);

trait User
{
    public static function OnBeforeUserLoginHandler(&$arFields)
    {
        $bAdminSection = (defined('ADMIN_SECTION') && ADMIN_SECTION === true);
        if ($bAdminSection) {
            return;
        }

        if (!static::checkPostParams()) {
            return;
        }

        if (!check_email($arFields['LOGIN'], true)) {
            return;
        }

        if (static::isHasUserLoginByEmail($arFields)) {
            return;
        }

        $limit = 3;
        extract(static::resolveUserLoginByEmail($arFields, $limit));

        if (!$isResolved && static::isManyUsersWithSameEmail($totalCount, $limit, email: $arFields['LOGIN'])) {
            return false;
        }
    }

    public static function checkPostParams()
    {
        $context = \Bitrix\Main\Application::getInstance()->getContext();
        $request = $context->getRequest();
        $arPost = $request->getPostList()->toArray();

        if (!$arPost['SITE_ID'] && !$arPost['SOLUTION_ID']) {
            return false;
        }
        if ($arPost['SOLUTION_ID'] !== static::moduleID) {
            return false;
        }

        $isEmailAsLogin = Solution::GetFrontParametrValue('LOGIN_EQUAL_EMAIL', $arPost['SITE_ID']) === 'Y';
        if (!$isEmailAsLogin) {
            return false;
        }

        return true;
    }

    public static function isHasUserLoginByEmail($arFields)
    {
        $arUser = \CUser::GetList('', '',
            [
                'LOGIN_EQUAL' => $arFields['LOGIN'],
                'ACTIVE' => 'Y',
                'BLOCKED' => 'N',
            ],
            [
                'FIELDS' => ['ID', 'PASSWORD'],
            ]
        )->Fetch();

        if (!$arUser) {
            return false;
        }

        if (!$passwordCorrect = Password::equals($arUser['PASSWORD'], $arFields['PASSWORD'])) {
            return false;
        }

        return true;
    }

    public static function resolveUserLoginByEmail($arFields, $limit)
    {
        $isResolved = false;
        $rsUsers = \CUser::GetList('', '',
            [
                '=EMAIL' => $arFields['LOGIN'],
                'LOGIN_EQUAL' => '~'.$arFields['LOGIN'], //not
                'ACTIVE' => 'Y',
                'BLOCKED' => 'N',
            ],
            [
                'FIELDS' => ['ID', 'LOGIN', 'PASSWORD'],
                'NAV_PARAMS' => [
                    'nPageSize' => $limit,
                ],
            ]
        );
        while ($arUser = $rsUsers->Fetch()) {
            if ($passwordCorrect = Password::equals($arUser['PASSWORD'], $arFields['PASSWORD'])) {
                $arFields['LOGIN'] = $arUser['LOGIN'];
                $isResolved = true;

                break;
            }
        }

        return [
            'totalCount' => $rsUsers->NavRecordCount,
            'isResolved' => $isResolved,
        ];
    }

    public static function isManyUsersWithSameEmail($totalCount, $limit, $email)
    {
        if ($limit >= $totalCount) {
            return false;
        }

        $GLOBALS['APPLICATION']->ThrowException(GetMessage('EMAILS_DUBLICATE_WHEN_AUTH', [
            '#DUBLICATES#' => $limit,
            '#EMAIL#' => $email,
        ]));

        return true;
    }
}

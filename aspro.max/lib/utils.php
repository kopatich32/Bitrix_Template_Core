<?php

namespace Aspro\Max;

use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\File;
use CMax as Solution;

class Utils
{
    public static function implodeClasses(array $arClasses, string $delimiter = ' '): string
    {
        return implode($delimiter, $arClasses);
    }

    public static function getPathWithTimestamp(string $path): string
    {
        $file = new File($_SERVER['DOCUMENT_ROOT'].$path);
        if (!$file->isExists()) {
            return $path;
        }

        return $path.'?'.$file->getModificationTime();
    }

    public static function getAgreementIdByOption(string $optionCode)
    {
        $defaultOptionValue = 'DEFAULT';
        $useUniqueAgreement = Option::get(Solution::moduleID, 'AGREEMENT_USE_UNIQUE', 'N') === 'Y';

        $agreementId = $defaultOptionValue;
        if ($useUniqueAgreement) {
            $agreementId = Option::get(Solution::moduleID, $optionCode, $defaultOptionValue);
        }

        if ($agreementId === $defaultOptionValue) {
            $agreementId = Option::get(Solution::moduleID, 'AGREEMENT', '0');
        }

        return $agreementId;
    }
}

<?
namespace Aspro\Max;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader;

use CMax as Solution,
    CMaxCache as SolutionCache;

Loc::loadMessages(__FILE__);

class Brand{

    public static function getLatinLetters(){
        $arLetters = [];

        for ( $i = 65; $i <= 90; $i++) { // A-Z
            $code = chr($i);
            $arLetters[] = [
                'LETTER' => $code,
                'CODE' => $code
            ];
        }
        return $arLetters;
    }

    public static function getCyrilicLetters(){
        $arLetters = [];
        $bUtf = defined('BX_UTF') && BX_UTF === true;
        [$start, $end] = [192, 223];
		if ($bUtf) [$start, $end] = [1040, 1071];

        $fixLetter = [
            218 => 'thd',
            219 => 'iy',
            220 => 'tsf',
            221 => 'ie',
            1066 => 'thd',
            1067 => 'iy',
            1068 => 'tsf',
            1069 => 'ie',
        ];


        for ( $i = $start; $i <= $end; $i++) { // cyrilic
            $code = $bUtf ? mb_chr($i) : chr($i);
            $arLetters[] = [
                'LETTER' => $code,
                'CODE' => strtoupper(\CUtil::translit($fixLetter[$i] ?? $code, 'ru'))
            ];
            if ($i === 197 || $i === 1045) {
                $code = Loc::getMessage('LETTER_EE');
                $arLetters[] = [
                    'LETTER' => $code,
                    'CODE' => strtoupper(\CUtil::translit($fixLetter[$i] ?? $code, 'ru'))
                ];
            }
        }
        return $arLetters;
    }
    
    public static function getLetters(){
        return array_merge(self::getLatinLetters(), self::getCyrilicLetters());
    }

    public static function getAlphabet($iblockID = 0, $arFilter = []){
        if (!$iblockID) return;
      
        $arLetters = self::getLetters();
        $arTmpFilter = [
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => $iblockID,
        ];
        // get brands started from alphanumeric characters
        foreach ($arLetters as $key => $arLetter) {
            $letter = $arLetter['LETTER'];
            $arItem = SolutionCache::CIBlockElement_GetList(
                [
                    'CACHE' => [
                        "MULTI" =>"N", 
                        "TAG" => SolutionCache::GetIBlockCacheTag($iblockID)
                    ]
                ], 
                array_merge(
                    [
                        'NAME' => $letter.'%',
                    ],
                    $arTmpFilter, 
                    (array)$arFilter
                ),
                false,
                [
                    'nTopCount' => 1
                ],
                ['ID']
            );
            if (!$arItem) {
                unset($arLetters[$key]);
            }
        }

        // get brands started from non-alphanumeric characters
        if (!$arLetters) {
            $arLetters = self::getLetters();
        }
        $arItem = SolutionCache::CIBlockElement_GetList(
            [
                'CACHE' => [
                    "MULTI" =>"N", 
                    "TAG" => SolutionCache::GetIBlockCacheTag($iblockID)
                ]
            ],
            array_merge(
                [
                    '!NAME' => array_map(function($a){ return $a.'%';}, array_column($arLetters, 'LETTER')),
                ],
                $arTmpFilter, 
                (array)$arFilter
            ),
            false,
            [
                'nTopCount' => 1
            ],
            ['ID']
        );
        if ($arItem) {
            array_unshift($arLetters, [
                'LETTER' => '0-9',
                'CODE' => 'DIGITS',
            ]);
        }

        return $arLetters;
	}
}

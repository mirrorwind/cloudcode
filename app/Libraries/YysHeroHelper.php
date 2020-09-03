<?php

namespace App\Libraries;

class YysHeroHelper
{
    public static function appendPrice(array &$heroArr): void
    {
        foreach ($heroArr as $k => $hero) {
            $heroArr[$k]['price'] = self::getHeroPrice($hero);
        }
    }

    /**
     * 返回每个式神的价格
     */
    public static function getHeroPrice(array $hero): int
    {
        $hereName = $hero['name'];

        if (method_exists(YysHeroHelper::class, $hero['name'])) {
            return call_user_func('\App\Libraries\YysHeroHelper::' . $hero['name'], $hero);
        } else {
            return self::其他式神($hero);
        }
    }

    /* ==== 在下面写所有式神的估价函数 ==== 
    
    你能获取到到的数据例子
    $hero = {
        "hp": 14977,
        "atk": 2011,
        "def": 623,
        "set": ["魅妖"],
        "spd": 190,
        "name": "数珠",
        "crate": 9,
        "cpower": 150,
        "debuff": 48,
        "resist": 119
    }*/

    public static function 其他式神($hero): float
    {
        return 10; // 一律十元
    }

    public static function 彼岸花($hero): float
    {
        // 这只是一个例子
        if ($hero['crate'] >= 100) {
            return 520; // 满爆彼岸花 520元
        } else {
            return 100;
        }
    }

    /* ==== 函数结束 ==== */
}

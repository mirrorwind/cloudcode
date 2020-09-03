<?php

namespace App\Transformers\Yys;

use App\Transformers\BaseTransformer;
use App\Libraries\YysYuhunHelper;

class YysHeroTransformer extends BaseTransformer
{
    public static function transform(array $list, array $yuhun = []): array
    {
        $list = array_filter($list, function ($item) {
            return $item['star'] >= 5;
        });

        return array_values(array_map(function ($item) use ($yuhun) {
            return self::transformItem($item, $yuhun);
        }, $list));
    }

    public static function transformItem(array $item, array $yuhun = [])
    {
        $res = [
            'name' => $item['name'],
            'atk' => self::getValue($item['attrs']['攻击']),
            'def' => self::getValue($item['attrs']['防御']),
            'hp' => self::getValue($item['attrs']['生命']),
            'crate' => self::getValue($item['attrs']['暴击']),
            'cpower' => self::getValue($item['attrs']['暴击伤害']),
            'spd' => self::getValue($item['attrs']['速度']),
            'debuff' => self::getValue($item['attrs']['效果命中']),
            'resist' => self::getValue($item['attrs']['效果抵抗']),
            'set' => self::getYuhunSet($item['equips'], $yuhun),
        ];

        return $res;
    }

    public static function getValue($field)
    {
        return (int) str_replace('%', '', $field['val']) + (int) str_replace('%', '', $field['add_val'] ?? '');
    }

    public static function getYuhunSet($equips, $yuhun): array
    {
        $set = [];
        if ($equips) {
            foreach ($equips as $one) {
                $name = $yuhun[$one]['name'];
                if (isset($set[$name])) {
                    $set[$name]++;
                } else {
                    $set[$name] = 1;
                }
            }
        }
        $res = [];
        foreach ($set as $yh => $num) {
            if (in_array($yh, ['土蜘蛛', '胧车', '荒骷髅', '地震鲶', '蜃气楼', '鬼灵歌伎']) && $num >= 2) {
                array_push($res, $yh);
            } elseif ($num >= 4) {
                array_unshift($res, $yh);
            }
        }
        return $res;
    }
}


/*

attrs:
攻击: {score: 4, val: "3136", add_val: "5316"}
效果命中: {score: 0, val: "0%"}
效果抵抗: {score: 0, val: "0%"}
暴击: {score: 4, val: "10%", add_val: "91%"}
暴击伤害: {score: 0, val: "211%"}
生命: {score: 2, val: "11165", add_val: "3144"}
速度: {score: 4, val: "113", add_val: "11"}
防御: {score: 1, val: "375", add_val: "133"}
__proto__: Object
awake: 1
born: 1520180945
equips: (6) ["5ded20102af7060360c2bcbf", "5cbb22eeed5b200b8bd10b46", "5cd5a21036f7fb255b672bd9", "5d5f910e2a65332003fc3612", "5e2c49d4838eb5174af1e37b", "5c2215058c091d68af37aa92"]
exp: 0
flag: 0
heroId: 219
heroUid: "5a9c1ed1f1963e4d15ced3ea"
level: 40
lock: true
name: "酒吞童子"
rarity: 4
selectSkills: (3) [2191, 2192, 2193]
skinfo: (3) [Array(2), Array(2), Array(2)]
skinid: 3
special_effect: (2) ["972190", "972191"]
star: 6
uid: "5a9c1ed1f1963e4d15ced3ea"
usingCards: []

*/

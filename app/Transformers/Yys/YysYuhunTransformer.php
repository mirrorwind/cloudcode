<?php

namespace App\Transformers\Yys;

use App\Transformers\BaseTransformer;
use App\Libraries\YysYuhunHelper;

class YysYuhunTransformer extends BaseTransformer
{
    public static function transform(array $list): array
    {
        $list = array_filter($list, function ($item) {
            return $item['level'] == 15;
        });

        return parent::transformItems($list, function ($item) {
            return self::listItem($item);
        });
    }

    public static function listItem($item)
    {
        $res = [
            'name' => $item['name'],
            'pos' => $item['pos'],
            'lv' => $item['level'],
            'star' => $item['qua'],
        ];

        $res['main'] = array_shift($item['attrs'])[0];

        if (empty($item['rattr'])) {
            // 如果没有成长数据
            $res['attrs'] = collect($item['attrs'])->mapWithKeys(function ($i) {
                return [$i[0] => (int) $i[1]];
            });
        } else {
            // 使用成长数据
            $res['attrs'] = self::getRawAttrs($item['rattr']);
        }

        if (!empty($item['single_attr'])) {
            list($key, $value) = $item['single_attr'];
            $res['attrs'][$key] = isset($res['attrs'][$key])
                ?
                $res['attrs'][$key] += (int) $value
                :
                (int) $value;
        }

        $res['score'] = YysYuhunHelper::getSingleScore($res);

        return $res;
    }

    public static $mapping = [
        'critRateAdditionVal' => ['暴击', 3],
        'critPowerAdditionVal' => ['暴击伤害', 4],
        'defenseAdditionRate' => ['防御加成', 3],
        'attackAdditionRate' => ['攻击加成', 3],
        'maxHpAdditionRate' => ['生命加成', 3],
        'defenseAdditionVal' => ['防御', 5],
        'attackAdditionVal' => ['攻击', 30],
        'maxHpAdditionVal' => ['生命', 114],
        'speedAdditionVal' => ['速度', 3],
        'debuffResist' => ['效果抵抗', 4],
        'debuffEnhance' => ['效果命中', 4],
    ];

    public static function getRawAttrs(array $rattrs)
    {
        $attrs = [];
        foreach ($rattrs as $rattr) {
            list($attrName, $attrBase) = self::$mapping[$rattr[0]];
            $attrs[$attrName] = ($attrs[$attrName] ?? 0) + $attrBase * $rattr[1];
        }
        foreach ($attrs as $k => $v) {
            $attrs[$k] = round($v, 2);
        }
        return $attrs;
    }
}

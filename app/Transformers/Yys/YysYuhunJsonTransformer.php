<?php

namespace App\Transformers\Yys;

use App\Transformers\BaseTransformer;

class YysYuhunJsonTransformer extends BaseTransformer
{
    // 目标是把json文件变成 cbg 的 api res
    public static function transform(string $yhString): array
    {
        $res = [
            'format_equip_name' => 'JSON文件用户',
            'server_name' => '未知服务器',
            'equip_desc' => [
                'inventory' => []
            ],
        ];

        //0御魂类型 1位置 2攻击加成 3生命加成 4速度 5暴击 6暴击伤害 7效果抵抗 8效果命中
        $lang = array(
            2 => '攻击加成',
            3 => '生命加成',
            4 => '速度',
            5 => '暴击',
            6 => '暴击伤害',
            7 => '效果抵抗',
            8 => '效果命中',
        );

        /*
        attrs: (5) [Array(2), Array(2), Array(2), Array(2), Array(2)]
        base_r: 1
        base_rindex: 2
        discard_time: 0
        exp: 0
        herouid: "5ceeb1eb8b608e2c73f89d3e"
        isuseless: false
        itemId: 120006
        level: 15
        lock: true
        name: "树妖"
        newGet: 0
        pos: 2
        qua: 6
        rattr: (9) [Array(2), Array(2), Array(2), Array(2), Array(2), Array(2), Array(2), Array(2), Array(2)]
        suitid: 300024
        uuid: "5bf2b5e08b608e0f96f34f20"

        'sn' => $item['game_ordersn'],
        'price' => round($item['price'] / 100),
        'nickname' => $item['format_equip_name'],
        'platform' => $item['platform_type'],
        'serverName' => $item['server_name'],
        'avalableTime' => strtotime($item['equip_lock_time_desc']),
        // more fields
        'roleId' => $item['seller_roleid'],
        'status' => $item['status'],
        'hp' => $detail['strength'],
        'gouyu' => $detail['goyu'],
        'lv15' => $detail['level_15'],
        'star6' => self::getStar6Count($detail['heroes']),
        'cards' => collect($detail['lbscards'])->sum('num'),
        'sp' => $detail['hero_history']['sp']['got'] ?? 0,
        'ssr' => $detail['hero_history']['ssr']['got'],
        // yuhun
        'yuhunScore' => 0,
        'yuhun' => YysYuhunTransformer::transform($detail['inventory'])
        */

        $rawArr = explode('|', $yhString);
        foreach ($rawArr as $raw) {
            $data = explode(',', $raw);
            $inventory = array(
                'name' => $data[0],
                'pos' => $data[1],
                'attrs' => [
                    [self::$mapping[$data[1]], 0]
                ],
                'level' => 15,
                'qua' => 6,
            );
            //寻找主属性
            for ($i = 2; $i <= 8; $i++) {
                if ($data[$i] >= 30) {
                    //设置主属性
                    $inventory['attrs'][0] = [$lang[$i], 0];
                    //溢出保留
                    if ($lang[$i] == '速度') {
                        $inventory['attrs'][] = [$lang[$i], $data[$i] - 57];
                    } elseif ($lang[$i] == '暴击伤害') {
                        $inventory['attrs'][] = [$lang[$i], $data[$i] - 89];
                    } else {
                        $inventory['attrs'][] = [$lang[$i], $data[$i] - 55];
                    }
                } elseif ($data[$i] > 0) {
                    $inventory['attrs'][] = [$lang[$i], $data[$i]];
                }
            }
            $res['equip_desc']['inventory'][] = $inventory;
        }

        $res['equip_desc'] = json_encode($res['equip_desc']);

        return $res;
    }

    public static $mapping = [
        1 => '攻击',
        2 => '防御加成',
        3 => '防御',
        4 => '防御加成',
        5 => '生命',
        6 => '防御加成',
    ];
}

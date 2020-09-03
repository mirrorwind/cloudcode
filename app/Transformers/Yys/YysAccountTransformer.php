<?php

namespace App\Transformers\Yys;

use App\Transformers\BaseTransformer;
use App\Libraries\YysYuhunHelper;

class YysAccountTransformer extends BaseTransformer
{
    public static function transform(array $item): array
    {
        $detail = json_decode($item['equip_desc'], true);

        $res = [
            'sn' => $item['game_ordersn'] ?? '',
            'price' => round(($item['price'] ?? 100) / 100),
            'nickname' => $item['format_equip_name'] ?? '',
            'platform' => $item['platform_type'] ?? '',
            'serverName' => $item['server_name'] ?? '未知服务器',
            'avalableTime' => strtotime($item['fair_show_end_time'] ?? ''),
            // more fields
            'roleId' => $item['seller_roleid'] ?? '',
            'status' => $item['status'] ?? 0,
            'hp' => $detail['strength'] ?? 0,
            'gouyu' => $detail['goyu'] ?? 0,
            'lv15' => $detail['level_15'] ?? 0,
            'star6' => self::getStar6Count($detail['heroes'] ?? []),
            'cards' => collect($detail['lbscards'] ?? [])->sum('num'),
            'sp' => $detail['hero_history']['sp']['got'] ?? 0,
            'ssr' => $detail['hero_history']['ssr']['got'] ?? 0,
            // yuhun
            'yuhunScore' => 0,
            'yuhun' => YysYuhunTransformer::transform($detail['inventory']),
            // hero
            'hero' => []
        ];

        $res['yuhunScore'] = round(array_sum(array_column($res['yuhun'], 'score')));
        $res['hero'] = empty($detail['heroes']) ? [] : YysHeroTransformer::transform($detail['heroes'], $detail['inventory']);

        return $res;
    }

    private static function getStar6Count(array $heroList)
    {
        return sizeof(array_filter($heroList, function ($item) {
            return $item['star'] == 6;
        }));
    }
}

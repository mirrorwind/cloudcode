<?php

namespace App\Transformers\Yys;

use App\Transformers\BaseTransformer;

class YysListTransformer extends BaseTransformer
{
    public static function transform(array $list): array
    {
        return parent::transformItems($list, function ($item) {
            return self::listItem($item);
        });
    }

    public static function listItem($item)
    {
        return [
            'sn' => $item['game_ordersn'],
            'price' => round($item['price'] / 100),
            'nickname' => $item['format_equip_name'],
            'platform' => $item['platform_type'],
            'serverName' => $item['server_name'],
            'avalableTime' => $item['pass_fair_show'] ? time() : null
        ];
    }
}

/*
allow_bargain: false
allow_urs_bargain: false
area_name: "全平台互通新区"
collect_num: 30
desc_sumup_short: "SSR 79   六星式神 50   签到781天"
equip_level: 60
equip_type: "0"
expire_remain_seconds: 1254579
format_equip_name: "小乔酒水"
game_channel: "netease"
game_ordersn: "202004190301616-24-BKV2NAFBLLNCN"
highlights: ["终极之巅"]
0: "终极之巅"
icon: "https://cbg-yys.res.netease.com//game_res/hero/322/322.png"
kindid: 1
level_desc: "60级"
other_info: {,…}
basic_attrs: ["SSR 79", "六星式神 50", "签到781天"]
desc_sumup: "SSR 79   六星式神 50   签到781天"
desc_sumup_short: "SSR 79   六星式神 50   签到781天"
highlights: ["终极之巅"]
icon: "https://cbg-yys.res.netease.com//game_res/hero/322/322.png"
level_desc: "60级"
top_heros: [{star: 6, name: "炼狱茨木童子", level: 40, max_level: true, rarity: 5, awake: 1, heroId: 322,…},…]
pass_fair_show: 0
platform_type: 2
price: 140000
server_name: "暖风春穗"
serverid: 24
storage_type: 4
tag: "auto-gen"
tag_key: "{"sort_key": "recommd", "tag": "auto-gen", "sort_order": "", "extern_tag": null}"
*/

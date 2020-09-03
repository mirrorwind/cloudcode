<?php

namespace App\Services;

use App\Transformers\Yys\YysListTransformer;
use App\Transformers\Yys\YysAccountTransformer;
use Illuminate\Support\Facades\Http;

class YysClient
{
    /**
     * get a list of yys account
     * strength = int
     * platform_type = 2:android
     */
    public function getAccountList(array $params = []): array
    {
        $query = array_merge([
            'act' => 'recommd_by_role',
            'search_type' => 'role',
            'count' => 15,
            'order_by' => 'price ASC',
            'pass_fair_show' => 1,
            'page' => 1,
            '_t' => time() . rand(100, 999),
        ], $params);

        $json = Http::withOptions(['verify' => false])
            ->get('https://recommd.yys.cbg.163.com/cgi-bin/recommend.py', $query)->json();

        return YysListTransformer::transform($json['result']);
    }

    /**
     * get account detail
     */
    public function getAccountDetail(string $sn): array
    {
        $query = [
            'serverid' => explode('-', $sn)[1],
            'ordersn' => $sn,
            'view_loc' => 'search|tag_key:{"sort_key": "price", "tag": "user", "sort_order": "ASC", "extern_tag": null}',
        ];

        $json = Http::withOptions(['verify' => false])
            ->asForm()
            ->post('https://yys.cbg.163.com/cgi/api/get_equip_detail', $query)->json();

        return YysAccountTransformer::transform($json['equip']);
    }
}

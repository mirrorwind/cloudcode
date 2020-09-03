<?php

namespace App\Http\Controllers\Yys;

use Illuminate\Http\Request;

use App\Services\YysClient;
use App\Http\Controllers\Controller;
use App\Repositories\YysAccountRepository;
use App\Libraries\YysYuhunHelper;
use App\Libraries\YysHeroHelper;
use App\Transformers\Yys\YysYuhunJsonTransformer;
use App\Transformers\Yys\YysAccountTransformer;

class YysController extends Controller
{
    protected $client;

    public function __construct(YysClient $client)
    {
        $this->client = $client;
    }

    public function home()
    {
        return view('yys.pages.home');
    }

    public function submit()
    {
        return view('yys.pages.submit');
    }

    public function list()
    {
        return view(
            'yys.pages.list',
            [
                'accountList' => YysAccountRepository::getAll([
                    'orderBy' => ['price', 'asc']
                ])
            ]
        );
    }

    public function ranking()
    {
        return view(
            'yys.pages.ranking',
            [
                'accountList' => YysAccountRepository::getRanking()
            ]
        );
    }

    public function detail($sn = null, Request $request)
    {
        if (!empty($request->post('yh_json'))) {
            $apiRes = YysYuhunJsonTransformer::transform($request->post('yh_json'));
            $account = YysAccountTransformer::transform($apiRes);
        } else {
            $accountMedel = YysAccountRepository::get($sn);
            if (!$accountMedel || !$accountMedel->roleId) {
                $account = $this->client->getAccountDetail($sn);
                YysAccountRepository::save($account);
            } else {
                $account = $accountMedel->toArray();
                if (!$account['yuhunScore'] || $request->get('reCalculate')) {
                    // 重新计算御魂分数
                    YysYuhunHelper::reCalculate($account);
                }
            }
        }

        $yuhunHelper = new YysYuhunHelper($account['yuhun']);
        YysHeroHelper::appendPrice($account['hero']);

        return view(
            'yys.pages.detail',
            [
                'account' => $account,
                'groupByName' => $yuhunHelper->groupByName(),
                'pveHeroes' => empty($account['hero']) ? [] : array_filter($account['hero'], function ($hero) {
                    return $hero['atk'] * $hero['cpower'] / 100 > 15000
                        && $hero['set'];
                }),
                'pvpHeroes' => empty($account['hero']) ? [] : array_filter($account['hero'], function ($hero) {
                    return $hero['debuff'] + $hero['resist'] >= 100
                        ||  $hero['spd'] >= 200;
                }),
                'topSpeeds' => $yuhunHelper->getTopSpeeds(),
                'topZcSpeed' => $yuhunHelper->getTopZhaocaiSpeed()
            ]
        );
    }

    public function model($sn)
    {
        return YysAccountRepository::get($sn);
    }


    /* ====== cron ====== */

    public function fetchList()
    {
        $list = $this->client->getAccountList();
        array_walk($list, function ($account) {
            YysAccountRepository::save($account);
        });
        return $list;
    }

    public function updateSingle()
    {
        $account = YysAccountRepository::getNoDetail();

        if ($account) {
            $account = $this->client->getAccountDetail($account->sn);
            YysAccountRepository::save($account);
            return $account['nickname'];
        } else {
            return 'null';
        }
    }
}

<?php

namespace App\Console\Commands\Yys;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\YysClient;
use App\Repositories\YysAccountRepository;

class YysUpdate extends Command
{
    protected $signature = 'yys:update {target} {--type=}';
    protected $description = 'Fetch users from CBG';

    private $client;

    public function __construct(YysClient $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    public function handle()
    {
        $target = $this->argument('target');
        return $this->$target();
    }

    // update account list
    private function list()
    {
        $type = $this->option('type');
        echo 'Fetching YYS account list: [type]' . $type, PHP_EOL;

        if ($type == 'cheap') {
            $param = [
                'platform_type' => 2,
                'strength' => 50000
            ];
        } else {
            $param = [];
        }

        $list = $this->client->getAccountList($param);
        $count = YysAccountRepository::saveAll($list);
        Log::notice("CMD; yys:update; list; {$count} accounts");
    }

    // update account detail
    private function account()
    {
        $account = YysAccountRepository::getNoDetail();

        if ($account) {
            echo "Fetching YYS account detail: {$account['nickname']}", PHP_EOL;
            $account = $this->client->getAccountDetail($account->sn);
            if ($account['price']) {
                YysAccountRepository::save($account);
                Log::notice("CMD; yys:update; account; {$account['nickname']}");
            } else {
                Log::error("CMD; yys:update; account; {$account['nickname']}");
            }
        } else {
            echo 'Nothing to update.';
        }
    }
}

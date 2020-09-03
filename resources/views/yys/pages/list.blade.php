@extends('yys.layouts.app')

@section('title')
账号列表
@endsection

@section('mainContent')
<!-- Page Heading -->
<h1 class="h3 mb-3 text-gray-800">账号列表</h1>

<div class="card shadow mb-4">
    <div class="table-responsive">
        <table class="table text-center table-bordered mb-0" width="100%" cellspacing="0">
            <thead>
                <tr class="bg-gray-300">
                    <th>价格</th>
                    <th>昵称</th>
                    <th>体力</th>
                    <th>勾玉</th>
                    <th>六星</th>
                    <th>+15</th>
                    <th>跑分</th>
                    <th>结界</th>
                    <th>SP,SSR</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($accountList as $account)
                <tr>
                    <td class="text-primary font-weight-bold">￥ {{ $account->price }}</td>
                    <td>
                        <a target="_blank"
                            href="https://yys.cbg.163.com/cgi/mweb/equip/{{ $account->serverId }}/{{ $account->sn }}">
                            {{ $account->nickname }}
                        </a>
                    </td>
                    <td>{{ round($account->hp/10000,1) }} 万</td>
                    <td>{{ $account->gouyu }}</td>
                    <td>{{ $account->star6 }}</td>
                    <td>{{ $account->lv15 }}</td>
                    <td>{{ $account->yuhunScore }}</td>
                    <td>{{ $account->cards }}</td>
                    <td>{{ $account->sp }}, {{ $account->ssr }}</td>
                    <td>
                        <a href="/yys/detail/{{ $account->sn}}" class="btn btn-info btn-sm">
                            <i class="fas fa-info-circle"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
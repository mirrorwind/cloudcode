@extends('yys.layouts.app')

@section('title')
跑分排行榜
@endsection

@section('mainContent')
<h1 class="h3 mb-3 text-gray-800">跑分排行榜</h1>

<div class="card shadow mb-4">
    <div class="table-responsive">
        <table class="table text-center table-bordered mb-0" id="dataTable" width="100%" cellspacing="0">
            <thead>
                <tr class="bg-gray-300">
                    <th>排 名</th>
                    <th>玩家昵称</th>
                    <th>服务器</th>
                    <th>六星式神</th>
                    <th>+15御魂</th>
                    <th>跑 分</th>
                    <th>操 作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($accountList as $account)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td class="text-primary">{{ $account->nickname }}</td>
                    <td>{{ $account->serverName }}</td>
                    <td>{{ $account->star6 }}</td>
                    <td>{{ number_format($account->lv15) }}</td>
                    <td class="text-primary font-weight-bold">{{ number_format($account->yuhunScore) }}</td>
                    <td>
                        <a href="/yys/detail/{{ $account->sn}}" class="btn btn-info btn-sm">
                            <span class="text"><i class="fas fa-info-circle"></i> 详情</span>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
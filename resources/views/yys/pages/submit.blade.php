@extends('yys.layouts.app')

@section('title')
数据提交
@endsection

@section('mainContent')

<h1 class="h3 mb-4 text-gray-800">查看我的跑分</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3 bg-gray-400">
        使用御魂json文件跑分
    </div>
    <div class="card-body">
        <form method="POST" action="/yys/detail/json">
            @csrf
            <div class="form-group">
                <label style="font-size:18px;">请打开 <a target="_blank"
                        href="https://bbs.nga.cn/read.php?tid=15220479"><u><b>御魂导出器</b>by火电太热</u></a> 产生的
                    <b>御魂json文件</b>，把里面的内容，全部复制到下面：（可以用记事本打开）</label>
                <textarea class="form-control" id="yh_json" name="yh_json" rows="8"
                    placeholder="粘贴一瞬间浏览器会卡住几秒到十几秒，为正常现象，请耐心等待，不要重复粘贴"></textarea>
            </div>
            <div class="text-right">
                <button type="submit" class="btn btn-primary" onclick="return json_filter();">提交！开始跑分</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 bg-gray-400">
        使用藏宝阁链接跑分
    </div>
    <div class="card-body">
        <div class="form-group">
            <label style="font-size:18px;">请前往藏宝阁，寻找想要跑分的号，把网址复制到下面：</label>
            <input onclick="this.select()" class="form-control" id="cbg_link" name="cbg_link"
                placeholder="请输入网址 https://..." />
        </div>
        <div class="text-right">
            <button type="button" class="btn btn-primary" onclick="helper.gotoDetail('cbg_link')">提交！开始跑分</button>
        </div>
    </div>
</div>

<script>
    function json_filter() {
    var str = document.getElementById('yh_json').value;
    if (str.substring(0, 30).indexOf('yuhun_ocr2') < 1) {
        alert('数据版本不对，仅支持 御魂导出器v2.2+的数据');
        return false;
    }
    var yh_data = JSON.parse(str);
    var rs_str = '';
    //0御魂类型 1位置 2攻击加成 3生命加成 4速度 5暴击 6暴击伤害 7效果抵抗 8效果命中
    var one = [];
    if (yh_data[0] != "yuhun_ocr2.0") {
        alert('数据解析失败');
        return false;
    }
    //
    for (var i in yh_data) {
        if (typeof yh_data[i] != 'object') continue;
        if (yh_data[i].御魂星级 < 6) continue;
        if (yh_data[i].御魂等级 < 15) continue;
        one.push(yh_data[i].御魂类型 + ',' + yh_data[i].位置 + ',' + attr_f(yh_data[i].攻击加成) + ',' + attr_f(yh_data[i].生命加成) + ',' + attr_f(yh_data[i].速度) + ',' + attr_f(yh_data[i].暴击) + ',' + attr_f(yh_data[i].暴击伤害) + ',' + attr_f(yh_data[i].效果抵抗) + ',' + attr_f(yh_data[i].效果命中));
    }
    document.getElementById('yh_json').value = one.join('|');
    return true;
}
function attr_f(v) {
    if (typeof v == 'undefined') return '';
    else if (v == 0) return '';
    else if (v < 2) return Math.round(v * 10000)/100;
    else return Math.round(v*100)/100;
}
</script>

@endsection
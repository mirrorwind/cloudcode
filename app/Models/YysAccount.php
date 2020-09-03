<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YysAccount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'yuhun' => 'array',
        'hero' => 'array',
    ];

    public function scopeWhereNoDetail($builder)
    {
        return $builder->whereNull('hp');
    }

    public function scopeWhereInStock($builder)
    {
        return $builder->whereNull('status', '=', 2);
    }

    public function getServerIdAttribute()
    {
        return explode('-', $this->sn)[1];
    }

    public function getStatusDescAttribute()
    {
        $status = [
            0 => '已取回',
            2 => '上架中',
            6 => '买家取走',
        ];
        return $status[$this->status] ?? '未知';
    }
}

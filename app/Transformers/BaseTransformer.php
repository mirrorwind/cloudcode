<?php

namespace App\Transformers;

class BaseTransformer
{
    public static function transformItems(array $data, callable $func): array
    {
        return array_values(array_map($func, $data));
    }
}

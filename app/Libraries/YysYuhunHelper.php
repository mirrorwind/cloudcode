<?php

namespace App\Libraries;

class YysYuhunHelper
{
    public $data = [];

    public function __construct(array $yuhunArr)
    {
        $this->data = $yuhunArr;
    }

    public function getScore()
    {
        $score = 0;

        foreach ($this->data as $one) {
            $score += self::getSingleScore($one);
        }

        return $score;
    }

    public static function getSingleScore(array $yuhun)
    {
        $mapping = self::$mapping[$yuhun['name']][$yuhun['pos']][$yuhun['main']];
        $score = 0;
        foreach ($yuhun['attrs']  as $attr => $value) {
            if (in_array($attr, $mapping)) {
                if (in_array($attr, ["生命加成", "防御加成", "攻击加成", "速度", "暴击"])) {
                    $score += $value / 2.7;
                } elseif (in_array($attr, ["暴击伤害", "效果命中", "效果抵抗"])) {
                    $score += $value / 3.6;
                }
            }
        }
        return round(
            pow($score + 2, 1.2) * self::getMultiple($yuhun)
        );
    }

    public static function getMultiple(array $yuhun): float
    {
        $multiple = 10;

        // 从位置考虑
        if ($yuhun['pos'] % 2 == 1) {
            // 1 3 5 号位
        } else {
            // 2 4 6 号位
            $multiple += 2;
            if (in_array($yuhun['main'], ["速度", "暴击", "暴击伤害", "效果命中", "效果抵抗"])) {
                $multiple += 3;
            }
        }

        //从种类考虑
        return $multiple;
    }

    public static function reCalculate(&$accountModel): void
    {
        $totalScore = 0;
        $yuhunNew = [];
        foreach ($accountModel->yuhun as $yuhun) {
            $yuhun['score'] = self::getSingleScore($yuhun);
            $yuhunNew[] = $yuhun;
            $totalScore += $yuhun['score'];
        }
        $accountModel->yuhunScore = round($totalScore);
        $accountModel->yuhun = $yuhunNew;
        $accountModel->save();
    }


    public function groupByName()
    {
        $groupByName = collect($this->data)->groupBy(function ($item) {
            return $item['name'];
        });
        $groupByPos = $groupByName->map(function ($items) {
            $base = [
                1 => ['count' => 0, 'score' => 0],
                2 => ['count' => 0, 'score' => 0],
                3 => ['count' => 0, 'score' => 0],
                4 => ['count' => 0, 'score' => 0],
                5 => ['count' => 0, 'score' => 0],
                6 => ['count' => 0, 'score' => 0],
            ];
            $res = collect($items)->groupBy(function ($i) {
                return $i['pos'];
            })->map(function ($ii) {
                return [
                    'count' => collect($ii)->count(),
                    'score' => collect($ii)->sum('score')
                ];
            })->toArray() + $base;
            krsort($res);
            return array_values($res);
        });

        return $groupByPos->toArray();
    }

    public function getTopSpeeds(string $yuhunName = null)
    {
        $res = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0
        ];
        foreach ($this->data as $yuhun) {
            if ($yuhunName && $yuhun['name'] != $yuhunName) {
                // 御魂种类过滤
                continue;
            }
            if ($yuhun['pos'] == 2) {
                $speed = ($yuhun['main'] == '速度' && isset($yuhun['star']) && $yuhun['star'] == 6)
                    ? ($yuhun['attrs']['速度'] ?? 0) : 0;
            } else {
                $speed = $yuhun['attrs']['速度'] ?? 0;
            }
            $res[$yuhun['pos']] = max($res[$yuhun['pos']], $speed);
        }
        return $res;
    }

    public function getTopZhaocaiSpeed()
    {
        $topSpeeds = $this->getTopSpeeds();
        $zcSpeeds = $this->getTopSpeeds('招财猫');
        $gap = [
            $topSpeeds[1] - $zcSpeeds[1],
            $topSpeeds[2] - $zcSpeeds[2],
            $topSpeeds[3] - $zcSpeeds[3],
            $topSpeeds[4] - $zcSpeeds[4],
            $topSpeeds[5] - $zcSpeeds[5],
            $topSpeeds[6] - $zcSpeeds[6],
        ];
        rsort($gap);
        return array_sum($zcSpeeds) + $gap[0] + $gap[1];
    }




    /* =================== */

    const PVE = ["攻击加成", "速度", "暴击", "暴击伤害"];
    const PVED = ["攻击加成", "速度", "暴击", "暴击伤害", "效果命中"];
    const PVP = ["速度", "效果命中", "效果抵抗"];
    const HEAL = ["生命加成", "速度", "暴击", "暴击伤害"];
    const PVEH = ["攻击加成", "生命加成", "速度", "暴击", "暴击伤害", "效果抵抗"];

    public static $mapping = [
        "兵主部" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ["攻击加成", "速度"],
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "狂骨" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => ['速度', '效果命中']
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ["攻击加成", "速度"],
                "防御加成" => [],
                "生命加成" => ['速度', '效果命中']
            ]
        ],

        "阴摩罗" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ["攻击加成", "速度"],
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "心眼" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => ['效果命中', '速度']
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ["攻击加成", "速度"],
                "防御加成" => [],
                "生命加成" => ['效果命中', '速度']
            ]
        ],

        "鸣屋" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVED,
                "攻击加成" => self::PVED,
                "防御加成" => [],
                "生命加成" => []
            ],
            3 => [
                "防御" => self::PVED
            ],
            4 => [
                "效果命中" => self::PVED,
                "效果抵抗" => [],
                "攻击加成" => self::PVED,
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVED
            ],
            6 => [
                "暴击" => self::PVED,
                "暴击伤害" => self::PVED,
                "攻击加成" => self::PVED,
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "狰" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "轮入道" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ["攻击加成", "速度"],
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "蝠翼" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ["攻击加成", "速度"],
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "青女房" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => ['速度'],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => ['速度'],
                "效果抵抗" => ['速度'],
                "攻击加成" => self::PVE,
                "防御加成" => ['速度'],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => ['速度'],
                "生命加成" => ['速度']
            ]
        ],

        "针女" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ['攻击加成', '速度'],
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "镇墓兽" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ['攻击加成', '速度'],
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "破势" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ['攻击加成', '速度'],
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "伤魂鸟" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ['攻击加成', '速度'],
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "网切" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ['攻击加成', '速度'],
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "三味" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ['攻击加成', '速度'],
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "涂佛" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::PVP,
                "暴击伤害" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "树妖" => [
            1 => [
                "攻击" => self::HEAL
            ],
            2 => [
                "速度" => self::HEAL,
                "攻击加成" => self::HEAL,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::HEAL
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::HEAL,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::HEAL
            ],
            6 => [
                "暴击" => self::HEAL,
                "暴击伤害" => self::HEAL,
                "攻击加成" => self::HEAL,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ]
        ],

        "木魅" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::PVP,
                "暴击伤害" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "薙魂" => [
            1 => [
                "攻击" => self::HEAL
            ],
            2 => [
                "速度" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::HEAL
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::HEAL
            ],
            6 => [
                "暴击" => self::HEAL,
                "暴击伤害" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ]
        ],

        "钟灵" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::HEAL,
                "暴击伤害" => self::PVE,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "镜姬" => [
            1 => [
                "攻击" => self::HEAL
            ],
            2 => [
                "速度" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::HEAL
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::HEAL
            ],
            6 => [
                "暴击" => self::HEAL,
                "暴击伤害" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "被服" => [
            1 => [
                "攻击" => self::HEAL
            ],
            2 => [
                "速度" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::HEAL
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::HEAL
            ],
            6 => [
                "暴击" => self::HEAL,
                "暴击伤害" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "涅槃之火" => [
            1 => [
                "攻击" => self::HEAL
            ],
            2 => [
                "速度" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::HEAL
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::HEAL
            ],
            6 => [
                "暴击" => self::HEAL,
                "暴击伤害" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "地藏像" => [
            1 => [
                "攻击" => self::HEAL
            ],
            2 => [
                "速度" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::HEAL
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::HEAL
            ],
            6 => [
                "暴击" => self::HEAL,
                "暴击伤害" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ]
        ],

        "魅妖" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::PVP,
                "暴击伤害" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "珍珠" => [
            1 => [
                "攻击" => self::HEAL
            ],
            2 => [
                "速度" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::HEAL
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::HEAL
            ],
            6 => [
                "暴击" => self::HEAL,
                "暴击伤害" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ]
        ],

        "日女巳时" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::PVP,
                "暴击伤害" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "反枕" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::PVP,
                "暴击伤害" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "招财猫" => [
            1 => [
                "攻击" => ["生命加成", "攻击加成", "速度", "暴击", "暴击伤害", "效果命中", "效果抵抗"]
            ],
            2 => [
                "速度" => ["生命加成", "攻击加成", "速度", "暴击", "暴击伤害", "效果命中", "效果抵抗"],
                "攻击加成" => ["攻击加成", "速度", "暴击", "暴击伤害"],
                "防御加成" => [],
                "生命加成" => ["生命加成", "速度", "暴击", "暴击伤害", "效果抵抗"]
            ],
            3 => [
                "防御" => ["生命加成", "攻击加成", "速度", "暴击", "暴击伤害", "效果命中", "效果抵抗"]
            ],
            4 => [
                "效果命中" => ["生命加成",  "速度", "效果命中", "效果抵抗"],
                "效果抵抗" => ["生命加成",  "速度",  "效果命中", "效果抵抗"],
                "攻击加成" => ["攻击加成", "速度", "暴击", "暴击伤害"],
                "防御加成" => ["速度"],
                "生命加成" => ["生命加成", "速度", "暴击", "暴击伤害"]
            ],
            5 => [
                "生命" => ["生命加成",  "攻击加成", "速度", "暴击", "暴击伤害", "效果命中", "效果抵抗"]
            ],
            6 => [
                "暴击" => ["生命加成", "攻击加成", "速度", "暴击", "暴击伤害",],
                "暴击伤害" => ["生命加成",  "攻击加成", "速度", "暴击", "暴击伤害"],
                "攻击加成" => ["攻击加成", "速度", "暴击", "暴击伤害"],
                "防御加成" => ["速度"],
                "生命加成" => ["生命加成", "速度", "效果命中", "效果抵抗"]
            ]
        ],

        "雪幽魂" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::PVP,
                "暴击伤害" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "飞缘魔" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::PVP,
                "暴击伤害" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "蚌精" => [
            1 => [
                "攻击" => self::HEAL
            ],
            2 => [
                "速度" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            3 => [
                "防御" => self::HEAL
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::HEAL
            ],
            6 => [
                "暴击" => self::HEAL,
                "暴击伤害" => self::HEAL,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => self::HEAL
            ]
        ],

        "火灵" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::PVP,
                "暴击伤害" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "幽谷响" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => ['生命加成', '速度']
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::PVP,
                "暴击伤害" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "返魂香" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => ['生命加成', '速度']
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::PVP,
                "暴击伤害" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "骰子鬼" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => ['生命加成', '速度']
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::PVP,
                "暴击伤害" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "魍魉之匣" => [
            1 => [
                "攻击" => self::PVP
            ],
            2 => [
                "速度" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ],
            3 => [
                "防御" => self::PVP
            ],
            4 => [
                "效果命中" => self::PVP,
                "效果抵抗" => self::PVP,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVP
            ],
            6 => [
                "暴击" => self::PVP,
                "暴击伤害" => self::PVP,
                "攻击加成" => self::PVP,
                "防御加成" => [],
                "生命加成" => self::PVP
            ]
        ],

        "鬼灵歌伎" => [
            1 => [
                "攻击" => self::PVE
            ],
            2 => [
                "速度" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            3 => [
                "防御" => self::PVE
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVE
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ]
        ],

        "蜃气楼" => [
            1 => [
                "攻击" => self::PVEH
            ],
            2 => [
                "速度" => self::PVEH,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::PVEH
            ],
            3 => [
                "防御" => self::PVEH
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => ['效果抵抗', '速度'],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::PVEH
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => ['效果抵抗', '速度']
            ]
        ],

        "地震鲶" => [
            1 => [
                "攻击" => self::PVEH
            ],
            2 => [
                "速度" => self::PVEH,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::PVEH
            ],
            3 => [
                "防御" => self::PVEH
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => ['效果抵抗', '速度'],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::PVEH
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => ['效果抵抗', '速度']
            ]
        ],

        "荒骷髅" => [
            1 => [
                "攻击" => self::PVED
            ],
            2 => [
                "速度" => self::PVED,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => ['速度', '效果命中']
            ],
            3 => [
                "防御" => self::PVED
            ],
            4 => [
                "效果命中" => ['效果命中', '速度'],
                "效果抵抗" => [],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => []
            ],
            5 => [
                "生命" => self::PVED
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => ['效果命中', "攻击加成", "速度"],
                "防御加成" => [],
                "生命加成" => ['速度', '效果命中']
            ]
        ],

        "胧车" => [
            1 => [
                "攻击" => self::PVEH
            ],
            2 => [
                "速度" => self::PVEH,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::PVEH
            ],
            3 => [
                "防御" => self::PVEH
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => ['效果抵抗', '速度'],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::PVEH
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => ['效果抵抗', '速度']
            ]
        ],

        "土蜘蛛" => [
            1 => [
                "攻击" => self::PVEH
            ],
            2 => [
                "速度" => self::PVEH,
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::PVEH
            ],
            3 => [
                "防御" => self::PVEH
            ],
            4 => [
                "效果命中" => [],
                "效果抵抗" => ['效果抵抗', '速度'],
                "攻击加成" => self::PVE,
                "防御加成" => [],
                "生命加成" => self::HEAL
            ],
            5 => [
                "生命" => self::PVEH
            ],
            6 => [
                "暴击" => self::PVE,
                "暴击伤害" => self::PVE,
                "攻击加成" => [],
                "防御加成" => [],
                "生命加成" => ['效果抵抗', '速度']
            ]
        ]
    ];
}

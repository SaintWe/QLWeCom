<?php

namespace App\Http\Service\JdParse;

use App\Http\Service\JdParseInterface;
use App\Models\JdCk;

class Jxgc extends JdParseInterface
{
    /**
     * null 或 0 使用主要进行推送，其他的数字使用其他推送
     *
     * @var string
     */
    public $push_id = null;

    public $title   = '/(京喜工厂)/i';

    /**
     * @param string $node 节点 ID
     * @param string $text 推送标题
     * @param string $desp 推送内容
     */
    public function handle(string $node, string $text, string $desp): array
    {
        $usernames = implode('|', array_merge($this->get_jd_nicknames(), $this->get_jd_usernames()));
        if ($usernames !== '') {
            $split_data = preg_split('/\n+(?=.*?(京东账号|京东号|账号\s?\d+|账号名称：)\s?\d*.*)/m', $desp, -1, PREG_SPLIT_NO_EMPTY);
            if ($split_data !== false) {
                $data = preg_grep('/(生产完成|已可兑换)/i', $split_data);
                foreach ($data as $value) {
                    if (preg_match('/(?<=京东账号|京东号|账号|账号名称：)\s?\d*.*(' . $usernames . ')/i', $value, $matches) === 1) {
                        $JdCk = JdCk::where(
                            static function ($query) use ($matches) {
                                $query->where('nickname', $matches[1])
                                    ->orWhere('username', $matches[1]);
                            }
                        )->whereNotNull('nickname')->first();
                        if ($JdCk !== null) {
                            $user = $JdCk->get_user();
                            if ($user !== null) {
                                $this->WeComPush->push_text($text . PHP_EOL . PHP_EOL . $value, $user->wecom_id, '', '', 0, 0, 1, 14400);
                            }
                        }
                    }
                }
            }
        }
        return [
            'retcode' => 0,
            'status'  => true,
            'message' => 'succeed.',
        ];
    }
}

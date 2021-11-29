<?php

namespace App\Http\Service\JdParse;

use App\Http\Service\JdParseInterface;
use App\Models\JdCk;
use Illuminate\Support\Facades\Cache;

class Cookie extends JdParseInterface
{
    /**
     * null 或 0 使用主要进行推送，其他的数字使用其他推送
     *
     * @var string
     */
    public $push_id = null;

    public $title   = '/(Cookie).*失效/i';

    /**
     * @param string $node 节点 ID
     * @param string $text 推送标题
     * @param string $desp 推送内容
     */
    public function handle(string $node, string $text, string $desp): array
    {
        // Cookie 失效只需要判断 pt_pin，且失效都是单独推送，无需分割
        $usernames = implode('|', $this->get_jd_usernames());
        if ($usernames !== '') {
            if (preg_match('/(?<=京东账号|京东号|账号|账号名称：)\s?\d*.*(' . $usernames . ')/i', $desp, $matches) === 1) {
                $JdCk = JdCk::where('username', $matches[1])->first();
                if ($JdCk !== null) {
                    /**
                     * @var JdWskey $JdCk
                     */
                    $user = $JdCk->get_user();
                    if ($user !== null) {
                        if ($JdCk->wskey2pt_key() === false) {
                            if (Cache::get('jd_cookie_' . $matches[1]) === null) {
                                Cache::put('jd_cookie_' . $matches[1], 0, 14400);   // 4 小时
                                $this->WeComPush->push_text($text . PHP_EOL . PHP_EOL . $desp, $user->wecom_id, '', '', 0, 0, 1, 14400);
                            }
                        }
                    }
                }
            }
        }
        return [
            'retcode' => 0,
            'status'  => true,
            'message' => 'succeed.'
        ];
    }
}

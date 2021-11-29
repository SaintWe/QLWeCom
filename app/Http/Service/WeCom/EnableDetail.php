<?php

namespace App\Http\Service\WeCom;

use App\Http\Service\WeComInterface;
use App\Models\PushUser;

class EnableDetail extends WeComInterface
{
    /**
     * 匹配命令
     */
    public const COMMAND = '/^(开启详细推送)$/i';

    /**
     * @param string $WeComID
     * @param string $Content
     * @param array  $Message
     */
    public static function handle(string $WeComID, string $Content, array $Message, array $matches = []): ?string
    {
        $user = PushUser::where('wecom_id', $WeComID)->first();
        if ($user === null) {
            return '你没有绑定节点，请先绑定节点';
        }
        return $user->enable_detail() ? '开启详细推送成功' : '开启详细推送失败';
    }
}

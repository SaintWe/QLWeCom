<?php

namespace App\Http\Service\WeCom;

use App\Http\Service\WeComInterface;
use App\Models\PushUser;

class DisableWskey extends WeComInterface
{
    /**
     * 匹配命令
     */
    public const COMMAND = '/^(关闭转换推送)$/i';

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
        return $user->disable_wskey2cookie() ? '关闭转换推送成功' : '关闭转换推送失败';
    }
}

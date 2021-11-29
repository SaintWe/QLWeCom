<?php

namespace App\Http\Service\WeCom;

use App\Http\Service\WeComInterface;

class Help extends WeComInterface
{
    /**
     * 匹配命令
     */
    public const COMMAND = '/^(帮助|help)$/i';

    /**
     * @param string $WeComID
     * @param string $Content
     * @param array  $Message
     */
    public static function handle(string $WeComID, string $Content, array $Message, array $matches = []): ?string
    {
        return '----全部指令----' . PHP_EOL
            . PHP_EOL
            . '绑定节点 ID' . PHP_EOL
            . '    - 示例：绑定节点 1' . PHP_EOL
            . '    - 说明：绑定指定的青龙节点' . PHP_EOL
            . PHP_EOL
            . '添加用户名 用户名' . PHP_EOL
            . '    - 示例：添加用户名 username' . PHP_EOL
            . '    - 说明：添加用户名之后才可使用短信登录或发送 Cookie 更新' . PHP_EOL
            . PHP_EOL
            . '开启详细推送' . PHP_EOL
            . '    - 说明：开启后接收各种任务的信息推送' . PHP_EOL
            . PHP_EOL
            . '关闭详细推送' . PHP_EOL
            . '    - 说明：关闭后仅接收资产变动和 Cookie 或 Wskey 失效的推送' . PHP_EOL
            . PHP_EOL
            . '开启转换推送' . PHP_EOL
            . '    - 说明：开启后会接收 Wskey 转 Cookie 成功的推送' . PHP_EOL
            . PHP_EOL
            . '关闭转换推送' . PHP_EOL
            . '    - 说明：关闭后会不会接收 Wskey 转 Cookie 的推送' . PHP_EOL
            . PHP_EOL
            . '----其他说明----' . PHP_EOL
            . PHP_EOL
            . '    - 发送你的 Cookie 可以更新 Cookie' . PHP_EOL
            . '    - 发送你的 Wskey 可以更新 Wskey' . PHP_EOL
            . '    - Wskey 格式：wskey=xxxxxx;pin=xxxxx;' . PHP_EOL
            ;
    }
}

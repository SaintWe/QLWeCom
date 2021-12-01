<?php

namespace App\Http\Service\WeCom;

use App\Http\Service\WeComInterface;
use App\Models\JdCk;
use App\Models\PushUser;
use Illuminate\Support\Str;

class addUsername extends WeComInterface
{
    /**
     * 匹配命令
     */
    public const COMMAND = '/(?<=^添加用户名).*/i';

    /**
     * 描述
     */
    public const DESCRIBE = '    - 示例：添加用户名 username' . PHP_EOL
        . '    - 说明：添加用户名之后才可使用短信登录或发送 Cookie 更新' . PHP_EOL;

    /**
     * @param string $WeComID
     * @param string $Content
     * @param array  $Message
     */
    public static function handle(string $WeComID, string $Content, array $Message, array $matches = []): ?string
    {
        $arguments = trim($matches[0]);
        if ($arguments == '') {
            return self::DESCRIBE;
        }
        if (strpos($arguments, ' ') !== false) {
            return '您的提交的用户名不能包含空格，请修正';
        }
        return self::execution($WeComID, $arguments);
    }

    /**
     * @param string $WeComID
     * @param string $UserName
     */
    public static function execution(string $WeComID, string $UserName): ?string
    {
        $user = PushUser::where('wecom_id', $WeComID)->first();
        if ($user === null) {
            return '你没有绑定节点，请先绑定节点';
        }
        $UserName = urldecode($UserName);
        if (JdCk::where('username', $UserName)->exists()) {
            return '提交的用户名已存在';
        }
        $JdCk           = new JdCk();
        $JdCk->uuid     = Str::uuid();
        $JdCk->user_id  = $user->id;
        $JdCk->username = urldecode($UserName);
        $JdCk->nickname = null;
        return $JdCk->save()
            ? '用户名【' . $UserName . '】提交成功' . PHP_EOL . PHP_EOL . '    - 请在今晚凌晨 1 点前添加有效的 Cookie，否则该用户名将被自动移除' . PHP_EOL . '    - 同时，您添加的账户所有推送均由您接收'
            : '提交失败，请稍后再试或联系管理员';
    }
}

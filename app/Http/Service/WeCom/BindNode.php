<?php

namespace App\Http\Service\WeCom;

use App\Http\Service\QingLong\QingLongApi;
use App\Http\Service\WeComInterface;
use App\Models\PushUser;

class BindNode extends WeComInterface
{
    /**
     * 匹配命令
     */
    public const COMMAND = '/(?<=^绑定节点).*/i';

    /**
     * 描述
     */
    public const DESCRIBE = '    - 示例：绑定节点 1' . PHP_EOL
            . '    - 说明：绑定指定的青龙节点' . PHP_EOL
            . '    - 注意：换绑的情况下您所有的账户将只能更新到新节点，而且受到该节点的余量限制，如 Cookie 无法上载到节点等' . PHP_EOL;

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
        if (is_numeric($arguments) === false) {
            return '你提供的节点 ID 非数字';
        }
        return self::execution($WeComID, $arguments);
    }

    /**
     * @param string $WeComID
     * @param string $NodeID
     */
    public static function execution(string $WeComID, string $NodeID): ?string
    {
        if (QingLongApi::use($NodeID) === null) {
            return '该 ID 不在允许的节点 ID 范围';
        }
        $user = PushUser::where('wecom_id', $WeComID)->first();
        if ($user === null) {
            $user           = new PushUser();
            $user->node_id  = $NodeID;
            $user->wecom_id = $WeComID;
            return $user->save() ? '绑定「节点' . $NodeID . '」成功' : '绑定失败';
        }
        if ($user->node_id == $NodeID) {
            return '你提供的 ID 和现在使用的 ID 一致，不予更改';
        }
        $user->node_id = $NodeID;
        return $user->save() ? '更换绑定到「节点' . $NodeID . '」成功' . PHP_EOL . PHP_EOL . '请注意：您所有的账户将只能更新到新节点，而且受到该节点的余量限制，如 Cookie 无法上载到节点等' : '更换绑定失败';
    }
}

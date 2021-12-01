<?php

namespace App\Http\Service\WeCom;

use App\Http\Service\Jd\JdAPI;
use App\Http\Service\QingLong\QingLongApi;
use App\Http\Service\WeComInterface;
use App\Models\JdCk;
use App\Models\PushUser;
use Illuminate\Support\Str;

class Cookie extends WeComInterface
{
    /**
     * 匹配命令
     */
    public const COMMAND = '/(Cookie)/i';

    /**
     * @param string $WeComID
     * @param string $Content
     * @param array  $Message
     */
    public static function handle(string $WeComID, string $Content, array $Message, array $matches = []): ?string
    {
        if (
            preg_match('/pt_pin=([^;]+?);/', $Content, $pt_pin) === 1
            &&
            preg_match('/pt_key=([^;]+?);/', $Content, $pt_key) === 1
        ) {
            $cookie = $pt_pin[0] . $pt_key[0];
            $user   = PushUser::where('wecom_id', $WeComID)->first();
            if ($user === null) {
                return '你没有绑定节点，请先绑定节点';
            }
            $nickname = JdAPI::check_get_nickname($cookie);
            if ($nickname === null) {
                return '你提供的 Cookie 有问题，请重新获取';
            }
            $QingLongApi = QingLongApi::use($user->node_id);
            if ($QingLongApi === null) {
                return '你所在的节点已经凉了，请更换绑定';
            }
            $env = $QingLongApi->env_get($pt_pin[0]);
            if ($env->json('data.0') === null) {
                if (QingLongApi::account_not_found($user->node_id) === false) {
                    return '管理员关闭了新用户注册';
                }
                if (QingLongApi::account_limit($user->node_id) <= $QingLongApi->jd_cks()) {
                    return '你所绑定的节点的注册账户已达上限，请尝试更换节点';
                }
                $env_post = $QingLongApi->env_post([
                    [
                        'name'    => 'JD_COOKIE',
                        'value'   => $cookie,
                        'remarks' => 'WeCom@' . $user->wecom_id,
                    ]
                ]);
                $resultText = $env_post->ok() ? ' Cookie 添加成功' : ' Cookie 添加失败，节点可能挂了';
            } else {
                $env_put = $QingLongApi->env_put([
                    'name'    => $env->json('data.0.name'),
                    'remarks' => $env->json('data.0.remarks'),
                    '_id'     => $env->json('data.0._id'),
                    'value'   => $cookie,
                ]);
                $resultText = $env_put->ok() ? ' Cookie 更新成功' : ' Cookie 更新失败，节点可能挂了';
            }
            $JdCk = JdCk::where('user_id', $user->id)->where('username', $pt_pin[1])->first();
            if ($JdCk === null) {
                $JdCk           = new JdCk();
                $JdCk->uuid     = Str::uuid();
                $JdCk->user_id  = $user->id;
                $JdCk->username = urldecode($pt_pin[1]);
            }
            $JdCk->nickname = $nickname;
            $JdCk->save();
            return '你的账号【' . $nickname . '】' . date('Y-m-d H:i:s') . $resultText;
        } else {
            return '未匹配到 pt_pin 和 pt_key';
        }
    }
}

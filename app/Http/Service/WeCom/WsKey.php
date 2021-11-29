<?php

namespace App\Http\Service\WeCom;

use App\Http\Service\Jd\JdAPI;
use App\Http\Service\QingLong\QingLongApi;
use App\Http\Service\WeComInterface;
use App\Models\JdCk;
use App\Models\JdWskey;
use App\Models\PushUser;

class WsKey extends WeComInterface
{
    /**
     * 匹配命令
     */
    public const COMMAND = '/(WsKey)/i';

    /**
     * @param string $WeComID
     * @param string $Content
     * @param array  $Message
     */
    public static function handle(string $WeComID, string $Content, array $Message, array $matches = []): ?string
    {
        if (
            preg_match('/pin=([^;]+?);/', $Content, $pin) === 1
            &&
            preg_match('/wskey=([^;]+?);/', $Content, $wskey) === 1
        ) {
            $wscookie = $pin[0] . $wskey[0];
            $user     = PushUser::where('wecom_id', $WeComID)->first();
            if ($user === null) {
                return '你没有绑定节点，请先绑定节点';
            }
            $JdCk = JdCk::where('user_id', $user->id)->where('username', $pin[1])->first();
            if ($JdCk === null) {
                return '你需要先通过企业微信更新一次此用户名的 Cookie 才能添加 Wskey';
            }
            $QingLongApi = QingLongApi::use($user->node_id);
            if ($QingLongApi === null) {
                return '你所在的节点已经凉了，请更换绑定';
            }
            $env = $QingLongApi->env_get('pt_' . $pin[0]);
            if ($env->json('data.0') === null) {
                return '你的 Cookie 不存在你绑定的节点，请先通过企业微信更新一次此用户名的 Cookie 才能添加 Wskey';
            }
            $cookie = JdAPI::wskey2pt_key($wscookie);
            if ($cookie === null) {
                return '你提供的 Wskey 有问题，请重新获取';
            }
            $nickname = JdAPI::check_get_nickname($cookie);
            if ($nickname === null) {
                return 'Wskey 转换的 Cookie 有问题，请等待修复';
            }
            $JdCk->nickname = $nickname;
            $JdCk->save();
            $JdWskey = JdWskey::where('user_id', $user->id)->where('username', $pin[1])->first();
            if ($JdWskey === null) {
                $JdWskey           = new JdWskey();
                $JdWskey->user_id  = $user->id;
                $JdWskey->username = $pin[1];
            }
            $JdWskey->enable = true;
            $JdWskey->wskey  = $wskey[1];
            $JdWskey->save();
            $env_put = $QingLongApi->env_put([
                'name'    => $env->json('data.0.name'),
                'remarks' => $env->json('data.0.remarks'),
                '_id'     => $env->json('data.0._id'),
                'value'   => $cookie,
            ]);
            $resultText = $env_put->ok() ? '更新成功' : '更新失败，节点可能挂了';
            return '你的账号【' . $nickname . '】Wskey 转换 Cookie ' . $resultText . PHP_EOL . PHP_EOL . date('Y-m-d H:i:s');
        } else {
            return '未匹配到 pin 和 wskey';
        }
    }
}

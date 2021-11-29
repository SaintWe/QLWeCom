<?php

namespace App\Http\Service;

use App\Http\Service\Jd\JdAPI;
use App\Http\Service\QingLong\QingLongApi;
use App\Http\Service\WeComPush\WeComPush;
use App\Models\JdCk;

class UpdateJdCk
{
    /**
     * update_jd_ck
     */
    public static function update_jd_ck(string $value, bool $is_push = true): array
    {
        if (preg_match('/pt_pin=([^;]+?);/', $value, $pt_pin) === 1 && preg_match('/pt_key=([^;]+?);/', $value, $pt_key) === 1) {
            $cookie   = $pt_pin[0] . $pt_key[0];
            $username = urldecode($pt_pin[1]);
            $JdCk     = JdCk::where('username', $username)->first();
            if ($JdCk === null) {
                return [
                    'code'   => 403,
                    'ck_id'  => 0,
                    'pt_pin' => '',
                    'pt_key' => '',
                    'msg'    => '你的京东账户不在允许范围内',
                ];
            }
            $user = $JdCk->get_user();
            if ($user === null) {
                return [
                    'code'   => 403,
                    'ck_id'  => 0,
                    'pt_pin' => '',
                    'pt_key' => '',
                    'msg'    => '你的京东账户不在允许范围内',
                ];
            }
            $nickname = JdAPI::check_get_nickname($cookie);
            if ($nickname === null) {
                return [
                    'code'   => 403,
                    'ck_id'  => 0,
                    'pt_pin' => '',
                    'pt_key' => '',
                    'msg'    => '你提供的 Cookie 有问题，请重新获取',
                ];
            }
            $JdCk->nickname = $nickname;
            $JdCk->save();
            $QingLongApi = QingLongApi::use($user->node_id);
            if ($QingLongApi === null) {
                return [
                    'code'   => 403,
                    'ck_id'  => 0,
                    'pt_pin' => '',
                    'pt_key' => '',
                    'msg'    => '你所在的节点已经凉了，请更换绑定',
                ];
            }
            $env = $QingLongApi->env_get($pt_pin[0]);
            if ($env->json('data.0') === null) {
                if (QingLongApi::account_limit($user->node_id) <= $QingLongApi->jd_cks()) {
                    return [
                        'code'   => 403,
                        'ck_id'  => 0,
                        'pt_pin' => '',
                        'pt_key' => '',
                        'msg'    => '你所绑定的节点的注册账户已达上限，请尝试更换节点',
                    ];
                }
                $env_post = $QingLongApi->env_post([
                    [
                        'name'    => 'JD_COOKIE',
                        'value'   => $cookie,
                        'remarks' => 'WeCom@' . $user->wecom_id,
                    ]
                ]);
                $statusCode = $env_post->ok() ? 200 : 403;
            } else {
                $env_put = $QingLongApi->env_put([
                    'name'    => $env->json('data.0.name'),
                    'remarks' => $env->json('data.0.remarks'),
                    '_id'     => $env->json('data.0._id'),
                    'value'   => $cookie,
                ]);
                $statusCode = $env_put->ok() ? 200 : 403;
            }
            $resultText  = '你的账号【' . $nickname . '】' . date('Y-m-d H:i:s');
            $resultText .= $statusCode === 200 ? ' Cookie 更新成功' : ' Cookie 更新失败，服务器可能挂了';
            if ($is_push) {
                $WeComPush = WeComPush::use();
                $WeComPush->push_text($resultText, $user->wecom_id, '', '', 0, 0, 1, 14400);
            }
            return [
                'code'   => $statusCode,
                'ck_id'  => $JdCk->id,
                'pt_pin' => $username,
                'pt_key' => $pt_key[0],
                'msg'    => $resultText,
            ];
        }
        return [
            'code'   => 403,
            'ck_id'  => 0,
            'pt_pin' => '',
            'pt_key' => '',
            'msg'    => '未匹配到 pt_pin 和 pt_key',
        ];
    }
}

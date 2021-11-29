<?php

namespace App\Http\Service\Jd;

use App\Http\Service\QingLong\QingLongApi;
use App\Http\Service\WeComPush\WeComPush;
use App\Http\Service\WskeySign;
use App\Models\JdWskey;
use App\Models\PushUser;
use Illuminate\Support\Facades\Http;

class JdAPI
{
    /**
     * Wskey 转 pt_key 并上传到青龙
     *
     * @param string         $username
     * @param PushUser|null  $user
     * @param WeComPush|null $WeComPush
     */
    public static function wskey2ptkey_up(string $username, PushUser $user = null, WeComPush $WeComPush = null): bool
    {
        $JdWskey = JdWskey::where('enable', 1)->where('username', $username)->first();
        if ($JdWskey !== null) {
            /** @var JdWskey $JdWskey */
            $user = $user ?? $JdWskey->get_user();
            if ($user !== null) {
                $cookie = self::wskey2pt_key($JdWskey->to_str());
                if ($cookie !== null) {
                    $nickname = self::check_get_nickname($cookie);
                    if ($nickname !== null) {
                        $QingLongApi = QingLongApi::use($user->node_id);
                        if ($QingLongApi !== null) {
                            $env = $QingLongApi->env_get('pt_pin=' . $username . ';');
                            if ($env->json('data.0') !== null) {
                                $env_put = $QingLongApi->env_put([
                                    'name'    => $env->json('data.0.name'),
                                    'remarks' => $env->json('data.0.remarks'),
                                    '_id'     => $env->json('data.0._id'),
                                    'value'   => $cookie,
                                ]);
                                if ($env_put->ok()) {
                                    if ($user->wskey2cookie) {
                                        $WeComPush = $WeComPush ?? WeComPush::use();
                                        $WeComPush->push_text('你旗下账户【' . $nickname . '】的 Wskey 成功转换成 Cookie', $user->wecom_id, '', '', 0, 0, 1, 14400);
                                    }
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param string $Cookie
     */
    public static function check_get_nickname($Cookie): ?string
    {
        $result = Http::withHeaders([
            'Accept'            => '*/*',
            'Accept-Encoding'   => 'gzip, deflate, br',
            'Accept-Language'   => 'zh-cn',
            'Connection'        => 'keep-alive',
            'Cookie'            => $Cookie,
            'Referer'           => 'https://home.m.jd.com/myJd/newhome.action',
            'User-Agent'        => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36',
            'Host'              => 'me-api.jd.com',
        ])->get(
            'https://me-api.jd.com/user_new/info/GetJDUserInfoUnion?orgFlag=JD_PinGou_New&callSource=mainorder&channel=4&isHomewhite=0&sceneval=2&_=' . time() . '&sceneval=2&g_login_type=1&g_ty=ls'
        );
        if ($result->ok() && $result->json('retcode', 0) == '0') {
            return $result->json('data.userInfo.baseInfo.nickname');
        }
        return null;
    }

    /**
     * @param string $Cookie
     */
    public static function check_get_nickname_bak($Cookie): ?string
    {
        $result = Http::withHeaders([
            'Connection'        => 'keep-alive',
            'Cookie'            => $Cookie,
            'Referer'           => 'https://home.m.jd.com/myJd/home.action',
            'User-Agent'        => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36',
        ])->get(
            'https://wq.jd.com/user_new/info/GetJDUserInfoUnion?orgFlag=JD_PinGou_New&callSource=mainorder'
        );
        if ($result->ok() && $result->json('retcode', 0) === 0) {
            return $result->json('data.userInfo.baseInfo.nickname');
        }
        return null;
    }

    /**
     * Wskey 转 pt_key
     *
     * @param string $Cookie
     */
    public static function wskey2pt_key($Cookie): ?string
    {
        $sign_info = WskeySign::genToken();
        if ($sign_info === null) {
            return null;
        }
        $tokenKey = Http::withHeaders([
            'Cookie'          => $Cookie,
            'User-Agent'      => 'okhttp/3.12.1;jdmall;android;version/10.1.2;build/89743;screen/1440x3007;os/11;network/wifi;',
            'Accept-Charset'  => 'UTF-8',
            'Accept-Encoding' => 'br,gzip,deflate'
        ])
            ->asForm()
            ->post(
                'https://api.m.jd.com/client.action?functionId=genToken',
                $sign_info
            );

        $cookie = Http::withOptions(['allow_redirects' => false])
            ->withHeaders([
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
                'User-Agent'      => 'okhttp/3.12.1;jdmall;android;version/10.1.2;build/89743;screen/1440x3007;os/11;network/wifi;',
                'Accept-Charset'  => 'UTF-8',
                'Accept-Encoding' => 'br,gzip,deflate'
            ])
            ->get(
                'https://un.m.jd.com/cgi-bin/app/appjmp',
                [
                    'tokenKey'    => $tokenKey->json('tokenKey'),
                    'to'          => 'https://plogin.m.jd.com/cgi-bin/m/thirdapp_auth_page?token=AAEAIEijIw6wxF2s3bNKF0bmGsI8xfw6hkQT6Ui2QVP7z1Xg',
                    'client_type' => 'apple',
                    'appid'       => 879,
                    'appup_type'  => 1,
                ]
            );

        $pt_pin = $cookie->cookies()->getCookieByName('pt_pin')->getValue();
        $pt_key = $cookie->cookies()->getCookieByName('pt_key')->getValue();
        if ($pt_pin !== null && $pt_key !== null && strpos($pt_key, 'fake_') === false) {
            return 'pt_pin=' . $pt_pin . ';pt_key=' . $pt_key . ';';
        }
        return null;
    }
}

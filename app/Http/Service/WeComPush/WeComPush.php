<?php

namespace App\Http\Service\WeComPush;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeComPush
{
    /**
     * @param string|int $push_id
     */
    public static function use($node_id = null): ?self
    {
        if (empty($node_id)) {
            return new self(
                env('WECOM_CORP_ID', ''),
                env('WECOM_APP_AGENT_ID', 0),
                env('WECOM_APP_SECRET', '')
            );
        }
        $corpid     = env('WECOM_CORP_ID_OTHER_' . $node_id, null);
        $agentid    = env('WECOM_APP_AGENT_ID_OTHER_' . $node_id, null);
        $corpsecret = env('WECOM_APP_SECRET_OTHER_' . $node_id, null);
        if (
            !empty($corpid)
            &&
            !empty($agentid)
            &&
            !empty($corpsecret)
        ) {
            return new self($corpid, $agentid, $corpsecret);
        }
        return null;
    }

    /**
     * @var string
     */
    protected $corpid;

    /**
     * @var string
     */
    protected $agentid;

    /**
     * @var string
     */
    protected $corpsecret;

    /**
     * @param string $corpid
     * @param string $agentid
     * @param string $corpsecret
     */
    public function __construct(string $corpid, string $agentid, string $corpsecret)
    {
        $this->corpid     = $corpid;
        $this->agentid    = $agentid;
        $this->corpsecret = $corpsecret;
    }

    public function push_text(
        string $message,
        string $touser                   = '@all',
        string $toparty                  = '@all',
        string $totag                    = '@all',
        int    $safe                     = 0,
        int    $enable_id_trans          = 0,
        int    $enable_duplicate_check   = 0,
        int    $duplicate_check_interval = 1800
    ): array {
        $data = [
            'msgtype'                  => 'text',
            'agentid'                  => $this->agentid,
            'text'                     => [
                'content' => $message,
            ],
            'touser'                   => $touser,
            'toparty'                  => $toparty,
            'totag'                    => $totag,
            'safe'                     => $safe,
            'enable_id_trans'          => $enable_id_trans,
            'enable_duplicate_check'   => $enable_duplicate_check,
            'duplicate_check_interval' => $duplicate_check_interval,
        ];
        $push = Http::post('https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=' . $this->getAccessToken(), $data);
        return [
            'ok'   => $push->json('errcode') === 0 ? true : false,
            'data' => $push->json()
        ];
    }

    public function push_markdown(
        string $message,
        string $touser                   = '@all',
        string $toparty                  = '@all',
        string $totag                    = '@all',
        int    $enable_duplicate_check   = 0,
        int    $duplicate_check_interval = 1800
    ): array {
        $data = [
            'msgtype'                  => 'markdown',
            'agentid'                  => $this->agentid,
            'markdown'                 => [
                'content' => $message,
            ],
            'touser'                   => $touser,
            'toparty'                  => $toparty,
            'totag'                    => $totag,
            'enable_duplicate_check'   => $enable_duplicate_check,
            'duplicate_check_interval' => $duplicate_check_interval,
        ];
        $push = Http::post('https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=' . $this->getAccessToken(), $data);
        return [
            'ok'   => $push->json('errcode') === 0 ? true : false,
            'data' => $push->json()
        ];
    }

    /**
     * 获取 Token
     */
    protected function getAccessToken(): ?string
    {
        $CacheKey = 'wecom_app_access_token_' . $this->corpid . '_' . $this->agentid;
        return Cache::get(
            $CacheKey,
            function () use ($CacheKey) {
                $Cache = $this->getToken();
                if ($Cache !== null) {
                    Cache::put($CacheKey, $Cache['access_token'], $Cache['expires_in'] - 500);
                    $Cache = $Cache['access_token'];
                }
                return $Cache;
            }
        );
    }

    /**
     * 获取新的 Token
     */
    protected function getToken(): ?array
    {
        $gettoken = Http::get(
            'https://qyapi.weixin.qq.com/cgi-bin/gettoken',
            [
                'corpid'     => $this->corpid,
                'corpsecret' => $this->corpsecret,
            ]
        );
        if ($gettoken->ok() && $gettoken->json('errcode') === 0) {
            return [
                'access_token' => $gettoken->json('access_token'),
                'expires_in'   => $gettoken->json('expires_in')
            ];
        }
        return null;
    }
}

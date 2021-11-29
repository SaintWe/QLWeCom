<?php

namespace App\Http\Service\QingLong;

use Illuminate\Support\Facades\Http;

class QingLongApi
{
    /**
     * @param string|int $node_id
     */
    public static function use($node_id): ?self
    {
        $url           = env('QINGLONG_URL_' . $node_id, null);
        $client_id     = env('QINGLONG_CLIENT_ID_' . $node_id, null);
        $client_secret = env('QINGLONG_CLIENT_SECRET_' . $node_id, null);
        if (
            !empty($url)
            &&
            !empty($client_id)
            &&
            !empty($client_secret)
        ) {
            return new self($url, $client_id, $client_secret);
        }
        return null;
    }

    /**
     * 是否允许不存在的账户添加
     *
     * @param string|int $node_id
     */
    public static function account_not_found($node_id): bool
    {
        return env('QINGLONG_ACCOUNT_NOT_FOUND_' . $node_id, false);
    }

    /**
     * 账户数量限制
     *
     * @param string|int $node_id
     */
    public static function account_limit($node_id): int
    {
        return env('QINGLONG_ACCOUNT_LIMIT_' . $node_id, 0);
    }

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $client_id;

    /**
     * @var string
     */
    protected $client_secret;

    /**
     * @param string $url
     * @param string $client_id
     * @param string $client_secret
     */
    public function __construct(string $url, string $client_id, string $client_secret)
    {
        $this->url              = $url;
        $this->client_id        = $client_id;
        $this->client_secret    = $client_secret;
    }

    /**
     * 获取新的 Token
     */
    protected function getAccessToken()
    {
        return Http::get(
            $this->url . '/open/auth/token',
            [
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
            ]
        )->json('data.token', '');
    }

    /**
     * 获取环境变量
     *
     * @param string $value
     */
    public function env_get(string $value = '')
    {
        return Http::withToken($this->getAccessToken())->get(
            $this->url . '/open/envs',
            [
                't'           => $this->getMillisecond(),
                'searchValue' => $value,
            ]
        );
    }

    /**
     * 创建环境变量
     *
     * @param array $data
     */
    public function env_post(array $data)
    {
        return Http::withToken($this->getAccessToken())->post(
            $this->url . '/open/envs?t=' . $this->getMillisecond(),
            $data
        );
    }

    /**
     * 更新环境变量
     *
     * @param array $data
     */
    public function env_put(array $data)
    {
        return Http::withToken($this->getAccessToken())->put(
            $this->url . '/open/envs?t=' . $this->getMillisecond(),
            $data
        );
    }

    /**
     * 获取定时任务
     *
     * @param string $value
     */
    public function crons_get(string $value = '')
    {
        return Http::withToken($this->getAccessToken())->get(
            $this->url . '/open/crons',
            [
                't'           => $this->getMillisecond(),
                'searchValue' => $value,
            ]
        );
    }

    /**
     * 获取定时任务 Log
     *
     * @param string $id
     */
    public function crons_get_log(string $id)
    {
        return Http::withToken($this->getAccessToken())->get(
            $this->url . '/open/crons/' . $id . '/log',
            [
                't' => $this->getMillisecond(),
            ]
        );
    }

    /**
     * 运行定时任务
     *
     * @param array $data
     */
    public function crons_run(array $data)
    {
        return Http::withToken($this->getAccessToken())->put(
            $this->url . '/open/crons/run?t=' . $this->getMillisecond(),
            $data
        );
    }

    /**
     * 停止定时任务
     *
     * @param array $data
     */
    public function crons_stop(array $data)
    {
        return Http::withToken($this->getAccessToken())->put(
            $this->url . '/open/crons/stop?t=' . $this->getMillisecond(),
            $data
        );
    }

    /**
     * 全部 JD CK 的数量
     */
    public function jd_cks(): int
    {
        $env = $this->env_get('JD_COOKIE');
        if ($env->ok() && $env->json('code') === 200) {
            return count($env->json('data'));
        } else {
            return 0;
        }
    }

    /**
     * 获取毫秒
     */
    protected function getMillisecond()
    {
        return bcmul(microtime(true), 1000);
    }
}

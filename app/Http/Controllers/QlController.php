<?php

namespace App\Http\Controllers;

use App\Http\Service\UpdateJdCk;
use App\Models\JdCk;
use Illuminate\Support\Facades\Cache;

class QlController extends Controller
{
    public function envs_get()
    {
        if (Cache::has('ql_usernames') === false) {
            return response()->json(
                [
                    'code' => 200,
                    'data' => []
                ]
            );
        }
        $cache = json_decode(Cache::get('ql_usernames'));
        $JdCk  = JdCk::whereIn('username', $cache)->get();
        $data  = [];
        foreach ($JdCk as $value) {
            $data[] = [
                '_id'       => $value->uuid,
                'remarks'   => 'WeCom@' . $value->get_user()->wecom_id,
                'name'      => 'JD_COOKIE',
                'value'     => $value->get_ql_ck_value(),
                'status'    => 0,
                'created'   => $value->created_at->timestamp,
                'timestamp' => date('D M d Y H:i:s \G\M\T+0800 (中国标准时间)', $value->created_at->timestamp),
                'position'  => $value->id,
            ];
        }
        return response()->json(
            [
                'code' => 200,
                'data' => $data
            ]
        );
    }

    public function envs_put()
    {
        $data = json_decode(request()->getContent(), true);
        if ($data !== null) {
            if ($data['name'] == 'JD_COOKIE' && $data['value'] != '') {
                $result = UpdateJdCk::update_jd_ck($data['value']);
                if ($result['code'] === 200) {
                    if (Cache::has('ql_usernames')) {
                        $cache        = Cache::get('ql_usernames');
                        $cache_data   = json_decode($cache);
                        $cache_data[] = $result['pt_pin'];
                        Cache::put('ql_usernames', json_encode($cache_data, 256), 86400);
                    } else {
                        Cache::put('ql_usernames', json_encode([$result['pt_pin']], 256), 86400);
                    }
                    $t = time();
                    return response()->json(
                        [
                            'code' => 200,
                            'data' => [
                                '_id'       => $result['ck_id'],
                                'remarks'   => $data['remarks'] ?? 'JD@' . $result['pt_pin'],
                                'name'      => 'JD_COOKIE',
                                'value'     => $data['value'],
                                'status'    => 0,
                                'created'   => $t,
                                'timestamp' => date('D M d Y H:i:s \G\M\T+0800 (中国标准时间)', $t),
                                'position'  => 0,
                            ]
                        ]
                    );
                }
            }
        }
        return response()->json(
            [
                'code' => 200,
                'data' => []
            ]
        );
    }

    public function envs_post()
    {
        $datas = request()->post();
        foreach ($datas as $data) {
            if ($data['name'] == 'JD_COOKIE' && $data['value'] != '') {
                $result = UpdateJdCk::update_jd_ck($data['value']);
                if ($result['code'] === 200) {
                    if (Cache::has('ql_usernames')) {
                        $cache        = Cache::get('ql_usernames');
                        $cache_data   = json_decode($cache);
                        $cache_data[] = $result['pt_pin'];
                        Cache::put('ql_usernames', json_encode($cache_data, 256), 86400);
                    } else {
                        Cache::put('ql_usernames', json_encode([$result['pt_pin']], 256), 86400);
                    }
                    $t = time();
                    return response()->json(
                        [
                            'code' => 200,
                            'data' => [
                                [
                                    '_id'       => $result['ck_id'],
                                    'remarks'   => 'JD@' . $result['pt_pin'],
                                    'name'      => 'JD_COOKIE',
                                    'value'     => $data['value'],
                                    'status'    => 0,
                                    'created'   => $t,
                                    'timestamp' => date('D M d Y H:i:s \G\M\T+0800 (中国标准时间)', $t),
                                    'position'  => 0,
                                ]
                            ],
                        ]
                    );
                }
            }
        }
        return response()->json(
            [
                'code' => 200,
                'data' => []
            ]
        );
    }

    /**
     * auth_token
     */
    public function auth_token()
    {
        if (
            request()->input('client_id') === env('CLIENT_ID')
            &&
            request()->input('client_secret') === env('CLIENT_SECRET')
        ) {
            return response()->json(
                [
                    'code' => 200,
                    'data' => [
                        'token'      => env('CLIENT_AUTHORIZATION'),
                        'token_type' => 'Bearer',
                        'expiration' => time() + 86400 * 30,
                    ]
                ]
            );
        }
        return response()->json(
            [
                'code'    => 400,
                'message' => 'client_id或client_seret有误',
            ]
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Service\JdParse\Cookie;
use App\Http\Service\JdParse\Other;
use App\Http\Service\UpdateJdCk;

class JdController extends Controller
{
    public function index()
    {
        $message = str_replace(PHP_EOL . PHP_EOL . '本通知 By：https://github.com/whyour/qinglong', '', request()->input('message'));
        $first   = strpos($message, PHP_EOL);
        $text    = substr($message, 0, $first);     // 标题
        $desp    = substr($message, $first + 1);    // 内容
        $node    = request()->input('node', 1);     // 节点
        if (strpos($text, 'cookie') !== false || strpos($desp, 'cookie') !== false) {
            return response()->json(
                (new \App\Http\Service\JdParse\Cookie)->handle($node, $text, $desp)
            );
        }
        $parsePath     = '\\App\\Http\\Service\\JdParse\\';
        $parseFilePath = base_path('app/Http/Service/JdParse/');
        $allParseFiles = glob($parseFilePath . '*.php');
        $allParses     = str_replace(
            [
                $parseFilePath,
                '.php'
            ],
            [
                $parsePath,
                ''
            ],
            $allParseFiles
        );
        foreach ($allParses as $parseClass) {
            if (
                class_exists($parseClass) === false
                ||
                $parseClass == Cookie::class
                ||
                $parseClass == Other::class
            ) {
                continue;
            }
            $triggerObject = new $parseClass();
            if (
                property_exists($triggerObject, 'title') === false
                ||
                preg_match($triggerObject->title, $text, $matches) === 0
            ) {
                unset($triggerObject);
                continue;
            }
            return response()->json(
                $triggerObject->handle($node, $text, $desp)
            );
        }
        // 未匹配到的其他推送
        return response()->json(
            (new \App\Http\Service\JdParse\Other())->handle($node, $text, $desp)
        );
    }

    /**
     * update_jd_ck
     */
    public function update_jd_ck()
    {
        if (request()->filled('value')) {
            $result = UpdateJdCk::update_jd_ck(request()->json('value', ''));
            return response()->json(['value' => $result['msg']], $result['code']);
        }
        return response()->json(['value' => '您未提供任何可用信息'], 403);
    }

    /**
     * get_update_ck_plugin
     */
    public function get_update_ck_plugin()
    {
        $class = [
            'shadowrocket.conf'  => 'Shadowrocket/update_jd_ck.conf',
            'loon.plugin'        => 'Loon/update_jd_ck.plugin',
            'surge.module'       => 'Surge/update_jd_ck.sgmodule',
            'surge.conf'         => 'Surge/update_jd_ck.conf',
            'update_jd_ck.js'    => 'update.js',
        ];
        $type = request()->input('type', '');
        if ($type == '' || !in_array($type, array_keys($class), true)) {
            return response('hello world');
        }
        $file_path = base_path('public/jd_update_ck/' . $class[$type]);
        if (is_file($file_path) === false) {
            return response('hello world');
        }
        $file = @file_get_contents($file_path);
        if ($file === false) {
            return response('hello world');
        }
        return response(
            str_replace(
                '%%BASE_URL%%',
                env('APP_URL', ''),
                $file
            )
        );
    }
}

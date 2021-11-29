<?php

namespace App\Http\Controllers;

use App\Http\Service\WeCom\Cookie;
use App\Http\Service\WeCom\Help;
use App\Http\Service\WeCom\WsKey;

class WeChatController extends Controller
{
    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function index()
    {
        $app = app('wechat.work');
        $app->server->push(function ($Message) {
            switch ($Message['MsgType']) {
                case 'text':
                    $WeComID = $Message['FromUserName'];
                    $Content = $Message['Content'];
                    if (strpos($Content, 'pt_pin=') !== false && strpos($Content, 'pt_key=') !== false) {
                        return Cookie::handle($WeComID, $Content, $Message, []);
                    }
                    if (strpos($Content, 'pin=') !== false && strpos($Content, 'wskey=') !== false) {
                        return WsKey::handle($WeComID, $Content, $Message, []);
                    }
                    $WeComPath     = '\\App\\Http\\Service\\WeCom\\';
                    $WeComFilePath = base_path('app/Http/Service/WeCom/');
                    $allWeComFiles = glob($WeComFilePath . '*.php');
                    $allWeComs     = str_replace(
                        [
                            $WeComFilePath,
                            '.php'
                        ],
                        [
                            $WeComPath,
                            ''
                        ],
                        $allWeComFiles
                    );
                    foreach ($allWeComs as $WeComClass) {
                        if (
                            class_exists($WeComClass) === false
                            ||
                            in_array(
                                $WeComClass,
                                [
                                    Cookie::class,
                                    Help::class,
                                    WsKey::class,
                                ]
                            )
                        ) {
                            continue;
                        }
                        if (preg_match($WeComClass::COMMAND, $Content, $matches) === 1) {
                            return $WeComClass::handle($WeComID, $Content, $Message, $matches);
                        }
                    }
                    return \App\Http\Service\WeCom\Help::handle($WeComID, $Content, $Message);
            }
        });
        return $app->server->serve();
    }
}

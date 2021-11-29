<?php

namespace App\Http\Service;

use Illuminate\Support\Facades\Http;

class WskeySign
{
    /**
     * 遍历接口
     */
    public static function genToken(): ?array
    {
        $object = new self();
        for ($i = 1; $i < 10; $i++) {
            $method = 'genToken_' . $i;
            if (method_exists($object, $method)) {
                $result = self::$method();
                if ($result !== null) {
                    return $result;
                }
            } else {
                break;
            }
        }
        return null;
    }

    /**
     * 动物园 - 接口
     */
    public static function genToken_1(): ?array
    {
        $result = Http::get('https://api.jds.codes/gentoken');
        if ($result->ok() && $result->json('data.sign') !== null) {
            parse_str($result->json('data.sign'), $info);
            return $info;
        }
        return null;
    }

    /**
     * Zy143L - 接口
     */
    public static function genToken_2(): ?array
    {
        $result = Http::get('https://hellodns.coding.net/p/sign/d/jsign/git/raw/master/sign');
        if ($result->ok() && $result->json('uuid') !== null) {
            return array_merge(
                [
                    'body' => [
                        'to'     => 'https://m.jd.com',
                        'action' => 'to'
                    ],
                ],
                $result->json()
            );
        }
        return null;
    }
}

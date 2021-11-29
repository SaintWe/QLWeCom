<?php

namespace App\Http\Service;

use App\Http\Service\WeComPush\WeComPush;
use App\Models\JdCk;

abstract class JdParseInterface
{
    /**
     * @var null|\App\Http\Service\WeComPush\WeComPush
     */
    public $WeComPush;

    public function __construct()
    {
        $this->WeComPush = WeComPush::use($this->push_id);
    }

    /**
     * @param string $node 节点 ID
     * @param string $text 推送标题
     * @param string $desp 推送内容
     */
    abstract public function handle(string $node, string $text, string $desp): array;

    public function get_jd_usernames()
    {
        return JdCk::whereNotNull('nickname')->pluck('username')->toArray();
    }

    public function get_jd_nicknames()
    {
        return JdCk::whereNotNull('nickname')->pluck('nickname')->toArray();
    }
}

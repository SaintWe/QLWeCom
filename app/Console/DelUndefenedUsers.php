<?php

namespace App\Console;

use App\Models\JdCk;
use Illuminate\Console\Command;

class DelUndefenedUsers extends Command
{
    /**
     * 命令名称及签名
     *
     * @var string
     */
    protected $signature = 'jd:del_undefened_users';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '';

    /**
     * 创建命令
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 执行命令
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('任务开始');
        JdCk::whereNull('nickname')->delete();
        $this->info('任务结束');
    }
}

<?php

namespace App\Console;

use App\Http\Service\Jd\JdAPI;
use App\Http\Service\WeComPush\WeComPush;
use App\Models\JdWskey;
use Illuminate\Console\Command;

class WskeyUpdate extends Command
{
    /**
     * 命令名称及签名
     *
     * @var string
     */
    protected $signature = 'jd:wskey_update';

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
        $WeComPush = WeComPush::use();
        $items     = JdWskey::where('enable', 1)->get();
        foreach ($items as $item) {
            /**
             * @var JdWskey $item
             */
            $user = $item->get_user();
            if ($user !== null) {
                $result = JdAPI::wskey2ptkey_up($item->username, $user, $WeComPush);
                if ($result === false) {
                    $WeComPush->push_text('你旗下账户【' . $item->username . '】的 Wskey 失效了，请更新', $user->wecom_id, '', '', 0, 0, 1, 14400);
                    $item->enable = false;
                    $item->save();
                }
            } else {
                $item->delete();
            }
        }
        $this->info('任务结束');
    }
}

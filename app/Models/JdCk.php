<?php

namespace App\Models;

use App\Http\Service\Jd\JdAPI;
use App\Http\Service\QingLong\QingLongApi;

class JdCk extends Model
{
    /**
     * @var string
     */
    protected $table = 'jd_ck';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * 转换属性
     *
     * @var array
     */
    protected $casts = [
        'updated_at' => 'datetime:Y-m-d H:i',
        'created_at' => 'datetime:Y-m-d H:i',
    ];

    public function get_user(): ?PushUser
    {
        return PushUser::find($this->user_id);
    }

    public function wskey2pt_key(): bool
    {
        $user = $this->get_user();
        return $user !== null ? JdAPI::wskey2ptkey_up($this->username, $user) : false;
    }

    public function get_ql_ck_value()
    {
        $user = $this->get_user();
        if ($user !== null) {
            $QingLongApi = QingLongApi::use($user->node_id);
            if ($QingLongApi !== null) {
                $env = $QingLongApi->env_get('pt_pin=' . urlencode($this->username) . ';');
                if ($env->json('data.0') !== null && $env->json('data.0.name') == 'JD_COOKIE') {
                    return $env->json('data.0.value');
                }
            }
        }
        return '';
    }
}

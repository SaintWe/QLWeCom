<?php

namespace App\Models;

class JdWskey extends Model
{
    /**
     * @var string
     */
    protected $table = 'jd_wskey';

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
        'enablen'    => 'bool',
        'updated_at' => 'datetime:Y-m-d H:i',
        'created_at' => 'datetime:Y-m-d H:i',
    ];

    public function get_user(): ?PushUser
    {
        return PushUser::find($this->user_id);
    }

    public function to_str(): string
    {
        return 'pin=' . $this->username . ';wskey=' . $this->wskey . ';';
    }
}

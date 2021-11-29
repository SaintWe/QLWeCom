<?php

namespace App\Models;

class PushUser extends Model
{
    /**
     * @var string
     */
    protected $table = 'push_user';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * 更新时间
     */
    const UPDATED_AT = null;

    /**
     * 转换属性
     *
     * @var array
     */
    protected $casts = [
        'created_at'   => 'datetime:Y-m-d H:i',
        'detail'       => 'bool',
        'wskey2cookie' => 'bool',
    ];

    /**
     * 开启详细推送
     */
    public function enable_detail(): bool
    {
        $this->detail = true;
        return $this->save();
    }

    /**
     * 关闭详细推送
     */
    public function disable_detail(): bool
    {
        $this->detail = false;
        return $this->save();
    }

    /**
     * 开启转换推送
     */
    public function enable_wskey2cookie(): bool
    {
        $this->wskey2cookie = true;
        return $this->save();
    }

    /**
     * 关闭转换推送
     */
    public function disable_wskey2cookie(): bool
    {
        $this->wskey2cookie = false;
        return $this->save();
    }
}

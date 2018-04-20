<?php

namespace App\Models\Wechat;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class WechatMenu extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'wechat_menu';

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setParentColumn('pid');
        $this->setOrderColumn('order');
        $this->setTitleColumn('title');
    }
}

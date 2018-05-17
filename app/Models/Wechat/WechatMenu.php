<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatMenu extends Model
{
    protected $table = 'wechat_menus';

    protected $fillable = [
        'type', 'buttons', 'match_rule'
    ];

    public function getButtonsAttribute($value)
    {
        return unserialize($value);
    }

    public function setButtonsAttribute($value)
    {
        $this->attributes['buttons'] = is_array($value) ? serialize($value) : $value;
    }

    public function getMatchRuleAttribute($value)
    {
        return unserialize($value);
    }

    public function setMatchRuleAttribute($value)
    {
        $this->attributes['match_rule'] = is_array($value) ? serialize($value) : $value;
    }
}

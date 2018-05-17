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
        return explode(',', $value);
    }

    public function setButtonsAttribute($value)
    {
        return $this->attributes['buttons'] = is_array($value) ? implode(',', $value) : $value;
    }

    public function getMatchRuleAttribute($value)
    {
        return explode(',', $value);
    }

    public function setMatchRuleAttribute($value)
    {
        return $this->attributes['match_rule'] = is_array($value) ? implode(',', $value) : $value;
    }
}

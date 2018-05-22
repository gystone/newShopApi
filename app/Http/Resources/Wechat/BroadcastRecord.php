<?php

namespace App\Http\Resources\Wechat;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Log;

class BroadcastRecord extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $app = app('wechat.official_account');
        if (isset($this->tos['users']) && is_array($this->tos['users'])) {
            $count = count($this->tos['users']);
        } elseif (isset($this->tos['users'])) {
            $count = $app->user_tag->usersOfTag($this->tos['users'])['count'] ?? 0;
        } else {
            $count = $app->user->list()['total'] ?? 0;
        }
        if ($this->msg_id) {
            $status = $app->broadcasting->status($this->msg_id);
        } else {
            $status = '未发送';
        }

        return [
            'id' => $this->id,
            'types' => $this->types,
            'send_time' => $this->send_time,
            'count' => $count,
            'status' => $status,
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\Admin\BaseController;
use App\Http\Requests\Wechat\BroadcastRecordRequest;
use App\Http\Resources\Wechat\BroadcastRecordCollection;
use App\Jobs\BroadcastMessage;
use App\Models\Wechat\BroadcastRecord;
use EasyWeChat\OfficialAccount\Application;

class BroadcastRecordController extends BaseController
{
    private $broadcasting;

    public function __construct(Application $application)
    {
        parent::__construct();
        $this->broadcasting = $application->broadcasting;
    }

    public function send(BroadcastRecordRequest $request)
    {
        if ($request->is_cron) {
            $send_time = $request->send_time;
            $delay_d = floor((strtotime($send_time) - time()) / 86400);
            $delay_h = floor((strtotime($send_time) - time()) % 86400 / 3600);
            $delay_m = ceil((strtotime($send_time) - time()) % 86400 % 3600 / 60);
            $delay = $delay_d * 24 * 60 + $delay_h * 60 + $delay_m;

            $record = BroadcastRecord::create([
                'tos' => $request->to,
                'message' => $request->message,
                'types' => $request->types,
                'is_cron' => $request->is_cron,
                'send_time' => $send_time,
            ]);
        } else {
            $send_time = date('Y-m-d H:i');
            $delay = 0;

            $record = BroadcastRecord::create([
                'tos' => $request->to,
                'message' => $request->message,
                'types' => $request->types,
                'is_cron' => $request->is_cron,
                'send_time' => $send_time
            ]);

        }

        if ($record) {
            BroadcastMessage::dispatch($record)->delay(now()->addMinutes($delay));
            return $this->success($record);
        } else {
            return $this->failed('发送失败, 消息未发送');
        }
    }

    public function history()
    {
        return $this->success(\request('page') ?
            new BroadcastRecordCollection(BroadcastRecord::paginate(\request('limit') ?? 20)) :
            \App\Http\Resources\Wechat\BroadcastRecord::collection(BroadcastRecord::all())
        );
    }

    public function delete(BroadcastRecord $record)
    {
        if (isset($record->msg_id)) {
            $res = $this->broadcasting->delete($record->msg_id);
        } else {
            $res = 0;
        }

        if ($res['errcode'] === 0 || $res === 0) {
            $record->delete();
            return $this->message('删除成功');
        }
    }
}

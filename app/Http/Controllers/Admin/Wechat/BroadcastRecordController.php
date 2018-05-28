<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Wechat\BroadcastRecordRequest;
use App\Http\Resources\Wechat\BroadcastRecordCollection;
use App\Jobs\BroadcastMessage;
use App\Models\Wechat\BroadcastRecord;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Media;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastRecordController extends ApiController
{
    private $broadcasting;

    public function __construct(Application $application)
    {
        auth()->shouldUse('api_admin');
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

            $record = \App\Models\Wechat\BroadcastRecord::create([
                'tos' => $request->to,
                'message' => $request->message,
                'types' => $request->types,
                'is_cron' => $request->is_cron,
                'send_time' => $send_time,
            ]);

//            if ($record) {
//                return $this->success($record);
//            } else {
//                return $this->failed('保存失败, 未存入数据库');
//            }
        } else {
            $send_time = date('Y-m-d H:i');
            $delay = 0;

            // 群发消息
//            switch ($request->types) {
//                case 'text':
//                    $message = new Text($request->message);
//                    break;
//                case 'image':
//                    $message = new Image($request->message);
//                    break;
//                case 'news':
//                    $message = new Media($request->message, 'mpnews');
//                    break;
//                case 'voice':
//                    $message = new Media($request->message, 'voice');
//                    break;
//                case 'video':
//                    $message = new Media($request->message, 'mpvideo');
//                    break;
//                default:
//                    $message = new Text($request->message);
//            }
//
//            $res = $this->broadcasting->sendMessage($message, $request->to['users']);
//            if ($res['errcode'] === 0) {
                $record = \App\Models\Wechat\BroadcastRecord::create([
                    'tos' => $request->to,
                    'message' => $request->message,
                    'types' => $request->types,
                    'is_cron' => $request->is_cron,
                    'send_time' => $send_time
                ]);
//
//                $record->msg_id = $res['msg_id'];
//                $record->save();
//
//                if ($record) {
//                    return $this->success($record);
//                } else {
//                    return $this->failed('发送失败, 未存入数据库');
//                }
//            } else {
//                return $this->failed('发送失败, 消息未发送');
//            }

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
        return $this->success(new BroadcastRecordCollection(\App\Models\Wechat\BroadcastRecord::paginate(20)));
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

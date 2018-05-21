<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\ApiController;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Media;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;

class BroadcastRecordController extends ApiController
{
    private $broadcasting;

    public function __construct(Application $application)
    {
        $this->broadcasting = $application->broadcasting;
    }

    public function send(Request $request)
    {
        if ($request->is_cron) {
            $send_time = $request->send_time;

            $record = \App\Models\Wechat\BroadcastRecord::create([
                'to' => $request->to,
                'message' => $request->message,
                'types' => $request->types,
                'is_cron' => $request->is_cron,
                'send_time' => $send_time
            ]);

            if ($record) {
                return $this->success($record);
            } else {
                return $this->failed('保存失败, 未存入数据库');
            }
        } else {
            $send_time = date('Y-m-d H:i');

            // TODO: 群发消息
            switch ($request->types) {
                case 'text':
                    $message = new Text($request->message);
                    break;
                case 'image':
                    $message = new Image($request->message);
                    break;
                case 'news':
                    $message = new Media($request->message, 'mpnews');
                    break;
                case 'voice':
                    $message = new Media($request->message, 'voice');
                    break;
                case 'video':
                    $message = new Media($request->message, 'mpvideo');
                    break;
                default:
                    $message = new Text($request->message);
            }

            $res = $this->broadcasting->sendMessage($message, $request->to['users']);
            if ($res['errcode'] === 0) {
                $record = \App\Models\Wechat\BroadcastRecord::create([
                    'to' => $request->to,
                    'message' => $request->message,
                    'types' => $request->types,
                    'is_cron' => $request->is_cron,
                    'send_time' => $send_time
                ]);

                if ($record) {
                    return $this->success($record);
                } else {
                    return $this->failed('发送失败, 未存入数据库');
                }
            } else {
                return $this->failed('发送失败, 消息未发送');
            }

        }

    }

    public function history()
    {
        return $this->success(\App\Models\Wechat\BroadcastRecord::paginate(10));
    }
}

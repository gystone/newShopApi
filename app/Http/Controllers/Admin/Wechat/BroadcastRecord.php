<?php

namespace App\Http\Controllers\Admin\Wechat;

use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Media;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BroadcastRecord extends Controller
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
        } else {
            $send_time = date('Y-m-d H:i:s');
        }

        // TODO: 群发消息
        switch ($request->type) {
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

        $this->broadcasting->sendMessage($message, $request->to);
    }
}

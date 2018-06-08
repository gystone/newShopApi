<?php

namespace App\Jobs;

use App\Models\Wechat\BroadcastRecord;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Media;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class BroadcastMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $record;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(BroadcastRecord $record)
    {
        $this->record = $record;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        $record = BroadcastRecord::where(['is_cron' => 1, 'send_time' => date('Y-m-d H:i')])->first();
        $record = $this->record;

        if ($record) {
            switch ($record->types) {
                case 'text':
                    $message = new Text($record->message);
                    break;
                case 'image':
                    $message = new Image($record->message);
                    break;
                case 'news':
                    $message = new Media($record->message, 'mpnews');
                    break;
                case 'voice':
                    $message = new Media($record->message, 'voice');
                    break;
                case 'video':
                    $message = new Media($record->message, 'mpvideo');
                    break;
                default:
                    $message = new Text($record->message);
            }
Log::info($record->send_time);
//            $broadcasting = app('wechat.official_account')->broadcasting;
//            $res = $broadcasting->sendMessage($message, $record->to['users']);
//            if (isset($res['msg_id'])) {
//                $record->msg_id = $res['msg_id'];
//                $record->save();
//            }
        }

    }
}

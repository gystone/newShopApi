<?php

namespace App\Jobs;

use App\Models\Wechat\WechatUser;
use App\Service\Wechat\WechatUserService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SaveWechatUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200;
    protected $openidList;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($openidList)
    {
        $this->openidList = json_decode($openidList, true);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WechatUserService $service)
    {
        $service->updateWechatUser($this->openidList);
    }
}

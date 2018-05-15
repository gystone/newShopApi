<?php

namespace App\Traits;

use EasyWeChat\OfficialAccount\Application;

/**
 * 发送模板消息
 * Trait SendWechatTemplateMessage
 * @package App\Traits
 */
trait SendWechatTemplateMessage
{
    protected $template_message;

    public function __construct(Application $application)
    {
        $this->template_message = $application->template_message;
    }

    public function sendTemplateMessage($openid, $template_id, $url, array $data)
    {
        return $this->template_message->send([
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'data' => $data,
        ]);
    }
}
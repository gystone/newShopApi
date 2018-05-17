<?php

namespace App\Http\Controllers;

use App\Models\Wechat\WechatReply;
use App\Models\Wechat\WechatUser;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Transfer;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Support\Facades\Log;

class WechatController extends Controller
{
    protected $server;
    protected $users;

    public function __construct(Application $application)
    {
        $this->server = $application->server;
        $this->users = $application->user;
    }

    public function serve()
    {
        Log::info('request arrived.');
        $this->server->push(function ($message) {
            switch ($message['MsgType']) {
                case 'event':
                    switch ($message['Event']) {
                        case 'subscribe':
                            $user = $this->users->get($message['FromUserName']);

                            WechatUser::updateOrCreate(
                                [
                                    'openid' => $message['FromUserName']
                                ],
                                [
                                    'openid' => $message['FromUserName'],
                                    'nickname' => $user['nickname'],
                                    'sex' => $user['sex'],
                                    'city' => $user['city'],
                                    'province' => $user['province'],
                                    'country' => $user['country'],
                                    'headimgurl' => $user['headimgurl'],
                                    'subscribe_time' => date('Y-m-d H:i:s', $user['subscribe_time']),
                                    'status' => 'subscribe',
                                    'remark' => $user['remark'],
                                    'tagid_list' => $user['tagid_list'],
                                    'subscribe_scene' => $user['subscribe_scene'],
                                ]
                            );

                            return '欢迎关注，亲爱的'.$user['nickname'];
                            break;
                        case 'unsubscribe':
                            WechatUser::where('openid', $message['FromUserName'])->update(
                                [
                                    'unsubscribe_time' => date('Y-m-d H:i:s'),
                                    'status' => 'unsubscribe'
                                ]
                            );
                            break;
                    }

                    return '收到事件消息';
                    break;
                case 'text':
                    Log::info($message['Content']);
                    $replys = WechatReply::where('is_open', 1);
                    $reply_equal = $replys->where(['is_equal' => 'equal', 'keyword' => strtolower($message['Content'])])->first();
                    if ($reply_equal) {
                        return $this->messageContent($reply_equal);
                    }
                    $replys_contain = $replys->where(['is_equal' => 'contain'])->latest()->get();Log::info($replys_contain);
                    foreach ($replys_contain as $reply) {
                        if (stripos(strtolower($message['Content']), $reply->keyword) >= 0) {
                            return $this->messageContent($reply);
                        }
                    }
                    $default_reply = $replys->where('keyword', '默认回复')->first();
                    if ($default_reply) {
                        return $default_reply->content['body'];
                    }
                    return '你好';
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                // ... 其它消息
                default:
                    return '收到其它消息';
                    break;
            }
        });

        Log::info('return response');
        return $this->server->serve();
    }

    public function messageContent(WechatReply $reply)
    {
        switch ($reply->type) {
            case 'text':
                return new Text($reply->content['body']);
                break;
            case 'image':
                break;
        }
    }
}
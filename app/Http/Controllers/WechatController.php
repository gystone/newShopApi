<?php

namespace App\Http\Controllers;

use App\Models\Wechat\WechatMenu;
use App\Models\Wechat\WechatUser;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
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
    {return $this->server->serve();
        Log::info('request arrived.');
//        $this->server->push(function ($message) {
//            switch ($message['MsgType']) {
//                case 'event':
//                    switch ($message['Event']) {
//                        case 'subscribe':
//                            $user = $this->users->get($message['FromUserName']);
//
//                            WechatUser::updateOrCreate(
//                                [
//                                    'openid' => $message['FromUserName']
//                                ],
//                                [
//                                    'openid' => $message['FromUserName'],
//                                    'nickname' => $user['nickname'],
//                                    'sex' => $user['sex'],
//                                    'city' => $user['city'],
//                                    'province' => $user['province'],
//                                    'country' => $user['country'],
//                                    'headimgurl' => $user['headimgurl'],
//                                    'subscribe_time' => date('Y-m-d H:i:s', $user['subscribe_time']),
//                                    'status' => 'subscribe',
//                                    'remark' => $user['remark'],
//                                    'tagid_list' => $user['tagid_list'],
//                                    'subscribe_scene' => $user['subscribe_scene'],
//                                ]
//                            );
//
//                            return '欢迎关注，亲爱的'.$user['nickname'];
//                            break;
//                        case 'unsubscribe':
//                            WechatUser::where('openid', $message['FromUserName'])->update(
//                                [
//                                    'unsubscribe_time' => date('Y-m-d H:i:s'),
//                                    'status' => 'unsubscribe'
//                                ]
//                            );
//                            break;
//                    }
//
//                    return '收到事件消息';
//                    break;
//                case 'text':
//                    Log::info($message['Content']);
//                    if ($message['Content'] == '客服') {
//                        return new Transfer();
//                    }
//                    break;
//                case 'image':
//                    return '收到图片消息';
//                    break;
//                case 'voice':
//                    return '收到语音消息';
//                    break;
//                case 'video':
//                    return '收到视频消息';
//                    break;
//                case 'location':
//                    return '收到坐标消息';
//                    break;
//                case 'link':
//                    return '收到链接消息';
//                    break;
//                // ... 其它消息
//                default:
//                    return '收到其它消息';
//                    break;
//            }
//        });
        $this->server->push(function ($message) {
            // $message['FromUserName'] // 用户的 openid
            // $message['MsgType'] // 消息类型：event, text....
            return "您好！欢迎使用 EasyWeChat";
        });
        Log::info('return response');
        return $this->server->serve();
    }
}
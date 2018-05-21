<?php

namespace App\Http\Controllers;

use App\Models\Wechat\WechatMaterial;
use App\Models\Wechat\WechatReply;
use App\Models\Wechat\WechatUser;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Transfer;
use EasyWeChat\Kernel\Messages\Voice;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Support\Facades\Log;

class WechatController extends Controller
{
    protected $server;
    protected $users;
    protected $customer_service;

    public function __construct(Application $application)
    {
        $this->server = $application->server;
        $this->users = $application->user;
        $this->customer_service = $application->customer_service;
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
                    $replys = WechatReply::where('is_open', 1)->get();

                    foreach ($replys as $reply) {
                        return $this->messageContent($reply, $message['FromUserName'], strtolower($message['Content']));
                    }
                    $default_reply = WechatReply::where('is_open', 1)->where('keyword', '默认回复')->first();
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

    private function messageContent(WechatReply $reply, $openid = null, $content = null)
    {
        if ($this->isMatch($reply->keywords, $content)) {
            $contents = $reply->contents;
            if ($reply->is_reply_all) {
                // 全部发送
                $last = array_pop($contents);
                foreach ($contents as $value) {
                    $this->customer_service->message($this->replyContent($value))->to($openid)->send();
                }
                return $this->replyContent($last);

            } else {
                // 随机一条
                $reply_rand = $contents[array_rand($contents)];

                return $this->replyContent($reply_rand);
            }
        }

    }

    private function isMatch(array $keywords, $content) : bool
    {
        // 匹配关键词
        // 按匹配方式排序
        $new_keywords = $this->sortArray($keywords);
        foreach ($new_keywords as $keyword) {
            if ($keyword['match_mode'] === 'equal' && $content === strtolower($keyword['content'])) {
                return true;
            } elseif ($keyword['match_mode'] === 'contain' && stripos($content, $keyword['content']) >= 0) {
                return true;
            }
        }

        return false;
    }

    private function sortArray(array $array) : array
    {
        $equal_arr = [];
        $contain_arr = [];
        foreach ($array as $value) {
            if ($value['match_mode'] === 'equal') {
                $equal_arr[] = $value;
            } else {
                $contain_arr[] = $value;
            }
        }

        return array_merge($equal_arr, $contain_arr);
    }

    private function replyContent(array $reply)
    {
        switch ($reply['type']) {
            case 'text':
                return new Text($reply['content']);
                break;
            case 'news':
                $msg = WechatMaterial::where(['media_id' => $reply['content'], 'type' => 'news'])->first();
                if ($msg && count($msg->content['news_item'])) {
                    $news_items = [];
                    foreach ($msg->content['news_item'] as $news_item) {
                        $news_items[] = new NewsItem([
                            'title'       => $news_item['title'],
                            'description' => $news_item['digest'],
                            'url'         => $news_item['url'],
                            'image'       => $news_item['thumb_url'],
                        ]);
                    }
                    return new News($news_items);
                }
                break;
            case 'image':
                $msg = WechatMaterial::where(['media_id' => $reply['content'], 'type' => 'image'])->first();
                if ($msg) {
                    return new Image($reply['content']);
                }
                break;
            case 'video':
                $msg = WechatMaterial::where(['media_id' => $reply['content'], 'type' => 'video'])->first();
                if ($msg) {}
                break;
            case 'voice':
                $msg = WechatMaterial::where(['media_id' => $reply['content'], 'type' => 'voice'])->first();
                if ($msg) {
                    return new Voice($reply['content']);
                }
                break;
        }
    }
}
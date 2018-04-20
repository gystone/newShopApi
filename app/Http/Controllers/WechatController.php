<?php

namespace App\Http\Controllers;

use App\Http\Resources\Patient;
use App\Mail\ClinicReport;
use App\Models\Wechat\WechatKeyword;
use App\Models\Wechat\WechatKF;
use App\Models\Wechat\WechatMenu;
use App\Models\Wechat\WechatNews;
use App\Models\Wechat\WechatUser;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Transfer;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WechatController extends Controller
{
    protected $server;
    protected $menu;
    protected $template_message;
    protected $app;
    protected $users;

    public function __construct()
    {
        $app = app('wechat.official_account');
        $this->app = $app;
        $this->server = $app->server;
        $this->menu = $app->menu;
        $this->template_message = $app->template_message;
        $this->users = $app->user;
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
                                    'status' => 'subscribe'
                                ]
                            );

                            if ($msg = WechatKeyword::where('key_text', '关注')->first()) {
                                switch ($msg->msg_type) {
                                    case 'text':
                                        return $msg->text->content;
                                        break;
                                    case 'news':
                                        $items = [
                                            new NewsItem([
                                                'title'       => $msg->news->title,
                                                'description' => $msg->news->description,
                                                'url'         => $msg->news->url,
                                                'image'       => url('upload/'.$msg->news->image),
                                            ]),
                                        ];
                                        $news = WechatNews::where('pid', $msg->news->id)->get();
                                        if (count($news) > 0 && count($news) <= 7) {
                                            foreach ($news as $k1 => $v1) {
                                                $items[] = new NewsItem([
                                                    'title'       => $v1->title,
                                                    'description' => $v1->description,
                                                    'url'         => $v1->url,
                                                    'image'       => url('upload/'.$v1->image),
                                                ]);
                                            }
                                        } else {
                                            Log::warning('多图文消息最多8条');
                                        }
                                        return new News($items);
                                        break;
                                }
                            }
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
                    if ($message['Content'] == '客服') {
                        return new Transfer();
                    }
                    foreach (WechatKeyword::all() as $k => $v) {
                        $res = stripos($message['Content'], $v->key_text);
                        if (is_numeric($res)) {
                            Log::info($res);
                            switch ($v->msg_type) {
                                case 'text':
                                    return $v->text->content;
                                    break;
                                case 'news':
                                    $items = [
                                        new NewsItem([
                                            'title'       => $v->news->title,
                                            'description' => $v->news->description,
                                            'url'         => $v->news->url,
                                            'image'       => url('upload/'.$v->news->image),
                                        ]),
                                    ];
                                    $news = WechatNews::where('pid', $v->news->id)->get();
                                    if (count($news) > 0 && count($news) <= 7) {
                                        foreach ($news as $k1 => $v1) {
                                            $items[] = new NewsItem([
                                                'title'       => $v1->title,
                                                'description' => $v1->description,
                                                'url'         => $v1->url,
                                                'image'       => url('upload/'.$v1->image),
                                            ]);
                                        }
                                    } else {
                                        Log::warning('多图文消息最多8条');
                                    }
                                    return new News($items);
                                    break;
                                default:
                                    return '收到文字消息';
                                    break;
                            }
                        }
                    }
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

    public function publishMenu()
    {
        $buttons = [];
        $button0 = WechatMenu::where('pid', 0)->OrderBy('order')->get();
        foreach ($button0 as $k => $v) {
            $buttons[$k]['name'] = $v->title;
            $button1 = WechatMenu::where('pid', $v->id)->OrderBy('order')->get();
            if (count($button1) > 0) {
                foreach ($button1 as $k1 => $v1) {
                    $buttons[$k]['sub_button'][$k1]['name'] = $v1->title;
                    if ($v1->type == 'view') {
                        $buttons[$k]['sub_button'][$k1]['type'] = $v1->type;
                        $buttons[$k]['sub_button'][$k1]['url'] = $v1->menu_key ?? $v1->menu_url;
                    } else {
                        $buttons[$k]['sub_button'][$k1]['type'] = $v1->type;
                        $buttons[$k]['sub_button'][$k1]['key'] = $v1->menu_key;
                    }
                }
            } elseif ($v->type == 'view') {
                $buttons[$k]['type'] = $v->type;
                $buttons[$k]['url'] = $v->menu_key ?? $v->menu_url;
            } else {
                $buttons[$k]['type'] = $v->type;
                $buttons[$k]['key'] = $v->menu_key;
            }
        }

        $res = $this->menu->create($buttons);
        Log::info($buttons);
        Log::info($res);
        return $res;
    }

    public function deleteMenu()
    {
        $res = $this->menu->delete();
        WechatMenu::where('id', '>=', 0)->delete();

        return $res;
    }

    /**
     * 发送公众号模板消息
     *
     * @param $openid
     * @param $url 链接
     * @param $first
     * @param $order_sn
     * @param $order_status
     * @param $remark
     */
    public function sendWechatTM($openid, $template_id, $url, array $data)
    {
        return $this->template_message->send([
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'data' => $data,
        ]);
    }

    /**
     * 多条发送
     * @param $to_types 用户组
     * @param $template_id
     * @param $url
     * @param array $data
     */
    public function sendMultiWechatTM($to_types, $template_id, $url, array $data)
    {
        $wechat_users = WechatUser::where('types', $to_types)->get();

        foreach ($wechat_users as $k => $v) {
            $res = $this->sendWechatTM($v->openid, $template_id, $url, $data);
            Log::info($v->nickname, $res);
        }
    }

    /**
     * 订单状态模板消息
     * @param $to_types 用户组
     * @param $url 跳转链接
     * @param $first 标题
     * @param $order_sn 订单号
     * @param $order_status 订单状态
     * @param $remark 备注
     */
    public function sendOrderTM($to_types, $url, $first, $order_sn, $order_status, $remark)
    {
        $template_id = '658LrsA6XeiTRxnBzgM9BEUMRTWWS9B2Wo5L-4qxwZo';
        $data = [
            'first' => $first,
            'OrderSn' => $order_sn,
            'OrderStatus' => $order_status,
            'remark' => $remark
        ];
        return $this->sendMultiWechatTM($to_types, $template_id, $url, $data);
    }

    /**
     * 订单状态模板消息(openid)
     * @param $openid
     * @param $url 跳转链接
     * @param $first 标题
     * @param $order_sn 订单号
     * @param $order_status 订单状态
     * @param $remark 备注
     */
    public function sendOTM($openid, $url, $first, $order_sn, $order_status, $remark)
    {
        $template_id = '658LrsA6XeiTRxnBzgM9BEUMRTWWS9B2Wo5L-4qxwZo';
        $data = [
            'first' => $first,
            'OrderSn' => $order_sn,
            'OrderStatus' => $order_status,
            'remark' => $remark
        ];
        return $this->sendWechatTM($openid, $template_id, $url, $data);
    }

    /**
     * 注册审核模板消息
     * @param $openid
     * @param $url
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $keyword3
     * @param $remark
     */
    public function sendRgTM($openid, $url, $first, $keyword1, $keyword2, $keyword3, $remark)
    {
        $template_id = 'x_XgyXwIJ0vqiO-cV2lXlzl5_NMnoqrWOmfwuVZT48U';
        $data = [
            'first' => $first,
            'keyword1' => $keyword1,
            'keyword2' => $keyword2,
            'keyword3' => $keyword3,
            'remark' => $remark
        ];
        return $this->sendWechatTM($openid, $template_id, $url, $data);
    }

    /**
     * 注册信息 群发客服
     * @param $to_types
     * @param $url
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $keyword3
     * @param $remark
     */
    public function sendRgSTM($to_types,$url, $first, $keyword1, $keyword2, $keyword3, $remark)
    {
        $template_id = 'x_XgyXwIJ0vqiO-cV2lXlzl5_NMnoqrWOmfwuVZT48U';
        $data = [
            'first' => $first,
            'keyword1' => $keyword1,
            'keyword2' => $keyword2,
            'keyword3' => $keyword3,
            'remark' => $remark
        ];
        return $this->sendMultiWechatTM($to_types, $template_id, $url, $data);
    }

    /**
     * 获取粉丝
     * @return string
     */
    public function getUser()
    {
        $list = $this->users->list();

        foreach ($list['data']['openid'] as $k => $v) {
            $user = $this->users->get($v);
            WechatUser::updateOrCreate(
                [
                    'openid' => $v
                ],
                [
                    'openid' => $v,
                    'nickname' => $user['nickname'],
                    'sex' => $user['sex'],
                    'city' => $user['city'],
                    'province' => $user['province'],
                    'country' => $user['country'],
                    'headimgurl' => $user['headimgurl'],
                    'subscribe_time' => date('Y-m-d H:i:s', $user['subscribe_time']),
                    'status' => 'subscribe'
                ]
            );
            Log::info('同步粉丝入库' . $user['nickname'] . $v);
        }
        return 'Ok';
    }

    /**
     * 添加客服
     * @param Request $request
     * @return mixed
     */
    public function addKF(Request $request)
    {
        $kf_account = trim($request->kf_account.'@'.env('WECHAT_OFFICIAL_ACCOUNT_ID'));
        $nickname = trim($request->nickname);
        $invite_wx = trim($request->invite_wx);
        $service = $this->app->customer_service;
        if ($kf_account && $nickname && $invite_wx) {
            $res_create = $service->create($kf_account, $nickname);
            if ($res_create['errcode'] == 0) {
                $res = $service->invite($kf_account, $invite_wx);
                if ($res['errcode'] == 0) {
                    $client = new Client();
                    $client->get(url('wechat/get_kf_list'));
                }
                return $res;
            }
            return $res_create;
        }
    }

    /**
     * 删除客服
     * @param Request $request
     * @return array
     */
    public function delKF(Request $request)
    {
        $id = $request->id;
        $service = $this->app->customer_service;
        $kf = WechatKF::find($id);
        if ($kf) {
            $res = $service->delete($kf->kf_account);
            if ($res['errcode'] == 0) {
                $kf->delete();
                return ['error' => 0, 'msg' => '删除成功'];
            } else {
                return ['error' => 1, 'msg' => '删除失败'];
            }
        } else {
            return ['error' => 1, 'msg' => '客服不存在'];
        }
    }

    /**
     * 获取客服列表
     * @return array
     */
    public function getKFList()
    {
        $service = $this->app->customer_service;
        $kf_list = $service->list();
        if (isset($kf_list['kf_list']) && count($kf_list['kf_list'])) {
            $kf_list = $kf_list['kf_list'];
            foreach ($kf_list as $item) {
                WechatKF::updateOrCreate(
                    [
                        'id' => $item['kf_id']
                    ],
                    [
                        'id' => $item['kf_id'],
                        'kf_account' => $item['kf_account'],
                        'kf_headimgurl' => substr($item['kf_headimgurl'], 0, strpos($item['kf_headimgurl'], '300?')),
                        'kf_nick' => $item['kf_nick']
                    ]
                );
            }
            return ['error' => 0, 'msg' => '成功获取客服列表'];
        } else {
            return ['error' => 1, 'msg' => '当前不存在客服'];
        }
    }


    /**
     * 获取jssdk
     */
    public function send_jssdk(Request $request)
    {
        if(isset($request->url)){ //获取来源url
            $this->app->jssdk->setUrl($request->url);
        }
       $jssdk_str = $this->app->jssdk->buildConfig(['hideAllNonBaseMenuItem', 'chooseWXPay'], $debug = false, $beta = false, $json = true);

    //   Log::info($jssdk_str);
       return $jssdk_str;
    }
}

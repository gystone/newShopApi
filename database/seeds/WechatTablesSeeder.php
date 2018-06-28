<?php

use Illuminate\Database\Seeder;

class WechatTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Wechat\WechatReply::create([
            'rule_name' => '关注回复',
            'keywords' => [],
            'contents' => [
                [
                    'type' => 'text',
                    'content' => ''
                ]
            ],
            'is_reply_all' => 1,
            'is_open' => 1
        ]);
        \App\Models\Wechat\WechatReply::create([
            'rule_name' => '默认回复',
            'keywords' => [],
            'contents' => [
                [
                    'type' => 'text',
                    'content' => ''
                ]
            ],
            'is_reply_all' => 1,
            'is_open' => 1
        ]);
    }
}

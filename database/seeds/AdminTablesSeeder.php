<?php

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Database\Seeder;

class AdminTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create a user.
        Administrator::truncate();
        Administrator::create([
            'username' => 'admin',
            'password' => bcrypt('admin'),
            'name'     => '超级管理员',
        ]);

        // create a role.
        Role::truncate();
        Role::create([
            'name' => '超级管理员',
            'permissions' => array('admin'),
        ]);

        // add role to user.
        Administrator::first()->roles()->save(Role::first());

        // add default menus.
        Menu::truncate();
        Menu::insert([
            [
                'parent_id' => 0,
                'order'     => 1,
                'title'     => '首页',
                'icon'      => 'fa-bar-chart',
                'uri'       => '/',
            ],
            [
                'parent_id' => 0,
                'order'     => 2,
                'title'     => '系统管理',
                'icon'      => 'fa-tasks',
                'uri'       => '',
            ],
            [
                'parent_id' => 2,
                'order'     => 3,
                'title'     => '用户',
                'icon'      => 'fa-users',
                'uri'       => 'auth/users',
            ],
            [
                'parent_id' => 2,
                'order'     => 4,
                'title'     => '角色',
                'icon'      => 'fa-user',
                'uri'       => 'auth/roles',
            ],
            [
                'parent_id' => 2,
                'order'     => 5,
                'title'     => '权限',
                'icon'      => 'fa-ban',
                'uri'       => 'auth/permissions',
            ],
            [
                'parent_id' => 2,
                'order'     => 6,
                'title'     => '菜单',
                'icon'      => 'fa-bars',
                'uri'       => 'auth/menu',
            ],
            [
                'parent_id' => 2,
                'order'     => 7,
                'title'     => '操作日志',
                'icon'      => 'fa-history',
                'uri'       => 'auth/logs',
            ],
            [
                'parent_id' => 0,
                'order'     => 3,
                'title'     => '微信公众平台',
                'icon'      => 'fa-wechat',
                'uri'       => '',
            ],
            [
                'parent_id' => 8,
                'order'     => 1,
                'title'     => '接入设置',
                'icon'      => 'fa-bars',
                'uri'       => '/wechat',
            ],
            [
                'parent_id' => 8,
                'order'     => 2,
                'title'     => '自定义菜单',
                'icon'      => 'fa-bars',
                'uri'       => '/wechat_menu',
            ],
            [
                'parent_id' => 8,
                'order'     => 3,
                'title'     => '微信粉丝',
                'icon'      => 'fa-bars',
                'uri'       => '/wechat_user',
            ],
            [
                'parent_id' => 8,
                'order'     => 4,
                'title'     => '文本消息',
                'icon'      => 'fa-bars',
                'uri'       => '/wechat_text',
            ],
            [
                'parent_id' => 8,
                'order'     => 5,
                'title'     => '关键字回复',
                'icon'      => 'fa-bars',
                'uri'       => '/wechat_keyword',
            ],
            [
                'parent_id' => 8,
                'order'     => 6,
                'title'     => '素材管理',
                'icon'      => 'fa-bars',
                'uri'       => '',
            ],
            [
                'parent_id' => 14,
                'order'     => 1,
                'title'     => '图文',
                'icon'      => 'fa-bars',
                'uri'       => '/wechat_news',
            ],
            [
                'parent_id' => 14,
                'order'     => 2,
                'title'     => '文章',
                'icon'      => 'fa-bars',
                'uri'       => '/wechat_article',
            ],
            [
                'parent_id' => 14,
                'order'     => 3,
                'title'     => '图片',
                'icon'      => 'fa-bars',
                'uri'       => '/wechat_image',
            ],
            [
                'parent_id' => 8,
                'order'     => 7,
                'title'     => '公众号客服',
                'icon'      => 'fa-bars',
                'uri'       => '/wechat_kf',
            ],
        ]);

        // add role to menu.
        Menu::find(2)->roles()->save(Role::first());
    }
}
<?php

use Illuminate\Database\Seeder;
use App\Models\Admin\AdminUser;
use App\Models\Admin\Role;

class AdminTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AdminUser::create([
            'username' => 'admin',
            'password' => '$2y$10$0wXh/bGVPaXvqwG/e4cIbOZQXJwp1yIkh1l/LsHyMGx1HZnFfY04W',
            'name'     => '超级管理员',
        ]);

        Role::create([
            'name' => '超级管理员',
            'permissions' => array('admin'),
        ]);

        // add role to user.
        AdminUser::first()->roles()->save(Role::first());

    }
}
<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class SystemUserSeeder extends Seeder
{
    public function run(){
        /*
         * 后台登录用户名密码
         * */
        DB::table('system_users')->insert([
            'name'=>'系统管理员',
            'email' => 'admin@yeah.net',
//            'password'=>'$2y$10$kDRmns5EnYQS/IKiC/pxxuK4.D1Utatj9mHlg7d.M3iOlhjN9/hyC',
            'password' => bcrypt('admin188'),
            'phone'=>'13983087661',
            'versions'=>'0',
            'type'=>'0',
            'sort'=> '0',
            'status'=>'0'
        ]);
    }
}

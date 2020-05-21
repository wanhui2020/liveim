<?php

namespace App\Console\Commands;

use App\Models\SystemConfig;
use Faker\Provider\Uuid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '系统安装';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        if ($this->confirm('确认进行系统初始化安装? [y|N]')) {
          $this->call('migrate');
            DB::beginTransaction();
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
           // Artisan::call('migrate');
//            Artisan::call('passport:install');

            if (count(SystemConfig::all()) > 0) {
                $this->info("系统已经初始化过，不需要重新初始化！");
                return;
            }

            //系统参数
            DB::table('system_config')->insert([
                'name' => '微游',
            ]);

            //系统用户
            DB::table('system_users')->insert([
                'name' => '系统管理员',
                'password' => bcrypt('20080808'),
                'email' => 'admin@yeah.net',
                'phone' => '13888888888',
            ]);

            DB::statement('SET FOREIGN_KEY_CHECKS = 1');//启用外键约束
            DB::commit();
            $this->info("系统初始化已成功！");
        }


        //$message = $this->argument('message');
        //  event(new \App\Events\Weixin\WeixinBindReceived('123'));
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('type')->default(0)->comment('类型:0 验证码 1：自定义消息');
            $table->string('phone', 50)->nullable()->comment('手机号码');
            $table->text('content')->nullable()->comment('消息内容');
            $table->string('verify_code', 20)->nullable()->comment('验证码');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms');
    }
}

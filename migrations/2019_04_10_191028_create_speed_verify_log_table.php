<?php
/**
 * 验证码日志数据存储表.
 * User: ChaoJiWuDiMaLaXiaoLongXia@gmail.com
 * Date: 2019/4/10
 */
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateSpeedVerifyLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('speed_verify_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('verify_mode', ['phone','email'])->comment('账户类型(phone:短信验证码,email:邮箱验证码)');
            $table->string('drive', 32)->comment('发送驱动通道');
            $table->string('verify_to', 64)->comment('接收短信的手机号码 OR 接收短信的邮箱地址');
            $table->string('verify_template', 32)->default('public')->comment('验证码使用场景(示例: public=默认,register=注册,login=登录,update=更新,reset-pwd=重置密码,forgot-pwd=找回密码...）');
            $table->string('verify_code', 32)->comment('验证码(经MD5加密的密文) ');
            $table->tinyInteger('is_sent')->default('0')->comment('是否发送成功，0等待发送,1发送成功,-1发送失败');
            $table->tinyInteger('is_verify')->default('0')->comment('是否验证，0未验证,1已验证');
            $table->text('result')->nullable()->comment('请求接口返回数据(过长信息将被截断)');
            $table->string('client_ip', 128)->nullable()->comment('客户端请求IP');
            $table->integer('expire_time')->default('0')->comment('过期时间');
            $table->integer('update_time')->default('0')->comment('更新时间');
            $table->integer('create_time')->default('0')->comment('创建时间');

            $table->index('verify_mode');
            $table->index('verify_to');
            $table->index('verify_template');
            $table->index('verify_code');
            $table->index('is_verify');
        });
        try{
            DB::statement("ALTER TABLE `".config('database.connections.mysql.prefix')."speed_verify_log` comment '极速验证码日志表'");
        }catch (\Exception $e)
        {

        }
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('speed_verify_log');
    }
}
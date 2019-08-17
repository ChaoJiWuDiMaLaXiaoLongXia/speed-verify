<?php
/**
 * Created by speed-verify.
 * User: ChaoJiWuDiMaLaXiaoLongXia@gmail.com
 * Date: 2019/4/10
 */
#declare(strict_types=1);
namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify;

use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Traits\SpeedVerifyTrait;
use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Clients\ClientRedis as Redis;
use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Clients\ClientLog as Log;
use Illuminate\Support\Facades\DB;

final class SpeedVerify
{
    use SpeedVerifyTrait;

    /**
     * 验证码发送模式 （phone:短信,email:邮箱）
     * @var string
     */
    protected $mode;

    /**
     * 默认发送驱动通道
     * 默认通道必需为已配置且已开启的驱动通道。
     * @var string
     */
    protected $drive;

    /**
     * 是否自动获取发送驱动通道（false:不自动获取，true:自动获取）
     * true时，无需指定驱动通道，自动获取首个与设定模板类型相匹配的通道。
     * @var bool
     */
    protected $auto_drive = false;

    /**
     * 签名
     * 短信验证码签名
     * @var string
     */
    protected $sign_name;

    /**
     * 模板类型
     * @var string
     */
    protected $template = 'public';

    /**
     * 接收短信的手机号码
     * 手机号码只支持国内手机号码，不需要加国家代码。
     * @var string
     */
    protected $to_phone_number;

    /**
     * 接收短信的邮箱地址
     * @var string
     */
    protected $to_email;

    /**
     * 验证码
     * @var string|array
     */
    protected $verify_code;

    /**
     * 验证码格式：1数字，2字母，3数字+字母
     * @var int
     */
    protected $code_type = 1;

    /**
     * 验证码长度
     * @var int
     */
    protected $code_length = 6;

    /**
     * 间隔发送时间（秒）0为不限制
     * @var int
     */
    protected $interval_time = 0;

    /**
     * 同类型验证码日发送次数限制（次）0为不限制
     * @var int
     */
    protected $day_send_max = 0;

    /**
     * 网络请求超时时间(秒)
     * @var int
     */
    protected $query_timeout = 5;


    /**
     * 验证码超时时间(秒)
     * @var int
     */
    protected $code_timeout = 300;

    /**
     * 普通短信/邮件内容
     * @var string
     */
    protected $send_message = '';

    /**
     * 启用验证码验证功能（true:启用验证功能,false:关闭验证功能）
     * @var bool
     */
    protected $code_verify = false;

    public function __construct()
    {
        $config              = self::config();
        $this->mode          = $config['mode'];
        $this->interval_time = $config['interval_time'];
        $this->day_send_max  = $config['day_send_max'];
        $this->sign_name     = $config['sign_name'];
        $this->query_timeout = $config['query_timeout'] ?? $this->query_timeout;
        $this->code_verify   = $config['code_verify'] ?? false;
        $this->code_timeout  = $config['code_timeout'];
    }

    /**
     * 设置验证码发送模式
     * @param string $mode
     * @return $this
     */
    public function setMode(string $mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * 设置发送驱动通道
     * @param string $drive
     * @return $this
     */
    public function setDrive(string $drive)
    {
        $this->drive = $drive;
        return $this;
    }

    /**
     * 设置模板类型或模板ID
     * @param string $template
     * @return $this
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * 设置接收人手机号码
     * @param string $phone_number
     * @return $this
     */
    public function setPhoneNumber(string $phone_number)
    {
        $this->to_phone_number = $phone_number;
        return $this;
    }

    /**
     * 设置接收人邮箱地址
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->to_email = $email;
        return $this;
    }

    /**
     * 设置验证码
     * @param string|array $verify_code
     * @return $this
     */
    public function setVerifyCode($verify_code)
    {
        $this->verify_code = $verify_code;
        return $this;
    }

    /**
     * 获取参数
     * @param string $option
     * @return null
     */
    public function __get(string $option)
    {
        if(property_exists($this, $option)) {
            return $this->$option ?? null;
        }
        return null;
    }

    /**
     * 发送验证码
     * @param string null $to
     * @param string|array null $verify_code
     * @return array|bool
     * @throws \Exception
     */
    public function send(string $to = null, $verify_code = null)
    {
        if(!empty($to)) {
            if(filter_var($to,FILTER_VALIDATE_EMAIL)) {
                $this->to_email = $to;
                $this->mode = 'email';
            } else {
                $this->to_phone_number = $to;
                $this->mode = 'phone';
            }
        }
        //验证码锁
        if(true !== ($verify = $this->lockVerify('verify'))) {
            return $verify;
        }

        if(!empty($verify_code)) {
            $this->verify_code = $verify_code;
        }

        if(empty($this->verify_code)) {
            $this->verify_code  = self::simpleRandString($this->code_length, $this->code_type);
        }
        if($this->code_verify) {
            try{
                //记录验证数据
                $insertGetId = $this->log_insert();
            }catch (\Exception $e) {
                Log::error(' 记录验证数据到mysql表出错！错误信息:'. $e->getMessage());
                return self::output(500, $e->getMessage());
            }
            if(empty($insertGetId)) {
                return self::output(20000, '验证码数据记录失败！验证码未发送。');
            }
        }

        $response = $this->getDrive()::send();
        if(10000 === $response['status'])
        {
            //发送成功
            $this->lockVerify('update');
            if($this->code_verify) {
                $this->send_is_sent($insertGetId);
            }
        }

        return $response;
    }

    /**
     * 校验验证码是否有效
     * @param string null $to
     * @param string|array null $verify_code
     * @return array
     */
    public function verifyCode(string $to = null, $verify_code = null)
    {
        //清除已过期的验证码
        DB::table('speed_verify_log')->where('expire_time', '<', time())->delete();
        if(!empty($to)) {
            if(filter_var($to,FILTER_VALIDATE_EMAIL)) {
                $this->to_email = $to;
                $this->mode = 'email';
            } else {
                $this->to_phone_number = $to;
                $this->mode = 'phone';
            }
        }
        if(!empty($verify_code)) {
            $this->verify_code = $verify_code;
        }
        $verify_to = $this->mode == 'phone' ? $this->to_phone_number : $this->to_email;
        $row = $this->get_log();
        if(!empty($row->id)
            && $row->verify_code == self::encryptionCode($verify_to, $this->template, $this->verify_code))
        {
            $this->log_is_verify($row->id);
            return self::output(10000, '验证码正确');
        }
        return self::output(20000, '验证码已失效');
    }
    /**
     * 发送消息、通知
     * 发送消息、通知不记录日志且不限制次数。
     * @param string null $to
     * @param string|array null $send_message
     * @return array|bool
     * @throws \Exception
     */
    public function sendMessage(string $to = null, $send_message = null)
    {
        if(!empty($to)) {
            if(filter_var($to,FILTER_VALIDATE_EMAIL)) {
                $this->to_email = $to;
                $this->mode = 'email';
            } else {
                $this->to_phone_number = $to;
                $this->mode = 'phone';
            }
        }
        if(!empty($send_message)) {
            $this->send_message = $send_message;
        }
        $response = $this->getDrive()::send();
        return $response;
    }

    /**
     * 自动路由驱动定义
     * @param string $template 模板类型或模板ID
     * @return $this
     */
    public function autoDrive(string $template = '')
    {
        if(!empty($template)) {
            $this->template = $template;
        }
        $this->auto_drive = true;
        return $this;
    }

    /**
     * 自动路由驱动加载
     * @return int|null|string
     * @throws \Exception
     */
    private function getAutoDrive()
    {
        if(!$this->auto_drive)
            return null;
        $config = $this->getModeConfig();
        $autoDrive = '';
        foreach ($config['drives'] as $drive => $item) {
            if($item['drive_state'] && in_array($this->template, array_keys($item['templates']))) {
                $this->drive = $autoDrive = $drive;
                break;
            }
        }
        if(empty($autoDrive)) {
            throw new \Exception('可用通道驱动不包含 "'.$this->template.'" 模板！');
        }
        return $this->drive;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function defaultDrive()
    {
        $config = $this->getModeConfig();
        $defaultDrive = $config['default'];
        if(empty($config['drives'][$defaultDrive])) {
            throw new \Exception('默认通道驱动 "'.$defaultDrive.'" 不存在！');
        }
        if(!in_array($this->template, array_keys($config['drives'][$defaultDrive]['templates']))) {
            throw new \Exception('可用通道驱动不包含 "'.$this->template.'" 模板！');
        }
        return $config['default'];
    }

    /**
     * @return object
     * @throws \Exception
     */
    private function getDrive()
    {
        if(true === $this->auto_drive) {
            $this->drive = $this->getAutoDrive();
        }else{
            if(empty($this->drive)) {
                $this->drive = $this->defaultDrive();
            }
        }
        $config = $this->getDriveConfig();
        if(!$config['drive_state']) {
            throw new \Exception('通道驱动'.$this->drive.'已关闭！');
        }
        return self::routeDrive($this->mode, $this->drive)::app($this)::setConfig($config);
    }

    /**
     * @return mixed
     */
    private function getModeConfig()
    {
        switch ($this->mode) {
            case 'email':
                $config = self::config()['email'];
                break;
            default:
                $config = self::config()['phone'];
        }
        return $config;
    }
    /**
     * @return mixed
     */
    private function getDriveConfig()
    {
        return self::config()[$this->mode]['drives'][$this->drive];
    }

    /**
     * 请求验证并记录日志
     * @param string $mode
     * @return array|bool
     */
    private function lockVerify($mode = 'verify')
    {
        $redisId = 'SpeedVerify:send_log:'.$this->mode;
        switch ($this->mode) {
            case 'phone':
                $redisId .= ':'.$this->to_phone_number;
                break;
            case 'email':
                $redisId .= ':'.$this->to_email;
                break;
            default:
                $redisId = null;
        }
        if(empty($redisId)) {
            return self::output(20000, '发送失败！不支持的通道类型。');
        }

        $redisId .= ':'.$this->template;
        $rIdSum   = $redisId . '_sum';

        if( 'verify' === $mode ) {
            if($this->interval_time > 0 && Redis::get($redisId) > 0) {
                return self::output(20001, '发送失败！请不要频繁发送。');
            }
            if($this->day_send_max > 0 && Redis::get($rIdSum) > $this->day_send_max) {
                return self::output(20002, '发送失败！已超出日允许发送次数。');
            }
        }else{
            //发送间隔限制
            if($this->interval_time > 0) {
                if(1 == Redis::incr($redisId)) {
                    Redis::expire($redisId, $this->interval_time);
                }
            }
            //同类型验证码日发送次数限制
            if($this->day_send_max > 0) {
                if(1 == Redis::incr($rIdSum)) {
                    Redis::expire($rIdSum, abs(strtotime(date('Y-m-d'). '23:59:59') - time()));
                }
            }
        }
        return true;
    }


}
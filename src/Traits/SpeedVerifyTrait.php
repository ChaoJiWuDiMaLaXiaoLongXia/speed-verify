<?php

namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Traits;

use Illuminate\Support\Facades\DB;

trait SpeedVerifyTrait
{
    /**
     * output统一格式输出
     * @param int $status
     * @param string $info
     * @param array $data
     * @param array|string $request
     * @param string|string $response
     * @return array
     */
    public final static function output(int $status, string $info, $data = [], $request = [], $response = '')
    {
        return [
            'status'   => $status,
            'info'     => $info,
            'data'     => $data,
            'request'  => $request,
            'response' => $response,
        ];
    }


    /**
     * 随机数
     * @param int $length
     * @param int $rand_type
     * @return string
     */
    public final static function simpleRandString($length = 6, $rand_type = 1)
    {
        switch ($rand_type)
        {
            case 1:
                $list='0123456789';
                break;
            case 2:
                $list='ABCDEFGHJKLMNPQRSTUVWXYZ';
                break;
            case 3:
                $list='0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
                break;
            default:
                $list='0123456789';
        }
        mt_srand((double) microtime() * 1000000);
        $newstring = '';
        if($length > 0)
        {
            while(strlen($newstring) < $length)
            {
                $newstring .= $list[mt_rand(0, strlen($list)-1)];
            }
        }
        return strtoupper($newstring);
    }

    /**
     * uuid
     * @return string
     */
    public final static function uuidString()
    {
        return strtoupper(md5(uniqid(mt_rand(), true)));
    }

    /**
     * 获取所有配置
     * @return mixed
     */
    public final static function config()
    {
        return config('speed-verify', []);
    }
    /**
     * 获取phone配置
     * @param $channel
     * @return mixed
     */
    public final static function getPhoneConfig($channel)
    {
        return config('speed-verify.phone.drive.' . $channel, []);
    }
    /**
     * 获取email配置
     * @param $channel
     * @return mixed
     */
    public final static function getEmailConfig($channel)
    {
        return config('speed-verify.email.drive.' . $channel, []);
    }
    /**
     * 邮件发送开关状态
     * @return mixed
     */
    public final static function emailEnable()
    {
        return config('speed-verify.email.enable', 0);
    }
    /**
     * 短信发送开关状态
     * @return mixed
     */
    public final static function phoneEnable()
    {
        return config('speed-verify.phone.enable', 0);
    }

    /**
     * @param $mode
     * @param $drive
     * @return object
     * @throws \Exception
     */
    public final static function routeDrive($mode, $drive)
    {
        $Path = '\ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives\\' . ucfirst($mode) . '\\' . $drive .
            'Drive';
        if( !class_exists($Path)
            || !method_exists($Path, 'app')
            || !method_exists($Path, 'setConfig')
            || !method_exists($Path, 'send')
        ) {
            throw new \Exception('通道驱动 "'.$Path.'" 不存在或者未完善！');
        }
        return (new \ReflectionClass($Path))->newInstance();
    }
    /**
     * 客户端IP地址获取
     * @param int $type
     * @param bool $adv
     * @return mixed
     */
    public final static function get_client_ip($type = 0, $adv = true)
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) return $ip[$type];
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) unset($arr[$pos]);
                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }


    /**
     * 加密验证码
     * @param $account
     * @param $template
     * @param $code
     * @return string
     */
    public final static function encryptionCode($account, $template, $code) {
        return md5($account. $template. serialize($code));
    }

    /**
     * 记录日志
     * @return int
     */
    protected function log_insert()
    {
        $verify_to   = $this->mode == 'phone' ? $this->to_phone_number : $this->to_email;
        $create_time = time();
        //清除当前接收者的验证码记录信息，保持单列验证
        DB::table('speed_verify_log')->where('verify_to', $verify_to)->delete();
        //记录验证数据
        $insertGetId = DB::table('speed_verify_log')->insertGetId([
            'verify_mode'     => $this->mode,
            'drive'           => $this->drive,
            'verify_to'       => $verify_to,
            'verify_template' => $this->template,
            'verify_code'     => self::encryptionCode($verify_to, $this->template, $this->verify_code),
            'is_sent'         => 0,
            'is_verify'       => 0,
            'result'          => $response['response'] ?? '',
            'client_ip'       => self::get_client_ip(),
            'expire_time'     => $create_time + $this->code_timeout,
            'create_time'     => $create_time,
        ]);
        return $insertGetId;
    }

    /**
     * 标记为发送成功
     * @param $insertGetId
     * @return int
     */
    protected function send_is_sent($insertGetId, $is_sent, $response)
    {
        return DB::table('speed_verify_log')
            ->where('id', $insertGetId)
            ->update([
                'is_sent'     => $is_sent,
                'result'      => $response['response'] ?? '',
                'update_time' => time(),

            ]);
    }

    /**
     * 获取日志
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|object
     */
    protected function get_log() {
        $verify_to = $this->mode == 'phone' ? $this->to_phone_number : $this->to_email;
        return DB::table('speed_verify_log')
            ->where('verify_mode', $this->mode)
            ->where('verify_to', $verify_to)
            ->where('verify_template', $this->template)
            ->where('is_verify', 0)
            ->where('expire_time', '>=', time())
            ->first();
    }

    /**
     * 标记为已验证
     * @param $id
     * @return int
     */
    protected function log_is_verify($id) {
        return DB::table('speed_verify_log')
            ->where('id', $id)
            ->where('is_verify', 0)
            ->update([
                'is_verify'   => 1,
                'update_time' => time(),
            ]);
    }

    /**
     * 清除已过期的验证码
     * @return int
     */
    protected function clear_expired_logs() {
        return DB::table('speed_verify_log')->where('expire_time', '<', time())->delete();
    }
}
<?php
/**
 * ClientLog.
 * User: ChaoJiWuDiMaLaXiaoLongXia@gmail.com
 * Date: 2019/4/11
 */
namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Clients;


/**
 * Class ClientRedis
 * @package ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Clients
 * @method static mixed get($key)
 * @method static mixed incr($key)
 * @method static mixed expire($key, $outTime)
 */
class ClientRedis
{
    private static $client;

    /**
     * __call
     * @param $methods
     * @param $vars
     * @return bool
     */
    public static function __callStatic($methods, $vars)
    {
        try{
            if(class_exists('\Illuminate\Support\Facades\Redis')){
                self::$client = \Illuminate\Support\Facades\Redis::class;
                return self::$client::$methods(...$vars);
            }else{
                self::$client = new \Predis\Client();
                return self::$client->$methods(...$vars);
            }
        }catch (\Exception $e) {
            return null;
        }
    }
}
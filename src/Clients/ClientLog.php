<?php
/**
 * ClientLog.
 * User: ChaoJiWuDiMaLaXiaoLongXia@gmail.com
 * Date: 2019/4/11
 */
namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Clients;

/**
 * Class ClientLog
 * @package ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Clients
 * @method static void emergency(string $message, array $context = [])
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static void log($level, string $message, array $context = [])
 */
class ClientLog
{
    public static $getMessage = '';

    /**
     * __call
     * @param $methods
     * @param $vars
     * @return bool
     */
    public static function __callStatic($methods, $vars)
    {
        try{
            if(class_exists('\Log')){
                if(!empty($vars[1]) && is_array($vars[1]))
                    \Log::$methods($vars[0], $vars[1]);
                else
                    \Log::$methods($vars[0]);
            }else{
                $msg = strtoupper($methods).':';
                if(is_string($vars))
                    $msg .= $vars;
                else
                    $msg .= json_encode($vars);
                error_log($msg);
            }
        }catch (\Exception $e) {
            self::$getMessage = $e->getMessage();
            return false;
        }
        return true;
    }
}
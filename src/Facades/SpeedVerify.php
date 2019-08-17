<?php

namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class SpeedVerify
 * @package ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Facades
 * @method $this setMode(string $mode)
 * @method $this setDrive(string $drive)
 * @method $this autoDrive(string $template = '')
 * @method $this setTemplate(string $template)
 * @method $this setPhoneNumber(string $phone_number)
 * @method $this setEmail(string $email)
 * @method $this setVerifyCode($verify_code)
 * @method array send(string $to = null, $verify_code = null)
 * @method array verifyCode(string $to = null, $verify_code = null)
 *
 */
class SpeedVerify extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'SpeedVerifys';
    }
}

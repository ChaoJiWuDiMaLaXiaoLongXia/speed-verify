<?php
/**
 * Created by speed-verify.
 * User: ChaoJiWuDiMaLaXiaoLongXia@gmail.com
 * Date: 2019/4/11
 */
namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives;

use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Traits\SpeedVerifyTrait;

abstract class BaseDrive implements DriveInterface
{
    use SpeedVerifyTrait;
    protected static $app = [];
    protected static $config  = [];

    /**
     * @param $app
     * @return string
     */
    public final static function app($app)
    {
        self::$app = $app;
        return static::class;
    }

    /**
     * @param array $config
     * @return string
     */
    public final static function setConfig(array $config)
    {
        self::$config = $config;
        return static::class;
    }

}
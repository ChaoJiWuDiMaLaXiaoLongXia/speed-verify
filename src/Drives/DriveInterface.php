<?php
/**
 * base interface.
 * User: ChaoJiWuDiMaLaXiaoLongXia@gmail.com
 * Date: 2019/4/11
 */
namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives;

interface DriveInterface
{
    public static function send();
    public static function result_format(array $request, $result, $response);
}
<?php
/**
 * 集合数据.
 * User: ChaoJiWuDiMaLaXiaoLongXia@gmail.com
 * Date: 2019/4/11
 */
namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives\Phone;

use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives\BaseDrive;
use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Clients\ClientLog as Log;
use GuzzleHttp\Client;

final class JuHeSMSDrive extends BaseDrive
{
    const DRIVE_NAME = 'JuHeSMS';
    const DRIVE_MODE = 'phone';
    const QUERY_URL  = 'http://v.juhe.cn/sms/send';

    /**
     * send
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function send()
    {
        $template = self::$config['templates'][self::$app->template] ?? self::$config['templates']['public'];
        $request  = [
            'key'       => self::$config['key'],
            'mobile'    => self::$app->to_phone_number,
            'tpl_id'    => $template,
            'tpl_value' => sprintf(self::$config['tpl_value'], self::$app->verify_code)
        ];

        if(true === self::phoneEnable())
        {
            try{
                $response = (new Client())->request('GET', self::QUERY_URL, [
                    'timeout' => self::$app->query_timeout,
                    'query'   => $request
                ])->getBody()->getContents();
            }catch (\Exception $e) {
                Log::error(self::DRIVE_NAME.' 通道发短信出错啦！错误信息:'. $e->getMessage());
                return self::output(500, $e->getMessage());
            }
        }else{
            $response = '{"reason":"操作成功","result":{"sid":"TEST:'.date('YmdHis').'","fee":1,"count":1},"error_code":0}';
        }
        $result   = json_decode((string) $response, true);
        return self::result_format($request, $result, $response);
    }

    /**
     * 数据格式转换
     * @param array $request
     * @param $result
     * @param $response
     * @return array
     */
    public static function result_format(array $request, $result, $response)
    {
        $result['error_code'] == 10000 ? $result['error_code']++ : true;
        $result['error_code'] == 0 ? $result['error_code'] = 10000 : true;
        return self::output(
            $result['error_code'],
            $result['reason'],
            [
                'mode'           => self::$app->mode,
                'drive'          => self::DRIVE_NAME,
                'drive_name'     => self::$config['drive_name'],
                'to_phone_number'=> self::$app->to_phone_number,
                'to_email'       => self::$app->to_email,
                'verify_code'    => self::$app->verify_code,
                'template'       => self::$app->template,
                'template_id'    => $request['tpl_id'],
                'result_sid'     => $result['result']['sid'],
            ],
            $request,
            $response
        );
    }
}
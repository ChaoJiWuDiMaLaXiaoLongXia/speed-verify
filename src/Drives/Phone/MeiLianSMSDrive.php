<?php
/**
 * 美联软通.
 * User: ChaoJiWuDiMaLaXiaoLongXia@gmail.com
 * Date: 2019/4/11
 */
namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives\Phone;

use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives\BaseDrive;
use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Clients\ClientLog as Log;
use GuzzleHttp\Client;

final class MeiLianSMSDrive extends BaseDrive
{
    const DRIVE_MODE = 'phone';
    const DRIVE_NAME = 'MeiLianSMS';
    const QUERY_URL  = 'http://m.5c.com.cn/api/send/index.php?';

    /**
     * send
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function send()
    {

        if(!empty(self::$app->send_message)) {
            $template = str_replace('{sign}', self::$app->sign_name, self::$app->send_message);
        }else{
            $template = self::$config['templates'][self::$app->template] ?? self::$config['templates']['public'];
            $template = str_replace('{sign}', self::$app->sign_name, $template);
            $template = str_replace('{code}', self::$app->verify_code, $template);
        }

        $request = [
            'username'     => self::$config['username'],
            'password_md5' => self::$config['password_md5'],
            'apikey'       => self::$config['apikey'],
            'mobile'       => self::$app->to_phone_number,
            'content'      => urlencode($template),
            'encode'       => 'UTF-8',
        ];

        if(true === self::phoneEnable())
        {
            try{
                $client = (new Client())->request('POST', self::QUERY_URL, [
                    'timeout' => self::$app->query_timeout,
                    'query'   => $request
                ]);
                $response = trim($client->getBody()->getContents());
                $header   = $client->getHeaders();
            }catch (\Exception $e){
                Log::error(self::DRIVE_NAME.' 通道发短信出错啦！错误信息:'. $e->getMessage());
                return self::output(500, $e->getMessage());
            }
        }else{
            $response = 'success:'.time().self::simpleRandString(4);
            $header   = null;
        }

        $result = (string) $response;
        return self::result_format($request, $result, ['body' =>$response, 'header' => $header]);
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
        $result = explode(':', $result);
        $result[0] == 'success' ? $result['error_code'] = 10000 : $result['error_code'] = 20000;
        return self::output(
            $result['error_code'],
            $result[0],
            [
                'mode'           => self::$app->mode,
                'drive'          => self::DRIVE_NAME,
                'drive_name'     => self::$config['drive_name'],
                'to_phone_number'=> self::$app->to_phone_number,
                'to_email'       => self::$app->to_email,
                'verify_code'    => self::$app->verify_code,
                'template'       => self::$app->template,
                'template_id'    => urldecode($request['content']),
                'result_sid'     => $result[0] ?? 'null',
            ],
            $request,
            json_encode($response)
        );
    }
}
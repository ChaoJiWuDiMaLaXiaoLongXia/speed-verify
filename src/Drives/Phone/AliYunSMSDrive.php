<?php
/**
 * 集合数据.
 * User: ChaoJiWuDiMaLaXiaoLongXia@gmail.com
 * Date: 2019/4/11
 */
namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives\Phone;

use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives\BaseDrive;
use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Clients\ClientLog as Log;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

/**
 * Class AliYunSMSDrive
 * @package ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Channel\Phone
 */
final class AliYunSMSDrive extends BaseDrive
{
    const DRIVE_NAME = 'AliYunSMS';
    const DRIVE_MODE = 'phone';

    /**
     * 发送短信
     * @return array
     */
    public static function send()
    {
        $template    = self::$config['templates'][self::$app->template] ?? self::$config['templates']['public'];
        $verify_code = is_array(self::$app->verify_code) ? self::$app->verify_code : ['code' =>
            self::$app->verify_code];
        $request = [
            'RegionId'      => self::$config['region_id'],
            'SignName'      => self::$app->sign_name,
            'PhoneNumbers'  => self::$app->to_phone_number,
            'TemplateCode'  => $template['template_code'],
            'TemplateParam' => json_encode($verify_code),
        ];

        if(true === self::phoneEnable())
        {
            try {
                AlibabaCloud::accessKeyClient(self::$config['key'], self::$config['secrete'])
                    ->regionId(self::$config['region_id'])
                    ->asDefaultClient();
                $result = AlibabaCloud::rpc()
                    ->product('Dysmsapi')
                    // ->scheme('https') // https | http
                    ->version('2017-05-25')
                    ->action('SendSms')
                    ->method('POST')
                    ->timeout(self::$app->query_timeout)
                    ->options([
                        'query' => $request,
                    ])
                    ->request();
                $response = $result->toArray();
            } catch (ClientException $e) {
                Log::error(self::DRIVE_NAME.' 通道发短信出错啦！错误信息:'. $e->getErrorMessage());
                return self::output(500, $e->getErrorMessage());
            } catch (ServerException $e) {
                Log::error(self::DRIVE_NAME.' 通道发短信出错啦！错误信息:'. $e->getErrorMessage());
                return self::output(500, $e->getErrorMessage());
            }
        }else{
            $response = [
                'Message'   => 'OK',
                'RequestId' => 'TEST:'.date('YmdHis'),
                'BizId'     => '211709961628977464^0',
                'Code'      => 'OK',
            ];
        }
        return self::result_format($request, $response, $response);
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
        $result['Code'] == 'OK' ? $status = 10000 : $status = 20000;
        return self::output(
            $status,
            $result['Message'],
            [
                'mode'           => self::$app->mode,
                'drive'          => self::DRIVE_NAME,
                'drive_name'     => self::$config['drive_name'],
                'to_phone_number'=> self::$app->to_phone_number,
                'to_email'       => self::$app->to_email,
                'verify_code'    => self::$app->verify_code,
                'template'       => self::$app->template,
                'template_id'    => $request['TemplateCode'],
                'result_sid'     => $result['RequestId'] ?? '',
            ],
            $request,
            json_encode($response)
        );
    }
}
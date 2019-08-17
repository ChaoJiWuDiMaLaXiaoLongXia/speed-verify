<?php
/**
 * Swiftmailer 发送邮件
 * User: ChaoJiWuDiMaLaXiaoLongXia@gmail.com
 * Date: 2019/4/11
 */
namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives\Email;
use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives\BaseDrive;
use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Clients\ClientLog as Log;

/**
 * Class SwiftmailerDrive
 * @package ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Channel\Email
 */
final class SwiftmailerDrive extends BaseDrive
{
    const DRIVE_NAME = 'Swiftmailer';
    const DRIVE_MODE = 'email';

    /**
     * 发送邮件
     * @return array
     */
    public static function send()
    {
        $template = self::$config['templates'][self::$app->template] ?? self::$config['templates']['public'];
        $request  = [
            'to_email' => self::$app->to_email,
            'tpl_id'   => [
                'subject' => str_replace( '{sign}', self::$app->sign_name, $template['subject']),
                'body'    => str_replace(
                    ['{sign}', '{code}'],
                    [self::$app->sign_name, self::$app->verify_code],
                    $template['body']),
            ],
        ];
        try {
            if(true === self::emailEnable()) {
                // Create the Transport
                $transport = (new \Swift_SmtpTransport(self::$config['host'],
                    self::$config['port'],
                    self::$config['encryption']
                ))
                    ->setUsername(self::$config['username'])
                    ->setPassword(self::$config['password']);
                // Create the Mailer using your created Transport
                $mailer = new \Swift_Mailer($transport);
                // Create a message
                $message = (new \Swift_Message($request['tpl_id']['subject']))
                    ->setFrom(self::$config['username'], self::$config['username'])
                    ->setTo($request['to_email'])
                    ->setBody($request['tpl_id']['body']);
                // Send the message
                $result = $mailer->send($message);
            }else{
                $result = 1;
            }
        }catch (\Exception $e)
        {
            $result = 500;
            Log::error(self::DRIVE_NAME.' 通道发邮件出错啦！错误信息:'. $e->getMessage());
            return self::output($result, $e->getMessage());
        }
        return self::result_format($request, $result, $result);
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
        if($result == '1')
        {
            $results = [
                'error_code' => 10000,
                'reason'     => '发送成功',
                'sid'        => time().self::simpleRandString()
            ];
        }else{
            $results = [
                'error_code' => 20000,
                'reason'     => '发送失败',
                'sid'        => time().self::simpleRandString()
            ];
        }
        return self::output(
            $results['error_code'],
            $results['reason'],
            [
                'mode'           => self::$app->mode,
                'drive'          => self::DRIVE_NAME,
                'drive_name'     => self::$config['drive_name'],
                'to_phone_number'=> self::$app->to_phone_number,
                'to_email'       => self::$app->to_email,
                'verify_code'    => self::$app->verify_code,
                'template'       => self::$app->template,
                'template_id'    => $request['tpl_id'],
                'result_sid'     => $results['sid'],
            ],
            $request,
            $response
        );
    }
}
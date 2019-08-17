<?php
/**
 * SpeedVerifyServiceProvider.
 */
namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify;
use Illuminate\Support\ServiceProvider;

final class SpeedVerifyServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if (function_exists('config_path')) {
            $this->publishes([
                realpath(__DIR__.'/../config/speed-verify.php') => config_path('speed-verify.php'),
            ]);
        }
        if (function_exists('database_path')) {
            $this->publishes([
                realpath(__DIR__.'/../migrations/2019_04_10_191028_create_speed_verify_log_table.php') => database_path
                ('migrations/2019_04_10_191028_create_speed_verify_log_table.php'),
            ]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('SpeedVerify', function () {
            return new SpeedVerify();
        });
    }
}
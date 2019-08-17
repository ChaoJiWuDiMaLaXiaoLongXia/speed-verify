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
        $this->app['SpeedVerifys']->register();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('SpeedVerifys', function () {
            return new SpeedVerify();
        });
    }

    /**
     * Get the services provided by the package.
     *
     * @return array
     */
    public function provides()
    {
        return ['SpeedVerifys'];
    }
}
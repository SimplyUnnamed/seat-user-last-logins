<?php


namespace SimplyUnnamed\Seat\UserLastLogin;


use Seat\Services\AbstractSeatPlugin;

class LastLoginServiceProvider extends AbstractSeatPlugin
{

    public function boot(){
        $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'last-login');
    }

    public function register(){
        $this->mergeConfigFrom(
            __DIR__.'/Config/package.corporation.menu.php', 'package.corporation.menu'
        );

    }

    public function getName(): string
    {
        return 'Last Login';
    }

    public function getPackageRepositoryUrl(): string
    {
        return '';
    }

    public function getPackagistPackageName(): string
    {
        return 'userlastlogin';
    }

    public function getPackagistVendorName(): string
    {
        return 'simplyunnamed';
    }
}
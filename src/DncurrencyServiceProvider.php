<?php

namespace Dnsoftware\Dncurrency;



use Dnsoftware\Dncurrency\Models\Dncurrencies;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class DncurrencyServiceProvider extends ServiceProvider
{
    public function boot()
    {

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'dncurrency');
//dd(base_path().'/resources/lang');
        // Переводы
        //$this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'dncurrency');
        $this->loadTranslationsFrom(base_path().'/resources/lang/vendor/dnsoftware', 'dncurrency');
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/dnsoftware'),
        ], 'lang');


        // Загрузка всех валют
        Dncurrencies::initialize();

    }

    public function register()
    {
        
    //    dd


    }




}
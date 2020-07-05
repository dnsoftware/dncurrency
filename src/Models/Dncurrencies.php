<?php

namespace Dnsoftware\Dncurrency\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;

class Dncurrencies extends Model
{
    public static $current_currency;

    protected $table = 'dncurrencies';
    protected $guarded = ['id'];

    protected static $alldata;



    protected static function initialize()
    {
        $user = Auth::user();

        if (self::$alldata == null) {
            self::$alldata = self::all()->keyBy('code');

            foreach (self::$alldata as $akey => $aval) {
                self::$alldata[$akey]->langdata = json_decode(self::$alldata[$aval->code]->langdata, true);
            }

        }

        $currency_code = self::getCurrentCurrencyCode($user, Request::all());

    }


    // Генерация/получение текущей кода текущей валюты
    public static function getCurrentCurrencyCode($user, $request)
    {
        // Выставляем текущую валюту
        // Получение по умолчанию из базы
        $db_default_currency = self::getDefaultCurrencyCode();

        // Если еще не установлена - проверяем залогинен ли юзер
        if(is_null(Cookie::get('current_dncurrency'))) {

            // Если не залогинен - выставляем валюту из базы
            if (is_null($user) ) {

                //Cookie::forever('current_dncurrency', $db_default_currency);
                Cookie::queue('current_dncurrency', $db_default_currency, 43200);

                $current_currency = $db_default_currency;


            } else {    // иначе = как у пользователя

                //Cookie::forever('current_dncurrency', $user->dncurrency);
                Cookie::queue('current_dncurrency', $user->dncurrency, 43200);
                $current_currency = $user->dncurrency;

            }

        } else {
            //dump(Request::cookie('current_dncurrency'));
            $current_currency = decrypt(Cookie::get('current_dncurrency'), false);
            //dd($current_currency);

        }


        self::$current_currency = $current_currency;
        return self::$current_currency;

    }


    public static function getField($currency_code, $fieldname, $locale = null)
    {
        //self::initialize();

        if (is_null($locale)) {
            $locale = app()->getLocale();
        }

        return self::$alldata[$currency_code]->$fieldname;

    }

    public static function getLangField($currency_code, $fieldname, $locale = null)
    {
        //self::initialize();

        if (is_null($locale)) {
            $locale = app()->getLocale();
        }

        $langdata = self::$alldata[$currency_code]->langdata;

        return $langdata[$locale][$fieldname];

    }

    // Получение кода валюты по умолчанию (которая указана в базе)
    public static function getDefaultCurrencyCode()
    {
        foreach (self::$alldata as $akey => $aval) {
            if ($aval->default == 1) {
                return $aval->code;
            }
        }
    }


    // Загрузка курсов
    public static function ratesLoad()
    {
        $xml_string = file_get_contents('http://www.cbr.ru/scripts/XML_daily.asp?date_req='.Carbon::now()->format('d/m/Y'));
        $xml = simplexml_load_string($xml_string);
        $json = json_encode($xml);
        $data = json_decode($json,TRUE);

        $rates = [];
        foreach ($data['Valute'] as $key => $val) {
            $rates[$val['CharCode']] = $val;
        }

        //dd($rates);

        $our_rates = self::all();
        foreach ($our_rates as $okey => $oval) {
            if ($oval->code == 'RUB') {
                continue;
            }

            $loaded_rate = str_replace(",", ".", $rates[$oval->code]['Value']);
            $loaded_nominal = str_replace(",", ".", $rates[$oval->code]['Nominal']);
            $oval->rate = floatval($loaded_rate) /  floatval($loaded_nominal);

            $oval->save();

        }

    }


    // Конвертация
    public static function convert($from, $to, $amount)
    {
        $rate = 0;

        //self::initialize();

        $default = self::where('default', 1)->first();

        $fromrow = self::where('code', $from)->first();
        $torow = self::where('code', $to)->first();

        if ($fromrow->code == $default->code) {
            $rate = $amount / $torow->rate;
        }
        else
        if ($torow->code == $default->code) {
            $rate = $amount * $fromrow->rate;
        }
        else {
            $rate = $amount * $fromrow->rate / $torow->rate;
        }

        return $rate;

    }

    // Конвертация в текущую валюту
    public static function convertToCurrent($amount, $currency_from)
    {

        $currency_amount = self::convert($currency_from, self::$current_currency, $amount);
        //$currency_amount = round($currency_amount, 5);

        return $currency_amount;
    }


    // Все валюты
    public static function getCurrencies()
    {
        //self::initialize();

        return self::$alldata;
    }

    // Задать текущую валюту
    public static function setCurrentCurrency($currency_code)
    {
        self::$current_currency = $currency_code;
    }



}

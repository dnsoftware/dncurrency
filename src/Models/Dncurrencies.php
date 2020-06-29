<?php

namespace Dnsoftware\Dncurrency\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Dncurrencies extends Model
{
    protected $table = 'dncurrencies';


    protected static $alldata;



    protected static function loadData()
    {
        if (self::$alldata == null) {
            self::$alldata = self::all()->keyBy('cid');
        }
    }

    public static function getField($currency_cid, $fieldname, $locale = null)
    {
        self::loadData();

        if (is_null($locale)) {
            $locale = app()->getLocale();
        }

        return self::$alldata[$currency_cid]->$fieldname;

    }

    public static function getLangField($currency_cid, $fieldname, $locale = null)
    {
        self::loadData();

        if (is_null($locale)) {
            $locale = app()->getLocale();
        }

        $langdata = json_decode(self::$alldata[$currency_cid]->langdata, true);

        return $langdata[$locale][$fieldname];

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

        self::loadData();

        $default = self::where('default', 1)->first();

        $fromrow = self::where('cid', $from)->first();
        $torow = self::where('cid', $to)->first();

        if ($fromrow->cid == $default->cid) {
            $rate = $amount / $torow->rate;
        }
        else
        if ($torow->cid == $default->cid) {
            $rate = $amount * $fromrow->rate;
        }
        else {
            $rate = $amount * $fromrow->rate / $torow->rate;
        }

        return $rate;

    }







}

<?php

namespace Dnsoftware\Dncurrency\Controllers;

use App\Http\Controllers\Controller;
use Dnsoftware\Dncurrency\Models\Dncurrencies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;


class DncurrencyController extends Controller
{

    public function displayCurrentCurrency()
    {

    }

    public function changeCurrency(Request $request)
    {
        $currencycode = $request->input('currencycode');
        $cookie = cookie('current_dncurrency', encrypt($currencycode, false), 43000);
        Dncurrencies::setCurrentCurrency($currencycode);

        return back()->cookie($cookie);
    }

}
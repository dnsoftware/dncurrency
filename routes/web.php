<?php


//Route::get('dncurrency/changecurrency', [\Dnsoftware\Dncurrency\Controllers\DncurrencyController::class, 'changecurrency']);
Route::get('dncurrency/changecurrency', 'DncurrencyController@changecurrency');
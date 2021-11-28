<?php

Route::group([
    'middleware' => 'web',
    'prefix'     => 'corporations',
    'namespace'  => 'SimplyUnnamed\Seat\UserLastLogin\Http\Controllers\Corporation'
], function(){

    Route::get('/{corporation}/last-login')
        ->name('corporation.views.last-login')
        ->uses('LastloginsController@index')
        ->middleware('can:corporation.tracking,corporation');

});
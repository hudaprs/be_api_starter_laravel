<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::namespace('Api\V1')->prefix('v1')->group(function() {
    /**
     * Auth
     */
    Route::namespace('Auth')->middleware('api')->prefix('auth')->group(function () {
        Route::post('login', 'AuthController@login');
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::post('me', 'AuthController@me');
        Route::post('register', 'AuthController@register');
        Route::post('verify', 'AuthController@verifyUser');
        Route::post('recover-password', 'AuthController@recoverPassword');
        Route::post('reset-password', 'AuthController@resetPassword');
    });
});
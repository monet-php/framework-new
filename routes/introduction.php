<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->get('/', function () {
    return view('monet::introduction');
});

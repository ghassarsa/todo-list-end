<?php

use App\Http\Controllers\Api\V1\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invoice', function () {
    return view('pdf.invoice');
});

Route::get('/invoice', [PaymentController::class, 'pdf']);
<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return response()->json([
        'message' => 'Laravel + Docker is working!',
        'status' => 'success',
    ]);
});

Route::get('/test-mail', function () {
    Mail::raw('Test email works!', function ($message) {
        $message->to('ansamalmgdlawi@gmail.com')
            ->subject('Test Email from Laravel');
    });

    return 'Email sent!';
});




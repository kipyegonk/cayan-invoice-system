<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

Route::get('/invoices', [InvoiceController::class, 'indexView']);
Route::get('/invoices/{id}', [InvoiceController::class, 'showView']);
Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'pdf']);
Route::get('/', function () {
    return view('welcome');
});

<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('basic')->group(function () {
    Route::get('quotes/{id}/verify', [InvoiceController::class, 'verifyQuote']);
    Route::get('invoices', [InvoiceController::class, 'index']);
    Route::get('invoices/{id}', [InvoiceController::class, 'show']);
    Route::get('invoices/{id}/pdf', [InvoiceController::class, 'pdf']);
    Route::post('invoices', [InvoiceController::class, 'store']);
});

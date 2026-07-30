<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

// Wrap these with your own auth middleware (e.g. auth:sanctum) as needed -
// left open here so the snippet drops cleanly into a fresh Laravel app.
Route::get('quotes/{id}/verify', [InvoiceController::class, 'verifyQuote']);

Route::get('invoices',        [InvoiceController::class, 'index']);
Route::get('invoices/{id}',   [InvoiceController::class, 'show']);
Route::post('invoices',       [InvoiceController::class, 'store']);

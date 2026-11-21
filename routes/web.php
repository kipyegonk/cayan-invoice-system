
<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('basic')->group(function () {
    Route::get('/', fn () => redirect('/invoices'));
    Route::get('/invoices', [InvoiceController::class, 'indexView']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'showView']);
    Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'pdf']);
    Route::post('/invoices/{id}/mark-paid', [InvoiceController::class, 'markPaid']);
});

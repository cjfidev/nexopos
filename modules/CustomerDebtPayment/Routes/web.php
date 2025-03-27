<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomerDebtPayment\Http\Controllers\CustomerDebtPaymentController;

Route::get( '/dashboard/customers-debt-payments', [ CustomerDebtPaymentController::class, 'debtPaymentList' ]);
Route::get( '/dashboard/customers-debt-payments/create', [ CustomerDebtPaymentController::class, 'createDebtPayment' ]);
Route::get( '/dashboard/customers-debt-payments/edit/{payments}', [ CustomerDebtPaymentController::class, 'editDebtPayment' ]);
Route::get( '/api/customers-debt-payments', [CustomerDebtPaymentController::class, 'getDebtPayments']);
Route::get('/api/debt-remaining/{customer_id}', [DebtController::class, 'getDebtRemaining']);

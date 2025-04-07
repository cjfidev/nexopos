<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomerDebt\Http\Controllers\CustomerDebtController;

Route::get( '/dashboard/customers-debts', [ CustomerDebtController::class, 'debtList' ]);
Route::get( '/dashboard/customers-debts/create', [ CustomerDebtController::class, 'createDebt' ]);
Route::get( '/dashboard/customers-debts/edit/{debts}', [ CustomerDebtController::class, 'editDebt' ]);
Route::get( '/api/customers-debts', [CustomerDebtController::class, 'getDebts']);
Route::get( '/api/customers-debts-summary/{customerId}', [CustomerDebtController::class, 'getDebtSummary']);


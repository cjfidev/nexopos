<?php

use Illuminate\Support\Facades\Route;
use Modules\ProcurementReturn\Http\Controllers\ProcurementReturnController;

Route::get( '/dashboard/procurements-returns', [ ProcurementReturnController::class, 'returnList' ]);
Route::get( '/dashboard/procurements-returns/create', [ ProcurementReturnController::class, 'createReturn' ]);
Route::get( '/dashboard/procurements-returns/edit/{return}', [ ProcurementReturnController::class, 'editReturn' ]);
Route::get( '/api/procurements-returns', [ProcurementReturnController::class, 'getReturns']);

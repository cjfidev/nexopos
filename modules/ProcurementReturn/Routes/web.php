<?php

use Illuminate\Support\Facades\Route;
use Modules\ProcurementReturn\Http\Controllers\ProcurementReturnBackupController;

Route::get( '/dashboard/procurements-returns', [ ProcurementReturnBackupController::class, 'returnList' ]);
Route::get( '/dashboard/procurements-returns/create', [ ProcurementReturnBackupController::class, 'createReturn' ]);
Route::get( '/dashboard/procurements-returns/edit/{return}', [ ProcurementReturnBackupController::class, 'editReturn' ]);
Route::get( '/api/procurements-returns', [ProcurementReturnBackupController::class, 'getReturns']);

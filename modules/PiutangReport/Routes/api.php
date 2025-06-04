<?php

use Modules\PiutangReport\Http\Controllers\PiutangReportController;
use Illuminate\Support\Facades\Route;

Route::post( 'reports/piutang-report/get', [ PiutangReportController::class, 'get' ] );
<?php

use Illuminate\Support\Facades\Route;
use Modules\PiutangReport\Http\Controllers\PiutangReportController;

Route::get( '/dashboard/piutang-report', [ PiutangReportController::class, 'index' ]);
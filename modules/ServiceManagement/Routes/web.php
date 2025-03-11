<?php

use Illuminate\Support\Facades\Route;
use Modules\ServiceManagement\Http\Controllers\ServiceManagementController;

Route::get( '/dashboard/service-managements', [ ServiceManagementController::class, 'serviceList' ]);
Route::get( '/dashboard/service-managements/create', [ ServiceManagementController::class, 'createService' ]);
Route::get( '/dashboard/service-managements/edit/{service}', [ ServiceManagementController::class, 'editService' ]);
Route::get( '/api/services', [ServiceManagementController::class, 'getServices']);

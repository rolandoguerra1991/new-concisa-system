<?php

use App\Http\Controllers\GenerateResportController;
use Illuminate\Support\Facades\Route;

Route::get('generate-report/{tariff}', GenerateResportController::class)->name('generate-report');

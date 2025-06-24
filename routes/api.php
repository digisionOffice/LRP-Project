<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestJournalController;
use App\Http\Controllers\TestAccountController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Test API routes for Cypress testing
// Only available in testing/local environment
Route::group(['prefix' => 'test', 'middleware' => ['web']], function () {
    // Journal test endpoints
    Route::post('journals', [TestJournalController::class, 'create']);
    Route::post('journals/batch', [TestJournalController::class, 'createBatch']);
    Route::delete('journals/clear', [TestJournalController::class, 'clear']);
    Route::get('journals/{journal}/balance', [TestJournalController::class, 'balance']);
    
    // Account test endpoints
    Route::get('accounts', [TestAccountController::class, 'index']);
    Route::post('accounts', [TestAccountController::class, 'create']);
    Route::post('accounts/batch', [TestAccountController::class, 'createBatch']);
    Route::post('accounts/defaults', [TestAccountController::class, 'createDefaults']);
    Route::delete('accounts/clear', [TestAccountController::class, 'clear']);
});

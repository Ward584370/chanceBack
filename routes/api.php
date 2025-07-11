<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvestmentOffersController;
use App\Http\Controllers\InvestmentOpprtunitiesController;
use App\Http\Controllers\InvestmentsController;
use App\Http\Controllers\OpprtunityImagesController;
use App\Http\Controllers\ReturnsController;
use App\Http\Controllers\StripePaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\FactoriesController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Models\User;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::controller(CategoriesController::class)->group(function(){
    Route::get('/categories/index','index');
    Route::post('/categories/store','store');
    Route::delete('/categories/destroy/{id}','destroy')->middleware('auth:sanctum');

});

Route::controller(FactoriesController::class)->group(function(){
    Route::get('/factories/indexForUser','indexForUser')->middleware('auth:sanctum');
    Route::get('/factories/getAllFactories','getAllFactories');
    Route::post('/factories/updateFactoryStatus/{id}','updateFactoryStatus');
    Route::post('/factories/updateFactory/{id}','updateFactory');
    Route::post('/factories/store','store')->middleware('auth:sanctum');
    Route::get('/factories/getfactorypending','getfactorypending');
    

});


Route::controller(InvestmentOpprtunitiesController::class)->group(function(){
    Route::post('/InvestmentOpprtunities/storeoppertunitiy/{id}','storeoppertunitiy')->middleware( 'auth:sanctum');
    Route::put('/InvestmentOpprtunities/updateOpportunity/{id}','updateOpportunity')->middleware('auth:sanctum');
    Route::get('/InvestmentOpprtunities/getAcceptedOpportunitiesWithDetails','getAcceptedOpportunitiesWithDetails');
    Route::get('/InvestmentOpprtunities/getFactoryOpportunities/{id}','getFactoryOpportunities');
    Route::get('/InvestmentOpprtunities/getOpportunitiesByCategory/{id}','getOpportunitiesByCategory');
    Route::post('/InvestmentOpprtunities/confirmPurchase','confirmPurchase')->middleware('auth:sanctum');
});

Route::controller(InvestmentsController::class)->group(function(){
    Route::get('/Investments/index','index');
    Route::get('/Investments/filterByUser','filterByUser')->middleware('auth:sanctum');

});

Route::controller(InvestmentOffersController::class)->group(function(){
    Route::get('/offer/index','index');
    Route::get('/offer/filterByCategory','filterByCategory');
    Route::post('/offer/storeOffer','storeOffer')->middleware('auth:sanctum');
    Route::post('/offer/buyOffer','buyOffer')->middleware('auth:sanctum');
});

Route::post('images/uploadFactoryImage/{id}', [OpprtunityImagesController::class, 'uploadFactoryImage']);

Route::controller(UserController::class)->group(function(){
    Route::get('/User/showProfile','showProfile')->middleware('auth:sanctum');
    Route::get('/User/getUsers','getUsers');
    Route::post('/User/updateProfile','updateProfile')->middleware('auth:sanctum');

});


Route::controller(TransactionController::class)->group(function(){
    Route::get('/Transaction/index','index');
    Route::get('/Transaction/getUserTransactions','getUserTransactions')->middleware('auth:sanctum');

});

Route::controller(ReturnsController::class)->group(function(){
    Route::get('/Returns/getUserReturns','getUserReturns')->middleware('auth:sanctum');
    Route::post('/Returns/distributeReturn/{id}','distributeReturn')->middleware('auth:sanctum');

});

Route::controller(StripePaymentController::class)->group(function(){
    Route::post('/Stripe/deposit','deposit')->middleware('auth:sanctum');
    Route::post('/Stripe/withdraw','withdraw')->middleware('auth:sanctum');

});
Route::get('/stripe/test', [StripePaymentController::class, 'testStripeConnection']);






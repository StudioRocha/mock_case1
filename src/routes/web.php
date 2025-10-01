<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MypageController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 未ログイン時はauthミドルウェアで/loginへリダイレクト
Route::get('/', [ItemController::class, 'index'])->name('items.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index');
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/sell', [\App\Http\Controllers\ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [\App\Http\Controllers\ItemController::class, 'store'])->name('items.store');
    Route::post('/item/{item}', [\App\Http\Controllers\ItemController::class, 'comment'])->name('items.comment');
    Route::post('/item/{item}/purchase', [\App\Http\Controllers\ItemController::class, 'purchase'])->name('items.purchase');
    Route::post('/item/{item}/like', [\App\Http\Controllers\ItemController::class, 'like'])->name('items.like');
});

// 商品詳細（未ログインでも閲覧可）
Route::get('/item/{item}', [\App\Http\Controllers\ItemController::class, 'show'])->name('items.show');

// Auth routes (Fortify互換). GETは自作ビュー、POSTはFortifyコントローラへ委譲
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

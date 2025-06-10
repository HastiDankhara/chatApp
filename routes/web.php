<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/dashboard', function () {
    $users = User::where('id', '!=', Auth::id())->get();
    return view('dashboard', [
        'users' => $users,
    ]);
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/chat/{id}', function ($id) {
    $users = User::where('id', '!=', Auth::id())->get();
    return view('chat', [
        'id' => $id,
    ]);
})
    ->middleware(['auth', 'verified'])
    ->name('chat');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';

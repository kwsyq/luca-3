<?php

use App\Http\Controllers\ChatGPTController;
use App\Http\Controllers\ProfileController;
use App\Models\Chat;
use App\Models\ChatItem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $chats=Chat::where('user_id', auth()->user()->id)->get();
    return Inertia::render('Dashboard', [
        'chats' => $chats,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/chats', function () {
    $chats=Chat::where('user_id', Auth::user()->id)->get();
    return Inertia::render('Chat', [
        'chats' => $chats,
    ]);
})->middleware(['auth', 'verified'])->name('chat.items');

Route::post('/chats', function () {
    $newchat=Chat::create([
        'user_id' => Auth::user()->id,
        'title' => 'Nuova chat',
        'subtitle' => '',
        'start_date' => now(),
        'last_update' => now()
        ]);
    $chats=Chat::where('user_id', auth()->user()->id)->get();
    return response()->json($newchat);
})->middleware(['auth', 'verified'])->name('chat.add');

Route::get('/chats/{chat_id}/messages', function ($chat_id) {
    $chatItems=ChatItem::where('chat_id', $chat_id)->orderBy('created_at', 'asc')->get();
    return response()->json($chatItems);
})->middleware(['auth', 'verified'])->name('chat.items');

Route::post('/chats/{chat_id}/messages', [ChatGPTController::class, 'getSimpleAnswer'])->middleware(['auth', 'verified'])->name('chat.items');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/test-route', function () {
    return 'This is a test route from Monza!'; // Added location for context
});
require __DIR__.'/auth.php';

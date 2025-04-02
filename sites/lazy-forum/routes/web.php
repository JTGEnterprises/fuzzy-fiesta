<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Forum\QuestionController;
use App\Http\Controllers\Forum\AnswerController;
use App\Http\Controllers\Forum\VoteController;
use App\Http\Controllers\Forum\ForumController;
use App\Http\Controllers\Forum\TagController;
use App\Http\Controllers\Forum\UserController;
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

// Home route redirects to forum home
Route::get('/', function () {
    return redirect()->route('forum.home');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// Public Forum Routes
Route::prefix('forum')->group(function () {
    // Forum Home
    Route::get('/', [ForumController::class, 'home'])->name('forum.home');
    
    // Questions (read only)
    Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('questions.show');
    
    // Tags (read only)
    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::get('/tags/{tag}', [TagController::class, 'show'])->name('tags.show');
    
    // Users (read only)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/questions', [UserController::class, 'questions'])->name('users.questions');
    Route::get('/users/{user}/answers', [UserController::class, 'answers'])->name('users.answers');
    
    // Search
    Route::get('/search', [ForumController::class, 'search'])->name('search');
});

// Authenticated Forum Routes
Route::prefix('forum')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Questions (create, edit, delete)
    Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    
    // Answers
    Route::resource('answers', AnswerController::class)->except(['index', 'create', 'show', 'store']);
    Route::post('/answers', [AnswerController::class, 'store'])->name('answers.store');
    Route::post('/questions/{question}/answers', [AnswerController::class, 'storeForQuestion'])->name('question.answers.store');
    Route::post('/answers/{answer}/accept', [AnswerController::class, 'accept'])->name('answers.accept');
    
    // Votes
    Route::post('/votes/{type}/{id}', [ForumController::class, 'vote'])->name('votes.store');
    
    // Bookmarks
    Route::post('/questions/{question}/bookmark', [ForumController::class, 'bookmark'])->name('bookmarks.toggle');
    Route::get('/bookmarks', [ForumController::class, 'bookmarks'])->name('bookmarks.index');
    
    // User Profile
    Route::get('/my-questions', [UserController::class, 'questions'])->name('my-questions');
    Route::get('/my-answers', [UserController::class, 'answers'])->name('my-answers');
});

// Redirect old routes to new prefixed routes
Route::get('/questions/create', function() {
    return redirect()->route('questions.create');
});

Route::get('/questions', function() {
    return redirect()->route('questions.index');
});

Route::get('/questions/{question}', function($question) {
    return redirect()->route('questions.show', $question);
})->where('question', '[0-9]+');

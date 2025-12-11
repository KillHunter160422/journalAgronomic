<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\AgronomicSurveysController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\MyFieldController;

// Главная страница
Route::get('/', function () {
    return view('home');
})->name('home');

// Авторизация
Route::get('/auth', [AuthController::class, 'showAuth'])->name('auth.form');
Route::post('/auth', [AuthController::class, 'processAuth'])->name('auth.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Отладка сессий
Route::prefix('debug')->group(function () {
    Route::get('/session', [DebugController::class, 'showSessionDebug'])->name('debug.session');
    Route::post('/session/test', [DebugController::class, 'testSession'])->name('debug.session.test');
    Route::post('/session/login', [DebugController::class, 'testLogin'])->name('debug.session.login');
    Route::post('/session/clear', [DebugController::class, 'clearSessions'])->name('debug.session.clear');
});

// Профиль пользователя
Route::prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/update', [ProfileController::class, 'update'])->name('profile.update');
});

// Админ-панель - ПЕРЕНЕСИТЕ ЭТО ВВЕРХ, ПЕРЕД ДРУГИМИ МАРШРУТАМИ
Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/fields', [AdminController::class, 'fields'])->name('admin.fields');
    Route::get('/roles', [AdminController::class, 'roles'])->name('admin.roles');
    
    // Управление пользователями
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::post('/users/{user}/activate', [AdminController::class, 'activateUser'])->name('admin.users.activate');
    Route::post('/users/{user}/deactivate', [AdminController::class, 'deactivateUser'])->name('admin.users.deactivate');
    
    // Управление ролями
    Route::post('/users/{user}/roles', [AdminController::class, 'assignRole'])->name('admin.users.assignRole');
    Route::delete('/users/{user}/roles/{role}', [AdminController::class, 'removeRole'])->name('admin.users.removeRole');
    Route::post('/roles', [AdminController::class, 'createRole'])->name('admin.roles.store');
    Route::delete('/roles/{role}', [AdminController::class, 'deleteRole'])->name('admin.roles.delete');
    
    // Управление полями
    Route::delete('/fields/{field}', [AdminController::class, 'deleteField'])->name('admin.fields.delete');
    
    // Экспорт и импорт пользователей
    Route::get('/users/export', [AdminController::class, 'exportUsers'])->name('admin.users.export');
    Route::post('/users/import', [AdminController::class, 'importUsers'])->name('admin.users.import');
    Route::get('/users/import/template', [AdminController::class, 'downloadImportTemplate'])->name('admin.users.import.template');
});

// Журнал наблюдений (публичные поля)
Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
Route::get('/journal/load-more/{skip}', [JournalController::class, 'loadMore'])->name('journal.load-more');

// Мои поля (все поля пользователя)
Route::prefix('my-fields')->group(function () {
    Route::get('/', [MyFieldController::class, 'show'])->name('my-fields.show');
    Route::post('/{field}/toggle-privacy', [MyFieldController::class, 'togglePrivacy'])->name('my-fields.toggle-privacy');
    Route::delete('/{field}', [MyFieldController::class, 'destroy'])->name('my-fields.destroy');
});

// РЕДИРЕКТЫ для обратной совместимости
Route::get('/field/{id}', function($id) {
    return redirect("/fields/{$id}", 301);
});
Route::get('/my_field', function() {
    return redirect("/my-fields", 301); // редирект со старого URL
});

// Маршруты для полей (общие)
Route::prefix('fields')->group(function () {
    // Статические маршруты ВЫШЕ динамических
    Route::get('/create', [FieldController::class, 'create'])->name('fields.create');
    Route::post('/', [FieldController::class, 'store'])->name('fields.store');
    Route::put('/{field}', [FieldController::class, 'update'])->name('fields.update');
    
    // Операции на поле
    Route::prefix('{field}/operations')->group(function () {
        Route::get('/create', [OperationController::class, 'create'])
            ->name('operations.create');
        Route::post('/', [OperationController::class, 'store'])
            ->name('operations.store');
        Route::get('/{operation}', [OperationController::class, 'show'])
            ->name('operations.show');
        Route::get('/{operation}/edit', [OperationController::class, 'edit'])
            ->name('operations.edit');
        Route::put('/{operation}', [OperationController::class, 'update'])
            ->name('operations.update');
        Route::delete('/{operation}', [OperationController::class, 'destroy'])
            ->name('operations.destroy');
    });
    
    // Агрономические обследования
    Route::prefix('{field}/surveys')->group(function () {
        Route::get('/create', [AgronomicSurveysController::class, 'create'])
            ->name('surveys.create');
        Route::post('/', [AgronomicSurveysController::class, 'store'])
            ->name('surveys.store');
        Route::get('/{survey}', [AgronomicSurveysController::class, 'show'])
            ->name('surveys.show');
        Route::delete('/{survey}', [AgronomicSurveysController::class, 'destroy'])
            ->name('surveys.destroy');
        Route::post('/{survey}/update', [AgronomicSurveysController::class, 'update']);
    });
    
    // Переключение приватности поля
    Route::post('/{field}/toggle-privacy', [FieldController::class, 'togglePrivacy'])
        ->name('fields.toggle-privacy');
    
    // Показ поля
    Route::get('/{field}', [FieldController::class, 'show'])->name('fields.show');
});
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebugController extends Controller
{
    public function showSessionDebug()
    {
        return view('debug.session');
    }
    
    public function testSession(Request $request)
    {
        // Тест PHP сессии
        session_start();
        $_SESSION['php_form_data'] = $request->test_data;
        
        // Тест Laravel сессии
        session(['laravel_form_data' => $request->test_data]);
        session()->save();
        
        return back()->with('success', 'Данные сохранены в обе сессии!');
    }
    
    public function testLogin(Request $request)
    {
        // Тестовый вход
        $username = $request->username ?: 'test_user';
        
        // PHP сессия
        session_start();
        $_SESSION['user_id'] = 999;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = 'test@example.com';
        
        // Laravel сессия (если есть модель User)
        if (Auth::check()) {
            Auth::logout();
        }
        
        return back()->with('success', 'Тестовый пользователь создан: ' . $username);
    }
    
    public function clearSessions(Request $request)
    {
        // Очищаем PHP сессию
        session_start();
        session_destroy();
        
        // Очищаем Laravel сессию
        session()->flush();
        
        // Выход из Laravel Auth
        if (Auth::check()) {
            Auth::logout();
        }
        
        return back()->with('success', 'Все сессии очищены!');
    }
}
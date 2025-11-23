<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showAuth(Request $request)
    {
        $is_register_mode = $request->has('show_register');
        return view('auth', ['is_register_mode' => $is_register_mode]);
    }

    private function handleLogin(Request $request)
    {
        $request->validate([
            'login' => 'required|string|min:3',
            'password' => 'required|string|min:6'
        ]);

        $login = $request->input('login');
        $password = $request->input('password');

        // Временная проверка
        if ($login === 'admin' && $password === 'password123') {
            return back()->with('success', 'Авторизация прошла успешно: ' . $login);
        }

        return back()->with('error', 'Неверный логин или пароль');
    }

    private function handleRegister(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:20|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'email' => 'required|email|max:255|unique:users',
            'fullname' => 'required|string|min:2|max:100',
            'agree_terms' => 'required'
        ], [
            'password.confirmed' => 'Пароли не совпадают',
            'agree_terms.required' => 'Необходимо согласие с условиями'
        ]);

        User::create([
            'username'=> $request->username,
            'fullname'=> $request->fullname,
            'email'=> $request->email,
            'password'=> Hash::make($request->password)
        ]);

        $credentials = $request->only('username', 'password');
        Auth::attempt($credentials);
        // пока перенаправляем на главную из-за того что отсутсвует журнал.
        return redirect('/home')->with('success', 'Успешная регистрация! Добро пожаловать ' 
    . $request->username . '!');
    }

    public function processAuth(Request $request)
    {
        if ($request->has('show_register')) {
            return view('auth', ['is_register_mode' => true]);
        }
        
        if ($request->has('show_login')) {
            return view('auth', ['is_register_mode' => false]);
        }

        if ($request->has('login_btn')) {
            return $this->handleLogin($request);
        }
        elseif ($request->has('register_btn')) {
            return $this->handleRegister($request);
        }
        else {
            return view('auth', ['is_register_mode' => false]);
        }
    }
}
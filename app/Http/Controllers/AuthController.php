<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

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
            'username' => 'required|string|min:3',
            'password' => 'required|string|min:6'
        ]);

        $credentials = $request->only('username', 'password');

        if(Auth::attempt($credentials)){
            $request->session()->regenerate();

            return redirect('/')->with('success', 'Вход успешно выполнен!');
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
            'email.unique'=> 'Эта почта уже привязана!',
            'password.confirmed' => 'Пароли не совпадают',
            'agree_terms.required' => 'Необходимо согласие с условиями'
        ]);

        $user = User::create([
            'username'=> $request->username,
            'fullname'=> $request->fullname,
            'email'=> $request->email,
            'password'=> Hash::make($request->password)
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        // пока перенаправляем на главную из-за того что отсутсвует журнал.
        return redirect('/')->with('success', 'Успешная регистрация! Добро пожаловать ' 
    . $request->username . '!');
    }

    private function saveSession(Request $request) {
        $userId = Auth::id();

        $sessionId = $request->session()->getId();

        \Illuminate\Support\Facades\Redis::setex("user_session:{$userId}", 3600, $sessionId);


        \Illuminate\Support\Facades\Redis::hmset("user:{$userId}", [
            "username" => Auth::user()->username,
            "email"=> Auth::user()->email,
            "last_login"=> now()->toDateTimeString()
        ]);
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
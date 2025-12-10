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

    $user = User::where('username', $request->username)->first();
    
    if ($user && Hash::check($request->password, $user->password)) {
        $roles = $user->getRoleNames();
        // ТОЛЬКО PHP сессия
        session_start();
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['username'] = $user->username;
        $_SESSION['email'] = $user->email;
        $_SESSION['fullname'] = $user->fullname;
        $_SESSION['avatar_url'] = $user->avatar_url;
        $_SESSION['roles'] = $roles;
        $_SESSION['_token'] = csrf_token();
        $_SESSION['logged_in_at'] = date('Y-m-d H:i:s');
        session_write_close();
        
        return redirect('/')->with('success', 'Добро пожаловать, ' . $user->username . '!');
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

    $roles = $user->getRoleNames();
    // PHP сессия
    session_start();
    $_SESSION['user_id'] = $user->user_id;
    $_SESSION['username'] = $user->username;
    $_SESSION['email'] = $user->email;
    $_SESSION['fullname'] = $user->fullname;
    $_SESSION['avatar_url'] = $user->avatar_url;
    $_SESSION['roles'] = $roles;
    $_SESSION['_token'] = csrf_token();
    $_SESSION['logged_in_at'] = date('Y-m-d H:i:s');
    session_write_close();
    
    return redirect('/home')->with('success', 'Успешная регистрация! Добро пожаловать ' . $request->username . '!');
}

    public function logout(Request $request){
        session_start();
        session_destroy();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/auth")->with("Success","Вы вышли из системы!");
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
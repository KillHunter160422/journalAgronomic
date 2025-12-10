<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Запускаем PHP сессию
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Проверяем авторизацию
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            return redirect('/auth')->with('error', 'Требуется авторизация');
        }
        
        // Проверяем роли
        $userRoles = $_SESSION['roles'] ?? [];
        
        // Проверяем есть ли хотя бы одна из требуемых ролей
        $hasRole = false;
        foreach ($roles as $role) {
            if (in_array($role, $userRoles)) {
                $hasRole = true;
                break;
            }
        }
        
        if (!$hasRole) {
            return redirect('/')->with('error', 'Доступ запрещен. Необходимые роли: ' . implode(', ', $roles));
        }
        
        return $next($request);
    }
}
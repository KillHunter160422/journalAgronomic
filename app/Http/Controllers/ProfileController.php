<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    // Показать профиль
    public function show()
    {
        if (!php_auth_check()) {
            return redirect('/auth')->with('error', 'Войдите для просмотра профиля');
        }
        
        $user = DB::table('users')
            ->where('user_id', php_session('user_id'))
            ->first();
            
        if (!$user) {
            abort(404, 'Пользователь не найден');
        }
        
        // Получаем статистику пользователя
        $stats = $this->getUserStats(php_session('user_id'));
        
        // Получаем роли пользователя
        $roles = DB::table('user_role_assignments as ura')
            ->join('user_roles as ur', 'ura.role_id', '=', 'ur.role_id')
            ->where('ura.user_id', $user->user_id)
            ->pluck('ur.role_name')
            ->toArray();
        
        return view('profile.show', compact('user', 'stats', 'roles'));
    }
    
    // Форма редактирования профиля
    public function edit()
    {
        if (!php_auth_check()) {
            return redirect('/auth')->with('error', 'Войдите для редактирования профиля');
        }
        
        $user = DB::table('users')
            ->where('user_id', php_session('user_id'))
            ->first();
            
        if (!$user) {
            abort(404, 'Пользователь не найден');
        }
        
        return view('profile.edit', compact('user'));
    }
    
    // Обновить профиль
public function update(Request $request)
{
    if (!php_auth_check()) {
        return redirect('/auth')->with('error', 'Войдите для редактирования профиля');
    }
    
    $user = DB::table('users')
        ->where('user_id', php_session('user_id'))
        ->first();
        
    if (!$user) {
        abort(404, 'Пользователь не найден');
    }
    
    $validated = $request->validate([
        'fullname' => 'nullable|string|max:150',
        'email' => 'required|email|max:50|unique:users,email,' . $user->user_id . ',user_id',
        'avatar' => 'nullable|image|max:2048', // 2MB max
    ]);
    
    $updateData = [
        'fullname' => $validated['fullname'] ?? $user->fullname,
        'email' => $validated['email'],
        'updated_at' => now()
    ];
    
    // Обработка загрузки аватара
    if ($request->hasFile('avatar')) {
        $avatarFile = $request->file('avatar');
        $avatarName = 'avatar_' . $user->user_id . '_' . time() . '.' . $avatarFile->getClientOriginalExtension();
        
        // Сохраняем в public/uploads/avatars
        $avatarPath = $avatarFile->storeAs('avatars', $avatarName, 'public');
        
        // Сохраняем URL
        $updateData['avatar_url'] = '/storage/' . $avatarPath;
    }
    
    DB::table('users')
        ->where('user_id', $user->user_id)
        ->update($updateData);
    
    return redirect()->route('profile.show')->with('success', 'Профиль обновлен');
}
    // Получить статистику пользователя
    private function getUserStats($userId)
    {
        return [
            'fields_count' => DB::table('fields')->where('user_id', $userId)->count(),
            'operations_count' => DB::table('fields_has_operation as fho')
                ->join('fields as f', 'fho.field_id', '=', 'f.field_id')
                ->where('f.user_id', $userId)
                ->count(),
            'surveys_count' => DB::table('agronomic_surveys as s')
                ->join('fields as f', 's.field_id', '=', 'f.field_id')
                ->where('f.user_id', $userId)
                ->count(),
            'public_fields' => DB::table('fields')
                ->where('user_id', $userId)
                ->where('is_public', 1)
                ->count(),
            'total_area' => DB::table('fields')
                ->where('user_id', $userId)
                ->sum('field_area')
        ];
    }
}
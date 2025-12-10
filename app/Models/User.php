<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'fullname',
        'email',
        'password',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Роли пользователей
     */
    public function roles(){
        return $this->belongsToMany(
        Role::class,
        'user_role_assignments',
        'user_id',
        'role_id'
        );
    }
    /**
     * Проверка ролей
     */
    public function hasRole($roleName)
    {
        return $this->roles()->where('role_name', $roleName)->exists();
    }
    
    /**
     * Проверка нескольких ролей
     */
    public function hasAnyRole($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return $this->roles()->whereIn('role_name', $roles)->exists();
    }
    
    /**
     * Проверка всех ролей
     */
    public function hasAllRoles($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        $userRoles = $this->roles->pluck('role_name')->toArray();
        
        foreach ($roles as $role) {
            if (!in_array($role, $userRoles)) {
                return false;
            }
        }
        
        return true;
    }
    
    
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }
    
    public function isModerator()
    {
        return $this->hasRole('moderator') || $this->isAdmin();
    }
    
    /**
     * Получить все названия ролей пользователя
     */
    public function getRoleNames()
    {
        return $this->roles->pluck('role_name')->toArray();
    }
}

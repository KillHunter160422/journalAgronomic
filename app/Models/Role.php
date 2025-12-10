<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'user_roles';
    protected $primaryKey = 'role_id';
    public $timestamps = false;
    
    protected $fillable = [
        'role_name'
    ];
    
    /**
     * Пользователи с этой ролью
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_role_assignments',
            'role_id',
            'user_id'
        );
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;

    protected $table = 'operation';
    protected $primaryKey = 'operation_id';
    public $timestamps = true;

    protected $fillable = [
        'operation_type',
        'operation_date',
        'description',
        'weather_conditions'
    ];

    public function fieldOperations()
    {
        return $this->hasMany(FieldOperation::class, 'operation_id', 'operation_id');
    }
}
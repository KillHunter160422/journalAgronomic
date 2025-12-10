<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FieldOperation extends Model
{
    use HasFactory;

    protected $table = 'fields_has_operation';
    public $timestamps = true;

    protected $fillable = [
        'field_id',
        'operation_id',
        'crop_id',
        'applied_materials',
        'application_rate', 
        'season_name',
        'vegetation_period'
    ];

    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id', 'field_id');
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class, 'operation_id', 'operation_id');
    }

    public function crop()
    {
        return $this->belongsTo(CropCatalog::class, 'crop_id', 'crop_id');
    }
}
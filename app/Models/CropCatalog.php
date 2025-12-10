<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CropCatalog extends Model
{
    use HasFactory;

    protected $table = 'crops_catalog';
    protected $primaryKey = 'crop_id';
    public $timestamps = true;

    protected $fillable = [
        'crop_name',
        'variety',
        'vegetation_period',
    ];

    public function fieldOperations()
    {
        return $this->hasMany(FieldOperation::class, 'crop_id', 'crop_id');
    }
}
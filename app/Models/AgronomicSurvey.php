<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgronomicSurvey extends Model
{
    use HasFactory;

    protected $table = 'agronomic_surveys';
    protected $primaryKey = 'survey_id';
    public $timestamps = true;

    protected $fillable = [
        'field_id',
        'survey_date',
        'pest_level',
        'disease_level',
        'condition_notes',
        'yield_mass_kq',
        'yield_per_hectare',
        'harvest_date'
    ];

    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id', 'field_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $primaryKey = 'field_id';
    protected $table = 'fields';
    
    protected $fillable = [
        'user_id',
        'field_name',
        'field_area',
        'polygon',
        'is_public'
    ];
    
    protected $appends = ['operations_count', 'surveys_count'];
    
    // Связь с пользователем
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    
    // Связь с операциями через промежуточную таблицу
    public function fieldOperations()
    {
        return $this->hasMany(FieldOperation::class, 'field_id', 'field_id');
    }
    
    // Связь с обследованиями
    public function surveys()
    {
        return $this->hasMany(AgronomicSurvey::class, 'field_id', 'field_id');
    }
    
    // Получить информацию о культуре
    public function getCropInfoAttribute()
    {
        $fieldOperation = $this->fieldOperations()->with('crop')->first();
        
        if ($fieldOperation && $fieldOperation->crop) {
            return [
                'crop_name' => $fieldOperation->crop->crop_name,
                'variety' => $fieldOperation->crop->variety,
                'vegetation_period' => $fieldOperation->crop->vegetation_period
            ];
        }
        
        return null;
    }
    
    // Получить статус поля
    public function getStatusAttribute()
    {
        $lastSurvey = $this->surveys()->latest('survey_date')->first();
        
        if (!$lastSurvey) {
            return 'planned';
        }
        
        // Проверяем наличие проблем
        $pestLevel = $lastSurvey->pest_level ?? '0';
        $diseaseLevel = $lastSurvey->disease_level ?? '0';
        
        $pestNum = convertLevelToNumber($pestLevel);
        $diseaseNum = convertLevelToNumber($diseaseLevel);
        
        if ($pestNum > 5 || $diseaseNum > 5) {
            return 'problem';
        }
        
        // Проверяем дату обследования
        $surveyDate = strtotime($lastSurvey->survey_date);
        $now = time();
        $daysDiff = ($now - $surveyDate) / (60 * 60 * 24);
        
        if ($daysDiff > 60) {
            return 'completed';
        }
        
        return 'active';
    }
    
    // Количество операций (accessor)
    public function getOperationsCountAttribute()
    {
        return $this->fieldOperations()->count();
    }
    
    // Количество обследований (accessor)
    public function getSurveysCountAttribute()
    {
        return $this->surveys()->count();
    }
    
    // Scope для получения полей журнала
    public function scopeForJournal($query, $currentUserId = null)
    {
        if (!$currentUserId) {
            return $query->where('is_public', 1);
        } else {
            return $query->where(function($q) use ($currentUserId) {
                $q->where('user_id', $currentUserId)
                  ->orWhere('is_public', 1);
            });
        }
    }
}
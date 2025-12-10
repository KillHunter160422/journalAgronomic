<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Field;
use App\Models\AgronomicSurvey;

class AgronomicSurveysController extends Controller
{
    // Показать форму создания обследования
    public function create($fieldId)
    {
        if (!php_auth_check()) {
            return redirect('/auth')->with('error', 'Войдите для добавления обследования');
        }
        
        $field = DB::table('fields')->where('field_id', $fieldId)->first();
        
        if (!$field) {
            abort(404, 'Поле не найдено');
        }
        
        if ($field->user_id != php_session('user_id')) {
            abort(403, 'Вы не можете добавлять обследования к этому полю');
        }
        
        return view('surveys.create', compact('field'));
    }
    
    // Сохранить обследование
    public function store(Request $request, $fieldId)
    {
        if (!php_auth_check()) {
            return redirect('/auth')->with('error', 'Войдите для добавления обследования');
        }
        
        $field = DB::table('fields')->where('field_id', $fieldId)->first();
        
        if (!$field) {
            abort(404, 'Поле не найдено');
        }
        
        if ($field->user_id != php_session('user_id')) {
            abort(403, 'Вы не можете добавлять обследования к этому полю');
        }
        
        $validated = $request->validate([
            'survey_date' => 'required|date',
            'pest_level' => 'nullable|string|max:50|in:Низкий,Средний,Высокий,Очень высокий',
            'disease_level' => 'nullable|string|max:50|in:Низкий,Средний,Высокий,Очень высокий',
            'condition_notes' => 'nullable|string',
            'yield_mass_kg' => 'nullable|numeric|min:0',
            'yield_per_hectare' => 'nullable|numeric|min:0',
            'harvest_date' => 'nullable|date|after_or_equal:survey_date'
        ]);
        
        DB::table('agronomic_surveys')->insert([
            'field_id' => $fieldId, // Привязка к полю
            'survey_date' => $validated['survey_date'],
            'pest_level' => $validated['pest_level'] ?? null,
            'disease_level' => $validated['disease_level'] ?? null,
            'condition_notes' => $validated['condition_notes'] ?? null,
            'yield_mass_kg' => $validated['yield_mass_kg'] ?? null,
            'yield_per_hectare' => $validated['yield_per_hectare'] ?? null,
            'harvest_date' => $validated['harvest_date'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return redirect("/fields/{$fieldId}")->with('success', 'Обследование успешно добавлено');
    }
    
    // Показать детали обследования
    public function show($fieldId, $surveyId)
    {
        $survey = DB::table('agronomic_surveys')
            ->where('survey_id', $surveyId)
            ->where('field_id', $fieldId)
            ->first();
            
        if (!$survey) {
            abort(404, 'Обследование не найдено');
        }
        
        $field = DB::table('fields')->where('field_id', $fieldId)->first();
        
        // Проверяем доступ
        if (!$field->is_public && !php_auth_check()) {
            abort(403, 'Доступ запрещен');
        }
        
        if (!$field->is_public && php_auth_check() && $field->user_id != php_session('user_id')) {
            abort(403, 'Доступ запрещен');
        }
        
        return view('surveys.show', compact('survey', 'field'));
    }
    
    // Удалить обследование
    public function destroy($fieldId, $surveyId)
    {
        if (!php_auth_check()) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }
        
        $field = DB::table('fields')->where('field_id', $fieldId)->first();
        
        if (!$field || $field->user_id != php_session('user_id')) {
            return response()->json(['error' => 'Недостаточно прав'], 403);
        }
        
        DB::table('agronomic_surveys')
            ->where('survey_id', $surveyId)
            ->where('field_id', $fieldId)
            ->delete();
        
        return response()->json(['success' => true]);
    }
    
    // Список всех обследований поля
    public function index($fieldId)
    {
        $field = DB::table('fields')->where('field_id', $fieldId)->first();
        
        if (!$field) {
            abort(404, 'Поле не найдено');
        }
        
        // Проверяем доступ
        if (!$field->is_public && !php_auth_check()) {
            abort(403, 'Доступ запрещен');
        }
        
        if (!$field->is_public && php_auth_check() && $field->user_id != php_session('user_id')) {
            abort(403, 'Доступ запрещен');
        }
        
        $surveys = DB::table('agronomic_surveys')
            ->where('field_id', $fieldId)
            ->orderBy('survey_date', 'desc')
            ->paginate(10);
        
        return view('surveys.index', compact('field', 'surveys'));
    }
    // SurveyController.php
public function update(Request $request, Field $field, AgronomicSurvey $survey)
{
    $validated = $request->validate([
        'survey_date' => 'required|date',
        'harvest_date' => 'nullable|date',
        'pest_level' => 'nullable|string|max:50',
        'disease_level' => 'nullable|string|max:50',
        'condition_notes' => 'nullable|string',
        'yield_mass_kg' => 'nullable|numeric|min:0',
        'yield_per_hectare' => 'nullable|numeric|min:0'
    ]);
    
    $survey->update($validated);
    
    return response()->json([
        'success' => true,
        'message' => 'Обследование обновлено'
    ]);
}
}
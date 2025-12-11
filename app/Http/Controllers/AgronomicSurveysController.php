<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Field;
use App\Models\AgronomicSurvey;
use Illuminate\Support\Facades\Log;

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
            'field_id' => $fieldId,
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
    
    // Обновить обследование - ИСПРАВЛЕННАЯ ВЕРСИЯ
    public function update(Request $request, $fieldId, $surveyId)
    {
        Log::info('=== UPDATE SURVEY START ===');
        Log::info('Field ID: ' . $fieldId);
        Log::info('Survey ID: ' . $surveyId);
        Log::info('User ID from session: ' . (php_session('user_id') ?? 'null'));
        Log::info('Request data: ', $request->all());
        
        try {
            // Проверяем авторизацию
            if (!php_auth_check()) {
                Log::error('User not authenticated');
                return response()->json([
                    'success' => false,
                    'error' => 'Требуется авторизация'
                ], 401);
            }
            
            $userId = php_session('user_id');
            Log::info('User authenticated: ' . $userId);
            
            // Ищем поле
            $field = DB::table('fields')->where('field_id', $fieldId)->first();
            
            if (!$field) {
                Log::error('Field not found: ' . $fieldId);
                return response()->json([
                    'success' => false,
                    'error' => 'Поле не найдено'
                ], 404);
            }
            
            Log::info('Field found, user_id: ' . $field->user_id);
            
            // Проверяем права доступа
            if ($field->user_id != $userId) {
                Log::error('Access denied. Field user: ' . $field->user_id . ', Current user: ' . $userId);
                return response()->json([
                    'success' => false,
                    'error' => 'У вас нет прав на редактирование этого обследования'
                ], 403);
            }
            
            // Ищем обследование
            $survey = DB::table('agronomic_surveys')
                ->where('survey_id', $surveyId)
                ->where('field_id', $fieldId)
                ->first();
            
            if (!$survey) {
                Log::error('Survey not found: ' . $surveyId);
                return response()->json([
                    'success' => false,
                    'error' => 'Обследование не найдено'
                ], 404);
            }
            
            Log::info('Survey found');
            
            // ВАЛИДАЦИЯ - упрощенная для отладки
            $validatedData = $request->validate([
                'survey_date' => 'required|date',
                'harvest_date' => 'nullable|date',
                'pest_level' => 'nullable|string|max:50',
                'disease_level' => 'nullable|string|max:50',
                'condition_notes' => 'nullable|string',
                'yield_mass_kg' => 'nullable|numeric|min:0',
                'yield_per_hectare' => 'nullable|numeric|min:0'
            ]);
            
            Log::info('Validation passed', $validatedData);
            
            // Подготовка данных для обновления
            $updateData = [
                'survey_date' => $validatedData['survey_date'],
                'harvest_date' => $validatedData['harvest_date'] ?? null,
                'pest_level' => $validatedData['pest_level'] ?? null,
                'disease_level' => $validatedData['disease_level'] ?? null,
                'condition_notes' => $validatedData['condition_notes'] ?? null,
                'yield_mass_kg' => $validatedData['yield_mass_kg'] ?? null,
                'yield_per_hectare' => $validatedData['yield_per_hectare'] ?? null,
                'updated_at' => now()
            ];
            
            Log::info('Update data prepared', $updateData);
            
            // Обновляем запись
            $updated = DB::table('agronomic_surveys')
                ->where('survey_id', $surveyId)
                ->where('field_id', $fieldId)
                ->update($updateData);
            
            Log::info('Update result: ' . ($updated ? 'success' : 'failed'));
            
            return response()->json([
                'success' => true,
                'message' => 'Обследование успешно обновлено',
                'data' => $updateData
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'error' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Exception in update: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => 'Внутренняя ошибка сервера: ' . $e->getMessage()
            ], 500);
        } finally {
            Log::info('=== UPDATE SURVEY END ===');
        }
    }
    
    
    // Получить данные обследования для редактирования
    public function getData($fieldId, $surveyId)
    {
        if (!php_auth_check()) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }
        
        $survey = DB::table('agronomic_surveys')
            ->where('survey_id', $surveyId)
            ->where('field_id', $fieldId)
            ->first();
            
        if (!$survey) {
            return response()->json(['error' => 'Обследование не найдено'], 404);
        }
        
        $field = DB::table('fields')->where('field_id', $fieldId)->first();
        
        // Проверяем права
        if (!$field || $field->user_id != php_session('user_id')) {
            return response()->json(['error' => 'Недостаточно прав'], 403);
        }
        
        return response()->json([
            'success' => true,
            'survey' => $survey
        ]);
    }
}
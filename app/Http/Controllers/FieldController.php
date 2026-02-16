<?php

namespace App\Http\Controllers;

use App\Models\Field;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Log;

class FieldController extends Controller
{
    // Показать список полей (журнал)
    public function index()
    {
        return view('journal');
    }
    
    // Показать форму создания поля
public function create()
{
    if (!php_auth_check()) {
        return redirect('/auth')->with('error', 'Войдите для добавления поля');
    }
    
    // Получаем культуры из таблицы crops_catalog
    $crops = DB::table('crops_catalog')
        ->select('crop_id', 'crop_name', 'variety')
        ->orderBy('crop_name')
        ->get();
    
    return view('fields.create', compact('crops'));
}
    
    // Сохранить новое поле
public function store(Request $request)
{
    if (!php_auth_check()) {
        return redirect('/auth')->with('error', 'Войдите для добавления поля');
    }
    
    $validated = $request->validate([
        'field_name' => 'required|string|max:255',
        'field_area' => 'required|numeric|min:0.1',
        'crop_id' => 'nullable|exists:crops_catalog,crop_id',
        'season_name' => 'nullable|string|max:50',
        'polygon' => 'nullable|string',
        'is_public' => 'boolean'
    ]);
    
    $field = new Field();
    $field->user_id = php_session('user_id');
    $field->field_name = $validated['field_name'];
    $field->field_area = $validated['field_area'];
    $field->polygon = $validated['polygon'] ?? null;
    $field->is_public = $validated['is_public'] ?? 0;
    $field->save();
    
    // Если выбрана культура, создаем запись в fields_has_operation
    if (!empty($validated['crop_id'])) {
        DB::table('fields_has_operation')->insert([
            'field_id' => $field->field_id,
            'operation_id' => 1,
            'crop_id' => $validated['crop_id'],
            'season_name' => $validated['season_name'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    return redirect('/journal')->with('success', 'Поле успешно создано');
}
    // Показать детальную информацию о поле
public function show($id)
{
    $field = Field::find($id);
    
    if (!$field) {
        abort(404, 'Поле не найдено');
    }
    
    // Проверяем доступ
    if (!$field->is_public && !php_auth_check()) {
        abort(403, 'Доступ запрещен. Поле приватное');
    }
    
    if (!$field->is_public && php_auth_check() && $field->user_id != php_session('user_id')) {
        abort(403, 'Доступ запрещен. Поле приватное');
    }
    
    // Получаем пользователя-владельца
    $owner = DB::table('users')->where('user_id', $field->user_id)->first();
    
    // Получаем последнюю активность
    $lastActivity = $this->getLastActivityDate($id);
    
    // Получаем операции
    $operations = DB::table('fields_has_operation as fho')
        ->leftJoin('operation as op', 'fho.operation_id', '=', 'op.operation_id')
        ->where('fho.field_id', $id)
        ->select('fho.*', 'op.operation_date', 'op.operation_type', 'op.description', 'op.weather_conditions', 'op.updated_at as op_updated_at')
        ->get();
        
    $surveys = DB::table('agronomic_surveys')
        ->where('field_id', $id)
        ->orderBy('survey_date', 'desc')
        ->get();
    
    // Получаем информацию о культуре
    $crop_info = $this->getCropInfo($id);
    
    return view('fields.show', compact('field', 'owner', 'operations', 'surveys', 'lastActivity', 'crop_info'));
}

/**
 * Получить информацию о культуре для поля
 */
private function getCropInfo($fieldId)
{
    try {
        $cropData = DB::table('fields_has_operation as fho')
            ->leftJoin('crops_catalog as cc', 'fho.crop_id', '=', 'cc.crop_id')
            ->where('fho.field_id', $fieldId)
            ->orderBy('fho.created_at', 'desc')
            ->select(
                'cc.crop_name', 
                'cc.variety', 
                'cc.vegetation_period', 
                'fho.season_name'  // Добавляем season_name из fields_has_operation
            )
            ->first();

        if ($cropData) {
            return [
                'crop_name' => $cropData->crop_name ?? null,
                'variety' => $cropData->variety ?? null,
                'vegetation_period' => $cropData->vegetation_period ?? null,
                'season_name' => $cropData->season_name ?? null  // Теперь есть в массиве
            ];
        }
    } catch (\Exception $e) {
        // Если таблицы нет или произошла ошибка
        Log::error('Error getting crop info: ' . $e->getMessage());
    }

    return [
        'crop_name' => null,
        'variety' => null,
        'vegetation_period' => null,
        'season_name' => null
    ];
}
public function togglePrivacy($id)
{
    if (!php_auth_check()) {
        return redirect('/auth')->with('error', 'Войдите для изменения настроек');
    }
    
    $field = Field::find($id);
    
    if (!$field) {
        abort(404, 'Поле не найдено');
    }
    
    // Проверяем, что пользователь владелец
    if ($field->user_id != php_session('user_id')) {
        abort(403, 'Недостаточно прав');
    }
    
    // Меняем статус приватности
    $field->is_public = !$field->is_public;
    $field->save();
    
    return redirect()->back()->with('success', 
        $field->is_public 
            ? 'Поле теперь публичное' 
            : 'Поле теперь приватное'
    );
}
// Получить дату последнего действия на поле
private function getLastActivityDate($fieldId)
{
    $dates = [];
    
    // 1. Ищем последнюю операцию (из таблицы operation)
    try {
        $lastOperation = DB::table('operation as op')
            ->join('fields_has_operation as fho', 'op.operation_id', '=', 'fho.operation_id')
            ->where('fho.field_id', $fieldId)
            ->orderBy('op.updated_at', 'desc')
            ->select('op.updated_at', 'op.operation_type')
            ->first();
        
        if ($lastOperation && $lastOperation->updated_at) {
            $dates[] = [
                'date' => $lastOperation->updated_at,
                'type' => 'operation',
                'details' => $lastOperation->operation_type
            ];
        }
    } catch (\Exception $e) {
        // Пропускаем ошибки
    }
    
    // 2. Ищем последнее обследование
    try {
        $lastSurvey = DB::table('agronomic_surveys')
            ->where('field_id', $fieldId)
            ->orderBy('updated_at', 'desc')
            ->select('updated_at', 'survey_date')
            ->first();
        
        if ($lastSurvey && $lastSurvey->updated_at) {
            $dates[] = [
                'date' => $lastSurvey->updated_at,
                'type' => 'survey',
                'details' => date('d.m.Y', strtotime($lastSurvey->survey_date))
            ];
        }
    } catch (\Exception $e) {
        // Пропускаем ошибки
    }
    
    // 3. Ищем в fields_has_operation (на всякий случай)
    try {
        $lastFHO = DB::table('fields_has_operation')
            ->where('field_id', $fieldId)
            ->orderBy('updated_at', 'desc')
            ->select('updated_at')
            ->first();
        
        if ($lastFHO && $lastFHO->updated_at) {
            $dates[] = [
                'date' => $lastFHO->updated_at,
                'type' => 'field_operation',
                'details' => 'Связь поле-операция'
            ];
        }
    } catch (\Exception $e) {
        // Пропускаем ошибки
    }
    
    // Если есть активности, возвращаем последнюю
    if (!empty($dates)) {
        // Сортируем по дате
        usort($dates, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $dates[0]; // Возвращаем последнюю активность
    }
    
    // Если нет действий, возвращаем дату обновления поля
    $field = DB::table('fields')
        ->where('field_id', $fieldId)
        ->select('updated_at')
        ->first();
    
    return [
        'date' => $field->updated_at ?? now(),
        'type' => 'field',
        'details' => 'Информация о поле'
    ];
}
// Обновить информацию о поле
public function update(Request $request, $id)
{
    if (!php_auth_check()) {
        return response()->json(['error' => 'Не авторизован'], 401);
    }
    
    $field = Field::find($id);
    
    if (!$field) {
        return response()->json(['error' => 'Поле не найдено'], 404);
    }
    
    if ($field->user_id != php_session('user_id')) {
        return response()->json(['error' => 'Недостаточно прав'], 403);
    }
    
    $validated = $request->validate([
        'field_name' => 'required|string|max:255',
        'field_area' => 'required|numeric|min:0.1',
        'polygon' => 'nullable|string',
        'is_public' => 'boolean'
    ]);
    
    // Обновляем поле
    $field->field_name = $validated['field_name'];
    $field->field_area = $validated['field_area'];
    $field->polygon = $validated['polygon'] ?? null;
    $field->is_public = $validated['is_public'] ?? 0;
    $field->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Информация о поле обновлена',
        'field' => $field
    ]);
}
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationController extends Controller
{
    // Показать форму создания операции
    public function create($fieldId)
    {
        if (!php_auth_check()) {
            return redirect('/auth')->with('error', 'Войдите для добавления операции');
        }
        
        // Проверяем, что поле принадлежит пользователю
        $field = DB::table('fields')->where('field_id', $fieldId)->first();
        
        if (!$field) {
            abort(404, 'Поле не найдено');
        }
        
        if ($field->user_id != php_session('user_id')) {
            abort(403, 'Вы не можете добавлять операции к этому полю');
        }
        
        // Получаем культуры для выбора
        $crops = DB::table('crops_catalog')->get();
        
        return view('operations.create', compact('field', 'crops'));
    }
    
    // Сохранить операцию
    public function store(Request $request, $fieldId)
    {
        if (!php_auth_check()) {
            return redirect('/auth')->with('error', 'Войдите для добавления операции');
        }
        
        // Проверяем, что поле принадлежит пользователю
        $field = DB::table('fields')->where('field_id', $fieldId)->first();
        
        if (!$field) {
            abort(404, 'Поле не найдено');
        }
        
        if ($field->user_id != php_session('user_id')) {
            abort(403, 'Вы не можете добавлять операции к этому полю');
        }
        
        $validated = $request->validate([
            'operation_type' => 'required|string|max:100',
            'operation_date' => 'required|date',
            'crop_id' => 'nullable|exists:crops_catalog,crop_id',
            'season_name' => 'nullable|string|max:50',
            'applied_materials' => 'nullable|string|max:255',
            'application_rate' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'weather_conditions' => 'nullable|string|max:255'
        ]);
        
        // Создаем операцию в таблице operation
        $operationId = DB::table('operation')->insertGetId([
            'operation_type' => $validated['operation_type'],
            'operation_date' => $validated['operation_date'],
            'description' => $validated['description'] ?? null,
            'weather_conditions' => $validated['weather_conditions'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Создаем связь в fields_has_operation
        DB::table('fields_has_operation')->insert([
            'field_id' => $fieldId,
            'operation_id' => $operationId,
            'crop_id' => $validated['crop_id'] ?? null,
            'season_name' => $validated['season_name'] ?? null,
            'applied_materials' => $validated['applied_materials'] ?? null,
            'application_rate' => $validated['application_rate'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return redirect()->route('fields.show', $fieldId)
            ->with('success', 'Операция успешно добавлена');
    }
    
    // Показать форму редактирования операции
    public function edit($fieldId, $operationId)
    {
        if (!php_auth_check()) {
            return redirect('/auth')->with('error', 'Войдите для редактирования операции');
        }
        
        // Получаем операцию
        $operation = DB::table('fields_has_operation as fho')
            ->join('operation as op', 'fho.operation_id', '=', 'op.operation_id')
            ->where('fho.field_id', $fieldId)
            ->where('fho.operation_id', $operationId)
            ->first();
            
        if (!$operation) {
            abort(404, 'Операция не найдена');
        }
        
        // Проверяем владельца поля
        $field = DB::table('fields')->where('field_id', $fieldId)->first();
        
        if ($field->user_id != php_session('user_id')) {
            abort(403, 'Вы не можете редактировать эту операцию');
        }
        
        $crops = DB::table('crops_catalog')->get();
        
        return view('operations.edit', compact('operation', 'field', 'crops'));
    }
    
    // Обновить операцию
    public function update(Request $request, $fieldId, $operationId)
    {
        if (!php_auth_check()) {
            return redirect('/auth')->with('error', 'Войдите для редактирования операции');
        }
        
        // Проверяем владельца
        $field = DB::table('fields')->where('field_id', $fieldId)->first();
        
        if ($field->user_id != php_session('user_id')) {
            abort(403, 'Вы не можете редактировать эту операцию');
        }
        
        $validated = $request->validate([
            'operation_type' => 'required|string|max:100',
            'operation_date' => 'required|date',
            'crop_id' => 'nullable|exists:crops_catalog,id',
            'season_name' => 'nullable|string|max:50',
            'applied_materials' => 'nullable|string|max:255',
            'application_rate' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'weather_conditions' => 'nullable|string|max:255'
        ]);
        
        // Обновляем operation
        DB::table('operation')
            ->where('operation_id', $operationId)
            ->update([
                'operation_type' => $validated['operation_type'],
                'operation_date' => $validated['operation_date'],
                'description' => $validated['description'] ?? null,
                'weather_conditions' => $validated['weather_conditions'] ?? null,
                'updated_at' => now()
            ]);
        
        // Обновляем fields_has_operation
        DB::table('fields_has_operation')
            ->where('field_id', $fieldId)
            ->where('operation_id', $operationId)
            ->update([
                'crop_id' => $validated['crop_id'] ?? null,
                'season_name' => $validated['season_name'] ?? null,
                'applied_materials' => $validated['applied_materials'] ?? null,
                'application_rate' => $validated['application_rate'] ?? null,
                'updated_at' => now()
            ]);
        
        return redirect()->route('fields.show', $fieldId)
            ->with('success', 'Операция успешно обновлена');
    }
    
    // Удалить операцию
    public function destroy($fieldId, $operationId)
    {
        if (!php_auth_check()) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }
        
        // Проверяем владельца
        $field = DB::table('fields')->where('field_id', $fieldId)->first();
        
        if ($field->user_id != php_session('user_id')) {
            return response()->json(['error' => 'Недостаточно прав'], 403);
        }
        
        // Удаляем из обеих таблиц
        DB::table('fields_has_operation')
            ->where('field_id', $fieldId)
            ->where('operation_id', $operationId)
            ->delete();
            
        DB::table('operation')
            ->where('operation_id', $operationId)
            ->delete();
        
        return response()->json(['success' => true]);
    }

    // Показать детали операции
public function show($fieldId, $operationId)
{
    // Получаем операцию с JOIN всех связанных данных
    $operation = DB::table('fields_has_operation as fho')
        ->join('operation as op', 'fho.operation_id', '=', 'op.operation_id')
        ->leftJoin('crops_catalog as cc', 'fho.crop_id', '=', 'cc.crop_id')
        ->where('fho.field_id', $fieldId)
        ->where('fho.operation_id', $operationId)
        ->select(
            'op.*',
            'fho.*',
            'cc.crop_name',
            'cc.variety',
            DB::raw('cc.crop_id as crop_catalog_id')
        )
        ->first();
        
    if (!$operation) {
        abort(404, 'Операция не найдена');
    }
    
    $field = DB::table('fields')->where('field_id', $fieldId)->first();
    
    // Проверяем доступ
    if (!$field->is_public && !php_auth_check()) {
        abort(403, 'Доступ запрещен');
    }
    
    if (!$field->is_public && php_auth_check() && $field->user_id != php_session('user_id')) {
        abort(403, 'Доступ запрещен');
    }
    
    return view('operations.show', compact('operation', 'field'));
}
}
<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MyFieldController extends Controller
{
    /**
     * Показать все поля текущего пользователя
     */
    public function show()
    {
        // Проверяем авторизацию через PHP сессию
        if (!php_auth_check()) {
            return redirect('/auth')->with('error', 'Необходимо авторизоваться');
        }

        $userId = php_session('user_id');
        
        // Получаем все поля пользователя
        $fields = DB::table('fields as f')
            ->leftJoin('users as u', 'f.user_id', '=', 'u.user_id')
            ->where('f.user_id', $userId)
            ->select(
                'f.*',
                'u.username as user_username',
                'u.email as user_email',
                DB::raw('(SELECT COUNT(*) FROM fields_has_operation WHERE field_id = f.field_id) as operations_count'),
                DB::raw('(SELECT COUNT(*) FROM agronomic_surveys WHERE field_id = f.field_id) as surveys_count')
            )
            ->orderBy('f.updated_at', 'desc')
            ->get();

        // Подсчитываем статистику
        $totalFields = count($fields);
        $publicFields = collect($fields)->where('is_public', 1)->count();
        $privateFields = collect($fields)->where('is_public', 0)->count();
        
        // Получаем данные о культурах
        foreach ($fields as $field) {
            $field->crop_info = $this->getCropInfo($field->field_id);
        }

        return view('my_fields.show', [  // Изменил путь на my_fields.show
            'fields' => $fields,
            'totalFields' => $totalFields,
            'publicFields' => $publicFields,
            'privateFields' => $privateFields,
            'user' => php_auth_user()
        ]);
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
                ->select('cc.crop_name', 'cc.variety', 'cc.vegetation_period', 'fho.season_name')
                ->first();

            if ($cropData) {
                return [
                    'crop_name' => $cropData->crop_name ?? null,
                    'variety' => $cropData->variety ?? null,
                    'vegetation_period' => $cropData->vegetation_period ?? null,
                    'season_name' => $cropData->season_name ?? null
                ];
            }
        } catch (\Exception $e) {
            // Если таблицы нет или произошла ошибка
        }

        return [
            'crop_name' => null,
            'variety' => null,
            'vegetation_period' => null,
            'season_name' => null
        ];
    }

    /**
     * Переключить приватность поля
     */
    public function togglePrivacy($id)
    {
        if (!php_auth_check()) {
            return response()->json(['success' => false, 'message' => 'Не авторизован'], 401);
        }

        $field = DB::table('fields')->where('field_id', $id)->first();
        
        if (!$field) {
            return response()->json(['success' => false, 'message' => 'Поле не найдено'], 404);
        }

        // Проверяем, что пользователь владелец
        if ($field->user_id != php_session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Недостаточно прав'], 403);
        }

        // Меняем статус приватности
        $newStatus = $field->is_public ? 0 : 1;
        DB::table('fields')
            ->where('field_id', $id)
            ->update(['is_public' => $newStatus, 'updated_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => $newStatus ? 'Поле теперь публичное' : 'Поле теперь приватное',
            'is_public' => $newStatus
        ]);
    }

    /**
     * Удалить поле
     */
    public function destroy($id)
    {
        if (!php_auth_check()) {
            return response()->json(['success' => false, 'message' => 'Не авторизован'], 401);
        }

        $field = DB::table('fields')->where('field_id', $id)->first();
        
        if (!$field) {
            return response()->json(['success' => false, 'message' => 'Поле не найдено'], 404);
        }

        // Проверяем, что пользователь владелец
        if ($field->user_id != php_session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Недостаточно прав'], 403);
        }

        try {
            // Удаляем связанные записи
            DB::table('fields_has_operation')->where('field_id', $id)->delete();
            DB::table('agronomic_surveys')->where('field_id', $id)->delete();
            
            // Удаляем само поле
            DB::table('fields')->where('field_id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Поле успешно удалено'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении: ' . $e->getMessage()
            ], 500);
        }
    }
}
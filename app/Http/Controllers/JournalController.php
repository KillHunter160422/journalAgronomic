<?php

namespace App\Http\Controllers;

use App\Models\Field;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    /**
     * Показать журнал наблюдений (публичные поля)
     */
    public function index()
    {
        $currentUserId = php_auth_check() ? php_session('user_id') : null;
        
        try {
            // Получаем ТОЛЬКО публичные поля
            $fields = DB::table('fields as f')
                ->leftJoin('users as u', 'f.user_id', '=', 'u.user_id')
                ->where('f.is_public', 1) // Только публичные!
                ->select(
                    'f.*',
                    'u.username',
                    'u.email',
                    'u.avatar_url',
                    DB::raw('(SELECT COUNT(*) FROM fields_has_operation WHERE field_id = f.field_id) as operations_count'),
                    DB::raw('(SELECT COUNT(*) FROM agronomic_surveys WHERE field_id = f.field_id) as surveys_count')
                )
                ->orderBy('f.updated_at', 'desc')
                ->limit(12)
                ->get();

            // Получаем данные о культурах
            foreach ($fields as $field) {
                $field->crop_info = $this->getCropInfo($field->field_id);
            }

            return view('journal', [
                'fields' => $fields,
                'currentUserId' => $currentUserId
            ]);
            
        } catch (\Exception $e) {
            return view('journal', [
                'fields' => collect([]),
                'currentUserId' => $currentUserId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Загрузить больше публичных полей (AJAX)
     */
    public function loadMore($skip = 0)
    {
        try {
            $fields = DB::table('fields as f')
                ->leftJoin('users as u', 'f.user_id', '=', 'u.user_id')
                ->where('f.is_public', 1)
                ->select(
                    'f.*',
                    'u.username',
                    'u.email',
                    'u.avatar_url',
                    DB::raw('(SELECT COUNT(*) FROM fields_has_operation WHERE field_id = f.field_id) as operations_count'),
                    DB::raw('(SELECT COUNT(*) FROM agronomic_surveys WHERE field_id = f.field_id) as surveys_count')
                )
                ->orderBy('f.updated_at', 'desc')
                ->skip($skip)
                ->limit(6)
                ->get();

            // Получаем данные о культурах
            foreach ($fields as $field) {
                $field->crop_info = $this->getCropInfo($field->field_id);
            }

            return response()->json([
                'success' => true,
                'fields' => $fields
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка загрузки данных'
            ], 500);
        }
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
}
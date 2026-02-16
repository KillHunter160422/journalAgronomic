@extends('sample.main')

@section('content')
<div class="container">
    <div class="edit-operation-container">
        <div class="breadcrumbs">
            <a href="{{ route('fields.show', $field->field_id) }}">← Назад к полю</a>
        </div>
        
        <div class="edit-operation-header">
            <h1>
                @if(php_auth_check() && php_session('user_id') == $field->user_id)
                    ✏️ Редактировать операцию
                @else
                    📋 Просмотр операции
                @endif
            </h1>
            <p>Поле: <strong>{{ $field->field_name }}</strong></p>
        </div>
        
        <div class="edit-operation-card">
            <form action="{{ route('operations.update', ['field' => $field->field_id, 'operation' => $operation->operation_id]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Скрытые поля для связей -->
                <input type="hidden" name="field_id" value="{{ $field->field_id }}">
                
                <div class="form-group">
                    <label for="operation_type">Тип операции *</label>
                    @if(php_auth_check() && php_session('user_id') == $field->user_id)
                        <select id="operation_type" name="operation_type" class="form-select" required>
                            <option value="">Выберите тип операции</option>
                            <option value="sowing" {{ old('operation_type', $operation->operation_type) == 'sowing' ? 'selected' : '' }}>🌱 Посев</option>
                            <option value="fertilization" {{ old('operation_type', $operation->operation_type) == 'fertilization' ? 'selected' : '' }}>🧪 Внесение удобрений</option>
                            <option value="treatment" {{ old('operation_type', $operation->operation_type) == 'treatment' ? 'selected' : '' }}>💊 Обработка (СЗР)</option>
                            <option value="harvest" {{ old('operation_type', $operation->operation_type) == 'harvest' ? 'selected' : '' }}>🌾 Уборка</option>
                            <option value="plowing" {{ old('operation_type', $operation->operation_type) == 'plowing' ? 'selected' : '' }}>🚜 Вспашка</option>
                            <option value="irrigation" {{ old('operation_type', $operation->operation_type) == 'irrigation' ? 'selected' : '' }}>💧 Полив</option>
                            <option value="soil_preparation" {{ old('operation_type', $operation->operation_type) == 'soil_preparation' ? 'selected' : '' }}>🔧 Подготовка почвы</option>
                            <option value="monitoring" {{ old('operation_type', $operation->operation_type) == 'monitoring' ? 'selected' : '' }}>📊 Мониторинг</option>
                        </select>
                    @else
                        <div class="form-display">
                            @switch($operation->operation_type)
                                @case('sowing') 🌱 Посев @break
                                @case('fertilization') 🧪 Внесение удобрений @break
                                @case('treatment') 💊 Обработка (СЗР) @break
                                @case('harvest') 🌾 Уборка @break
                                @case('plowing') 🚜 Вспашка @break
                                @case('irrigation') 💧 Полив @break
                                @case('soil_preparation') 🔧 Подготовка почвы @break
                                @case('monitoring') 📊 Мониторинг @break
                                @default {{ $operation->operation_type }}
                            @endswitch
                        </div>
                    @endif
                </div>
                
                <div class="form-group">
                    <label for="operation_date">Дата операции *</label>
                    @if(php_auth_check() && php_session('user_id') == $field->user_id)
                        <input type="date" id="operation_date" name="operation_date" 
                               value="{{ old('operation_date', date('Y-m-d', strtotime($operation->operation_date))) }}" 
                               required>
                    @else
                        <div class="form-display">
                            {{ date('d.m.Y', strtotime($operation->operation_date)) }}
                        </div>
                    @endif
                </div>
                
                <div class="form-group">
                    <label for="description">Описание операции</label>
                    @if(php_auth_check() && php_session('user_id') == $field->user_id)
                        <textarea id="description" name="description" rows="4" 
                                  placeholder="Опишите детали операции...">{{ old('description', $operation->description) }}</textarea>
                    @else
                        <div class="form-display">
                            {{ $operation->description ?: 'Не указано' }}
                        </div>
                    @endif
                </div>
                
                <div class="form-group">
                    <label for="weather_conditions">Погодные условия</label>
                    @if(php_auth_check() && php_session('user_id') == $field->user_id)
                        <input type="text" id="weather_conditions" name="weather_conditions"
                               value="{{ old('weather_conditions', $operation->weather_conditions) }}"
                               placeholder="Например: Солнечно, +20°C">
                    @else
                        <div class="form-display">
                            {{ $operation->weather_conditions ?: 'Не указано' }}
                        </div>
                    @endif
                </div>
                
                <!-- Детали операции из таблицы fields_has_operation -->
                @if(isset($fields_has_operation) && ($fields_has_operation->applied_materials || $fields_has_operation->application_rate || $fields_has_operation->crop_id || $fields_has_operation->season_name))
                <div class="form-section">
                    <h3>📋 Детали применения</h3>
                    
                    @if(isset($fields_has_operation->applied_materials) && $fields_has_operation->applied_materials)
                    <div class="form-group">
                        <label>Применяемые материалы</label>
                        @if(php_auth_check() && php_session('user_id') == $field->user_id)
                            <input type="text" id="applied_materials" name="applied_materials"
                                   value="{{ old('applied_materials', $fields_has_operation->applied_materials ?? '') }}"
                                   placeholder="Например: Аммиачная селитра, Гербицид 'Раундап'">
                        @else
                            <div class="form-display">
                                {{ $fields_has_operation->applied_materials }}
                            </div>
                        @endif
                    </div>
                    @endif
                    
                    @if(isset($fields_has_operation->application_rate) && $fields_has_operation->application_rate)
                    <div class="form-group">
                        <label>Норма внесения</label>
                        @if(php_auth_check() && php_session('user_id') == $field->user_id)
                            <div class="input-with-unit">
                                <input type="number" id="application_rate" name="application_rate" 
                                       step="0.01" min="0"
                                       value="{{ old('application_rate', $fields_has_operation->application_rate ?? '') }}"
                                       placeholder="Например: 150">
                                <span class="unit">кг/га</span>
                            </div>
                        @else
                            <div class="form-display">
                                {{ $fields_has_operation->application_rate }} кг/га
                            </div>
                        @endif
                    </div>
                    @endif
                    
                    @if(isset($fields_has_operation->crop_id) && $fields_has_operation->crop_id && isset($crops))
                    <div class="form-group">
                        <label>Культура</label>
                        @if(php_auth_check() && php_session('user_id') == $field->user_id)
                            <select id="crop_id" name="crop_id" class="form-select">
                                <option value="">Выберите культуру</option>
                                @foreach($crops as $crop)
                                    <option value="{{ $crop->crop_id }}" 
                                            {{ old('crop_id', $fields_has_operation->crop_id ?? '') == $crop->crop_id ? 'selected' : '' }}>
                                        {{ $crop->crop_name ?? 'Культура #' . $crop->crop_id }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="form-display">
                                @php
                                    $currentCrop = collect($crops)->firstWhere('crop_id', $fields_has_operation->crop_id);
                                @endphp
                                {{ $currentCrop->crop_name ?? 'Культура #' . $fields_has_operation->crop_id }}
                            </div>
                        @endif
                    </div>
                    @endif
                    
                    @if(isset($fields_has_operation->season_name) && $fields_has_operation->season_name)
                    <div class="form-group">
                        <label>Сезон/Кампания</label>
                        @if(php_auth_check() && php_session('user_id') == $field->user_id)
                            <input type="text" id="season_name" name="season_name"
                                   value="{{ old('season_name', $fields_has_operation->season_name ?? '') }}"
                                   placeholder="Например: Весна 2024">
                        @else
                            <div class="form-display">
                                {{ $fields_has_operation->season_name }}
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
                
                <div class="form-actions">
                    @if(php_auth_check() && php_session('user_id') == $field->user_id)
                        <!-- 3 кнопки для автора -->
                        <a href="{{ route('fields.show', $field->field_id) }}" class="btn-cancel">Отмена</a>
                        <a href="{{ route('operations.show', ['field' => $field->field_id, 'operation' => $operation->operation_id]) }}" class="btn-view">Просмотр</a>
                        <button type="submit" class="btn-submit">Сохранить</button>
                    @else
                        <!-- 1 кнопка для неавторизованных/не-авторов -->
                        <a href="{{ route('fields.show', $field->field_id) }}" class="btn-return">← Вернуться к полю</a>
                    @endif
                </div>
                
                @if($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<style>
.edit-operation-container {
    max-width: 700px;
    margin: 30px auto;
    padding: 0 20px;
}

.breadcrumbs {
    margin-bottom: 20px;
}

.breadcrumbs a {
    color: #47866A;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.breadcrumbs a:hover {
    text-decoration: underline;
}

.edit-operation-header {
    margin-bottom: 30px;
    text-align: center;
}

.edit-operation-header h1 {
    color: #47866A;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.edit-operation-header p {
    color: #666;
    margin: 0;
    font-size: 16px;
}

.edit-operation-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border: 1px solid #eaeaea;
}

.form-section {
    margin: 30px 0;
    padding: 25px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
}

.form-section h3 {
    color: #47866A;
    margin-top: 0;
    margin-bottom: 25px;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 10px;
    border-bottom: 2px solid #eaeaea;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

/* Стили для редактируемых полей (автор) */
.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.3s;
    background-color: #fff;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #47866A;
    outline: none;
    box-shadow: 0 0 0 3px rgba(71, 134, 106, 0.1);
}

/* Стили для отображения (не-автор)*/
.form-display {
    padding: 12px 0;
    font-size: 16px;
    color: #333;
    line-height: 1.5;
    border-bottom: 1px solid #f0f0f0;
    min-height: 44px;
    display: flex;
    align-items: center;
}

.form-display:last-child {
    border-bottom: none;
}

.input-with-unit {
    position: relative;
    display: flex;
}

.input-with-unit input {
    flex: 1;
    padding-right: 70px;
}

.input-with-unit .unit {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    font-size: 14px;
}

/* Кнопки */
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 40px;
    padding-top: 25px;
    border-top: 1px solid #eee;
}

/* 3 кнопки для автора */
.form-actions .btn-cancel,
.form-actions .btn-view,
.form-actions .btn-submit {
    flex: 1;
    padding: 14px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-cancel {
    background: #f8f9fa;
    color: #666;
    border: 1px solid #ddd;
}

.btn-cancel:hover {
    background: #e9ecef;
}

.btn-view {
    background: #6c757d;
    color: white;
    border: 1px solid #6c757d;
}

.btn-view:hover {
    background: #5a6268;
    color: white;
}

.btn-submit {
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    font-weight: bold;
}

.btn-submit:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(71, 134, 106, 0.2);
}

/* 1 кнопка для неавторизованных */
.btn-return {
    flex: 1;
    padding: 14px;
    background: #47866A;
    color: white;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-return:hover {
    background: #3a7557;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(71, 134, 106, 0.2);
}

/* Алерт ошибок */
.alert {
    padding: 15px;
    border-radius: 8px;
    margin-top: 20px;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert-danger ul {
    padding-left: 20px;
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .edit-operation-container {
        padding: 0 15px;
    }
    
    .edit-operation-card {
        padding: 20px;
    }
    
    .form-section {
        padding: 15px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    /* На мобильных для автора тоже 1 кнопка в столбик */
    .form-actions .btn-cancel,
    .form-actions .btn-view,
    .form-actions .btn-submit {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Если пользователь не автор, делаем форму неактивной
    const isAuthor = @json(php_auth_check() && php_session('user_id') == ($field->user_id ?? 0));
    
    if (!isAuthor) {
        // Все поля становятся readOnly
        const form = document.querySelector('form');
        if (form) {
            // Отключаем все элементы формы
            const formElements = form.querySelectorAll('input, textarea, select, button[type="submit"]');
            formElements.forEach(element => {
                element.disabled = true;
                element.style.pointerEvents = 'none';
                element.style.opacity = '0.7';
            });
            
            // Скрываем кнопку submit если она есть
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.style.display = 'none';
            }
        }
    }
});
</script>
@endsection
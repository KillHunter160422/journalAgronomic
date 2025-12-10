@extends('sample.main')

@section('header-title')
Добавить операцию на поле
@endsection

@section('content')
<div class="create-operation-container">
    <h1>Добавить операцию на поле: {{ $field->field_name }}</h1>
    
    <form action="{{ route('operations.store', $field->field_id) }}" method="POST" class="operation-form">
        @csrf
        
        <div class="form-group">
            <label for="operation_type">Тип операции *</label>
            <select id="operation_type" name="operation_type" class="form-select" required>
                <option value="">-- Выберите тип операции --</option>
                <option value="Вспашка">Вспашка</option>
                <option value="Боронование">Боронование</option>
                <option value="Посев">Посев</option>
                <option value="Полив">Полив</option>
                <option value="Внесение удобрений">Внесение удобрений</option>
                <option value="Обработка от вредителей">Обработка от вредителей</option>
                <option value="Уборка урожая">Уборка урожая</option>
                <option value="Планирование">Планирование</option>
                <option value="Обследование">Обследование</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="operation_date">Дата операции *</label>
            <input type="date" id="operation_date" name="operation_date" 
                   value="{{ date('Y-m-d') }}" required>
        </div>
        
        <div class="form-group">
            <label for="crop_id">Культура (необязательно)</label>
            <select id="crop_id" name="crop_id" class="form-select">
                <option value="">-- Выберите культуру --</option>
                @foreach($crops as $crop)
                    <option value="{{ $crop->crop_id }}">
                        {{ $crop->crop_name }}
                        @if($crop->variety)
                            ({{ $crop->variety }})
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="form-group">
            <label for="season_name">Сезон</label>
            <select id="season_name" name="season_name" class="form-select">
                <option value="">-- Выберите сезон --</option>
                <option value="Весна">Весна</option>
                <option value="Лето">Лето</option>
                <option value="Осень">Осень</option>
                <option value="Зима">Зима</option>
                <option value="Озимый">Озимый</option>
                <option value="Яровой">Яровой</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="applied_materials">Примененные материалы</label>
            <input type="text" id="applied_materials" name="applied_materials" 
                   placeholder="Например: Аммиачная селитра, Гербицид">
        </div>
        
        <div class="form-group">
            <label for="application_rate">Норма внесения (кг/га)</label>
            <input type="number" id="application_rate" name="application_rate" 
                   step="0.01" min="0" placeholder="Например: 150">
        </div>
        
        <div class="form-group">
            <label for="weather_conditions">Погодные условия</label>
            <input type="text" id="weather_conditions" name="weather_conditions" 
                   placeholder="Например: Солнечно, +20°C">
        </div>
        
        <div class="form-group">
            <label for="description">Описание</label>
            <textarea id="description" name="description" rows="4" 
                     placeholder="Детальное описание операции..."></textarea>
        </div>
        
        <div class="form-actions">
            <a href="{{ route('fields.show', $field->field_id) }}" class="btn-cancel">Отмена</a>
            <button type="submit" class="btn-submit">Добавить операцию</button>
        </div>
    </form>
</div>

<style>
.create-operation-container {
    max-width: 700px;
    margin: 0 auto;
    padding: 30px 20px;
}

.create-operation-container h1 {
    text-align: center;
    color: #47866A;
    margin-bottom: 30px;
    font-size: 24px;
}

.operation-form {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
    font-size: 14px;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="date"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #47866A;
    box-shadow: 0 0 0 2px rgba(71, 134, 106, 0.1);
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-submit, .btn-cancel {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
    text-decoration: none;
}

.btn-submit {
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    font-weight: bold;
}

.btn-submit:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
}

.btn-cancel {
    background: #f8f9fa;
    color: #666;
    border: 1px solid #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-cancel:hover {
    background: #e9ecef;
    color: #333;
}

@media (max-width: 768px) {
    .operation-form {
        padding: 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Устанавливаем максимальную дату как сегодня
    const dateInput = document.getElementById('operation_date');
    const today = new Date().toISOString().split('T')[0];
    dateInput.max = today;
    
    // Автоподстановка сезона по дате
    dateInput.addEventListener('change', function() {
        const date = new Date(this.value);
        const month = date.getMonth() + 1;
        const seasonSelect = document.getElementById('season_name');
        
        if (seasonSelect.value === '') {
            if (month >= 3 && month <= 5) {
                seasonSelect.value = 'Весна';
            } else if (month >= 6 && month <= 8) {
                seasonSelect.value = 'Лето';
            } else if (month >= 9 && month <= 11) {
                seasonSelect.value = 'Осень';
            } else {
                seasonSelect.value = 'Зима';
            }
        }
    });
});
</script>
@endsection
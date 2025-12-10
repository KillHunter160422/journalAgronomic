@extends('sample.main')

@section('header-title')
Добавить агрономическое обследование
@endsection

@section('content')
<div class="create-survey-container">
    <h1>Добавить обследование для поля: {{ $field->field_name }}</h1>
    
    <form action="{{ route('surveys.store', $field->field_id) }}" method="POST" class="survey-form">
        @csrf
        
        <div class="form-group">
            <label for="survey_date">Дата обследования *</label>
            <input type="date" id="survey_date" name="survey_date" 
                   value="{{ date('Y-m-d') }}" required>
        </div>
        
        <div class="form-group">
            <label for="past_level">Уровень вредителей (past_level)</label>
            <select id="past_level" name="past_level" class="form-select">
                <option value="">-- Выберите уровень --</option>
                <option value="Низкий">Низкий</option>
                <option value="Средний">Средний</option>
                <option value="Высокий">Высокий</option>
                <option value="Очень высокий">Очень высокий</option>
            </select>
            <small class="hint">Поле "past_level" в БД (возможно pests/вредители)</small>
        </div>
        
        <div class="form-group">
            <label for="disease_level">Уровень болезней *</label>
            <select id="disease_level" name="disease_level" class="form-select">
                <option value="">-- Выберите уровень --</option>
                <option value="Низкий">Низкий</option>
                <option value="Средний">Средний</option>
                <option value="Высокий">Высокий</option>
                <option value="Очень высокий">Очень высокий</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="condition_nodes">Состояние растений (condition_nodes)</label>
            <textarea id="condition_nodes" name="condition_nodes" rows="4" 
                     placeholder="Опишите состояние растений, узлов, листьев..."></textarea>
            <small class="hint">Поле "condition_nodes" в БД</small>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="yield_mass_log">Масса урожая (yield_mass_log, кг)</label>
                <input type="number" id="yield_mass_log" name="yield_mass_log" 
                       step="0.01" min="0" placeholder="Например: 1250.5">
                <small class="hint">Поле "yield_mass_log" в БД</small>
            </div>
            
            <div class="form-group">
                <label for="yield_per_hectare">Урожайность (yield_per_hectare, ц/га)</label>
                <input type="number" id="yield_per_hectare" name="yield_per_hectare" 
                       step="0.01" min="0" placeholder="Например: 35.8">
            </div>
        </div>
        
        <div class="form-group">
            <label for="harvest_date">Дата сбора урожая</label>
            <input type="date" id="harvest_date" name="harvest_date">
        </div>
        
        <div class="form-actions">
            <a href="/fields/{{ $field->field_id }}" class="btn-cancel">Отмена</a>
            <button type="submit" class="btn-submit">Добавить обследование</button>
        </div>
    </form>
</div>

<style>
.create-survey-container {
    max-width: 700px;
    margin: 0 auto;
    padding: 30px 20px;
}

.create-survey-container h1 {
    text-align: center;
    color: #47866A;
    margin-bottom: 30px;
    font-size: 24px;
}

.survey-form {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: flex;
    gap: 20px;
}

.form-row .form-group {
    flex: 1;
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

.hint {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
    font-style: italic;
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
    .survey-form {
        padding: 20px;
    }
    
    .form-row {
        flex-direction: column;
        gap: 15px;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Устанавливаем максимальную дату как сегодня
    const dateInput = document.getElementById('survey_date');
    const today = new Date().toISOString().split('T')[0];
    dateInput.max = today;
    
    // Автоматический расчет урожайности если есть масса и площадь
    const yieldMassInput = document.getElementById('yield_mass_log');
    const yieldPerHectareInput = document.getElementById('yield_per_hectare');
    const fieldArea = {{ $field->field_area ?? 1 }};
    
    yieldMassInput.addEventListener('change', function() {
        if (this.value && fieldArea > 0) {
            const yieldPerHectare = (parseFloat(this.value) / fieldArea) / 10; // кг/га в ц/га
            if (!yieldPerHectareInput.value) {
                yieldPerHectareInput.value = yieldPerHectare.toFixed(2);
            }
        }
    });
    
    // Проверка что дата сбора урожая не раньше даты обследования
    const harvestDateInput = document.getElementById('harvest_date');
    
    dateInput.addEventListener('change', function() {
        harvestDateInput.min = this.value;
    });
});
</script>
@endsection
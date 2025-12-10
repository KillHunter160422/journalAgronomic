@extends('sample.main')

@section('header-title')
Добавить новое поле
@endsection

@section('content')
<div class="create-field-container">
    <h1>Добавить новое поле</h1>
    
    <form action="{{ route('fields.store') }}" method="POST" class="field-form">
        @csrf <!-- Laravel CSRF -->
        
        <div class="form-group">
            <label for="field_name">Название поля *</label>
            <input type="text" id="field_name" name="field_name" required 
                   placeholder="Например: Северное поле, Поле №1"
                   value="{{ old('field_name') }}">
            @error('field_name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="field_area">Площадь поля (га) *</label>
            <input type="number" id="field_area" name="field_area" 
                   step="0.01" min="0.1" required 
                   placeholder="Например: 25.5"
                   value="{{ old('field_area') }}">
            @error('field_area')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        
        <!-- Выбор культуры из таблицы crops_catalog -->
        <div class="form-group">
            <label for="crop_id">Культура (необязательно)</label>
            <select id="crop_id" name="crop_id" class="form-select">
                <option value="">-- Выберите культуру --</option>
                @foreach($crops as $crop)
                    <option value="{{ $crop->crop_id }}" 
                        {{ old('crop_id') == $crop->crop_id ? 'selected' : '' }}>
                        {{ $crop->crop_name }}
                        @if($crop->variety)
                            ({{ $crop->variety }})
                        @endif
                    </option>
                @endforeach
            </select>
            <small class="hint">Выбор культуры поможет в планировании операций</small>
            @error('crop_id')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        
        <!-- Сезон для культуры -->
        <div class="form-group">
            <label for="season_name">Сезон (необязательно)</label>
            <select id="season_name" name="season_name" class="form-select">
                <option value="">-- Выберите сезон --</option>
                <option value="Весна" {{ old('season_name') == 'Весна' ? 'selected' : '' }}>Весна</option>
                <option value="Лето" {{ old('season_name') == 'Лето' ? 'selected' : '' }}>Лето</option>
                <option value="Осень" {{ old('season_name') == 'Осень' ? 'selected' : '' }}>Осень</option>
                <option value="Зима" {{ old('season_name') == 'Зима' ? 'selected' : '' }}>Зима</option>
                <option value="Озимый" {{ old('season_name') == 'Озимый' ? 'selected' : '' }}>Озимый</option>
                <option value="Яровой" {{ old('season_name') == 'Яровой' ? 'selected' : '' }}>Яровой</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="polygon">Координаты полигона (необязательно)</label>
            <textarea id="polygon" name="polygon" rows="3" 
                     placeholder="Координаты в формате GeoJSON или просто текст">{{ old('polygon') }}</textarea>
        </div>
        
        <div class="form-group checkbox-group">
            <label>
                <input type="checkbox" name="is_public" value="1" 
                    {{ old('is_public', true) ? 'checked' : '' }}>
                <span>Сделать поле публичным (видимым для всех)</span>
            </label>
            <small class="hint">Если не отмечено, поле будет видно только вам</small>
        </div>
        
        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="history.back()">Отмена</button>
            <button type="submit" class="btn-submit">Создать поле</button>
        </div>
    </form>
</div>

<style>
.create-field-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 30px 20px;
}

.create-field-container h1 {
    text-align: center;
    color: #47866A;
    margin-bottom: 30px;
}

.field-form {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #47866A;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.hint {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 13px;
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
}

.btn-submit {
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    font-weight: bold;
}

.btn-submit:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
}

.btn-cancel {
    background: #f8f9fa;
    color: #666;
    border: 1px solid #ddd;
}

.btn-cancel:hover {
    background: #e9ecef;
}

@media (max-width: 768px) {
    .field-form {
        padding: 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>
@endsection
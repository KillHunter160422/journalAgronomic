@extends('sample.main')

@section('header-title')
Обследование поля
@endsection

@section('content')
<div class="survey-show-container">
    <div class="survey-header">
        <h1>Обследование поля: {{ $field->field_name }}</h1>
        <div class="survey-meta">
            <span class="survey-date">Дата: {{ date('d.m.Y', strtotime($survey->survey_date)) }}</span>
            <a href="/fields/{{ $field->field_id }}" class="btn-back">← Назад к полю</a>
        </div>
    </div>
    
    <div class="survey-details">
        <!-- Основная информация -->
        <div class="info-card">
            <h2>Основная информация</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Поле:</span>
                    <span class="value">{{ $field->field_name }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Площадь поля:</span>
                    <span class="value">{{ $field->field_area }} га</span>
                </div>
                <div class="info-item">
                    <span class="label">Дата обследования:</span>
                    <span class="value">{{ date('d.m.Y', strtotime($survey->survey_date)) }}</span>
                </div>
                @if($survey->harvest_date)
                <div class="info-item">
                    <span class="label">Дата сбора урожая:</span>
                    <span class="value">{{ date('d.m.Y', strtotime($survey->harvest_date)) }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Уровни вредителей и болезней -->
        <div class="info-card">
            <h2>Состояние растений</h2>
            <div class="status-grid">
                @if($survey->pest_level)
                <div class="status-item">
                    <span class="label">Уровень вредителей:</span>
                    <span class="status-badge {{ strtolower($survey->pest_level) }}">
                        {{ $survey->pest_level }}
                    </span>
                </div>
                @endif
                
                @if($survey->disease_level)
                <div class="status-item">
                    <span class="label">Уровень болезней:</span>
                    <span class="status-badge {{ strtolower($survey->disease_level) }}">
                        {{ $survey->disease_level }}
                    </span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Заметки о состоянии -->
        @if($survey->condition_notes)
        <div class="info-card">
            <h2>Заметки о состоянии растений</h2>
            <div class="notes-content">
                <p>{{ $survey->condition_notes }}</p>
            </div>
        </div>
        @endif
        
        <!-- Урожайность -->
        @if($survey->yield_mass_kg || $survey->yield_per_hectare)
        <div class="info-card">
            <h2>Урожайность</h2>
            <div class="yield-grid">
                @if($survey->yield_mass_kg)
                <div class="yield-item">
                    <span class="label">Масса урожая:</span>
                    <span class="value">{{ $survey->yield_mass_kg }} кг</span>
                </div>
                @endif
                
                @if($survey->yield_per_hectare)
                <div class="yield-item">
                    <span class="label">Урожайность с гектара:</span>
                    <span class="value">{{ $survey->yield_per_hectare }} ц/га</span>
                </div>
                @endif
                
                @if($survey->yield_mass_kg && $survey->yield_per_hectare)
                <div class="yield-item">
                    <span class="label">Подтверждение расчета:</span>
                    <span class="value">
                        @php
                            $calculated = ($survey->yield_mass_kg / $field->field_area) / 10;
                            $difference = abs($calculated - $survey->yield_per_hectare);
                        @endphp
                        Расчёт: {{ number_format($calculated, 2) }} ц/га 
                        (разница: {{ number_format($difference, 2) }} ц/га)
                    </span>
                </div>
                @endif
            </div>
        </div>
        @endif
        
        <!-- Дополнительная информация -->
        <div class="info-card">
            <h2>Дополнительная информация</h2>
            <div class="meta-grid">
                <div class="meta-item">
                    <span class="label">Дата создания записи:</span>
                    <span class="value">{{ date('d.m.Y H:i', strtotime($survey->created_at)) }}</span>
                </div>
                <div class="meta-item">
                    <span class="label">Последнее обновление:</span>
                    <span class="value">{{ date('d.m.Y H:i', strtotime($survey->updated_at)) }}</span>
                </div>
            </div>
        </div>
        
        <!-- Кнопки действий -->
        @if(php_auth_check() && $field->user_id == php_session('user_id'))
        <div class="action-buttons">
            <button class="btn-edit" onclick="openEditSurveyModal()">
                ✏️ Редактировать обследование
            </button>
            <button class="btn-delete" 
                    data-survey-id="{{ $survey->survey_id }}"
                    data-field-id="{{ $field->field_id }}">
                🗑️ Удалить
            </button>
        </div>
        @endif
    </div>
</div>

<!-- Модальное окно редактирования обследования -->
<div class="modal" id="editSurveyModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Редактировать обследование</h2>
            <span class="modal-close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editSurveyForm" method="POST">
                @csrf
                <input type="hidden" name="field_id" value="{{ $field->field_id }}">
                <input type="hidden" name="survey_id" value="{{ $survey->survey_id }}">
                
                <div class="form-group">
                    <label for="survey_date">Дата обследования *</label>
                    <input type="date" 
                           id="survey_date" 
                           name="survey_date" 
                           class="form-control"
                           value="{{ date('Y-m-d', strtotime($survey->survey_date)) }}"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="harvest_date">Дата сбора урожая</label>
                    <input type="date" 
                           id="harvest_date" 
                           name="harvest_date" 
                           class="form-control"
                           value="{{ $survey->harvest_date ? date('Y-m-d', strtotime($survey->harvest_date)) : '' }}">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="pest_level">Уровень вредителей</label>
                        <select id="pest_level" name="pest_level" class="form-control">
                            <option value="">Выберите уровень</option>
                            <option value="Низкий" {{ $survey->pest_level == 'Низкий' ? 'selected' : '' }}>Низкий</option>
                            <option value="Средний" {{ $survey->pest_level == 'Средний' ? 'selected' : '' }}>Средний</option>
                            <option value="Высокий" {{ $survey->pest_level == 'Высокий' ? 'selected' : '' }}>Высокий</option>
                            <option value="Очень высокий" {{ $survey->pest_level == 'Очень высокий' ? 'selected' : '' }}>Очень высокий</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="disease_level">Уровень болезней</label>
                        <select id="disease_level" name="disease_level" class="form-control">
                            <option value="">Выберите уровень</option>
                            <option value="Низкий" {{ $survey->disease_level == 'Низкий' ? 'selected' : '' }}>Низкий</option>
                            <option value="Средний" {{ $survey->disease_level == 'Средний' ? 'selected' : '' }}>Средний</option>
                            <option value="Высокий" {{ $survey->disease_level == 'Высокий' ? 'selected' : '' }}>Высокий</option>
                            <option value="Очень высокий" {{ $survey->disease_level == 'Очень высокий' ? 'selected' : '' }}>Очень высокий</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="condition_notes">Заметки о состоянии растений</label>
                    <textarea id="condition_notes" 
                              name="condition_notes" 
                              class="form-control" 
                              rows="4"
                              placeholder="Опишите состояние растений...">{{ $survey->condition_notes ?? '' }}</textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="yield_mass_kg">Масса урожая (кг)</label>
                        <input type="number" 
                               id="yield_mass_kg" 
                               name="yield_mass_kg" 
                               class="form-control"
                               step="0.01"
                               min="0"
                               value="{{ $survey->yield_mass_kg ?? '' }}"
                               placeholder="Например: 5000">
                    </div>
                    
                    <div class="form-group">
                        <label for="yield_per_hectare">Урожайность (ц/га)</label>
                        <input type="number" 
                               id="yield_per_hectare" 
                               name="yield_per_hectare" 
                               class="form-control"
                               step="0.01"
                               min="0"
                               value="{{ $survey->yield_per_hectare ?? '' }}"
                               placeholder="Например: 50.5">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.survey-show-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 30px 20px;
}

.survey-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.survey-header h1 {
    color: #47866A;
    margin: 0 0 10px 0;
}

.survey-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.survey-date {
    font-size: 16px;
    color: #666;
    font-weight: 500;
}

.btn-back {
    display: inline-block;
    padding: 8px 15px;
    background: #f8f9fa;
    color: #47866A;
    text-decoration: none;
    border-radius: 6px;
    border: 1px solid #ddd;
    font-size: 14px;
    transition: all 0.3s;
}

.btn-back:hover {
    background: #e9ecef;
    color: #3a7557;
}

.info-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.info-card h2 {
    color: #333;
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
    font-size: 18px;
}

.info-grid, .status-grid, .yield-grid, .meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.info-item, .status-item, .yield-item, .meta-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f5f5f5;
}

.info-item .label, 
.status-item .label, 
.yield-item .label,
.meta-item .label {
    color: #666;
    font-weight: 500;
}

.info-item .value,
.yield-item .value,
.meta-item .value {
    font-weight: bold;
    color: #333;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-badge.низкий {
    background: #e7f7ef;
    color: #2e7d5f;
}

.status-badge.средний {
    background: #fff3e0;
    color: #ef6c00;
}

.status-badge.высокий {
    background: #ffebee;
    color: #c62828;
}

.status-badge.очень_высокий {
    background: #f44336;
    color: white;
}

.notes-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    line-height: 1.6;
    color: #333;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn-edit, .btn-delete {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-edit {
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    font-weight: bold;
}

.btn-edit:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
}

.btn-delete {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
    font-weight: bold;
}

.btn-delete:hover {
    background: #ffcdd2;
    color: #b71c1c;
}

/* Модальное окно */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow: auto;
}

.modal-content {
    background-color: white;
    margin: 50px auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    padding: 20px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.modal-close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.3s;
}

.modal-close:hover {
    opacity: 1;
}

.modal-body {
    padding: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-control:focus {
    border-color: #47866A;
    outline: none;
    box-shadow: 0 0 0 3px rgba(71, 134, 106, 0.1);
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn {
    padding: 10px 25px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-cancel {
    background: #f5f5f5;
    color: #666;
}

.btn-cancel:hover {
    background: #e0e0e0;
}

.btn-primary {
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    font-weight: bold;
}

.btn-primary:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .survey-show-container {
        padding: 15px;
    }
    
    .survey-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .info-grid, .status-grid, .yield-grid, .meta-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item, .status-item, .yield-item, .meta-item {
        flex-direction: column;
        gap: 5px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .info-card {
        padding: 15px;
    }
    
    .modal-content {
        margin: 20px auto;
        width: 95%;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .modal-body {
        padding: 20px;
    }
}
</style>

<script>
// Функции для работы с модальным окном
function openEditSurveyModal() {
    const modal = document.getElementById('editSurveyModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('editSurveyModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Закрытие модального окна при клике вне его
window.addEventListener('click', function(event) {
    const modal = document.getElementById('editSurveyModal');
    if (event.target === modal) {
        closeModal();
    }
});

// Обработка отправки формы редактирования
document.getElementById('editSurveyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const fieldId = formData.get('field_id');
    const surveyId = formData.get('survey_id');
    
    fetch(`/fields/${fieldId}/surveys/${surveyId}/update`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Обследование успешно обновлено');
            location.reload(); // Перезагружаем страницу для отображения изменений
        } else {
            alert('Ошибка: ' + (data.error || 'Не удалось обновить обследование'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка сети при обновлении обследования');
    });
});

// Удаление обследования (остается как было)
document.addEventListener('DOMContentLoaded', function() {
    const deleteBtn = document.querySelector('.btn-delete');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            const surveyId = this.dataset.surveyId;
            const fieldId = this.dataset.fieldId;
            
            if (confirm('Вы уверены, что хотите удалить это обследование?')) {
                fetch(`/fields/${fieldId}/surveys/${surveyId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Обследование удалено');
                        window.location.href = `/fields/${fieldId}`;
                    } else {
                        alert('Ошибка: ' + (data.error || 'Не удалось удалить обследование'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ошибка сети');
                });
            }
        });
    }
});
</script>
@endsection
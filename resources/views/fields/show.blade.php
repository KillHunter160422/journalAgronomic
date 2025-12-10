@extends('sample.main')

@section('content')
<div class="field-show-container">
    <div class="field-header">
        <h1>{{ $field->field_name }}</h1>
    
            @if(php_auth_check() && $field->user_id == php_session('user_id'))
            <div class="field-actions">
                <form action="{{ route('fields.toggle-privacy', $field->field_id) }}" method="POST" class="privacy-form">
                    @csrf
                    <button type="submit" class="btn-toggle-privacy {{ $field->is_public ? 'public' : 'private' }}">
                        {{ $field->is_public ? '🔓 Сделать приватным' : '🔒 Сделать публичным' }}
                    </button>
                </form>
            </div>
            @endif
    </div>
    <!-- Статус приватности -->
    <div class="privacy-badge {{ $field->is_public ? 'public' : 'private' }}">
        {{ $field->is_public ? 'Публичное поле' : 'Приватное поле' }}
    </div>

    <div class="field-info-card">
        <h2>Основная информация</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Площадь:</span>
                <span class="value">{{ number_format($field->field_area, 2) }} га</span>
            </div>
            <div class="info-item">
                <span class="label">Автор:</span>
                <span class="value">
                    @if($owner)
                        {{ $owner->username }}
                    @else
                        ID {{ $field->user_id }}
                    @endif
                </span>
            </div>
            <div class="info-item">
                <span class="label">Дата создания:</span>
                <span class="value">{{ $field->created_at->format('d.m.Y H:i') }}</span>
            </div>
            <div class="info-item">
                <span class="label">Последняя активность:</span>
                <span class="value">
                    {{ date('d.m.Y H:i', strtotime($lastActivity['date'])) }}
                    <br>
                    <small class="activity-hint">
                        @switch($lastActivity['type'])
                            @case('operation')
                                📝 Операция: {{ $lastActivity['details'] ?? 'действие' }}
                                @break
                            @case('survey')
                                🔍 Обследование от {{ $lastActivity['details'] }}
                                @break
                            @case('field_operation')
                                🔗 Изменение связи поле-операция
                                @break
                            @case('field')
                                📋 Изменение информации о поле
                                @break
                            @default
                                📅 Действие на поле
                        @endswitch
                    </small>
                </span>
            </div>
        </div>
        
        @if(php_auth_check() && $field->user_id == php_session('user_id'))
        <div class="edit-field-section">
            <button class="btn-edit-field" data-field-id="{{ $field->field_id }}">
                ✏️ Редактировать информацию о поле
            </button>
        </div>
        @endif
    </div>

    <!-- Операции на поле -->
    <div class="field-info-card">
        <div class="section-header">
            <h2>Операции на поле</h2>
            @if(php_auth_check() && $field->user_id == php_session('user_id'))
                <a href="{{ route('operations.create', $field->field_id) }}" class="btn-add-small">
                    ➕ Добавить операцию
                </a>
            @endif
        </div>
        
        @if($operations->count() > 0)
        <div class="operations-list">
            <table class="operations-table">
                <thead>
                    <tr>
                        <th>Тип операции</th>
                        <th>Дата</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($operations as $operation)
                    <tr>
                        <td>
                            <a href="{{ route('operations.show', ['field' => $field->field_id, 'operation' => $operation->operation_id]) }}" 
                               class="operation-link">
                                {{ $operation->operation_type ?? 'Не указано' }}
                            </a>
                        </td>
                        <td>{{ date('d.m.Y', strtotime($operation->operation_date)) }}</td>
                        <td>
                            <span class="status-badge completed">
                                {{ $operation->status ?? 'Выполнено' }}
                            </span>
                        </td>
                        <td class="operation-actions">
                            @if(php_auth_check() && $field->user_id == php_session('user_id'))
                            <a href="{{ route('operations.edit', ['field' => $field->field_id, 'operation' => $operation->operation_id]) }}" 
                               class="btn-edit-small">✏️</a>
                            <button class="btn-delete-operation" 
                                    data-operation-id="{{ $operation->operation_id }}"
                                    data-field-id="{{ $field->field_id }}">🗑️</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="no-data">Операции на этом поле не найдены.</p>
        @endif
    </div>

    <!-- Агрономические обследования -->
    <div class="field-info-card">
        <div class="section-header">
            <h2>Агрономические обследования</h2>
            @if(php_auth_check() && $field->user_id == php_session('user_id'))
                <a href="{{ route('surveys.create', $field->field_id) }}" class="btn-add-small">
                    ➕ Добавить обследование
                </a>
            @endif
        </div>
        
        @if($surveys->count() > 0)
        <div class="surveys-list">
            @foreach($surveys as $survey)
            <div class="survey-item">
                <div class="survey-header">
                    <h3>Обследование от {{ date('d.m.Y', strtotime($survey->survey_date)) }}</h3>
                    <div class="survey-actions">
                        <a href="{{ route('surveys.show', ['field' => $field->field_id, 'survey' => $survey->survey_id]) }}" 
                           class="btn-view">👁️ Просмотр</a>
                        @if(php_auth_check() && $field->user_id == php_session('user_id'))
                        <button class="btn-delete-survey" 
                                data-survey-id="{{ $survey->survey_id }}"
                                data-field-id="{{ $field->field_id }}">🗑️</button>
                        @endif
                    </div>
                </div>
                
                <div class="survey-summary">
                    @if($survey->pest_level || $survey->disease_level)
                    <div class="survey-status">
                        @if($survey->pest_level)
                        <span class="status-badge {{ strtolower($survey->pest_level) }}">
                            Вредители: {{ $survey->pest_level }}
                        </span>
                        @endif
                        
                        @if($survey->disease_level)
                        <span class="status-badge {{ strtolower($survey->disease_level) }}">
                            Болезни: {{ $survey->disease_level }}
                        </span>
                        @endif
                    </div>
                    @endif
                    
                    @if($survey->yield_per_hectare)
                    <div class="survey-yield">
                        <strong>Урожайность:</strong> {{ $survey->yield_per_hectare }} ц/га
                    </div>
                    @endif
                    
                    @if($survey->condition_notes)
                    <div class="survey-notes">
                        <p>{{ Str::limit($survey->condition_notes, 100) }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="no-data">Агрономические обследования не проводились.</p>
        @endif
    </div>

    <!-- Модальное окно редактирования поля -->
    <div id="editFieldModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Редактировать поле</h2>
                <button class="modal-close">&times;</button>
            </div>
            
            <form id="editFieldForm" method="POST" class="modal-form">
                @csrf
                @method('PUT')
                
                <input type="hidden" name="field_id" value="{{ $field->field_id }}">
                
                <div class="form-group">
                    <label for="edit_field_name">Название поля *</label>
                    <input type="text" id="edit_field_name" name="field_name" 
                           value="{{ $field->field_name }}" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_field_area">Площадь поля (га) *</label>
                    <input type="number" id="edit_field_area" name="field_area" 
                           step="0.01" min="0.1" required 
                           value="{{ $field->field_area }}">
                </div>
                
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="is_public" value="1" 
                            {{ $field->is_public ? 'checked' : '' }}>
                        <span>Публичное поле (видимое для всех)</span>
                    </label>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel">Отмена</button>
                    <button type="submit" class="btn-submit">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Кнопка возврата -->
    <div class="back-button">
        <a href="{{ url()->previous() }}" class="btn-back">← Назад</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Переключение приватности
    const privacyBtn = document.querySelector('.btn-toggle-privacy');
    if (privacyBtn) {
        privacyBtn.addEventListener('click', function() {
            const fieldId = this.dataset.fieldId;
            const isPublic = this.dataset.currentPublic === '1';
            
            fetch(`/fields/${fieldId}/toggle-privacy`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    is_public: isPublic ? 0 : 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем текст кнопки и бейдж
                    const newStatus = !isPublic;
                    this.textContent = newStatus ? 'Сделать приватным' : 'Сделать публичным';
                    this.dataset.currentPublic = newStatus ? '1' : '0';
                    
                    const badge = document.querySelector('.privacy-badge');
                    if (newStatus) {
                        badge.textContent = 'Публичное поле';
                        badge.classList.remove('private');
                        badge.classList.add('public');
                    } else {
                        badge.textContent = 'Приватное поле';
                        badge.classList.remove('public');
                        badge.classList.add('private');
                    }
                    
                    alert('Статус поля обновлен');
                } else {
                    alert('Ошибка: ' + (data.error || 'Не удалось изменить статус'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
    
    // Модальное окно редактирования поля
    const editFieldBtn = document.querySelector('.btn-edit-field');
    const editFieldModal = document.getElementById('editFieldModal');
    const modalClose = document.querySelector('.modal-close');
    const modalCancel = document.querySelector('.modal .btn-cancel');
    const editFieldForm = document.getElementById('editFieldForm');

    if (editFieldBtn) {
        editFieldBtn.addEventListener('click', function() {
            editFieldModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    }

    function closeModal() {
        editFieldModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (modalCancel) modalCancel.addEventListener('click', closeModal);

    // Закрытие при клике вне модального окна
    window.addEventListener('click', function(event) {
        if (event.target === editFieldModal) {
            closeModal();
        }
    });

    // Отправка формы редактирования
    if (editFieldForm) {
        editFieldForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const fieldId = formData.get('field_id');
            
            fetch(`/fields/${fieldId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Информация о поле обновлена');
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.error || 'Не удалось обновить поле'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка сети');
            });
        });
    }
    
    // Удаление операции
    document.querySelectorAll('.btn-delete-operation').forEach(button => {
        button.addEventListener('click', function() {
            const operationId = this.dataset.operationId;
            const fieldId = this.dataset.fieldId;
            
            if (confirm('Удалить эту операцию?')) {
                fetch(`/fields/${fieldId}/operations/${operationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closest('tr').remove();
                        alert('Операция удалена');
                    } else {
                        alert('Ошибка: ' + (data.error || 'Не удалось удалить операцию'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ошибка сети');
                });
            }
        });
    });
    
    // Удаление обследования
    document.querySelectorAll('.btn-delete-survey').forEach(button => {
        button.addEventListener('click', function() {
            const surveyId = this.dataset.surveyId;
            const fieldId = this.dataset.fieldId;
            
            if (confirm('Удалить это обследование?')) {
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
                        this.closest('.survey-item').remove();
                        alert('Обследование удалено');
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
    });
});
</script>

<style>
.field-show-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px 20px;
}

.field-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.field-header h1 {
    color: #47866A;
    margin: 0;
}

.field-actions {
    display: flex;
    gap: 10px;
}

.btn-add-operation {
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
    text-decoration: none;
    font-size: 14px;
}

.btn-add-operation:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
}

.privacy-badge {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 30px;
}

.privacy-badge.public {
    background-color: #e7f7ef;
    color: #2e7d5f;
    border: 1px solid #a5d6c7;
}

.privacy-badge.private {
    background-color: #f5f5f5;
    color: #666;
    border: 1px solid #ddd;
}

.field-info-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.field-info-card h2 {
    color: #333;
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f5f5f5;
}

.info-item .label {
    color: #666;
}

.info-item .value {
    font-weight: bold;
    color: #333;
}

.activity-hint {
    color: #666;
    font-size: 12px;
    font-style: italic;
    display: block;
    margin-top: 3px;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    border-left: 3px solid #47866A;
}

.edit-field-section {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    text-align: center;
}

.btn-edit-field {
    background: #e3f2fd;
    color: #1565c0;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-edit-field:hover {
    background: #bbdefb;
    transform: translateY(-2px);
}

/* Стили для операций */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.btn-add-small {
    background: #47866A;
    color: white;
    padding: 8px 15px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s;
}

.btn-add-small:hover {
    background: #3a7557;
    transform: translateY(-2px);
}

.operations-table {
    width: 100%;
    border-collapse: collapse;
}

.operations-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    border-bottom: 2px solid #e9ecef;
    color: #666;
}

.operations-table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

.operation-link {
    color: #47866A;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s;
}

.operation-link:hover {
    color: #3a7557;
    text-decoration: underline;
}

.operation-actions {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-edit-small {
    background: #e3f2fd;
    color: #1565c0;
    border: none;
    padding: 4px 8px;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    font-size: 12px;
}

.btn-delete-operation, .btn-delete-survey {
    background: #ffebee;
    color: #c62828;
    border: none;
    padding: 4px 8px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.status-badge.completed {
    background: #e7f7ef;
    color: #2e7d5f;
}

.status-badge.низкий {
    background: #e7f7ef;
    color: #2e7d5f;
}

.status-badge.средний {
    background: #fff3e0;
    color: #ef6c00;
}

.status-badge.высокий, .status-badge.очень_высокий {
    background: #ffebee;
    color: #c62828;
}

/* Стили для обследований */
.surveys-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.survey-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid #47866A;
}

.survey-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.survey-header h3 {
    margin: 0;
    font-size: 16px;
    color: #333;
}

.survey-actions {
    display: flex;
    gap: 10px;
}

.btn-view {
    background: #e3f2fd;
    color: #1565c0;
    padding: 5px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 13px;
}

.survey-summary {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.survey-status {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.survey-yield {
    font-size: 14px;
    color: #333;
}

.survey-notes {
    font-size: 13px;
    color: #666;
    line-height: 1.4;
}

.no-data {
    color: #999;
    font-style: italic;
    text-align: center;
    padding: 20px;
}

/* Стили модального окна */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    overflow-y: auto;
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    width: 90%;
    max-width: 600px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
}

.modal-header h2 {
    margin: 0;
    color: #47866A;
    font-size: 20px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #666;
    line-height: 1;
}

.modal-close:hover {
    color: #333;
}

.modal-form {
    padding: 25px;
}

.modal .form-group {
    margin-bottom: 20px;
}

.modal .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}

.modal .form-group input,
.modal .form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
}

.modal-actions {
    display: flex;
    gap: 15px;
    margin-top: 25px;
}

.modal-actions button {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
}

.modal-actions .btn-cancel {
    background: #f8f9fa;
    color: #666;
    border: 1px solid #ddd;
}

.modal-actions .btn-submit {
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    font-weight: bold;
}

.btn-toggle-privacy {
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
}

.btn-toggle-privacy:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
}

.btn-back {
    display: inline-block;
    padding: 10px 20px;
    background: #f8f9fa;
    color: #666;
    text-decoration: none;
    border-radius: 6px;
    border: 1px solid #ddd;
    transition: all 0.3s;
}

.btn-back:hover {
    background: #e9ecef;
    color: #333;
}

.back-button {
    text-align: center;
    margin-top: 30px;
}

@media (max-width: 768px) {
    .field-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .field-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-add-operation, .btn-toggle-privacy {
        width: 100%;
        text-align: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .field-info-card {
        padding: 15px;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .survey-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .survey-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .operations-table {
        display: block;
        overflow-x: auto;
    }
}
</style>
@endsection
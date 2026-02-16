@extends('sample.main')

@section('content')
<div class="field-show-container">
    <!-- Хлебные крошки -->
    <div class="breadcrumbs">
        <a href="{{ route('journal.index') }}">Журнал полей</a> /
        <a href="{{ route('my-fields.show') }}">Мои поля</a> /
        <span>Поле "{{ $field->field_name }}"</span>
    </div>

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

    <!-- Основная информация о поле (отдельный блок) -->
    <div class="field-info-card">
        <h2>📋 Основная информация</h2>
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
                <span class="value">{{ date('d.m.Y H:i', strtotime($field->created_at)) }}</span>
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

    <!-- Информация о культуре (отдельный блок) -->
    @if(isset($crop_info) && !empty($crop_info['crop_name']))
    <div class="field-info-card">
        <h2>🌾 Информация о культуре</h2>
        <div class="crop-info-section">
            <div class="crop-info-item">
                <div class="crop-details">
                    <span class="label">Культура:</span>
                    <span class="value">{{ $crop_info['crop_name'] }}</span>
                </div>
            </div>
            
            @if(!empty($crop_info['variety']))
            <div class="crop-info-item">
                <div class="crop-details">
                    <span class="label">Сорт:</span>
                    <span class="value">{{ $crop_info['variety'] }}</span>
                </div>
            </div>
            @endif
            
            @if(!empty($crop_info['vegetation_period']))
            <div class="crop-info-item">
                <div class="crop-details">
                    <span class="label">Вегетационный период:</span>
                    <span class="value">{{ $crop_info['vegetation_period'] }} дней</span>
                </div>
            </div>
            @endif
            
            @if(!empty($crop_info['season_name']))
            <div class="crop-info-item">
                <div class="crop-details">
                    <span class="label">Сезон:</span>
                    <span class="value">{{ $crop_info['season_name'] }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Операции на поле -->
    <div class="field-info-card">
        <div class="section-header">
            <h2>📋 Операции на поле</h2>
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
                        <th>Погодные условия</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($operations as $operation)
                    <tr>
                        <td>
                            <div class="operation-type">
                                @php
                                    $operationTypes = [
                                        'sowing' => '🌱 Посев',
                                        'fertilization' => '🧪 Внесение удобрений',
                                        'treatment' => '💊 Обработка',
                                        'harvest' => '🌾 Уборка',
                                        'plowing' => '🚜 Вспашка',
                                        'irrigation' => '💧 Полив'
                                    ];
                                    $operationType = $operationTypes[$operation->operation_type] ?? $operation->operation_type;
                                @endphp
                                {{ $operationType }}
                            </div>
                            @if($operation->description)
                            <div class="operation-description">
                                {{ Str::limit($operation->description, 50) }}
                            </div>
                            @endif
                        </td>
                        <td>{{ date('d.m.Y', strtotime($operation->operation_date)) }}</td>
                        <td>
                            @if($operation->weather_conditions)
                            <span class="weather-info">
                                {{ $operation->weather_conditions }}
                            </span>
                            @else
                            <span class="no-info">—</span>
                            @endif
                        </td>
                        <td class="operation-actions">
                            @if(php_auth_check() && $field->user_id == php_session('user_id'))
                                <!-- Редактировать -->
                                <a href="{{ route('operations.edit', ['field' => $field->field_id, 'operation' => $operation->operation_id]) }}" 
                                   class="btn-edit-small" title="Редактировать операцию">
                                    ✏️
                                </a>
                                
                                <!-- Просмотр -->
                                <a href="{{ route('operations.show', ['field' => $field->field_id, 'operation' => $operation->operation_id]) }}" 
                                   class="btn-view-small" title="Просмотр операции">
                                    👁️
                                </a>
                                
                                <!-- Удалить -->
                                <button class="btn-delete-operation" 
                                        data-operation-id="{{ $operation->operation_id }}"
                                        data-field-id="{{ $field->field_id }}"
                                        title="Удалить операцию">🗑️</button>
                            @else
                                <!-- Для неавторизованных только просмотр -->
                                <a href="{{ route('operations.show', ['field' => $field->field_id, 'operation' => $operation->operation_id]) }}" 
                                   class="btn-view-only" title="Просмотр операции">
                                    👁️
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="no-data-message">
            <p>📭 На этом поле еще нет операций</p>
            @if(php_auth_check() && $field->user_id == php_session('user_id'))
            <a href="{{ route('operations.create', $field->field_id) }}" class="btn-add-operation">
                ➕ Добавить первую операцию
            </a>
            @endif
        </div>
        @endif
    </div>

    <!-- Агрономические обследования -->
    <div class="field-info-card">
        <div class="section-header">
            <h2>🔍 Агрономические обследования</h2>
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
                            🐛 Вредители: {{ $survey->pest_level }}
                        </span>
                        @endif
                        
                        @if($survey->disease_level)
                        <span class="status-badge {{ strtolower($survey->disease_level) }}">
                            🦠 Болезни: {{ $survey->disease_level }}
                        </span>
                        @endif
                    </div>
                    @endif
                    
                    @if($survey->yield_per_hectare)
                    <div class="survey-yield">
                        <strong>🌾 Урожайность:</strong> {{ $survey->yield_per_hectare }} ц/га
                    </div>
                    @endif
                    
                    @if($survey->condition_notes)
                    <div class="survey-notes">
                        <p>{{ Str::limit($survey->condition_notes, 150) }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="no-data-message">
            <p>📊 Агрономические обследования не проводились</p>
            @if(php_auth_check() && $field->user_id == php_session('user_id'))
            <a href="{{ route('surveys.create', $field->field_id) }}" class="btn-add-operation">
                ➕ Провести первое обследование
            </a>
            @endif
        </div>
        @endif
    </div>

    <!-- Модальное окно редактирования поля -->
    <div id="editFieldModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>✏️ Редактировать поле</h2>
                <button class="modal-close">&times;</button>
            </div>
            
            <form id="editFieldForm" method="POST" class="modal-form">
                @csrf
                @method('PUT')
                
                <input type="hidden" name="field_id" value="{{ $field->field_id }}">
                
                <div class="form-group">
                    <label for="edit_field_name">Название поля *</label>
                    <input type="text" id="edit_field_name" name="field_name" 
                           value="{{ $field->field_name }}" required
                           placeholder="Введите название поля">
                </div>
                
                <div class="form-group">
                    <label for="edit_field_area">Площадь поля (га) *</label>
                    <input type="number" id="edit_field_area" name="field_area" 
                           step="0.01" min="0.1" max="10000" required 
                           value="{{ $field->field_area }}"
                           placeholder="Например: 50.5">
                </div>
                
                @if($field->polygon)
                <div class="form-group">
                    <label for="edit_field_polygon">Координаты полигона</label>
                    <textarea id="edit_field_polygon" name="polygon" 
                              rows="3" placeholder="Координаты в формате GeoJSON">{{ $field->polygon }}</textarea>
                </div>
                @endif
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="is_public" value="1" 
                            {{ $field->is_public ? 'checked' : '' }}>
                        <span>Публичное поле (видимое для всех пользователей)</span>
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
        <a href="{{ url()->previous() }}" class="btn-back">← Назад к списку полей</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Переключение приватности (AJAX)
    document.querySelectorAll('.privacy-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const fieldId = this.action.split('/').pop();
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сети');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Обновляем бейдж и кнопку
                    const badge = document.querySelector('.privacy-badge');
                    const button = this.querySelector('.btn-toggle-privacy');
                    
                    if (data.is_public) {
                        badge.textContent = 'Публичное поле';
                        badge.className = 'privacy-badge public';
                        button.textContent = '🔓 Сделать приватным';
                        button.classList.remove('private');
                        button.classList.add('public');
                    } else {
                        badge.textContent = 'Приватное поле';
                        badge.className = 'privacy-badge private';
                        button.textContent = '🔒 Сделать публичным';
                        button.classList.remove('public');
                        button.classList.add('private');
                    }
                    
                    // Показываем уведомление
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message || 'Ошибка при изменении статуса', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Если ошибка сети, просто делаем стандартную отправку формы
                console.log('AJAX не сработал, отправляем форму стандартным способом');
                this.submit();
            });
        });
    });
    
    // Управление модальными окнами
    const modals = {
        editField: document.getElementById('editFieldModal')
    };
    
    const closeButtons = document.querySelectorAll('.modal-close, .btn-cancel');
    
    function openModal(modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Закрытие модальных окон
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal);
            }
        });
    });
    
    // Закрытие при клике вне модального окна
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal(event.target);
        }
    });
    
    // Закрытие по ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            for (const modal of Object.values(modals)) {
                if (modal.style.display === 'block') {
                    closeModal(modal);
                }
            }
        }
    });
    
    // Модальное окно редактирования поля
    const editFieldBtn = document.querySelector('.btn-edit-field');
    if (editFieldBtn) {
        editFieldBtn.addEventListener('click', function() {
            openModal(modals.editField);
        });
    }
    
    // Отправка формы редактирования поля (AJAX)
    const editFieldForm = document.getElementById('editFieldForm');
    if (editFieldForm) {
        editFieldForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const fieldId = formData.get('field_id');
            const submitBtn = this.querySelector('.btn-submit');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Сохранение...';
            submitBtn.disabled = true;
            
            fetch(`/fields/${fieldId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-HTTP-Method-Override': 'PUT',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сети');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Обновляем данные на странице
                    const fieldNameElement = document.querySelector('.field-header h1');
                    const fieldAreaElement = document.querySelector('.info-item .value');
                    const privacyBadge = document.querySelector('.privacy-badge');
                    
                    if (fieldNameElement) {
                        fieldNameElement.textContent = data.field.name;
                    }
                    
                    const areaValue = parseFloat(formData.get('field_area'));
                    if (fieldAreaElement && !isNaN(areaValue)) {
                        fieldAreaElement.textContent = areaValue.toFixed(2) + ' га';
                    }
                    
                    const isPublic = formData.get('is_public') === '1';
                    if (privacyBadge) {
                        privacyBadge.textContent = isPublic ? 'Публичное поле' : 'Приватное поле';
                        privacyBadge.className = `privacy-badge ${isPublic ? 'public' : 'private'}`;
                    }
                    
                    closeModal(modals.editField);
                    showNotification(data.message || 'Информация о поле обновлена', 'success');
                    
                    const privacyButton = document.querySelector('.btn-toggle-privacy');
                    if (privacyButton) {
                        privacyButton.textContent = isPublic ? '🔓 Сделать приватным' : '🔒 Сделать публичным';
                        privacyButton.className = `btn-toggle-privacy ${isPublic ? 'public' : 'private'}`;
                    }
                } else {
                    showNotification(data.error || 'Ошибка при обновлении поля', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ошибка сети. Пожалуйста, обновите страницу.', 'error');
                // При ошибке сети закрываем модальное окно
                closeModal(modals.editField);
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Удаление операции (AJAX)
    document.querySelectorAll('.btn-delete-operation').forEach(button => {
        button.addEventListener('click', function() {
            const operationId = this.dataset.operationId;
            const fieldId = this.dataset.fieldId;
            
            if (confirm('Вы уверены, что хотите удалить эту операцию? Это действие нельзя отменить.')) {
                fetch(`/fields/${fieldId}/operations/${operationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ошибка сети');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        this.closest('tr').remove();
                        showNotification('Операция удалена', 'success');
                    } else {
                        showNotification(data.error || 'Не удалось удалить операцию', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Ошибка сети. Пожалуйста, обновите страницу.', 'error');
                });
            }
        });
    });
    
    // Удаление обследования (AJAX)
    document.querySelectorAll('.btn-delete-survey').forEach(button => {
        button.addEventListener('click', function() {
            const surveyId = this.dataset.surveyId;
            const fieldId = this.dataset.fieldId;
            
            if (confirm('Вы уверены, что хотите удалить это обследование? Это действие нельзя отменить.')) {
                fetch(`/fields/${fieldId}/surveys/${surveyId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ошибка сети');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        this.closest('.survey-item').remove();
                        showNotification('Обследование удалено', 'success');
                    } else {
                        showNotification(data.error || 'Не удалось удалить обследование', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Ошибка сети. Пожалуйста, обновите страницу.', 'error');
                });
            }
        });
    });
    
    // Функция для показа уведомлений
    function showNotification(message, type = 'info') {
        // Создаем элемент уведомления
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button class="notification-close">&times;</button>
        `;
        
        // Добавляем на страницу
        document.body.appendChild(notification);
        
        // Анимация появления
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Автоматическое скрытие
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
        
        // Закрытие по кнопке
        notification.querySelector('.notification-close').addEventListener('click', function() {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        });
    }
});
</script>

<style>
.field-show-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Хлебные крошки */
.breadcrumbs {
    margin-bottom: 20px;
    font-size: 14px;
    color: #666;
}

.breadcrumbs a {
    color: #47866A;
    text-decoration: none;
}

.breadcrumbs a:hover {
    text-decoration: underline;
}

.breadcrumbs span {
    color: #333;
    font-weight: 500;
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
    font-size: 28px;
}

.field-actions {
    display: flex;
    gap: 10px;
}

.privacy-badge {
    display: inline-block;
    padding: 6px 15px;
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
    border: 1px solid #eaeaea;
}

.field-info-card h2 {
    color: #333;
    margin-top: 0;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
    font-size: 22px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f5f5f5;
}

.info-item .label {
    color: #666;
    font-weight: 500;
}

.info-item .value {
    font-weight: bold;
    color: #333;
}

.activity-hint {
    color: #666;
    font-size: 12px;
    display: block;
    margin-top: 5px;
    background: #f8f9fa;
    padding: 6px 10px;
    border-radius: 4px;
    border-left: 3px solid #47866A;
}

/* Информация о культуре (отдельный блок) */
.crop-info-section {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.crop-info-item {
    padding: 12px 0;
    border-bottom: 1px solid #f5f5f5;
}

.crop-info-item:last-child {
    border-bottom: none;
}

.crop-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.crop-details .label {
    color: #666;
    font-weight: 500;
}

.crop-details .value {
    font-weight: bold;
    color: #333;
}

.edit-field-section {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    text-align: center;
}

.btn-edit-field {
    background: #e3f2fd;
    color: #1565c0;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 500;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-edit-field:hover {
    background: #bbdefb;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Операции */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.btn-add-small, .btn-add-operation {
    background: #47866A;
    color: white;
    padding: 10px 18px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-add-small:hover, .btn-add-operation:hover {
    background: #3a7557;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.operations-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.operations-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    border-bottom: 2px solid #e9ecef;
    color: #666;
    font-weight: 600;
}

.operations-table td {
    padding: 15px;
    border-bottom: 1px solid #eee;
    vertical-align: top;
}

.operation-type {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.operation-description {
    color: #666;
    font-size: 13px;
    line-height: 1.4;
}

.weather-info {
    color: #47866A;
    font-size: 13px;
    background: #f0f7f4;
    padding: 4px 10px;
    border-radius: 4px;
}

.no-info {
    color: #999;
    font-style: italic;
}

.operation-actions {
    display: flex;
    gap: 8px;
    white-space: nowrap;
}

.btn-view-small, .btn-edit-small, .btn-delete-operation, .btn-view-only {
    background: none;
    border: none;
    padding: 8px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 36px;
    border: 1px solid transparent;
}

/* Кнопки для автора */
.btn-view-small {
    color: #47866A;
    border: 1px solid #e7f7ef;
    background: #f0f7f4;
}

.btn-view-small:hover {
    background: #e7f7ef;
    color: #3a7557;
    border-color: #47866A;
}

.btn-edit-small {
    color: #1565c0;
    border: 1px solid #e3f2fd;
    background: #e3f2fd;
}

.btn-edit-small:hover {
    background: #bbdefb;
    color: #0d47a1;
    border-color: #1565c0;
}

.btn-delete-operation, .btn-delete-survey {
    color: #c62828;
    border: 1px solid #ffebee;
    background: #ffebee;
}

.btn-delete-operation:hover, .btn-delete-survey:hover {
    background: #ffcdd2;
    border-color: #c62828;
}

/* Кнопка "только просмотр" для неавторизованных */
.btn-view-only {
    color: #47866A;
    border: 1px solid #e7f7ef;
    background: #f0f7f4;
    padding: 10px 15px;
    min-height: 40px;
}

.btn-view-only:hover {
    background: #e7f7ef;
    color: #3a7557;
    border-color: #47866A;
    transform: translateY(-2px);
}

/* Обследования */
.surveys-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.survey-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border-left: 4px solid #47866A;
    transition: all 0.3s;
}

.survey-item:hover {
    background: #f0f7f4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.survey-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.survey-header h3 {
    margin: 0;
    font-size: 17px;
    color: #333;
    font-weight: 600;
}

.survey-actions {
    display: flex;
    gap: 10px;
}

.btn-view {
    background: #e3f2fd;
    color: #1565c0;
    padding: 8px 15px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s;
}

.btn-view:hover {
    background: #bbdefb;
}

.survey-summary {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.survey-status {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.status-badge.низкий, .status-badge.completed {
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

.survey-yield {
    font-size: 15px;
    color: #333;
    background: #fff3e0;
    padding: 8px 12px;
    border-radius: 6px;
    border-left: 3px solid #ff9800;
}

.survey-notes {
    font-size: 14px;
    color: #666;
    line-height: 1.5;
    background: white;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #eee;
}

.no-data-message {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.no-data-message p {
    font-size: 16px;
    margin-bottom: 20px;
}

/* Модальные окна */
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
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
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
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #666;
    line-height: 1;
    transition: color 0.3s;
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
    font-weight: 600;
    color: #333;
}

.modal .form-group input,
.modal .form-group textarea,
.modal .form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    transition: all 0.3s;
}

.modal .form-group input:focus,
.modal .form-group textarea:focus,
.modal .form-group select:focus {
    border-color: #47866A;
    outline: none;
    box-shadow: 0 0 0 3px rgba(71, 134, 106, 0.1);
}

.form-select {
    background-color: white;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
    width: auto;
    transform: scale(1.2);
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
    transition: all 0.3s;
}

.modal-actions .btn-cancel {
    background: #f8f9fa;
    color: #666;
    border: 1px solid #ddd;
}

.modal-actions .btn-cancel:hover {
    background: #e9ecef;
}

.modal-actions .btn-submit {
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    font-weight: bold;
}

.modal-actions .btn-submit:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
}

/* Кнопка возврата */
.back-button {
    text-align: center;
    margin-top: 40px;
}

.btn-back {
    display: inline-block;
    padding: 12px 25px;
    background: #f8f9fa;
    color: #666;
    text-decoration: none;
    border-radius: 8px;
    border: 1px solid #ddd;
    transition: all 0.3s;
    font-weight: 500;
}

.btn-back:hover {
    background: #e9ecef;
    color: #333;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Уведомления */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-width: 300px;
    max-width: 400px;
    transform: translateX(400px);
    transition: transform 0.3s ease-out;
    z-index: 10000;
    border-left: 4px solid #47866A;
}

.notification.show {
    transform: translateX(0);
}

.notification.success {
    border-left-color: #4CAF50;
}

.notification.error {
    border-left-color: #f44336;
}

.notification-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #666;
    margin-left: 15px;
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
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-toggle-privacy:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
}

.btn-toggle-privacy.public {
    background: linear-gradient(90deg, #47866A, #5CA08A);
}

.btn-toggle-privacy.private {
    background: linear-gradient(90deg, #666, #888);
}

@media (max-width: 768px) {
    .field-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .field-actions {
        width: 100%;
    }
    
    .btn-toggle-privacy {
        width: 100%;
        justify-content: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .field-info-card {
        padding: 20px;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .survey-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .survey-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .operations-table {
        display: block;
        overflow-x: auto;
    }
    
    .operation-actions {
        flex-direction: column;
        gap: 5px;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
}
</style>
@endsection
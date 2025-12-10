@extends('sample.main')

@section('header-title')
Детали операции
@endsection

@section('content')
<div class="operation-show-container">
    <div class="operation-header">
        <h1>{{ $operation->operation_type ?? 'Операция' }}</h1>
        <div class="operation-meta">
            <span class="operation-date">Дата: {{ date('d.m.Y', strtotime($operation->operation_date)) }}</span>
            <a href="/fields/{{ $field->field_id }}" class="btn-back">← Назад к полю</a>
        </div>
    </div>
    
    <div class="operation-details">
        <!-- Основная информация -->
        <div class="info-card">
            <h2>Основная информация</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Поле:</span>
                    <span class="value">{{ $field->field_name }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Тип операции:</span>
                    <span class="value">{{ $operation->operation_type ?? 'Не указано' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Дата операции:</span>
                    <span class="value">{{ date('d.m.Y', strtotime($operation->operation_date)) }}</span>
                </div>
                @if($operation->weather_conditions)
                <div class="info-item">
                    <span class="label">Погодные условия:</span>
                    <span class="value">{{ $operation->weather_conditions }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Культура и материалы -->
        <div class="info-card">
            <h2>Детали операции</h2>
            <div class="info-grid">
                @if($operation->crop_name)
                <div class="info-item">
                    <span class="label">Культура:</span>
                    <span class="value">
                        {{ $operation->crop_name }}
                        @if($operation->variety)
                            ({{ $operation->variety }})
                        @endif
                    </span>
                </div>
                @endif
                
                @if($operation->season_name)
                <div class="info-item">
                    <span class="label">Сезон:</span>
                    <span class="value">{{ $operation->season_name }}</span>
                </div>
                @endif
                
                @if($operation->applied_materials)
                <div class="info-item">
                    <span class="label">Примененные материалы:</span>
                    <span class="value">{{ $operation->applied_materials }}</span>
                </div>
                @endif
                
                @if($operation->application_rate)
                <div class="info-item">
                    <span class="label">Норма внесения:</span>
                    <span class="value">{{ $operation->application_rate }} кг/га</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Описание -->
        @if($operation->description)
        <div class="info-card">
            <h2>Описание операции</h2>
            <div class="description-content">
                <p>{{ $operation->description }}</p>
            </div>
        </div>
        @endif
        
        <!-- Техническая информация -->
        <div class="info-card">
            <h2>Техническая информация</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">ID операции:</span>
                    <span class="value">#{{ $operation->operation_id }}</span>
                </div>
                <div class="info-item">
                    <span class="label">ID поля:</span>
                    <span class="value">#{{ $field->field_id }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Дата создания записи:</span>
                    <span class="value">{{ date('d.m.Y H:i', strtotime($operation->created_at)) }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Последнее обновление:</span>
                    <span class="value">{{ date('d.m.Y H:i', strtotime($operation->updated_at)) }}</span>
                </div>
            </div>
        </div>
        
        <!-- Кнопки действий -->
        @if(php_auth_check() && $field->user_id == php_session('user_id'))
        <div class="action-buttons">
            <a href="{{ route('operations.edit', ['field' => $field->field_id, 'operation' => $operation->operation_id]) }}" 
               class="btn-edit">
                ✏️ Редактировать операцию
            </a>
            <button class="btn-delete" 
                    data-operation-id="{{ $operation->operation_id }}"
                    data-field-id="{{ $field->field_id }}">
                🗑️ Удалить операцию
            </button>
        </div>
        @endif
    </div>
</div>

<style>
.operation-show-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 30px 20px;
}

.operation-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.operation-header h1 {
    color: #47866A;
    margin: 0 0 10px 0;
    font-size: 28px;
}

.operation-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.operation-date {
    font-size: 16px;
    color: #666;
    font-weight: 500;
    background: #f8f9fa;
    padding: 6px 12px;
    border-radius: 20px;
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

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
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
    min-width: 180px;
}

.info-item .value {
    font-weight: bold;
    color: #333;
    text-align: right;
    flex-grow: 1;
}

.description-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    line-height: 1.6;
    color: #333;
    font-size: 15px;
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

@media (max-width: 768px) {
    .operation-show-container {
        padding: 15px;
    }
    
    .operation-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: column;
        gap: 5px;
    }
    
    .info-item .label {
        min-width: auto;
    }
    
    .info-item .value {
        text-align: left;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .info-card {
        padding: 15px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Удаление операции
    const deleteBtn = document.querySelector('.btn-delete');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            const operationId = this.dataset.operationId;
            const fieldId = this.dataset.fieldId;
            
            if (confirm('Вы уверены, что хотите удалить эту операцию?')) {
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
                        alert('Операция удалена');
                        window.location.href = `/fields/${fieldId}`;
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
    }
});
</script>
@endsection
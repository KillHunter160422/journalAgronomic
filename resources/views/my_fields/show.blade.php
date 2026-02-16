@extends('sample.main')

@section('header-title')
Мои поля
@endsection

@section('content')
<div class="journal-container">
    <div class="journal-header">
        <h1>Мои поля</h1>
        <div class="journal-controls">
            <button class="btn-add-field" onclick="location.href='/fields/add'">
                <span>+</span> Добавить поле
            </button>
            <div class="filter-controls">
                <select class="filter-select" id="filterPrivacy">
                    <option value="all">Все поля</option>
                    <option value="public">Публичные</option>
                    <option value="private">Приватные</option>
                </select>
                <select class="filter-select" id="filterSort">
                    <option value="newest">Сначала новые</option>
                    <option value="oldest">Сначала старые</option>
                    <option value="operations">По количеству операций</option>
                </select>
            </div>
        </div>
    </div>

    <div class="fields-container">
        <?php
        use App\Models\Field;
        
        $currentUserId = php_auth_check() ? php_session('user_id') : null;
        
        try {
            $fields = \App\Models\Field::with('user')
                ->where('user_id', $currentUserId)
                ->orderBy('updated_at', 'desc')
                ->limit(12)
                ->get();
            
            if ($fields->isEmpty()): ?>
                <div class="no-fields">
                    <p>У вас пока нет полей</p>
                    <button class="btn-add-field-large" onclick="location.href='/fields/add'">
                        <span>+</span> Создать первое поле
                    </button>
                </div>
            <?php else: 
                foreach ($fields as $field):
                    $updatedDate = date('d.m.Y', strtotime($field->updated_at));
                    $createdDate = date('d.m.Y', strtotime($field->created_at));
                    
                    $cropInfo = $field->crop_info;
                    $cropName = $cropInfo['crop_name'] ?? null;
                    $variety = $cropInfo['variety'] ?? null;
                    $vegetationPeriod = $cropInfo['vegetation_period'] ?? null;
                    
                    $status = $field->status;
                    $statusClass = getStatusClass($status);
                    $statusText = getStatusText($status);
                    
                    $cropColor = getCropColor($cropName ?? 'default');
        ?>
        
        <div class="field-card" data-field-id="<?php echo $field->field_id; ?>">
            <div class="field-header" style="background: <?php echo $cropColor; ?>;" onclick="location.href='/fields/<?php echo $field->field_id; ?>'">
                <div class="field-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="field-user-info">
                    <h3>Моё поле</h3>
                    <p class="field-name"><?php echo htmlspecialchars($field->field_name ?? 'Без названия'); ?></p>
                </div>
                <div class="field-actions">
                    <button class="btn-edit-field" onclick="openEditModal(<?php echo $field->field_id; ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>
            
            <div class="privacy-badge" onclick="location.href='/fields/<?php echo $field->field_id; ?>'">
                <?php if ($field->is_public): ?>
                    <span class="public-badge" title="Публичное поле">
                        🌍 Публичное
                    </span>
                <?php else: ?>
                    <span class="private-badge" title="Приватное поле">
                        🔒 Приватное
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="field-body" onclick="location.href='/fields/<?php echo $field->field_id; ?>'">
                <div class="crop-info">
                    <?php if ($cropName): ?>
                    <div class="info-item">
                        <span class="label">Культура:</span>
                        <span class="value"><?php echo htmlspecialchars($cropName); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($variety): ?>
                    <div class="info-item">
                        <span class="label">Сорт:</span>
                        <span class="value"><?php echo htmlspecialchars($variety); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($vegetationPeriod): ?>
                    <div class="info-item">
                        <span class="label">Вегетационный период:</span>
                        <span class="value"><?php echo htmlspecialchars($vegetationPeriod); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="field-stats">
                    <div class="stat-item">
                        <span class="stat-icon">📏</span>
                        <div>
                            <span class="stat-value"><?php echo $field->field_area ?? 0; ?> га</span>
                            <span class="stat-label">Площадь</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-icon">⚙️</span>
                        <div>
                            <span class="stat-value"><?php echo $field->operations_count ?? 0; ?></span>
                            <span class="stat-label">Операций</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-icon">📊</span>
                        <div>
                            <span class="stat-value"><?php echo $field->surveys_count ?? 0; ?></span>
                            <span class="stat-label">Обследований</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="field-footer" onclick="location.href='/fields/<?php echo $field->field_id; ?>'">
                <div class="dates">
                    <span class="date-label">Создано: <?php echo $createdDate; ?></span>
                    <span class="date-label">Обновлено: <?php echo $updatedDate; ?></span>
                </div>
                <div class="field-status">
                    <span class="status-badge <?php echo $statusClass; ?>">
                        <?php echo $statusText; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <?php 
                endforeach;
            endif;
            
        } catch (\Exception $e) { ?>
            <div class="error-message">
                <p>Ошибка загрузки данных. Пожалуйста, попробуйте позже.</p>
                <p style="font-size: 12px; color: #999;"><?php echo htmlspecialchars($e->getMessage()); ?></p>
            </div>
        <?php } ?>
    </div>
    
    <?php if (isset($fields) && count($fields) >= 12): ?>
    <div class="load-more-container">
        <button class="load-more-btn" onclick="loadMoreFields()">
            Показать еще
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Модальное окно редактирования поля -->
<div id="editFieldModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Редактировать поле</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editFieldForm" method="POST">
                @csrf
                <input type="hidden" name="field_id" id="editFieldId">
                
                <div class="form-group">
                    <label for="editFieldName">Название поля *</label>
                    <input type="text" id="editFieldName" name="field_name" required>
                </div>
                
                <div class="form-group">
                    <label for="editFieldArea">Площадь (га) *</label>
                    <input type="number" id="editFieldArea" name="field_area" step="0.01" min="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="editCropName">Культура</label>
                    <select id="editCropName" name="crop_name">
                        <option value="">Выберите культуру</option>
                        <option value="Пшеница">Пшеница</option>
                        <option value="Ячмень">Ячмень</option>
                        <option value="Кукуруза">Кукуруза</option>
                        <option value="Подсолнечник">Подсолнечник</option>
                        <option value="Рапс">Рапс</option>
                        <option value="Соя">Соя</option>
                        <option value="Картофель">Картофель</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editVariety">Сорт</label>
                    <input type="text" id="editVariety" name="variety">
                </div>
                
                <div class="form-group">
                    <label for="editVegetationPeriod">Вегетационный период</label>
                    <select id="editVegetationPeriod" name="vegetation_period">
                        <option value="">Выберите период</option>
                        <option value="Яровой">Яровой</option>
                        <option value="Озимый">Озимый</option>
                        <option value="Многолетний">Многолетний</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editStatus">Статус</label>
                    <select id="editStatus" name="status">
                        <option value="active">Активное</option>
                        <option value="planned">Запланировано</option>
                        <option value="completed">Завершено</option>
                        <option value="problem">Проблемное</option>
                    </select>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="editIsPublic" name="is_public">
                    <label for="editIsPublic">Публичное поле (видно другим пользователям)</label>
                </div>
                
                <div class="form-group">
                    <label for="editDescription">Описание поля</label>
                    <textarea id="editDescription" name="description" rows="3"></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Отмена</button>
                    <button type="submit" class="btn-primary">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Основные стили */
    .journal-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        min-height: calc(100vh - 120px);
    }
    
    .journal-header {
        margin-bottom: 30px;
        text-align: center;
    }
    
    .journal-header h1 {
        color: #47866A;
        font-size: 28px;
        margin-bottom: 20px;
        font-weight: 600;
    }
    
    .journal-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .btn-add-field {
        background: linear-gradient(90deg, #47866A, #5CA08A);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        font-size: 14px;
    }
    
    .btn-add-field:hover {
        background: linear-gradient(90deg, #3a7557, #4A8C74);
        transform: translateY(-2px);
    }
    
    .btn-add-field span {
        font-size: 18px;
    }
    
    .filter-controls {
        display: flex;
        gap: 10px;
    }
    
    .filter-select {
        padding: 8px 15px;
        border: 2px solid #ddd;
        border-radius: 6px;
        background: white;
        color: #333;
        cursor: pointer;
        font-size: 14px;
        transition: border-color 0.3s;
    }
    
    .filter-select:focus {
        border-color: #47866A;
        outline: none;
    }
    
    /* Сетка полей */
    .fields-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }
    
    @media (max-width: 1366px) {
        .fields-container {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .journal-container {
            padding: 15px;
        }
    }
    
    @media (max-width: 768px) {
        .fields-container {
            grid-template-columns: 1fr;
        }
        
        .journal-controls {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-controls {
            flex-direction: column;
        }
    }
    
    /* Карточка поля */
    .field-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
        height: 100%;
    }
    
    .field-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .field-header {
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: white;
        position: relative;
        min-height: 90px;
    }
    
    .field-avatar {
        width: 50px;
        height: 50px;
        flex-shrink: 0;
    }
    
    .field-avatar i {
        font-size: 35px;
        color: rgba(255,255,255,0.9);
    }
    
    .field-user-info {
        flex-grow: 1;
        min-width: 0;
    }
    
    .field-user-info h3 {
        margin: 0 0 5px 0;
        font-size: 16px;
        text-align: left;
        opacity: 0.9;
    }
    
    .field-name {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
        text-align: left;
        line-height: 1.3;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .field-actions {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 10;
    }
    
    .btn-edit-field {
        background: rgba(255, 255, 255, 0.3);
        border: 2px solid white;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
    }
    
    .btn-edit-field:hover {
        background: rgba(255, 255, 255, 0.5);
        transform: scale(1.1);
    }
    
    .privacy-badge {
        padding: 8px 15px;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        background: #fafafa;
        cursor: pointer;
    }
    
    .public-badge, .private-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }
    
    .public-badge {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .private-badge {
        background: #f8d7da;
        color: #721c24;
    }
    
    .field-body {
        padding: 15px;
        flex-grow: 1;
        cursor: pointer;
    }
    
    .crop-info {
        margin-bottom: 15px;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
        padding-bottom: 6px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .label {
        color: #666;
        font-size: 13px;
    }
    
    .value {
        font-weight: 500;
        color: #333;
        font-size: 13px;
        text-align: right;
        max-width: 60%;
        word-break: break-word;
    }
    
    .field-stats {
        display: flex;
        gap: 10px;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-top: 10px;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }
    
    .stat-icon {
        font-size: 20px;
        flex-shrink: 0;
    }
    
    .stat-value {
        display: block;
        font-weight: bold;
        font-size: 14px;
        color: #47866A;
    }
    
    .stat-label {
        display: block;
        font-size: 11px;
        color: #666;
    }
    
    .field-footer {
        padding: 12px 15px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fafafa;
        cursor: pointer;
    }
    
    .dates {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .date-label {
        font-size: 10px;
        color: #888;
    }
    
    .status-badge {
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: bold;
    }
    
    .status-badge.active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-badge.completed {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-badge.planned {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-badge.problem {
        background: #f8d7da;
        color: #721c24;
    }
    
    /* Пустые поля */
    .no-fields {
        text-align: center;
        padding: 50px 20px;
        grid-column: 1 / -1;
    }
    
    .no-fields p {
        font-size: 18px;
        color: #666;
        margin-bottom: 20px;
    }
    
    .btn-add-field-large {
        background: linear-gradient(90deg, #47866A, #5CA08A);
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 auto;
        transition: all 0.3s ease;
    }
    
    .btn-add-field-large:hover {
        background: linear-gradient(90deg, #3a7557, #4A8C74);
        transform: translateY(-2px);
    }
    
    .btn-add-field-large span {
        font-size: 24px;
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
        background-color: rgba(0,0,0,0.5);
        overflow-y: auto;
    }
    
    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 0;
        border-radius: 15px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        position: relative;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #47866A, #5CA08A);
        color: white;
        padding: 20px;
        border-radius: 15px 15px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h2 {
        margin: 0;
        font-size: 22px;
        font-weight: 600;
    }
    
    .close {
        font-size: 28px;
        cursor: pointer;
        color: white;
        opacity: 0.8;
        transition: opacity 0.3s;
    }
    
    .close:hover {
        opacity: 1;
    }
    
    .modal-body {
        padding: 25px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
        font-size: 14px;
    }
    
    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s;
        box-sizing: border-box;
    }
    
    .form-group input[type="text"]:focus,
    .form-group input[type="number"]:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #47866A;
        outline: none;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin: 0;
    }
    
    .checkbox-group label {
        margin: 0;
        cursor: pointer;
    }
    
    .modal-footer {
        padding: 20px 25px 25px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .btn-primary {
        background: linear-gradient(90deg, #47866A, #5CA08A);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s;
    }
    
    .btn-primary:hover {
        background: linear-gradient(90deg, #3a7557, #4A8C74);
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
    }
    
    /* Кнопка "Показать еще" */
    .load-more-container {
        text-align: center;
        margin-top: 30px;
    }
    
    .load-more-btn {
        background: #6c757d;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.3s;
    }
    
    .load-more-btn:hover {
        background: #5a6268;
    }
    
    /* Сообщение об ошибке */
    .error-message {
        text-align: center;
        padding: 30px;
        color: #dc3545;
        grid-column: 1 / -1;
        font-size: 16px;
    }
</style>

<script>
    // Функции для модального окна
    function openEditModal(fieldId) {
        // Загружаем данные поля
        fetch(`/fields/${fieldId}/get-data`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const field = data.field;
                    
                    // Заполняем форму
                    document.getElementById('editFieldId').value = field.field_id;
                    document.getElementById('editFieldName').value = field.field_name || '';
                    document.getElementById('editFieldArea').value = field.field_area || '';
                    
                    // Заполняем crop_info
                    if (field.crop_info) {
                        document.getElementById('editCropName').value = field.crop_info.crop_name || '';
                        document.getElementById('editVariety').value = field.crop_info.variety || '';
                        document.getElementById('editVegetationPeriod').value = field.crop_info.vegetation_period || '';
                    }
                    
                    document.getElementById('editStatus').value = field.status || 'active';
                    document.getElementById('editIsPublic').checked = field.is_public || false;
                    document.getElementById('editDescription').value = field.description || '';
                    
                    // Показываем модальное окно
                    document.getElementById('editFieldModal').style.display = 'block';
                } else {
                    alert('Ошибка загрузки данных поля');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при загрузке данных');
            });
    }
    
    function closeModal() {
        document.getElementById('editFieldModal').style.display = 'none';
    }
    
    // Закрытие модального окна при клике вне его
    window.onclick = function(event) {
        const modal = document.getElementById('editFieldModal');
        if (event.target == modal) {
            closeModal();
        }
    }
    
    // Обработка отправки формы
    document.getElementById('editFieldForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const fieldId = document.getElementById('editFieldId').value;
        
        // Показываем индикатор загрузки
        const submitBtn = this.querySelector('.btn-primary');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Сохранение...';
        submitBtn.disabled = true;
        
        fetch(`/fields/${fieldId}/update`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                location.reload();
            } else {
                alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при сохранении');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Фильтрация полей
    function filterFields() {
        const privacyFilter = document.getElementById('filterPrivacy').value;
        const sortFilter = document.getElementById('filterSort').value;
        
        const fieldCards = document.querySelectorAll('.field-card');
        
        // Сначала показываем все карточки
        fieldCards.forEach(card => {
            const isPublic = card.querySelector('.public-badge') !== null;
            
            if (privacyFilter === 'all') {
                card.style.display = '';
            } else if (privacyFilter === 'public' && !isPublic) {
                card.style.display = 'none';
            } else if (privacyFilter === 'private' && isPublic) {
                card.style.display = 'none';
            } else {
                card.style.display = '';
            }
        });
        
        // Сортировка
        const visibleCards = Array.from(fieldCards).filter(card => card.style.display !== 'none');
        
        
    }
    
    document.getElementById('filterPrivacy').addEventListener('change', filterFields);
    document.getElementById('filterSort').addEventListener('change', filterFields);
    
    // Загрузка дополнительных полей
    function loadMoreFields() {
        const btn = document.querySelector('.load-more-btn');
        const currentCount = document.querySelectorAll('.field-card').length;
        
        btn.disabled = true;
        btn.textContent = 'Загрузка...';
        
        fetch(`/my-fields/load-more/${currentCount}`)
            .then(response => response.json())
            .then(data => {
                if (data.fields && data.fields.length > 0) {
                    // Реализация добавления новых полей
                    btn.textContent = 'Показать еще';
                    btn.disabled = false;
                } else {
                    document.querySelector('.load-more-container').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка загрузки данных');
                btn.textContent = 'Показать еще';
                btn.disabled = false;
            });
    }
</script>
@endsection
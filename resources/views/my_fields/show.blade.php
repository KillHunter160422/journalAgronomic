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
                    <option value="surveys">По количеству обследований</option>
                </select>
            </div>
        </div>
    </div>

    <div class="fields-container">
        <?php
        use App\Models\Field;
        
        $currentUserId = php_auth_check() ? php_session('user_id') : null;
        
        try {
            // Получаем ВСЕ поля пользователя (и публичные, и приватные)
            $fields = \App\Models\Field::with(['user', 'surveys'])
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
                    
                    // Получаем количество обследований
                    $surveysCount = $field->surveys_count ?? $field->surveys->count() ?? 0;
                    
                    // Получаем дату последнего обследования
                    $lastSurveyDate = null;
                    if ($field->surveys && $field->surveys->isNotEmpty()) {
                        $lastSurvey = $field->surveys->sortByDesc('survey_date')->first();
                        $lastSurveyDate = date('d.m.Y', strtotime($lastSurvey->survey_date));
                    }
        ?>
        
        <div class="field-card">
            <div class="field-header" style="background: <?php echo $cropColor; ?>;" onclick="location.href='/fields/<?php echo $field->field_id; ?>'">
                <div class="field-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="field-user-info">
                    <h3>Моё поле</h3>
                    <p class="field-name"><?php echo htmlspecialchars($field->field_name ?? 'Без названия'); ?></p>
                </div>
                <div class="field-actions">
                    <button class="btn-edit-field" onclick="openEditModal(event, <?php echo $field->field_id; ?>)">
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
                
                <!-- Бейдж обследований -->
                <?php if ($surveysCount > 0): ?>
                <div class="survey-badge" title="Количество обследований">
                    <i class="fas fa-clipboard-check"></i>
                    <span><?php echo $surveysCount; ?></span>
                </div>
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
                            <span class="stat-value"><?php echo $surveysCount; ?></span>
                            <span class="stat-label">Обследований</span>
                        </div>
                    </div>
                </div>
                
                <!-- Последнее обследование -->
                <?php if ($lastSurveyDate): ?>
                <div class="last-survey-info">
                    <div class="survey-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="survey-details">
                        <div class="survey-title">Последнее обследование</div>
                        <div class="survey-date"><?php echo $lastSurveyDate; ?></div>
                    </div>
                    <button class="btn-add-survey" 
                            onclick="event.stopPropagation(); location.href='/fields/<?php echo $field->field_id; ?>/surveys/add'">
                        <i class="fas fa-plus"></i> Добавить
                    </button>
                </div>
                <?php else: ?>
                <div class="no-surveys">
                    <div class="no-survey-icon">
                        <i class="fas fa-clipboard"></i>
                    </div>
                    <div class="no-survey-text">
                        <div class="no-survey-title">Нет обследований</div>
                        <div class="no-survey-subtitle">Начните вести мониторинг поля</div>
                    </div>
                    <button class="btn-add-survey-primary" 
                            onclick="event.stopPropagation(); location.href='/fields/<?php echo $field->field_id; ?>/surveys/add'">
                        <i class="fas fa-plus"></i> Создать
                    </button>
                </div>
                <?php endif; ?>
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

<!-- Модальное окно редактирования -->
<div class="modal" id="editFieldModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-edit me-2"></i>
                    Редактировать поле
                </h4>
                <button type="button" class="btn-close" onclick="closeEditModal()">
                    &times;
                </button>
            </div>
            
            <form id="editFieldForm" method="POST" class="edit-field-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="field_id" id="editFieldId">
                    
                    <div class="form-section">
                        <h5 class="section-title">
                            <i class="fas fa-info-circle me-2"></i>
                            Основная информация
                        </h5>
                        
                        <div class="form-group">
                            <label for="editFieldName" class="form-label">
                                <i class="fas fa-tag me-1"></i>
                                Название поля *
                            </label>
                            <input type="text" 
                                   id="editFieldName" 
                                   name="field_name" 
                                   class="form-input" 
                                   placeholder="Введите название поля"
                                   required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="editFieldArea" class="form-label">
                                    <i class="fas fa-ruler-combined me-1"></i>
                                    Площадь (га) *
                                </label>
                                <input type="number" 
                                       id="editFieldArea" 
                                       name="field_area" 
                                       class="form-input" 
                                       step="0.01" 
                                       min="0.01" 
                                       placeholder="0.00"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="editStatus" class="form-label">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Статус
                                </label>
                                <select id="editStatus" name="status" class="form-select">
                                    <option value="active">Активное</option>
                                    <option value="planned">Запланировано</option>
                                    <option value="completed">Завершено</option>
                                    <option value="problem">Проблемное</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h5 class="section-title">
                            <i class="fas fa-seedling me-2"></i>
                            Информация о культуре
                        </h5>
                        
                        <div class="form-group">
                            <label for="editCropName" class="form-label">
                                <i class="fas fa-leaf me-1"></i>
                                Культура
                            </label>
                            <select id="editCropName" name="crop_name" class="form-select">
                                <option value="">Выберите культуру</option>
                                <option value="Пшеница">Пшеница</option>
                                <option value="Ячмень">Ячмень</option>
                                <option value="Кукуруза">Кукуруза</option>
                                <option value="Подсолнечник">Подсолнечник</option>
                                <option value="Рапс">Рапс</option>
                                <option value="Соя">Соя</option>
                                <option value="Картофель">Картофель</option>
                                <option value="Овес">Овес</option>
                                <option value="Рожь">Рожь</option>
                                <option value="Гречиха">Гречиха</option>
                                <option value="Горох">Горох</option>
                                <option value="Фасоль">Фасоль</option>
                                <option value="Лен">Лен</option>
                                <option value="Свекла">Свекла</option>
                                <option value="Морковь">Морковь</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="editVariety" class="form-label">
                                    <i class="fas fa-dna me-1"></i>
                                    Сорт
                                </label>
                                <input type="text" 
                                       id="editVariety" 
                                       name="variety" 
                                       class="form-input" 
                                       placeholder="Название сорта">
                            </div>
                            
                            <div class="form-group">
                                <label for="editVegetationPeriod" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Период вегетации
                                </label>
                                <select id="editVegetationPeriod" name="vegetation_period" class="form-select">
                                    <option value="">Выберите период</option>
                                    <option value="Яровой">Яровой</option>
                                    <option value="Озимый">Озимый</option>
                                    <option value="Многолетний">Многолетний</option>
                                    <option value="Однолетний">Однолетний</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h5 class="section-title">
                            <i class="fas fa-cog me-2"></i>
                            Дополнительные настройки
                        </h5>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" 
                                       id="editIsPublic" 
                                       name="is_public"
                                       class="checkbox-input">
                                <label for="editIsPublic" class="checkbox-label">
                                    <i class="fas fa-globe me-2"></i>
                                    <div class="checkbox-text">
                                        <strong>Публичное поле</strong>
                                        <small>Видно другим пользователям для обмена опытом</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editDescription" class="form-label">
                                <i class="fas fa-align-left me-1"></i>
                                Описание поля
                            </label>
                            <textarea id="editDescription" 
                                      name="description" 
                                      class="form-textarea" 
                                      rows="3"
                                      placeholder="Дополнительная информация о поле..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" 
                            class="btn btn-cancel" 
                            onclick="closeEditModal()">
                        <i class="fas fa-times me-1"></i>
                        Отмена
                    </button>
                    <button type="submit" 
                            class="btn btn-primary"
                            id="submitEditBtn">
                        <i class="fas fa-save me-1"></i>
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Основные стили журнала */
    .journal-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .journal-header {
        margin-bottom: 30px;
    }
    
    .journal-header h1 {
        text-align: center;
        color: #47866A;
        font-size: 28px;
        margin-bottom: 20px;
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
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(71, 134, 106, 0.2);
    }
    
    .btn-add-field:hover {
        background: linear-gradient(90deg, #3a7557, #4A8C74);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(71, 134, 106, 0.3);
    }
    
    .btn-add-field span {
        font-size: 22px;
        font-weight: bold;
    }
    
    .filter-controls {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .filter-select {
        padding: 10px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        color: #333;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        min-width: 180px;
        transition: border-color 0.3s;
    }
    
    .filter-select:focus {
        outline: none;
        border-color: #47866A;
        box-shadow: 0 0 0 3px rgba(71, 134, 106, 0.1);
    }
    
    /* Карточки полей */
    .fields-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }
    
    .field-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        cursor: pointer;
        transition: all 0.3s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
        border: 1px solid #f0f0f0;
    }
    
    .field-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }
    
    .field-header {
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 15px;
        color: white;
        position: relative;
        min-height: 120px;
    }
    
    .field-avatar {
        width: 70px;
        height: 70px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    .field-avatar i {
        font-size: 36px;
        color: rgba(255, 255, 255, 0.95);
    }
    
    .field-user-info {
        flex-grow: 1;
        min-width: 0;
    }
    
    .field-user-info h3 {
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 600;
        opacity: 0.9;
        text-align: left;
    }
    
    .field-name {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        opacity: 0.95;
        text-align: left;
        line-height: 1.3;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }
    
    /* Кнопка редактирования */
    .field-actions {
        position: absolute;
        top: 20px;
        right: 20px;
    }
    
    .btn-edit-field {
        background: rgba(255, 255, 255, 0.25);
        border: 2px solid white;
        color: white;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 18px;
    }
    
    .btn-edit-field:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    /* Privacy badge с обследованиями */
    .privacy-badge {
        padding: 14px 20px;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(to right, #fafafa, #ffffff);
        cursor: pointer;
    }
    
    .privacy-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .public-badge, .private-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .public-badge {
        background: #e3f2fd;
        color: #1565c0;
        border: 1px solid #bbdefb;
    }
    
    .private-badge {
        background: #fce4ec;
        color: #c2185b;
        border: 1px solid #f8bbd9;
    }
    
    /* Бейдж обследований в заголовке */
    .survey-badge {
        background: linear-gradient(135deg, #4CAF50, #2E7D32);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .survey-badge i {
        font-size: 12px;
    }
    
    .field-body {
        padding: 24px;
        flex-grow: 1;
        cursor: pointer;
    }
    
    .crop-info {
        margin-bottom: 24px;
        background: #f8f9fa;
        padding: 18px;
        border-radius: 10px;
        border-left: 4px solid #47866A;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .label {
        color: #6c757d;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .value {
        font-weight: 600;
        color: #343a40;
        font-size: 15px;
        text-align: right;
        max-width: 60%;
    }
    
    /* Статистика */
    .field-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        padding: 18px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        margin-top: 16px;
        margin-bottom: 20px;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .stat-icon {
        font-size: 28px;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #47866A, #5CA08A);
        color: white;
        border-radius: 10px;
    }
    
    .stat-value {
        display: block;
        font-weight: 700;
        font-size: 18px;
        color: #212529;
        margin-bottom: 2px;
    }
    
    .stat-label {
        display: block;
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Блок обследований */
    .last-survey-info {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: linear-gradient(135deg, #E8F5E9, #C8E6C9);
        border-radius: 10px;
        border: 1px solid #C8E6C9;
        margin-top: 20px;
    }
    
    .survey-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #4CAF50, #2E7D32);
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    
    .survey-details {
        flex-grow: 1;
    }
    
    .survey-title {
        font-size: 14px;
        font-weight: 600;
        color: #2E7D32;
        margin-bottom: 4px;
    }
    
    .survey-date {
        font-size: 13px;
        color: #388E3C;
        font-weight: 500;
    }
    
    .btn-add-survey {
        background: white;
        color: #4CAF50;
        border: 2px solid #4CAF50;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }
    
    .btn-add-survey:hover {
        background: #4CAF50;
        color: white;
        transform: translateY(-2px);
    }
    
    /* Блок без обследований */
    .no-surveys {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px;
        background: linear-gradient(135deg, #FFF3E0, #FFECB3);
        border-radius: 10px;
        border: 1px solid #FFE082;
        margin-top: 20px;
    }
    
    .no-survey-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #FF9800, #F57C00);
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    
    .no-survey-text {
        flex-grow: 1;
    }
    
    .no-survey-title {
        font-size: 14px;
        font-weight: 600;
        color: #EF6C00;
        margin-bottom: 4px;
    }
    
    .no-survey-subtitle {
        font-size: 13px;
        color: #F57C00;
    }
    
    .btn-add-survey-primary {
        background: linear-gradient(135deg, #FF9800, #F57C00);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        box-shadow: 0 2px 4px rgba(255, 152, 0, 0.2);
    }
    
    .btn-add-survey-primary:hover {
        background: linear-gradient(135deg, #F57C00, #E65100);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(255, 152, 0, 0.3);
    }
    
    .field-footer {
        padding: 18px 24px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(to right, #fafafa, #ffffff);
        cursor: pointer;
    }
    
    .dates {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    
    .date-label {
        font-size: 12px;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-badge.active {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-badge.completed {
        background: linear-gradient(135deg, #d1ecf1, #bee5eb);
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .status-badge.planned {
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .status-badge.problem {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    /* Модальное окно редактирования */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(3px);
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .modal-dialog {
        width: 100%;
        max-width: 600px;
        animation: slideUp 0.3s ease;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .modal-content {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        border: 1px solid #e0e0e0;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #47866A, #5CA08A);
        color: white;
        padding: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .modal-title {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        display: flex;
        align-items: center;
    }
    
    .btn-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        padding: 0;
    }
    
    .btn-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }
    
    /* Форма редактирования */
    .modal-body {
        padding: 0;
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .edit-field-form {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .form-section {
        padding: 24px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .form-section:last-child {
        border-bottom: none;
    }
    
    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #47866A;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        align-items: center;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #495057;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.3s;
        background: white;
        color: #333;
    }
    
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #47866A;
        box-shadow: 0 0 0 3px rgba(71, 134, 106, 0.1);
    }
    
    .form-textarea {
        resize: vertical;
        min-height: 100px;
        font-family: inherit;
    }
    
    /* Чекбокс */
    .checkbox-group {
        display: flex;
        align-items: flex-start;
        padding: 16px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 2px solid #e9ecef;
        transition: all 0.3s;
    }
    
    .checkbox-group:hover {
        border-color: #47866A;
        background: #f1f8e9;
    }
    
    .checkbox-input {
        margin-right: 12px;
        margin-top: 3px;
        width: 20px;
        height: 20px;
        accent-color: #47866A;
        cursor: pointer;
    }
    
    .checkbox-label {
        flex: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .checkbox-text {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .checkbox-text strong {
        color: #212529;
        font-size: 15px;
    }
    
    .checkbox-text small {
        color: #6c757d;
        font-size: 13px;
    }
    
    /* Кнопки модального окна */
    .modal-footer {
        padding: 24px;
        background: #f8f9fa;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        border-top: 1px solid #e9ecef;
    }
    
    .btn {
        padding: 12px 28px;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 140px;
    }
    
    .btn-cancel {
        background: #f8f9fa;
        color: #6c757d;
        border: 2px solid #dee2e6;
    }
    
    .btn-cancel:hover {
        background: #e9ecef;
        color: #495057;
        border-color: #ced4da;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #47866A, #5CA08A);
        color: white;
        border: 2px solid #47866A;
        box-shadow: 0 4px 6px rgba(71, 134, 106, 0.2);
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #3a7557, #4A8C74);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(71, 134, 106, 0.3);
    }
    
    .btn-primary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }
    
    /* Состояния для пустых полей */
    .no-fields {
        text-align: center;
        padding: 60px 20px;
        grid-column: 1 / -1;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    .no-fields p {
        font-size: 20px;
        color: #6c757d;
        margin-bottom: 30px;
        font-weight: 500;
    }
    
    .btn-add-field-large {
        background: linear-gradient(135deg, #47866A, #5CA08A);
        color: white;
        border: none;
        padding: 18px 36px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 700;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 auto;
        transition: all 0.3s ease;
        box-shadow: 0 6px 15px rgba(71, 134, 106, 0.3);
    }
    
    .btn-add-field-large:hover {
        background: linear-gradient(135deg, #3a7557, #4A8C74);
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(71, 134, 106, 0.4);
    }
    
    .btn-add-field-large span {
        font-size: 28px;
        font-weight: bold;
    }
    
    .error-message {
        text-align: center;
        padding: 40px;
        color: #dc3545;
        grid-column: 1 / -1;
        background: #fff5f5;
        border-radius: 16px;
        border: 2px solid #ffcdd2;
    }
    
    .error-message p {
        font-size: 18px;
        margin-bottom: 10px;
    }
    
    .load-more-container {
        text-align: center;
        margin-top: 40px;
    }
    
    .load-more-btn {
        background: linear-gradient(135deg, #6c757d, #5a6268);
        color: white;
        border: none;
        padding: 14px 32px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .load-more-btn:hover {
        background: linear-gradient(135deg, #5a6268, #495057);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    
    .load-more-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .journal-container {
            padding: 15px;
        }
        
        .journal-controls {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        
        .filter-controls {
            flex-direction: column;
            width: 100%;
        }
        
        .filter-select {
            width: 100%;
        }
        
        .fields-container {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .field-card {
            margin-bottom: 0;
        }
        
        .field-stats {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        
        .privacy-badge {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }
        
        .last-survey-info,
        .no-surveys {
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }
        
        .survey-details,
        .no-survey-text {
            text-align: center;
        }
        
        .btn-add-survey,
        .btn-add-survey-primary {
            width: 100%;
            justify-content: center;
        }
        
        .modal-dialog {
            margin: 0;
            max-width: 95%;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        
        .modal-header,
        .modal-footer,
        .form-section {
            padding: 20px;
        }
        
        .btn {
            min-width: 120px;
            padding: 12px 20px;
        }
        
        .field-header {
            padding: 20px;
        }
        
        .field-body {
            padding: 20px;
        }
        
        .field-footer {
            padding: 16px 20px;
        }
    }
    
    @media (max-width: 480px) {
        .journal-header h1 {
            font-size: 24px;
        }
        
        .btn-add-field,
        .btn-add-field-large {
            width: 100%;
            justify-content: center;
        }
        
        .modal-title {
            font-size: 18px;
        }
        
        .modal-header {
            padding: 20px;
        }
        
        .section-title {
            font-size: 15px;
        }
        
        .btn {
            width: 100%;
        }
        
        .modal-footer {
            flex-direction: column;
        }
        
        .field-name {
            font-size: 18px;
        }
    }
</style>

<script>
// Модальное окно редактирования
const editModal = document.getElementById('editFieldModal');
let isSubmitting = false;

function openEditModal(event, fieldId) {
    event.stopPropagation();
    
    // Показываем загрузку
    const submitBtn = document.getElementById('submitEditBtn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Загрузка...';
    submitBtn.disabled = true;
    
    // Показываем модальное окно
    editModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
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
                
                // Возвращаем кнопку в нормальное состояние
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Сохранить изменения';
                submitBtn.disabled = false;
            } else {
                alert('Ошибка загрузки данных поля');
                closeEditModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при загрузке данных');
            closeEditModal();
        });
}

function closeEditModal() {
    editModal.style.display = 'none';
    document.body.style.overflow = 'auto';
    isSubmitting = false;
    
    // Сбрасываем форму
    document.getElementById('editFieldForm').reset();
    document.getElementById('submitEditBtn').innerHTML = '<i class="fas fa-save me-1"></i> Сохранить изменения';
    document.getElementById('submitEditBtn').disabled = false;
}

// Закрытие модального окна при клике вне его
window.addEventListener('click', function(event) {
    if (event.target === editModal) {
        if (!isSubmitting) {
            closeEditModal();
        }
    }
});

// Обработка отправки формы
document.getElementById('editFieldForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (isSubmitting) return;
    
    isSubmitting = true;
    const submitBtn = document.getElementById('submitEditBtn');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Сохранение...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    const fieldId = document.getElementById('editFieldId').value;
    
    fetch(`/fields/${fieldId}/update`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            submitBtn.innerHTML = '<i class="fas fa-check me-1"></i> Успешно!';
            
            // Через 1 секунду закрываем и перезагружаем
            setTimeout(() => {
                closeEditModal();
                location.reload();
            }, 1000);
        } else {
            alert('Ошибка: ' + data.message);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            isSubmitting = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при сохранении');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        isSubmitting = false;
    });
});

// Валидация формы
document.getElementById('editFieldForm').addEventListener('input', function() {
    const name = document.getElementById('editFieldName').value.trim();
    const area = document.getElementById('editFieldArea').value;
    const submitBtn = document.getElementById('submitEditBtn');
    
    if (name && area && parseFloat(area) > 0) {
        submitBtn.disabled = false;
    } else {
        submitBtn.disabled = true;
    }
});

// Фильтрация полей
function filterFields() {
    const privacyFilter = document.getElementById('filterPrivacy').value;
    const sortFilter = document.getElementById('filterSort').value;
    
    const fieldCards = document.querySelectorAll('.field-card');
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
                // Здесь будет логика добавления новых полей
            } else {
                document.querySelector('.load-more-container').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка загрузки данных');
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Показать еще';
        });
}

// Закрытие модального окна по Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && editModal.style.display === 'flex' && !isSubmitting) {
        closeEditModal();
    }
});
</script>
@endsection
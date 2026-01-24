# Security Guide - Lumixa Manufacturing System (LMS)

## Обзор безопасности

LMS реализует многоуровневую защиту:

1. **Аутентификация** - проверка личности пользователя
2. **Авторизация (RBAC)** - контроль доступа по ролям
3. **Защита данных** - шифрование, prepared statements
4. **Защита сессий** - безопасные cookies, CSRF
5. **Защита файлов** - изоляция приватных директорий

## 1. Аутентификация

### Password Hashing

```php
// Хеширование при создании пользователя
$hash = password_hash($password, PASSWORD_DEFAULT);

// Проверка при входе
password_verify($inputPassword, $storedHash);
```

- Используется bcrypt (PASSWORD_DEFAULT)
- Cost factor: 10 (по умолчанию PHP)
- Автоматическая миграция при смене алгоритма

### Ограничение попыток входа

- Максимум: 5 попыток за 15 минут
- После превышения: блокировка IP на 15 минут
- Логирование всех попыток

### Сессии

```php
// Безопасные настройки сессии
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1'); // только HTTPS
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
```

### Принудительная смена пароля

- При первом входе
- После сброса пароля администратором
- Хранится флаг `must_change_password` в таблице users

## 2. RBAC (Role-Based Access Control)

### Роли

| Роль | Описание |
|------|----------|
| Admin | Полный доступ ко всем функциям |
| Manager | Управление каталогом, производством, отчеты |
| Accountant | Склад, документы, партии |
| Worker | Выполнение задач, Print Queue |
| Viewer | Только просмотр (read-only) |

### Permissions

Формат: `module.action`

```
users.view
users.create
users.edit
users.delete

items.view
items.create
items.edit
items.delete

documents.view
documents.create
documents.post
documents.cancel

backups.view
backups.create
backups.restore
backups.delete
```

### Проверка прав

```php
// В контроллере
$this->authorize('documents.post');

// В middleware
if (!$rbac->can($user, 'documents.post')) {
    throw new ForbiddenException();
}

// В view (для UI элементов)
<?php if ($auth->can('documents.post')): ?>
    <button>Post Document</button>
<?php endif; ?>
```

## 3. CSRF Protection

### Генерация токена

```php
// При старте сессии
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
```

### Включение в формы

```html
<form method="POST">
    <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
    <!-- поля формы -->
</form>
```

### Проверка

```php
// В middleware для всех POST запросов
if ($_POST['_csrf_token'] !== $_SESSION['csrf_token']) {
    throw new CSRFException('Invalid CSRF token');
}
```

## 4. SQL Injection Protection

### Prepared Statements (обязательно!)

```php
// ПРАВИЛЬНО ✅
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// НЕПРАВИЛЬНО ❌
$pdo->query("SELECT * FROM users WHERE email = '$email'");
```

### Escaping для LIKE

```php
$search = str_replace(['%', '_'], ['\\%', '\\_'], $search);
$stmt = $pdo->prepare("SELECT * FROM items WHERE name LIKE ?");
$stmt->execute(["%$search%"]);
```

## 5. XSS Protection

### Output Escaping

```php
// ВСЕГДА экранировать вывод!
<?= htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8') ?>

// Или через helper
<?= e($userInput) ?>
```

### Content-Type Headers

```php
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
```

### CSP Headers (рекомендуется)

```php
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'");
```

## 6. File Upload Security

### Validation

```php
// Проверка типа файла
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    throw new ValidationException('File type not allowed');
}

// Проверка MIME type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($tmpFile);
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
if (!in_array($mimeType, $allowedMimes)) {
    throw new ValidationException('Invalid file type');
}

// Проверка размера
if ($fileSize > 10 * 1024 * 1024) { // 10MB
    throw new ValidationException('File too large');
}
```

### Storage

```php
// Генерация безопасного имени
$safeName = bin2hex(random_bytes(16)) . '.' . $ext;

// Хранение вне web root (лучше)
// Или в public/uploads с организацией по датам
$path = 'uploads/' . date('Y/m/d') . '/' . $safeName;
```

### Доступ

- Никогда не выполнять загруженные файлы как PHP
- Использовать отдельный домен для uploads (идеально)
- Проверять права доступа при скачивании

## 7. Directory Protection

### Nginx

```nginx
# Запрет доступа к приватным директориям
location ~ ^/(app|config|storage|docs|views)/ {
    deny all;
    return 404;
}

# Запрет доступа к скрытым файлам
location ~ /\. {
    deny all;
}

# Запрет доступа к .php вне public
location ~ \.php$ {
    # Только если внутри public/
    if ($request_uri !~ ^/[^/]*\.php$) {
        return 404;
    }
    # ... fastcgi config
}
```

### Apache (.htaccess в корне)

```apache
# Запрет прямого доступа
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(app|config|storage|docs|views)/ - [F,L]
</IfModule>
```

## 8. Debug Mode Security

### Ограничения

```php
// Доступ к debug информации только если:
// 1. APP_DEBUG = true
// 2. Пользователь залогинен
// 3. Пользователь имеет роль Admin

if (!$config['app_debug'] || !$auth->check() || !$auth->user()->hasRole('admin')) {
    // Не показывать debug info
}
```

### Что НЕ показывать в production

- Stack traces
- SQL queries
- Переменные окружения
- Пути к файлам
- Версии софта

## 9. Backup Security

### Ограничение доступа

```php
// Только Admin может:
$this->authorize('backups.create');  // создавать
$this->authorize('backups.restore'); // восстанавливать
$this->authorize('backups.delete');  // удалять
```

### Подтверждение критичных операций

```php
// Restore требует подтверждения
if (!$request->input('confirm_restore')) {
    return $this->view('admin/backups/confirm_restore');
}
```

### Аудит

```php
// Все backup операции логируются
$audit->log('backup.created', ['filename' => $filename]);
$audit->log('backup.restored', ['filename' => $filename, 'confirmed_by' => $user->id]);
```

## 10. Security Headers

### Обязательные

```php
// В bootstrap или middleware
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
```

### Рекомендуемые

```php
header('Strict-Transport-Security: max-age=31536000; includeSubDomains'); // HSTS
header("Content-Security-Policy: default-src 'self'");
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
```

## 11. Audit Trail

### Что логируется

- Все входы/выходы
- Изменения пользователей/ролей
- Проведение документов
- Создание/изменение BOM/Routing
- Резервирование/списание материалов
- Backup/Restore операции
- Изменения настроек

### Формат записи

```php
[
    'action' => 'document.posted',
    'user_id' => 1,
    'entity_type' => 'document',
    'entity_id' => 123,
    'old_values' => ['status' => 'draft'],
    'new_values' => ['status' => 'posted'],
    'ip_address' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...',
    'created_at' => '2024-01-15 10:30:00'
]
```

## 12. Checklist безопасности

### Перед запуском

- [ ] `APP_DEBUG = false` в production
- [ ] Сложные пароли для DB и Admin
- [ ] HTTPS настроен (SSL сертификат)
- [ ] Приватные директории защищены
- [ ] File permissions настроены (640/750)
- [ ] Backup директория не доступна извне
- [ ] Логи не содержат sensitive данных

### Регулярно

- [ ] Обновлять PHP и MySQL
- [ ] Проверять audit log на подозрительную активность
- [ ] Ротировать логи
- [ ] Проверять целостность файлов
- [ ] Делать backup

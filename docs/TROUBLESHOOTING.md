# Troubleshooting Guide - Lumixa Manufacturing System (LMS)

## Быстрая диагностика

### 1. Проверьте Health Endpoint

```bash
curl -I https://lms.lumixa.io/health
```

**Ожидаемый результат:** `HTTP/1.1 200 OK`

**Если не 200:**
- 404 → Проблема с web root или rewrite rules
- 500 → Ошибка PHP (см. логи)
- Connection refused → Web server не запущен

### 2. Откройте Diagnostics Dashboard

URL: `/admin/diagnostics` (требует входа как Admin + APP_DEBUG=true)

Проверьте:
- Все ✅ зеленые → система в порядке
- Есть ❌ красные → следуйте рекомендациям ниже

---

## Типичные проблемы

### Белый экран (White Screen of Death)

**Симптомы:** Страница полностью пустая, никакого контента

**Причины и решения:**

1. **PHP fatal error**
   ```bash
   # Включите отображение ошибок временно
   # В public/index.php добавьте в начало:
   ini_set('display_errors', '1');
   error_reporting(E_ALL);
   ```

2. **Ошибка синтаксиса PHP**
   ```bash
   php -l public/index.php
   # Проверит синтаксис файла
   ```

3. **Права на файлы**
   ```bash
   ls -la public/index.php
   # Должен быть читаемым для www-data
   chmod 644 public/index.php
   ```

4. **Отсутствует PHP extension**
   ```bash
   php -m | grep -E "pdo_mysql|mbstring|json"
   # Все должны быть в списке
   ```

### Ошибка 500 Internal Server Error

**Проверьте логи:**

```bash
# Логи приложения
tail -50 /var/www/lms/storage/logs/app.log

# Логи PHP
tail -50 /var/log/php8.2-fpm.log

# Логи Nginx
tail -50 /var/log/nginx/error.log

# Логи Apache
tail -50 /var/log/apache2/error.log
```

**Типичные причины:**

1. **Нет подключения к БД**
   ```bash
   mysql -u lms_user -p -h localhost lms_db -e "SELECT 1"
   # Должно вернуть 1
   ```

2. **Неправильные credentials в config**
   - Проверьте `config/config.php`
   - Убедитесь, что пароль правильный

3. **Нет прав на storage**
   ```bash
   ls -la /var/www/lms/storage/
   # www-data должен иметь права записи
   chmod -R 775 storage/
   chown -R www-data:www-data storage/
   ```

### Ошибка 404 Not Found

**Для всех страниц:**

1. **Web root неправильный**
   - Nginx/Apache должен указывать на `/public`
   - НЕ на корень проекта

2. **Rewrite rules не работают**

   Apache:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

   Nginx:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

3. **Проверьте .htaccess (Apache)**
   ```bash
   cat public/.htaccess
   # Должен содержать RewriteEngine On
   ```

### Setup Wizard не запускается

**Проверьте:**

1. **Файл config существует**
   ```bash
   ls -la config/config.php
   # Если не существует - скопируйте из config.example.php
   ```

2. **Права на storage**
   ```bash
   mkdir -p storage/logs storage/backups
   chmod -R 775 storage/
   ```

3. **Вручную откройте:**
   ```
   https://lms.lumixa.io/setup
   ```

### Не удается войти (Login не работает)

1. **Проверьте сессии**
   ```bash
   ls -la /var/lib/php/sessions/
   # или
   ls -la /tmp/
   # www-data должен иметь права записи
   ```

2. **Проверьте таблицу users**
   ```sql
   SELECT id, email, is_active FROM users;
   -- is_active должен быть 1
   ```

3. **Сбросьте пароль вручную**
   ```php
   // Создайте временный скрипт
   $hash = password_hash('newpassword', PASSWORD_DEFAULT);
   echo $hash;
   ```
   ```sql
   UPDATE users SET password = 'хеш_сверху' WHERE email = 'admin@example.com';
   ```

### CSRF Token Invalid

**Причины:**

1. **Сессия истекла** → Перелогиньтесь
2. **Открыто несколько вкладок** → Используйте одну вкладку
3. **Сессии не сохраняются** → Проверьте права на session директорию

### Миграции не применяются

**Через Diagnostics:**
1. Admin → Diagnostics
2. Найдите секцию "Migrations"
3. Нажмите "Run Pending Migrations"

**Вручную через MySQL:**
```sql
-- Проверить текущие миграции
SELECT * FROM migrations ORDER BY id;

-- Проверить наличие таблиц
SHOW TABLES;
```

### Backup не создается

1. **Проверьте права**
   ```bash
   ls -la storage/backups/
   chmod 775 storage/backups/
   ```

2. **Проверьте место на диске**
   ```bash
   df -h /var/www/lms/storage/
   ```

3. **Проверьте zip extension**
   ```bash
   php -m | grep zip
   ```

### Restore не работает

1. **Убедитесь в правах Admin**
2. **Проверьте целостность архива**
   ```bash
   unzip -t storage/backups/backup_xxx.zip
   ```

3. **Проверьте логи**
   ```bash
   tail -100 storage/logs/app.log | grep -i restore
   ```

---

## Диагностические команды

### Проверка PHP

```bash
# Версия
php -v

# Модули
php -m

# Конфигурация
php -i | grep -E "memory_limit|max_execution|upload_max"

# Проверка синтаксиса
php -l public/index.php
```

### Проверка MySQL

```bash
# Подключение
mysql -u lms_user -p lms_db

# Статус
mysqladmin -u root -p status

# Проверка таблиц
mysqlcheck -u lms_user -p lms_db
```

### Проверка прав

```bash
# Все файлы
ls -laR /var/www/lms/

# Только storage
ls -laR /var/www/lms/storage/

# Владелец процесса PHP
ps aux | grep php
```

### Проверка сети

```bash
# Локальный health check
curl -v http://localhost/health

# DNS
nslookup lms.lumixa.io

# SSL
openssl s_client -connect lms.lumixa.io:443
```

---

## Request ID для поддержки

При возникновении ошибки система показывает **Error ID** (например: `abc123def456`).

**Для поиска в логах:**

```bash
grep "abc123def456" storage/logs/app.log
```

Это покажет полную информацию об ошибке включая:
- Время
- URL запроса
- Пользователя
- Stack trace

---

## Контакты поддержки

При обращении в поддержку предоставьте:

1. Error ID (если есть)
2. URL страницы где произошла ошибка
3. Действия которые привели к ошибке
4. Версию браузера
5. Скриншот (если применимо)

---

## FAQ

**Q: Как сбросить систему к начальному состоянию?**

A:
```bash
# 1. Удалить таблицы
mysql -u lms_user -p lms_db -e "DROP DATABASE lms_db; CREATE DATABASE lms_db;"

# 2. Удалить .installed flag (если есть)
rm storage/.installed

# 3. Открыть /setup заново
```

**Q: Как включить debug режим?**

A: В `config/config.php`:
```php
'app_debug' => true,
```
⚠️ Не забудьте выключить после отладки!

**Q: Где хранятся загруженные файлы?**

A: `public/uploads/` с организацией по датам (YYYY/MM/DD/)

**Q: Как очистить кэш?**

A:
```bash
rm -rf storage/cache/*
```

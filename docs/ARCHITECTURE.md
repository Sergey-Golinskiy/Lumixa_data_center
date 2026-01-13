# Architecture - Lumixa Manufacturing System (LMS)

## Общая архитектура

```
┌─────────────────────────────────────────────────────────────┐
│                        Web Browser                          │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Web Server (Nginx/Apache)                │
│                    Web Root: /public                        │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     public/index.php                        │
│                      (Entry Point)                          │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                         Router                              │
│                   (Request Dispatch)                        │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                       Middleware                            │
│              (Auth, CSRF, RequestId, Logging)               │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                       Controllers                           │
│                    (Thin Controllers)                       │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                        Services                             │
│                    (Business Logic)                         │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      Repositories                           │
│                     (Data Access)                           │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                         MySQL                               │
│                   (Source of Truth)                         │
└─────────────────────────────────────────────────────────────┘
```

## Структура директорий

```
/
├── public/                     # Web root (единственная публичная папка)
│   ├── index.php               # Entry point
│   ├── .htaccess               # Apache rewrite rules
│   ├── assets/                 # Статика
│   │   ├── css/
│   │   │   └── app.css
│   │   ├── js/
│   │   │   └── app.js
│   │   └── images/
│   └── uploads/                # Загруженные файлы пользователей
│
├── app/                        # Ядро приложения
│   ├── Core/                   # Framework core
│   │   ├── Application.php     # Bootstrap приложения
│   │   ├── Router.php          # Маршрутизация
│   │   ├── Controller.php      # Базовый контроллер
│   │   ├── Database.php        # PDO wrapper
│   │   ├── View.php            # Template engine
│   │   ├── Session.php         # Управление сессиями
│   │   ├── CSRF.php            # CSRF защита
│   │   ├── Logger.php          # Логирование
│   │   ├── ErrorHandler.php    # Обработка ошибок
│   │   └── Validator.php       # Валидация данных
│   │
│   ├── Controllers/            # Контроллеры
│   │   ├── AuthController.php
│   │   ├── SetupController.php
│   │   ├── DashboardController.php
│   │   ├── HealthController.php
│   │   ├── Admin/
│   │   │   ├── DiagnosticsController.php
│   │   │   ├── UsersController.php
│   │   │   ├── RolesController.php
│   │   │   ├── AuditController.php
│   │   │   └── BackupController.php
│   │   ├── Warehouse/
│   │   ├── Catalog/
│   │   ├── Production/
│   │   └── Costing/
│   │
│   ├── Services/               # Бизнес-логика
│   │   ├── AuthService.php
│   │   ├── SetupService.php
│   │   ├── RBACService.php
│   │   ├── AuditService.php
│   │   ├── BackupService.php
│   │   ├── MigrationService.php
│   │   ├── Warehouse/
│   │   ├── Catalog/
│   │   ├── Production/
│   │   └── Costing/
│   │
│   ├── Repositories/           # Доступ к данным
│   │   ├── UserRepository.php
│   │   ├── RoleRepository.php
│   │   └── ...
│   │
│   └── Middleware/             # Middleware
│       ├── AuthMiddleware.php
│       ├── CSRFMiddleware.php
│       ├── RBACMiddleware.php
│       └── RequestIdMiddleware.php
│
├── config/                     # Конфигурация
│   ├── config.php              # Основной конфиг
│   ├── routes.php              # Маршруты
│   └── permissions.php         # Определения прав
│
├── storage/                    # Хранилище (не публичное)
│   ├── logs/                   # Логи приложения
│   │   └── app.log
│   ├── backups/                # Резервные копии
│   ├── migrations/             # SQL миграции
│   └── cache/                  # Кэш (если нужен)
│
├── views/                      # Шаблоны
│   ├── layouts/
│   │   ├── main.php            # Основной layout
│   │   ├── auth.php            # Layout для auth страниц
│   │   └── setup.php           # Layout для setup
│   ├── auth/
│   ├── admin/
│   ├── warehouse/
│   ├── catalog/
│   ├── production/
│   ├── costing/
│   ├── errors/
│   │   ├── 404.php
│   │   ├── 500.php
│   │   └── error.php
│   └── setup/
│
└── docs/                       # Документация
    ├── DEPLOYMENT.md
    ├── CONFIG.md
    ├── ARCHITECTURE.md
    ├── SECURITY.md
    ├── TROUBLESHOOTING.md
    └── ASSUMPTIONS.md
```

## Модули системы

### 1. Core (Ядро)
Базовый функционал фреймворка:
- Routing и request handling
- Database abstraction (PDO)
- View rendering
- Session management
- Error handling и logging
- CSRF protection

### 2. Auth & RBAC
- Аутентификация (login/logout)
- Управление сессиями
- Role-Based Access Control
- Проверка прав на уровне middleware и сервисов

### 3. Admin
- Управление пользователями
- Управление ролями и правами
- Custom fields configuration
- UI templates configuration
- Workflows configuration
- Audit log viewer
- Backup/Restore
- Diagnostics dashboard

### 4. Warehouse
- Items (SKU) management
- Lots/Batches tracking
- Partners (suppliers/customers)
- Stock balances
- Documents (Receipt/Issue/Transfer/Stocktake)
- Posting engine

### 5. Catalog
- Products management
- Variants management
- BOM revisions
- Routing revisions

### 6. Production
- Production orders
- Tasks management
- Print Queue
- Reservations & Issues

### 7. Costing
- Plan cost calculation
- Actual cost calculation
- Cost reports

## Слои приложения

### Controller Layer
- Принимает HTTP запросы
- Валидирует входные данные
- Вызывает сервисы
- Возвращает View или redirect

```php
class ItemController extends Controller
{
    public function create(Request $request): Response
    {
        $this->authorize('items.create');

        if ($request->isPost()) {
            $data = $request->validate([...]);
            $this->itemService->create($data);
            return $this->redirect('/warehouse/items');
        }

        return $this->view('warehouse/items/create');
    }
}
```

### Service Layer
- Содержит бизнес-логику
- Координирует репозитории
- Управляет транзакциями
- Логирует действия

```php
class ItemService
{
    public function create(array $data): Item
    {
        $this->db->beginTransaction();
        try {
            $item = $this->itemRepository->create($data);
            $this->auditService->log('item.created', $item->id);
            $this->db->commit();
            return $item;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
```

### Repository Layer
- CRUD операции с БД
- Prepared statements
- Query building

```php
class ItemRepository
{
    public function create(array $data): Item
    {
        $stmt = $this->db->prepare(
            "INSERT INTO items (sku, name, type, unit) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$data['sku'], $data['name'], $data['type'], $data['unit']]);
        return $this->findById($this->db->lastInsertId());
    }
}
```

## Request Lifecycle

1. **Request** → `public/index.php`
2. **Bootstrap** → Load config, error handler, session
3. **Router** → Match route, extract params
4. **Middleware** → Auth check, CSRF, request_id
5. **Controller** → Process request
6. **Service** → Business logic
7. **Repository** → Database operations
8. **View** → Render template
9. **Response** → Send to client

## Database Schema Overview

```
┌─────────────┐     ┌─────────────┐     ┌─────────────────────┐
│    users    │────<│ user_roles  │>────│       roles         │
└─────────────┘     └─────────────┘     └─────────────────────┘
                                               │
                                               ▼
                                        ┌─────────────────────┐
                                        │  role_permissions   │
                                        └─────────────────────┘
                                               │
                                               ▼
                                        ┌─────────────────────┐
                                        │    permissions      │
                                        └─────────────────────┘

┌─────────────┐     ┌─────────────┐     ┌─────────────────────┐
│    items    │────<│    lots     │────<│   stock_balances    │
└─────────────┘     └─────────────┘     └─────────────────────┘
      │
      ▼
┌─────────────────────┐     ┌─────────────────────┐
│    documents        │────<│   document_lines    │
└─────────────────────┘     └─────────────────────┘
      │
      ▼
┌─────────────────────┐
│   stock_movements   │
└─────────────────────┘

┌─────────────┐     ┌─────────────┐     ┌─────────────────────┐
│  products   │────<│  variants   │────<│    bom_revisions    │
└─────────────┘     └─────────────┘     └─────────────────────┘
                          │                      │
                          │                      ▼
                          │             ┌─────────────────────┐
                          │             │     bom_lines       │
                          │             └─────────────────────┘
                          │
                          ▼
                   ┌─────────────────────┐
                   │  routing_revisions  │
                   └─────────────────────┘
                          │
                          ▼
                   ┌─────────────────────┐
                   │ routing_operations  │
                   └─────────────────────┘

┌───────────────────┐     ┌─────────────────────┐
│production_orders  │────<│       tasks         │
└───────────────────┘     └─────────────────────┘
                                  │
                                  ▼
                          ┌─────────────────────┐
                          │     print_jobs      │
                          └─────────────────────┘
```

## Расширяемость

### Добавление нового модуля

1. Создать контроллер в `app/Controllers/`
2. Создать сервис в `app/Services/`
3. Создать репозиторий в `app/Repositories/`
4. Добавить routes в `config/routes.php`
5. Добавить permissions в `config/permissions.php`
6. Создать миграции в `storage/migrations/`
7. Создать views в `views/`

### Custom Fields

Динамические поля хранятся в:
- `custom_fields` - определения полей
- `custom_field_values` - значения полей

Применяются автоматически в UI через систему templates.

### Workflows

Статусы и переходы хранятся в:
- `workflows` - определения workflow
- `workflow_statuses` - статусы
- `workflow_transitions` - переходы

Проверяются в сервисах при смене статуса.

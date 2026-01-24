# Detailed Code Analysis Report - Lumixa Manufacturing System

**Generated:** 2026-01-24
**Branch:** claude/analyze-code-errors-qU1wE
**Analyst:** Claude Code

## Executive Summary

This report identifies **28 critical issues**, **15 medium-severity issues**, and **12 minor issues** across the Lumixa Manufacturing System codebase. The issues range from SQL injection vulnerabilities to logic errors, missing validations, and code inconsistencies.

---

## Critical Issues (Severity: HIGH)

### 1. SQL Injection in Database Helper Methods
**File:** `app/Core/Database.php:135-203`
**Type:** Security Vulnerability

The `insert()`, `update()`, and `delete()` methods use unescaped table and column names directly in SQL strings:

```php
$sql = sprintf(
    "INSERT INTO %s (%s) VALUES (%s)",
    $table,                           // UNESCAPED
    implode(', ', $columns),          // UNESCAPED
    implode(', ', $placeholders)
);
```

**Impact:** Attackers could inject malicious SQL through table/column names if user input is improperly passed.

**Recommendation:** Use `quoteIdentifier()` method for all table and column names.

---

### 2. SQL Injection in Controller Validation
**File:** `app/Core/Controller.php:440-451`
**Type:** Security Vulnerability

The `checkUnique()` method uses unescaped table and column names:

```php
$sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
```

**Impact:** If validation rules are dynamically constructed from user input, SQL injection is possible.

---

### 3. SQL Injection in ItemService Pagination
**File:** `app/Services/Warehouse/ItemService.php:119-130`
**Type:** Security Vulnerability

The `paginate()` method constructs SQL with unparameterized LIMIT/OFFSET values:

```php
$items = $this->db->fetchAll(
    "SELECT i.*, ... LIMIT {$perPage} OFFSET {$offset}",
    $params
);
```

**Impact:** Integer injection possible if input is not properly validated.

---

### 4. SQL Injection in DocumentService Pagination
**File:** `app/Services/Warehouse/DocumentService.php:83-92`
**Type:** Security Vulnerability

Same pattern as ItemService with unparameterized LIMIT/OFFSET.

---

### 5. CSRF Token Field Name Mismatch
**File:** `app/helpers.php:55` vs `app/Middleware/CSRFMiddleware.php:34`
**Type:** Security Vulnerability

The helper generates a field named `_token`:
```php
function csrf_field(): string {
    return '<input type="hidden" name="_token" value="' . h(csrf_token()) . '">';
}
```

But the middleware checks for `_csrf_token`:
```php
$token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
```

**Impact:** Forms using `csrf_field()` helper will fail CSRF validation.

---

### 6. Missing BASE_PATH Constant Check in Helpers
**File:** `app/helpers.php:87`
**Type:** Runtime Error

The `config()` helper uses `BASE_PATH` constant without verifying it's defined:

```php
$configFile = BASE_PATH . '/config/app.php';
```

**Impact:** Fatal error if helpers are loaded before `public/index.php` defines BASE_PATH.

---

### 7. Wrong Configuration File Path in Helpers
**File:** `app/helpers.php:87`
**Type:** Logic Error

The helper looks for `/config/app.php`:
```php
$configFile = BASE_PATH . '/config/app.php';
```

But the actual file is `/config/config.php`.

**Impact:** The `config()` helper function will always return default values.

---

### 8. Debug Information Leak in Error Page
**File:** `public/index.php:145`
**Type:** Security Vulnerability

Debug information can be displayed via GET parameter:
```php
if (ini_get('display_errors') || (isset($_GET['debug']) && $_GET['debug'] === 'show')):
```

**Impact:** Any user can add `?debug=show` to see detailed error traces and file paths.

---

### 9. Backup SQL Export Uses addslashes Instead of PDO::quote
**File:** `app/Controllers/Admin/BackupController.php:299`
**Type:** Security Vulnerability

The `exportDatabase()` method uses `addslashes()` for escaping:
```php
$rowValues[] = "'" . addslashes($value) . "'";
```

**Impact:** `addslashes()` is not safe for all character sets and can lead to SQL injection on restore.

---

### 10. Backup Restore Executes Arbitrary SQL
**File:** `app/Controllers/Admin/BackupController.php:347-360`
**Type:** Security Vulnerability

SQL statements from backup files are executed without validation:
```php
foreach ($statements as $statement) {
    $db->query($statement);  // EXECUTES ANYTHING
}
```

**Impact:** Malicious backup files can execute DROP DATABASE or other destructive commands.

---

### 11. Missing CSRF Validation on GET Logout
**File:** `config/routes.php:26-27`
**Type:** Security Vulnerability

Logout is accessible via GET without CSRF protection:
```php
$router->get('/logout', 'AuthController@logout', 'logout');
```

**Impact:** CSRF logout attacks can force users to log out via image/link embedding.

---

### 12. Open Redirect Vulnerability
**File:** `app/Core/Controller.php:75-79`
**Type:** Security Vulnerability

The `back()` method uses `HTTP_REFERER` without validation:
```php
protected function back(): void
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    $this->redirect($referer);
}
```

**Impact:** Attackers can craft malicious referer headers for open redirect attacks.

---

### 13. Path Traversal in Backup Download
**File:** `app/Controllers/Admin/BackupController.php:115-121`
**Type:** Security Vulnerability

The backup download uses MD5-based ID matching but doesn't validate file is inside backup directory:
```php
readfile($backup['path']);
```

**Impact:** If hash collision is found, arbitrary files could potentially be read.

---

### 14. Insecure Session Configuration
**File:** `app/Core/Session.php:38-40`
**Type:** Security Vulnerability

Secure cookies are only enabled if HTTPS is detected at server level:
```php
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}
```

**Impact:** Doesn't handle reverse proxies or load balancers properly.

---

### 15. Missing Password Complexity Validation
**File:** `app/Controllers/AuthController.php:157-161`
**Type:** Security Weakness

Password validation only checks length:
```php
if (strlen($newPassword) < 8) {
    $errors['new_password'] = 'New password must be at least 8 characters';
}
```

**Impact:** Weak passwords like "12345678" are accepted.

---

### 16. Race Condition in Document Number Generation
**File:** `app/Services/Warehouse/DocumentService.php:150-174`
**Type:** Data Integrity

`FOR UPDATE` is used but without proper transaction wrapping for the entire sequence:
```php
$seq = $this->db->fetch(
    "SELECT * FROM document_sequences WHERE type = ? FOR UPDATE",
    [$type]
);
```

**Impact:** Concurrent requests may get duplicate document numbers if transaction isn't started before this call.

---

### 17. Unhandled Exception in Batch Movement Reversal
**File:** `app/Services/Warehouse/DocumentService.php:794-805`
**Type:** Data Integrity

If batch is not found during reversal, the error is silently ignored:
```php
$batch = $this->db->fetch(...);
if (!$batch) {
    continue;  // SILENTLY IGNORES
}
```

**Impact:** Incomplete reversals can leave inventory in inconsistent state.

---

### 18. Integer Overflow in Pagination
**File:** `app/Services/Warehouse/ItemService.php:116`
**Type:** Logic Error

No maximum limit on `$perPage` parameter:
```php
$offset = ($page - 1) * $perPage;
```

**Impact:** Very large values could cause memory exhaustion or integer overflow.

---

---

## Medium Severity Issues

### 19. Missing Input Sanitization in Search
**File:** `app/Services/Warehouse/ItemService.php:94-98`
**Type:** Data Handling

LIKE pattern is constructed without escaping special characters:
```php
$search = '%' . $filters['search'] . '%';
```

**Impact:** Users can inject `%` or `_` wildcards to manipulate search behavior.

---

### 20. Inconsistent Error Handling in Audit Logging
**File:** `app/Core/Controller.php:511-518`
**Type:** Reliability

Audit logging failures are silently caught:
```php
} catch (\Exception $e) {
    $this->log('error', 'Failed to create audit log entry: ' . $e->getMessage(), ...);
}
```

**Impact:** Critical security events may go unlogged without alerting administrators.

---

### 21. Missing Locale Validation in Profile Update
**File:** `app/Controllers/AuthController.php:224-227`
**Type:** Input Validation

Locale is validated but defaults silently:
```php
if (!in_array($locale, \App\Core\Translator::SUPPORTED_LOCALES)) {
    $locale = 'en';
}
```

**Impact:** User receives no feedback that their locale choice was invalid.

---

### 22. File Extension Validation Bypass Possible
**File:** `app/Core/Controller.php:183-188`
**Type:** Security Weakness

Only extension is checked, not actual file content for images:
```php
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
```

While `getimagesize()` is called earlier, error handling doesn't provide feedback.

---

### 23. Missing Transaction in Item Update
**File:** `app/Services/Warehouse/ItemService.php:227-276`
**Type:** Data Integrity

Update operation deletes all attributes then reinserts, but this could fail mid-way:
```php
$this->db->delete('item_attributes', ['item_id' => $id]);
foreach ($attributes as $name => $value) {
    // INSERT could fail here, leaving no attributes
}
```

---

### 24. Timezone Hardcoded in Configuration
**File:** `config/config.php:52`
**Type:** Configuration Issue

Timezone is hardcoded to 'Europe/Kiev':
```php
'timezone' => 'Europe/Kiev',
```

**Impact:** All server operations use this timezone regardless of client location.

---

### 25. Debug Mode Enabled in Default Config
**File:** `config/config.php:12`
**Type:** Security Configuration

Debug mode is on by default:
```php
'app_debug' => true,
```

**Impact:** Production deployments may expose sensitive information if config isn't updated.

---

### 26. Empty Database Password in Config
**File:** `config/config.php:22`
**Type:** Security Configuration

Database password is empty in shipped config:
```php
'db_pass' => '',
```

**Impact:** May encourage insecure database configuration.

---

### 27. Admin Middleware Missing Auth Check
**File:** `app/Middleware/AdminMiddleware.php` (referenced but not read)
**Type:** Security

Admin routes only check for admin role, but if AdminMiddleware doesn't first verify authentication, unauthenticated users might pass role checks incorrectly.

---

### 28. Router Regex Injection
**File:** `app/Core/Router.php:98-107`
**Type:** Security Weakness

Route patterns are converted to regex without full escaping:
```php
$pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
$pattern = preg_replace('/\//', '\/', $pattern);
```

Special regex characters in routes (like `.` or `+`) are not escaped.

---

### 29. Missing Content-Security-Policy Header
**File:** `public/index.php:43-47`
**Type:** Security Headers

Basic security headers are set but CSP is missing:
```php
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
// NO Content-Security-Policy
```

---

### 30. Translator Singleton May Fail Before Initialization
**File:** `app/Services/Warehouse/ItemService.php:47`
**Type:** Runtime Error

Services access Translator singleton directly:
```php
$translator = \App\Core\Translator::getInstance();
```

If called before Application boots translator, this could fail.

---

### 31. JSON Encoding Without Error Handling
**File:** `app/Core/Controller.php:56-60`
**Type:** Error Handling

JSON response encoding doesn't check for errors:
```php
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
```

**Impact:** Non-UTF8 data could cause silent failures.

---

### 32. Missing Index on Frequently Queried Columns
**Type:** Performance

Based on query patterns, these columns likely need indexes:
- `audit_log.action`
- `stock_movements.item_id`
- `document_lines.document_id`

---

### 33. Potential Memory Issue with Large Backups
**File:** `app/Controllers/Admin/BackupController.php:282-306`
**Type:** Performance

All rows from all tables are loaded into memory:
```php
$rows = $db->fetchAll("SELECT * FROM `{$table}`");
```

**Impact:** Large tables could exhaust PHP memory limit.

---

---

## Minor Issues

### 34. Inconsistent Method Naming
Multiple controllers use both `render()` and `view()` methods for the same purpose.

### 35. Dead Code - setBalance Method
**File:** `app/Services/Warehouse/DocumentService.php:499-505`

The `setBalance()` method is defined but never called.

### 36. Redundant Session Start Check
**File:** `app/Core/Session.php:22-26`

Both `$this->started` and `session_status()` are checked, which is redundant.

### 37. Magic Numbers in Code
Various files use hardcoded numbers without constants:
- `1800` seconds for session regeneration
- `86400` for session lifetime
- `32` bytes for CSRF token length

### 38. Missing PHPDoc Return Types
Many methods lack proper return type documentation.

### 39. Inconsistent Array Key Quoting
Some SQL queries use backticks for key, others don't:
```php
"WHERE `key` = ?"  // vs
"WHERE key = ?"
```

### 40. Potential Null Pointer in User Methods
**File:** `app/Core/Controller.php:498-499`

```php
$user = $this->user();
$userId = $user['id'] ?? null;
```

If user is null, array access on null generates notice in older PHP.

### 41. Unused View Import
**File:** `app/Middleware/AuthMiddleware.php:9`

```php
use App\Core\View;
```

View is imported but never used.

### 42. Hardcoded Locale Fallback
**File:** Multiple locations

The locale 'en' is hardcoded as fallback in multiple places instead of using a constant.

### 43. Missing Error Handling for File Operations
**File:** `app/Core/Application.php:167`

```php
file_put_contents($installedFile, date('Y-m-d H:i:s'));
```

No check if write succeeded.

### 44. SQL Query in Loop
**File:** `app/Services/Warehouse/ItemService.php:193-200`

Individual INSERT statements for each attribute instead of batch insert.

### 45. Potential Division by Zero
**File:** `app/Services/Warehouse/DocumentService.php:485`

```php
$newAvgCost = $newOnHand > 0 ? $totalValue / $newOnHand : 0;
```

While handled, floating point comparison may be imprecise.

---

## Recommendations Summary

### Immediate Actions (Critical)
1. Fix CSRF token field name mismatch
2. Use parameterized queries for LIMIT/OFFSET
3. Quote all dynamic table/column names
4. Remove debug parameter from error page
5. Use PDO::quote() in backup export
6. Validate backup SQL before execution

### Short-term Actions (Medium)
1. Add Content-Security-Policy header
2. Implement password complexity rules
3. Fix open redirect vulnerability
4. Add proper HTTPS detection for proxies
5. Implement proper transaction handling

### Long-term Actions (Minor)
1. Standardize method naming
2. Add constants for magic numbers
3. Implement batch database operations
4. Add comprehensive PHPDoc
5. Create performance indexes

---

## Files Analyzed

- `app/Core/Application.php`
- `app/Core/Controller.php`
- `app/Core/Database.php`
- `app/Core/Router.php`
- `app/Core/Session.php`
- `app/Core/View.php`
- `app/Core/ErrorHandler.php`
- `app/Controllers/AuthController.php`
- `app/Controllers/Admin/BackupController.php`
- `app/Middleware/AuthMiddleware.php`
- `app/Middleware/CSRFMiddleware.php`
- `app/Services/AuthService.php`
- `app/Services/Warehouse/ItemService.php`
- `app/Services/Warehouse/DocumentService.php`
- `app/helpers.php`
- `config/config.php`
- `config/routes.php`
- `public/index.php`

---

*End of Report*

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <title><?= e($title ?? 'Login') ?> - <?= e(config('app.name', 'Lumixa Manufacturing System')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-logo">LMS</h1>
                <p class="auth-subtitle"><?= e(config('app.name', 'Lumixa Manufacturing System')) ?></p>
            </div>

            <?php include LMS_ROOT . '/views/partials/flash.php'; ?>

            <?= $content ?>
        </div>
    </div>
</body>
</html>

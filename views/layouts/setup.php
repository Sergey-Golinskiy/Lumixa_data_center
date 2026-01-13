<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <title><?= e($title ?? 'Setup') ?> - Lumixa Manufacturing System</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="setup-body">
    <div class="setup-container">
        <div class="setup-card">
            <div class="setup-header">
                <h1 class="setup-logo">LMS</h1>
                <p class="setup-subtitle">Setup Wizard</p>
            </div>

            <?php include LMS_ROOT . '/views/partials/flash.php'; ?>

            <?= $content ?>
        </div>
    </div>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $this->e($title ?? 'Setup') ?> - Lumixa LMS</title>
    <link rel="stylesheet" href="<?= $this->asset('css/app.css') ?>">
</head>
<body class="setup-page">
    <div class="setup-container">
        <div class="setup-card">
            <div class="setup-header">
                <div class="logo">
                    <span class="logo-icon">L</span>
                    <span class="logo-text">Lumixa LMS</span>
                </div>
                <h1>Installation Wizard</h1>
                <p class="subtitle">Follow the steps below to set up your system</p>
            </div>

            <!-- Flash Messages -->
            <?php foreach (['success', 'error', 'warning', 'info'] as $type): ?>
                <?php if ($message = $this->flash($type)): ?>
                <div class="alert alert-<?= $type ?>">
                    <?= $this->e($message) ?>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="setup-body">
                <?= $this->yield('content') ?>
            </div>

            <div class="setup-footer">
                <p>&copy; <?= date('Y') ?> Lumixa Manufacturing System</p>
            </div>
        </div>
    </div>

    <script src="<?= $this->asset('js/app.js') ?>"></script>
</body>
</html>

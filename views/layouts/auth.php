<!DOCTYPE html>
<html lang="<?= $this->e($currentLocale ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $this->e($title ?? 'Login') ?> - <?= $this->e($config['app_name'] ?? 'Lumixa LMS') ?></title>
    <link rel="stylesheet" href="<?= $this->asset('css/app.css') ?>">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">
                    <span class="logo-icon">L</span>
                    <span class="logo-text">Lumixa LMS</span>
                </div>
                <h1><?= $this->e($title ?? 'Login') ?></h1>
            </div>

            <!-- Flash Messages -->
            <?php foreach (['success', 'error', 'warning', 'info'] as $type): ?>
                <?php if ($message = $this->flash($type)): ?>
                <div class="alert alert-<?= $type ?>">
                    <?= $this->e($message) ?>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="auth-body">
                <?= $this->yield('content') ?>
            </div>

            <div class="auth-footer">
                <div class="auth-language-switcher">
                    <?php foreach ($localeNames as $code => $name): ?>
                    <a href="/lang/<?= $code ?>" class="lang-link <?= $currentLocale === $code ? 'active' : '' ?>" title="<?= $this->e($name) ?>">
                        <?= strtoupper($code) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <p>&copy; <?= date('Y') ?> Lumixa. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>

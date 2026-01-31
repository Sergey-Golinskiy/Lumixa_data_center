<!DOCTYPE html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $this->e($title ?? 'Setup') ?> - Lumixa LMS</title>
    <link rel="shortcut icon" href="/assets/velzon/images/favicon.ico">
    <link href="/assets/velzon/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/velzon/css/icons.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/velzon/css/app.min.css" rel="stylesheet" type="text/css">
    <style>
        body { background: #f3f3f9; }
        .setup-container { min-height: 100vh; padding: 40px 20px; }
        .logo-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 12px; display: inline-flex;
            align-items: center; justify-content: center;
            color: #fff; font-size: 24px;
        }
        .check-pass { color: #0ab39c; }
        .check-fail { color: #f06548; }
        .check-warn { color: #f7b84b; }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <span class="logo-icon"><i class="ri-stack-line"></i></span>
                        </div>
                        <h2 class="text-primary">Lumixa LMS</h2>
                        <p class="text-muted">Installation Wizard - Follow the steps below to set up your system</p>
                    </div>

                    <!-- Flash Messages -->
                    <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $type => $alertClass): ?>
                        <?php if ($message = $this->flash($type)): ?>
                        <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show" role="alert">
                            <?= $this->e($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Content -->
                    <?= $this->yield('content') ?>

                    <!-- Footer -->
                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">&copy; <?= date('Y') ?> Lumixa Manufacturing System</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/velzon/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

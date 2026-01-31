<!DOCTYPE html>
<html lang="<?= $this->e($currentLocale ?? 'en') ?>" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $this->e($title ?? 'Login') ?> - <?= $this->e($config['app_name'] ?? 'Lumixa LMS') ?></title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= $this->asset('velzon/images/favicon.ico') ?>">

    <!-- Velzon CSS -->
    <link href="<?= $this->asset('velzon/css/bootstrap.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= $this->asset('velzon/css/icons.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= $this->asset('velzon/css/app.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= $this->asset('velzon/css/custom.min.css') ?>" rel="stylesheet" type="text/css">

    <style>
        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 10px; display: inline-flex;
            align-items: center; justify-content: center;
            color: #fff; font-size: 20px;
        }
        .auth-logo-text { font-size: 22px; font-weight: 600; color: #fff; margin-left: 10px; }
        .auth-lang-dropdown { position: absolute; top: 20px; right: 20px; z-index: 100; }
    </style>
</head>
<body>

    <div class="auth-page-wrapper pt-5">
        <!-- auth page bg -->
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>
            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <!-- Language Dropdown -->
        <div class="auth-lang-dropdown">
            <div class="dropdown">
                <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php
                    $flagMap = ['en' => 'us', 'uk' => 'russia', 'ru' => 'russia', 'de' => 'germany', 'pl' => 'spain'];
                    $flagFile = $flagMap[$currentLocale ?? 'en'] ?? 'us';
                    ?>
                    <img src="<?= $this->asset("velzon/images/flags/{$flagFile}.svg") ?>" alt="<?= strtoupper($currentLocale ?? 'en') ?>" height="16" class="rounded me-1">
                    <?= strtoupper($currentLocale ?? 'en') ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <?php foreach ($localeNames ?? [] as $code => $name):
                        $flag = $flagMap[$code] ?? 'us';
                    ?>
                    <a href="/lang/<?= $code ?>" class="dropdown-item <?= ($currentLocale ?? 'en') === $code ? 'active' : '' ?>">
                        <img src="<?= $this->asset("velzon/images/flags/{$flag}.svg") ?>" alt="<?= $name ?>" class="me-2 rounded" height="16">
                        <?= $this->e($name) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- auth page content -->
        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div>
                                <a href="/" class="d-inline-block auth-logo">
                                    <span class="logo-icon"><i class="ri-stack-line"></i></span>
                                    <span class="auth-logo-text">Lumixa</span>
                                </a>
                            </div>
                            <p class="mt-3 fs-15 fw-medium"><?= $this->__('app_description') ?? 'Logistics Management System' ?></p>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4 card-bg-fill">
                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary"><?= $this->e($title ?? 'Welcome') ?></h5>
                                    <p class="text-muted"><?= $this->__('sign_in_to_continue') ?? 'Sign in to continue to Lumixa.' ?></p>
                                </div>

                                <!-- Flash Messages -->
                                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $type => $alertClass): ?>
                                    <?php if ($message = $this->flash($type)): ?>
                                    <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show mt-3" role="alert">
                                        <?= $this->e($message) ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <div class="p-2 mt-4">
                                    <?= $this->yield('content') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">&copy; <?= date('Y') ?> Lumixa LMS. All rights reserved.</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- JAVASCRIPT -->
    <script src="<?= $this->asset('velzon/libs/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= $this->asset('velzon/libs/simplebar/simplebar.min.js') ?>"></script>
    <script src="<?= $this->asset('velzon/libs/node-waves/waves.min.js') ?>"></script>
    <script src="<?= $this->asset('velzon/libs/feather-icons/feather.min.js') ?>"></script>
    <script src="<?= $this->asset('velzon/js/plugins.js') ?>"></script>

    <script>
    // Password visibility toggle
    document.querySelectorAll('.password-addon').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.closest('.auth-pass-inputgroup').querySelector('.password-input');
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('ri-eye-fill');
                icon.classList.add('ri-eye-off-fill');
            } else {
                input.type = 'password';
                icon.classList.remove('ri-eye-off-fill');
                icon.classList.add('ri-eye-fill');
            }
        });
    });
    </script>
</body>
</html>

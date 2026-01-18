<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Access Denied - Lumixa LMS</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="error-page">
    <div class="error-container">
        <div class="error-code">403</div>
        <h1>Access Denied</h1>
        <p><?= htmlspecialchars($message ?? "You don't have permission to access this page.") ?></p>
        <div class="error-actions">
            <a href="/" class="btn btn-primary">Go to Dashboard</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
        </div>
    </div>
</body>
</html>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($pageTitle ?? 'Public') . ' · ' . config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="public-body">
    <main class="public-wrap">
        <?php if (flash('message')): ?>
            <div class="flash"><?= e(flash('message')) ?></div>
        <?php endif; ?>
        <?= $content ?>
    </main>
</body>
</html>


<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($pageTitle ?? 'App') . ' · ' . config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script defer src="<?= e(asset('js/app.js')) ?>"></script>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">S</div>
            <div>
                <div class="brand-title"><?= e(config('app.name')) ?></div>
                <div class="brand-subtitle">portable by design</div>
            </div>
        </div>

        <?php if (auth_user()): ?>
            <nav class="nav">
                <a href="/dashboard">Dashboard</a>
                <a href="/articles">Статьи</a>
                <a href="/events">События</a>
                <a href="/simulations">Симуляции</a>
            </nav>

            <div class="tenant-card">
                <div class="eyebrow">Организация</div>
                <strong><?= e(current_organization()['name'] ?? 'N/A') ?></strong>
                <div><?= e(current_role() ?? 'member') ?></div>
            </div>

            <form method="post" action="/logout" class="logout-form">
                <?= csrf_field() ?>
                <button class="button button-secondary" type="submit">Выйти</button>
            </form>
        <?php else: ?>
            <nav class="nav">
                <a href="/">Главная</a>
                <a href="/login">Вход</a>
            </nav>
        <?php endif; ?>
    </aside>

    <main class="content">
        <?php if (flash('message')): ?>
            <div class="flash"><?= e(flash('message')) ?></div>
        <?php endif; ?>

        <?php if (errors() !== []): ?>
            <div class="flash flash-error">
                <?= e(implode(' ', array_map(static fn(array $messages): string => $messages[0], errors()))) ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>
</div>
</body>
</html>


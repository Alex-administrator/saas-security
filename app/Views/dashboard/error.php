<section class="center-card">
    <div class="eyebrow">HTTP <?= e((string) ($status ?? 500)) ?></div>
    <h1>Что-то пошло не так</h1>
    <p><?= e($message ?? 'Неизвестная ошибка') ?></p>
    <small>request_id: <?= e(request_id()) ?></small>
</section>


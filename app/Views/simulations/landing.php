<?php $status = (string) ($target['status'] ?? 'pending'); ?>
<section class="center-card training-card">
    <div class="eyebrow">Security awareness training</div>
    <h1>Это была безопасная обучающая симуляция</h1>
    <p><strong><?= e($target['program_title']) ?></strong></p>
    <p><?= e($target['program_description']) ?></p>
    <p>Мы не собираем реальные пароли и чувствительные данные. Здесь фиксируются только безопасные события обучения: открытие, сообщение о подозрении и завершение сценария.</p>

    <?php if ($status === 'pending'): ?>
    <p>Чтобы зафиксировать осознанное открытие сценария, начните обучение кнопкой ниже.</p>
    <div class="button-row">
        <form method="post" action="/simulate/<?= e($target['token']) ?>/open">
            <?= csrf_field() ?>
            <button class="button" type="submit">Открыть сценарий</button>
        </form>
    </div>
    <?php elseif ($status === 'completed'): ?>
    <p>Обучение уже завершено. Спасибо за участие.</p>
    <?php else: ?>
    <div class="button-row">
        <?php if ($status !== 'reported'): ?>
        <form method="post" action="/simulate/<?= e($target['token']) ?>/report">
            <?= csrf_field() ?>
            <button class="button" type="submit">Я бы сообщил об этом письме</button>
        </form>
        <?php endif; ?>
        <?php if ($status !== 'completed'): ?>
        <form method="post" action="/simulate/<?= e($target['token']) ?>/complete">
            <?= csrf_field() ?>
            <button class="button button-secondary" type="submit">Завершить обучение</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</section>

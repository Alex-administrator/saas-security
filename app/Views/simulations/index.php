<section class="page-head">
    <div>
        <div class="eyebrow">Awareness</div>
        <h1>Безопасные симуляции</h1>
    </div>
    <a class="button" href="/simulations/create">Новый сценарий</a>
</section>

<section class="table-card">
    <?php foreach ($simulations as $simulation): ?>
        <article class="list-row list-row-large">
            <div>
                <strong><?= e($simulation['title']) ?></strong>
                <p><?= e($simulation['description']) ?></p>
                <small><?= e($simulation['template_name']) ?> · <?= e($simulation['status']) ?></small>
            </div>
            <div class="actions">
                <?php if ($simulation['status'] === 'draft'): ?>
                    <form method="post" action="/simulations/<?= e((string) $simulation['id']) ?>/launch">
                        <?= csrf_field() ?>
                        <button class="button button-secondary" type="submit">Launch</button>
                    </form>
                <?php else: ?>
                    <a class="button button-secondary" href="/api/v1/simulations/<?= e((string) $simulation['id']) ?>/report">Report</a>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>


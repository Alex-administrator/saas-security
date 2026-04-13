<section class="page-head">
    <div>
        <div class="eyebrow">Workspace</div>
        <h1>Операционный обзор</h1>
        <p>Текущая подписка: <strong><?= e($subscription['plan_name'] ?? 'не назначена') ?></strong></p>
    </div>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <span><?= e((string) $overview['metrics']['articles']) ?></span>
        <small>статей</small>
    </article>
    <article class="stat-card">
        <span><?= e((string) $overview['metrics']['events']) ?></span>
        <small>событий</small>
    </article>
    <article class="stat-card">
        <span><?= e((string) $overview['metrics']['simulations']) ?></span>
        <small>симуляций</small>
    </article>
</section>

<section class="two-column">
    <div class="panel">
        <div class="section-head">
            <h2>Последние статьи</h2>
            <a href="/articles">Открыть</a>
        </div>
        <?php foreach ($overview['recent_articles'] as $article): ?>
            <div class="list-row">
                <div>
                    <strong><?= e($article['title']) ?></strong>
                    <small><?= e($article['status']) ?></small>
                </div>
                <small><?= e($article['author_name']) ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="panel">
        <div class="section-head">
            <h2>Ближайшие события</h2>
            <a href="/events">К календарю</a>
        </div>
        <?php foreach ($overview['upcoming_events'] as $event): ?>
            <div class="list-row">
                <div>
                    <strong><?= e($event['title']) ?></strong>
                    <small><?= e($event['location']) ?></small>
                </div>
                <small><?= e($event['starts_at_utc']) ?> UTC</small>
            </div>
        <?php endforeach; ?>
    </div>
</section>


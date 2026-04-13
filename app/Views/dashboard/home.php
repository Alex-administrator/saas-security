<section class="hero">
    <div>
        <div class="eyebrow">Multi-tenant SaaS</div>
        <h1>Платформа для awareness, defensive tooling и безопасных обучающих симуляций</h1>
        <p>Приложение собрано так, чтобы его можно было перенести на другой сервер без переписывания путей и локальных настроек: весь runtime идет через `.env`, Docker и изолированный `public`-root.</p>
        <a class="button" href="/login">Войти в консоль</a>
    </div>
    <div class="hero-card">
        <div class="metric"><span><?= count($articles) ?></span><small>публичных статей</small></div>
        <div class="metric"><span><?= count($events) ?></span><small>ближайших событий</small></div>
        <div class="metric"><span>100%</span><small>portable config</small></div>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2>Последние статьи</h2>
    </div>
    <div class="card-grid">
        <?php foreach ($articles as $article): ?>
            <article class="card">
                <div class="eyebrow"><?= e($article['organization_name']) ?></div>
                <h3><a href="/blog/<?= e($article['slug']) ?>"><?= e($article['title']) ?></a></h3>
                <p><?= e($article['excerpt']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2>Ближайшие события</h2>
    </div>
    <div class="card-grid">
        <?php foreach ($events as $event): ?>
            <article class="card">
                <div class="eyebrow"><?= e($event['organization_name']) ?></div>
                <h3><?= e($event['title']) ?></h3>
                <p><?= e($event['description']) ?></p>
                <small><?= e($event['starts_at_utc']) ?> UTC</small>
            </article>
        <?php endforeach; ?>
    </div>
</section>


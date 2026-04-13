<section class="page-head">
    <div>
        <div class="eyebrow">Calendar</div>
        <h1>События</h1>
    </div>
    <a class="button" href="/events/create">Добавить событие</a>
</section>

<section class="table-card">
    <?php foreach ($events as $event): ?>
        <article class="list-row list-row-large">
            <div>
                <strong><?= e($event['title']) ?></strong>
                <p><?= e($event['description']) ?></p>
                <small><?= e($event['location']) ?></small>
            </div>
            <div class="datetime-meta">
                <small>start: <?= e($event['starts_at_utc']) ?> UTC</small>
                <small>end: <?= e($event['ends_at_utc']) ?> UTC</small>
            </div>
        </article>
    <?php endforeach; ?>
</section>


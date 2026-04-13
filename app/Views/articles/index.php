<section class="page-head">
    <div>
        <div class="eyebrow">Content</div>
        <h1>Статьи организации</h1>
    </div>
    <a class="button" href="/articles/create">Новая статья</a>
</section>

<section class="table-card">
    <?php foreach ($articles as $article): ?>
        <article class="list-row list-row-large">
            <div>
                <strong><?= e($article['title']) ?></strong>
                <p><?= e($article['excerpt']) ?></p>
                <small><?= e($article['author_name']) ?> · <?= e($article['status']) ?></small>
            </div>
            <div class="actions">
                <?php if ($article['status'] !== 'published'): ?>
                    <form method="post" action="/articles/<?= e((string) $article['id']) ?>/publish">
                        <?= csrf_field() ?>
                        <button class="button button-secondary" type="submit">Publish</button>
                    </form>
                <?php else: ?>
                    <a class="button button-secondary" href="/blog/<?= e($article['slug']) ?>">Open</a>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>


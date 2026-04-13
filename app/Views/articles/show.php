<article class="article-page">
    <div class="eyebrow"><?= e($article['organization_name']) ?></div>
    <h1><?= e($article['title']) ?></h1>
    <p class="article-excerpt"><?= e($article['excerpt']) ?></p>
    <div class="article-body">
        <?= nl2br(e($article['content'])) ?>
    </div>
</article>


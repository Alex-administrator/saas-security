<section class="page-head">
    <div>
        <div class="eyebrow">Editor</div>
        <h1>Новая статья</h1>
    </div>
</section>

<form method="post" action="/articles" class="panel form-grid">
    <?= csrf_field() ?>
    <label>
        <span>Заголовок</span>
        <input type="text" name="title" value="<?= e((string) old('title')) ?>" required>
    </label>
    <label>
        <span>Краткое описание</span>
        <textarea name="excerpt" rows="3" required><?= e((string) old('excerpt')) ?></textarea>
    </label>
    <label>
        <span>Cover image URL</span>
        <input type="url" name="cover_image" value="<?= e((string) old('cover_image')) ?>">
    </label>
    <label>
        <span>Контент</span>
        <textarea name="content" rows="12" required><?= e((string) old('content')) ?></textarea>
    </label>
    <button class="button" type="submit">Сохранить черновик</button>
</form>


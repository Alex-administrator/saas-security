<section class="page-head">
    <div>
        <div class="eyebrow">Planner</div>
        <h1>Новое событие</h1>
    </div>
</section>

<form method="post" action="/events" class="panel form-grid">
    <?= csrf_field() ?>
    <label>
        <span>Название</span>
        <input type="text" name="title" value="<?= e((string) old('title')) ?>" required>
    </label>
    <label>
        <span>Описание</span>
        <textarea name="description" rows="5" required><?= e((string) old('description')) ?></textarea>
    </label>
    <label>
        <span>Локация</span>
        <input type="text" name="location" value="<?= e((string) old('location')) ?>">
    </label>
    <label>
        <span>Начало (UTC)</span>
        <input type="datetime-local" name="starts_at_utc" value="<?= e((string) old('starts_at_utc')) ?>" required>
    </label>
    <label>
        <span>Окончание (UTC)</span>
        <input type="datetime-local" name="ends_at_utc" value="<?= e((string) old('ends_at_utc')) ?>" required>
    </label>
    <button class="button" type="submit">Сохранить событие</button>
</form>

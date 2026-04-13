<section class="page-head">
    <div>
        <div class="eyebrow">Training</div>
        <h1>Новая безопасная симуляция</h1>
        <p>Адресаты допускаются только из доменов, указанных у организации.</p>
    </div>
</section>

<form method="post" action="/simulations" class="panel form-grid">
    <?= csrf_field() ?>
    <label>
        <span>Название</span>
        <input type="text" name="title" value="<?= e((string) old('title')) ?>" required>
    </label>
    <label>
        <span>Описание</span>
        <textarea name="description" rows="4" required><?= e((string) old('description')) ?></textarea>
    </label>
    <label>
        <span>Шаблон</span>
        <select name="template_name">
            <option value="credential_check">Credential check</option>
            <option value="invoice_review">Invoice review</option>
            <option value="document_share">Document share</option>
        </select>
    </label>
    <label>
        <span>Адресаты</span>
        <textarea name="targets" rows="8" placeholder="employee@example.com, Ivan Ivanov" required><?= e((string) old('targets')) ?></textarea>
    </label>
    <button class="button" type="submit">Сохранить сценарий</button>
</form>


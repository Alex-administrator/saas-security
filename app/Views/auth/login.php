<section class="auth-card">
    <div class="eyebrow">Secure access</div>
    <h1>Вход в организационную консоль</h1>
    <p>Для локального запуска после `seed` используйте `admin@example.com / ChangeMe123!`.</p>

    <form method="post" action="/login" class="form-grid">
        <?= csrf_field() ?>
        <label>
            <span>Email</span>
            <input type="email" name="email" value="<?= e((string) old('email')) ?>" required>
        </label>
        <label>
            <span>Пароль</span>
            <input type="password" name="password" required>
        </label>
        <button class="button" type="submit">Войти</button>
    </form>
</section>


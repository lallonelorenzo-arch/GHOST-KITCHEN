<?php
use ViewHelpers as V;
/** @var string|null $errore */
/** @var string $email */
?>
<section class="page-hero compact-hero">
    <h1>Accedi</h1>
    <p>Entra nella tua area personale per gestire prenotazioni, disponibilita e richieste.</p>
</section>

<section class="section auth-section">
    <form class="auth-card" method="post" action="<?= V::e(V::url('/login')) ?>">
        <?php if (!empty($errore)): ?>
            <div class="alert"><?= V::e($errore) ?></div>
        <?php endif; ?>
        <label>Email
            <input name="email" type="email" value="<?= V::e($email ?? '') ?>" required>
        </label>
        <label>Password
            <span class="password-field">
                <input name="password" type="password" required autocomplete="current-password">
                <button type="button" class="password-toggle" data-password-toggle aria-label="Mostra password">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </span>
        </label>
        <button class="btn btn-accent" type="submit">Accedi</button>
        <p class="auth-switch">Non hai un account? <a href="<?= V::e(V::url('/registrazione')) ?>">Registrati</a></p>
    </form>
</section>

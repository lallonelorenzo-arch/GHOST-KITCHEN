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
            <input name="password" type="password" required>
        </label>
        <button class="btn btn-accent" type="submit">Accedi</button>
    </form>
</section>

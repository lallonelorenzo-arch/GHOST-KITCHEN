<?php
use ViewHelpers as V;
/** @var array $certificazioni */
/** @var string|null $messaggioAccesso */
$certificazioni = $certificazioni ?? [];
?>
<section class="page-hero compact-hero uc-page-hero">
    <span class="badge">UC13</span>
    <h1>Validazione certificazioni</h1>
    <p>Approva o rifiuta le certificazioni inviate dagli chef.</p>
</section>

<section class="section uc-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <section class="uc-panel">
        <h2>Certificazioni in attesa</h2>
        <?php if ($certificazioni === []): ?><p class="uc-muted">Nessuna certificazione in attesa.</p><?php endif; ?>
        <div class="uc-list">
            <?php foreach ($certificazioni as $certificazione): ?>
                <article class="uc-list-item">
                    <strong>#<?= V::e($certificazione->getIdCertificazione()) ?> - <?= V::e($certificazione->getTipo()) ?></strong>
                    <span>Chef #<?= V::e($certificazione->getIdChef()) ?></span>
                    <a class="btn btn-primary" href="<?= V::e(V::url('/certificazioni/' . $certificazione->getIdCertificazione())) ?>">Dettaglio</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>

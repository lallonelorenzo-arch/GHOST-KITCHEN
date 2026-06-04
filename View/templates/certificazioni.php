<?php
use ViewHelpers as V;
/** @var array $certificazioni */
/** @var string|null $messaggioAccesso */
$certificazioni = $certificazioni ?? [];
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Validazione certificazioni</h1>
    <p>Controlla i documenti caricati dagli chef e registra una decisione motivata.</p>
</section>

<section class="section ops-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <section class="ops-panel">
        <h2>Certificazioni in attesa</h2>
        <?php if ($certificazioni === []): ?><p class="muted-text">Nessuna certificazione in attesa.</p><?php endif; ?>
        <div class="ops-list">
            <?php foreach ($certificazioni as $certificazione): ?>
                <article class="ops-list-item">
                    <strong>#<?= V::e($certificazione->getIdCertificazione()) ?> - <?= V::e($certificazione->getTipo()) ?></strong>
                    <span>Chef #<?= V::e($certificazione->getIdChef()) ?></span>
                    <a class="btn btn-primary" href="<?= V::e(V::url('/certificazioni/' . $certificazione->getIdCertificazione())) ?>">Dettaglio</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>

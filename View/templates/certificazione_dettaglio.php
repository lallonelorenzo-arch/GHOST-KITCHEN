<?php
use ViewHelpers as V;
/** @var ECertificazione|null $certificazione */
/** @var EChef|null $chef */
/** @var string|null $messaggioAccesso */
$certificazione = $certificazione ?? null;
$chef = $chef ?? null;
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Dettaglio certificazione</h1>
    <p>Verifica documento, chef collegato e note prima di approvare o rifiutare.</p>
</section>

<section class="section ops-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <?php if ($certificazione !== null): ?>
        <div class="ops-grid">
            <article class="ops-panel">
                <h2><?= V::e($certificazione->getTipo()) ?></h2>
                <dl class="ops-meta">
                    <div><dt>ID</dt><dd>#<?= V::e($certificazione->getIdCertificazione()) ?></dd></div>
                    <div><dt>Stato</dt><dd><?= V::e($certificazione->getStato()) ?></dd></div>
                    <div><dt>Caricata</dt><dd><?= V::e($certificazione->getDataCaricamento()) ?></dd></div>
                </dl>
                <p><?= V::e($certificazione->getNomeFile()) ?></p>
                <?php if ($certificazione->getPathFile() !== ''): ?>
                    <a class="btn btn-ghost" href="<?= V::e(V::url($certificazione->getPathFile())) ?>">Apri file</a>
                <?php endif; ?>
                <?php if ($chef !== null): ?>
                    <p class="muted-text">Chef: <?= V::e($chef->getNome() . ' ' . $chef->getCognome()) ?></p>
                <?php endif; ?>
            </article>

            <article class="ops-panel">
                <h2>Decisione</h2>
                <form class="ops-form" method="post" action="<?= V::e(V::url('/certificazioni/' . $certificazione->getIdCertificazione() . '/approva')) ?>">
                    <label>Note admin
                        <textarea name="noteAdmin" rows="3"><?= V::e($certificazione->getNoteAdmin()) ?></textarea>
                    </label>
                    <button class="btn btn-accent" type="submit">Approva</button>
                </form>
                <form class="ops-form" method="post" action="<?= V::e(V::url('/certificazioni/' . $certificazione->getIdCertificazione() . '/rifiuta')) ?>">
                    <label>Motivo rifiuto
                        <textarea name="noteAdmin" rows="3"><?= V::e($certificazione->getNoteAdmin()) ?></textarea>
                    </label>
                    <button class="btn btn-ghost" type="submit">Rifiuta</button>
                </form>
            </article>
        </div>
    <?php else: ?>
        <div class="empty-state">Certificazione non disponibile.</div>
    <?php endif; ?>
</section>

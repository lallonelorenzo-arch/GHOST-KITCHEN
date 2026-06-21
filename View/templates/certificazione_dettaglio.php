<?php
use ViewHelpers as V;
/** @var ECertificazione|null $certificazione */
/** @var mixed|null $owner */
/** @var string|null $ownerLabel */
/** @var string|null $scadenza */
/** @var string|null $messaggioAccesso */
$certificazione = $certificazione ?? null;
$owner = $owner ?? null;
$ownerLabel = $ownerLabel ?? null;
$scadenza = $scadenza ?? null;
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Dettaglio certificazione</h1>
    <p>Verifica documento, owner collegato e scadenza prima di modificare lo stato.</p>
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
                    <div><dt>Stato</dt><dd><?= V::e(str_replace('_', ' ', $certificazione->getStato())) ?></dd></div>
                    <div><dt>Caricata</dt><dd><?= V::e($certificazione->getDataCaricamento()) ?></dd></div>
                    <div><dt>Validata</dt><dd><?= V::e($certificazione->getDataValidazione() !== '' ? $certificazione->getDataValidazione() : 'n/d') ?></dd></div>
                    <div><dt>Scadenza</dt><dd><?= V::e($scadenza ?? 'non impostata') ?></dd></div>
                </dl>
                <p><?= V::e($certificazione->getNomeFile()) ?></p>
                <?php if ($certificazione->getPathFile() !== ''): ?>
                    <a class="btn btn-ghost" href="<?= V::e(V::url($certificazione->getPathFile())) ?>" target="_blank" rel="noopener">Apri file</a>
                <?php endif; ?>
                <?php if ($ownerLabel !== null): ?>
                    <p class="muted-text"><?= V::e($ownerLabel) ?></p>
                <?php endif; ?>
                <?php if ($certificazione->getNoteAdmin() !== ''): ?>
                    <p class="muted-text">Note precedenti: <?= V::e($certificazione->getNoteAdmin()) ?></p>
                <?php endif; ?>
            </article>

            <article class="ops-panel">
                <h2>Decisione admin</h2>
                <form class="ops-form" method="post" action="<?= V::e(V::url('/certificazioni/' . $certificazione->getIdCertificazione() . '/approva')) ?>">
                    <label>Data scadenza certificato
                        <input type="date" name="dataScadenza" value="<?= V::e($certificazione->getDataScadenza()) ?>" required>
                    </label>
                    <label>Note approvazione
                        <textarea name="noteAdmin" rows="3"><?= V::e($certificazione->getNoteAdmin()) ?></textarea>
                    </label>
                    <button class="btn btn-primary" type="submit">Approva con scadenza</button>
                </form>
                <form class="ops-form" method="post" action="<?= V::e(V::url('/certificazioni/' . $certificazione->getIdCertificazione() . '/rifiuta')) ?>">
                    <label>Motivo rifiuto
                        <textarea name="noteAdmin" rows="3"><?= V::e($certificazione->getNoteAdmin()) ?></textarea>
                    </label>
                    <button class="btn btn-danger" type="submit">Rifiuta</button>
                </form>
                <?php if ($certificazione->getStato() !== ECertificazione::STATO_IN_ATTESA): ?>
                    <form class="ops-form" method="post" action="<?= V::e(V::url('/certificazioni/' . $certificazione->getIdCertificazione() . '/in-attesa')) ?>">
                        <label>Note revisione
                            <textarea name="noteAdmin" rows="3"><?= V::e($certificazione->getNoteAdmin()) ?></textarea>
                        </label>
                        <button class="btn btn-ghost" type="submit">Rimetti in attesa</button>
                    </form>
                <?php endif; ?>
            </article>
        </div>
    <?php else: ?>
        <div class="empty-state">Certificazione non disponibile.</div>
    <?php endif; ?>
</section>

<?php
use ViewHelpers as V;
/** @var array $richiesteChef */
/** @var array $richiesteGhostKitchen */
/** @var string|null $messaggioAccesso */
?>
<section class="page-hero compact-hero uc-page-hero">
    <span class="badge">UC7</span>
    <h1>Gestione richieste</h1>
    <p>Accetta o rifiuta le prenotazioni in attesa.</p>
</section>

<section class="section uc-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <div class="uc-grid">
        <section class="uc-panel">
            <h2>Richieste chef</h2>
            <?php if (($richiesteChef ?? []) === []): ?><p class="uc-muted">Nessuna richiesta chef in attesa.</p><?php endif; ?>
            <div class="uc-list">
                <?php foreach (($richiesteChef ?? []) as $richiesta): ?>
                    <article class="uc-list-item uc-request">
                        <strong>#<?= V::e($richiesta->getIdPrenotazione()) ?> - <?= V::e($richiesta->getDataServizio()) ?></strong>
                        <span><?= V::e($richiesta->getOraInizio()) ?> - <?= V::e($richiesta->getOraFine()) ?>, <?= V::e($richiesta->getNumeroPersone()) ?> persone</span>
                        <form method="post" action="<?= V::e(V::url('/richieste/chef/' . $richiesta->getIdPrenotazione() . '/accetta')) ?>"><button class="btn btn-accent" type="submit">Accetta</button></form>
                        <form method="post" action="<?= V::e(V::url('/richieste/chef/' . $richiesta->getIdPrenotazione() . '/rifiuta')) ?>"><input name="motivo" placeholder="Motivo"><button class="btn btn-ghost" type="submit">Rifiuta</button></form>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="uc-panel">
            <h2>Richieste ghost kitchen</h2>
            <?php if (($richiesteGhostKitchen ?? []) === []): ?><p class="uc-muted">Nessuna richiesta ghost kitchen in attesa.</p><?php endif; ?>
            <div class="uc-list">
                <?php foreach (($richiesteGhostKitchen ?? []) as $richiesta): ?>
                    <article class="uc-list-item uc-request">
                        <strong>#<?= V::e($richiesta->getIdPrenotazione()) ?> - <?= V::e($richiesta->getDataServizio()) ?></strong>
                        <span><?= V::e($richiesta->getOraInizio()) ?> - <?= V::e($richiesta->getOraFine()) ?>, richiedente <?= V::e($richiesta->getTipoRichiedente()) ?></span>
                        <form method="post" action="<?= V::e(V::url('/richieste/ghost-kitchen/' . $richiesta->getIdPrenotazione() . '/accetta')) ?>"><button class="btn btn-accent" type="submit">Accetta</button></form>
                        <form method="post" action="<?= V::e(V::url('/richieste/ghost-kitchen/' . $richiesta->getIdPrenotazione() . '/rifiuta')) ?>"><input name="motivo" placeholder="Motivo"><button class="btn btn-ghost" type="submit">Rifiuta</button></form>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</section>

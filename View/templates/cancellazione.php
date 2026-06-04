<?php
use ViewHelpers as V;
/** @var string|null $tipoPrenotazione */
/** @var EPrenotazione|null $prenotazione */
/** @var EPagamento|null $pagamento */
/** @var array $rimborsoStimato */
/** @var array $form */
/** @var ECancellazione|null $cancellazione */
/** @var ERimborso|null $rimborso */
/** @var string|null $messaggioAccesso */
/** @var string|null $erroreForm */
/** @var string|null $messaggioSuccesso */
$tipoPrenotazione = $tipoPrenotazione ?? 'chef';
$prenotazione = $prenotazione ?? null;
$pagamento = $pagamento ?? null;
$cancellazione = $cancellazione ?? null;
$rimborso = $rimborso ?? null;
$tipoSlug = $tipoPrenotazione === 'ghost_kitchen' ? 'ghost-kitchen' : 'chef';
$idPrenotazione = $prenotazione !== null ? (int) $prenotazione->getIdPrenotazione() : (int) ($idPrenotazione ?? 0);
$rimborsoStimato = $rimborsoStimato ?? [];
$form = $form ?? [];
?>
<section class="page-hero compact-hero uc-page-hero">
    <span class="badge">UC9</span>
    <h1>Cancellazione e rimborso</h1>
    <p>Richiedi la cancellazione e verifica il rimborso stimato.</p>
</section>

<section class="section uc-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>
    <?php if (!empty($erroreForm)): ?><div class="alert"><?= V::e($erroreForm) ?></div><?php endif; ?>
    <?php if (!empty($messaggioSuccesso)): ?><div class="notice"><?= V::e($messaggioSuccesso) ?></div><?php endif; ?>

    <div class="uc-grid">
        <article class="uc-panel">
            <h2>Riepilogo prenotazione</h2>
            <?php if ($prenotazione !== null): ?>
                <dl class="uc-meta">
                    <div><dt>ID</dt><dd>#<?= V::e($prenotazione->getIdPrenotazione()) ?></dd></div>
                    <div><dt>Stato</dt><dd><?= V::e($prenotazione->getStato()) ?></dd></div>
                    <div><dt>Importo</dt><dd>&euro; <?= V::e(V::money($prenotazione->getImportoTotale())) ?></dd></div>
                </dl>
            <?php endif; ?>
            <div class="uc-list">
                <div class="uc-list-item"><strong>Pagamento</strong><span><?= $pagamento !== null ? V::e($pagamento->getStato()) : 'non trovato' ?></span></div>
                <div class="uc-list-item"><strong>Penale stimata</strong><span>&euro; <?= V::e(V::money((float) ($rimborsoStimato['penale'] ?? 0))) ?></span></div>
                <div class="uc-list-item"><strong>Rimborso stimato</strong><span>&euro; <?= V::e(V::money((float) ($rimborsoStimato['importoRimborsabile'] ?? 0))) ?></span></div>
            </div>
            <?php if (($rimborsoStimato['trovato'] ?? false) !== true): ?>
                <p class="uc-muted"><?= V::e($rimborsoStimato['messaggio'] ?? 'Rimborso non calcolabile per questa prenotazione.') ?></p>
            <?php endif; ?>
        </article>

        <form class="uc-panel uc-form" method="post" action="<?= V::e(V::url('/cancellazione/' . $tipoSlug . '/' . $idPrenotazione)) ?>">
            <h2>Motivo cancellazione</h2>
            <label>Motivo
                <textarea name="motivo" rows="5" required><?= V::e($form['motivo'] ?? '') ?></textarea>
            </label>
            <button class="btn btn-accent" type="submit">Conferma cancellazione</button>
            <?php if ($cancellazione !== null): ?>
                <div class="notice">Cancellazione #<?= V::e($cancellazione->getIdCancellazione()) ?> - <?= V::e($cancellazione->getStato()) ?></div>
            <?php endif; ?>
            <?php if ($rimborso !== null): ?>
                <div class="notice">Rimborso #<?= V::e($rimborso->getIdRimborso()) ?> richiesto per &euro; <?= V::e(V::money($rimborso->getImporto())) ?></div>
            <?php endif; ?>
        </form>
    </div>
</section>

<?php
use ViewHelpers as V;
/** @var string|null $tipoPrenotazione */
/** @var int|null $idPrenotazione */
/** @var float|null $importo */
/** @var array $form */
/** @var EPagamento|null $pagamento */
/** @var string|null $messaggioAccesso */
/** @var string|null $erroreForm */
/** @var string|null $messaggioSuccesso */
// Dati preparati da CPagamento: il template invia solo la conferma della simulazione.
$tipoPrenotazione = $tipoPrenotazione ?? 'chef';
$tipoSlug = $tipoPrenotazione === 'ghost_kitchen' ? 'ghost-kitchen' : 'chef';
$form = $form ?? [];
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Pagamento</h1>
    <p>Controlla riepilogo e importo prima di confermare la transazione.</p>
</section>

<section class="section ops-flow">
    <!-- Messaggi generati dal controller: accesso richiesto, errore di validazione o pagamento completato. -->
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>
    <?php if (!empty($erroreForm)): ?><div class="alert"><?= V::e($erroreForm) ?></div><?php endif; ?>
    <?php if (!empty($messaggioSuccesso)): ?><div class="notice"><?= V::e($messaggioSuccesso) ?></div><?php endif; ?>

    <div class="ops-grid">
        <!-- Riepilogo della prenotazione e dell'importo calcolato lato controller/foundation. -->
        <article class="ops-panel">
            <h2>Riepilogo</h2>
            <dl class="ops-meta">
                <div><dt>Prenotazione</dt><dd>#<?= V::e($idPrenotazione ?? '') ?></dd></div>
                <div><dt>Tipo</dt><dd><?= V::e($tipoPrenotazione) ?></dd></div>
                <div><dt>Importo</dt><dd>&euro; <?= V::e(V::money((float) ($importo ?? 0))) ?></dd></div>
            </dl>
            <?php if ($pagamento !== null): ?>
                <div class="notice">Pagamento #<?= V::e($pagamento->getIdPagamento()) ?> - <?= V::e($pagamento->getStato()) ?></div>
            <?php endif; ?>
        </article>

        <!-- Il sistema interbancario simulato risponde sempre positivamente. -->
        <form class="ops-panel ops-form" method="post" action="<?= V::e(V::url('/pagamento/' . $tipoSlug . '/' . (int) $idPrenotazione)) ?>">
            <h2>Dati pagamento</h2>
            <p class="muted-text">La conferma invia al sistema di pagamento simulato i dati della prenotazione e dell utente collegato.</p>
            <button class="btn btn-accent" type="submit">Conferma pagamento</button>
        </form>
    </div>
</section>

<?php
use ViewHelpers as V;
/** @var EGhostKitchen|null $ghostKitchen */
/** @var array $disponibilita */
/** @var string|null $tipoRichiedente */
/** @var array $form */
/** @var string|null $messaggioAccesso */
/** @var string|null $erroreForm */
/** @var string|null $messaggioSuccesso */
/** @var EPrenotazioneGhostKitchen|null $prenotazione */
/** @var bool|null $ghostKitchenPrenotabile */
$ghostKitchenPrenotabile = $ghostKitchenPrenotabile ?? true;
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Prenotazione ghost kitchen</h1>
    <p>Blocca uno slot cucina e invia al gestore una richiesta completa di orari e note operative.</p>
</section>

<section class="section ops-flow">
    <?php if (!empty($errore)): ?>
        <div class="alert"><?= V::e($errore) ?></div>
    <?php elseif ($ghostKitchen !== null): ?>
        <?php if (!empty($messaggioAccesso)): ?>
            <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
        <?php endif; ?>
        <?php if (!empty($erroreForm)): ?><div class="alert"><?= V::e($erroreForm) ?></div><?php endif; ?>
        <?php if (!empty($messaggioSuccesso)): ?><div class="notice"><?= V::e($messaggioSuccesso) ?></div><?php endif; ?>

        <div class="ops-grid">
            <article class="ops-panel">
                <h2><?= V::e($ghostKitchen->getNome()) ?></h2>
                <p><?= V::e($ghostKitchen->getDescrizione()) ?></p>
                <dl class="ops-meta">
                    <div><dt>Citta</dt><dd><?= V::e($ghostKitchen->getCitta()) ?></dd></div>
                    <div><dt>Prezzo orario</dt><dd>&euro; <?= V::e(V::money($ghostKitchen->getPrezzoOrario())) ?></dd></div>
                    <div><dt>Capienza</dt><dd><?= V::e($ghostKitchen->getCapienza()) ?></dd></div>
                </dl>
            </article>

            <?php if ($ghostKitchenPrenotabile): ?>
            <form class="ops-panel ops-form" method="post" action="<?= V::e(V::url('/prenotazione/ghost-kitchen/' . $ghostKitchen->getId())) ?>">
                <h2>Dati richiesta</h2>
                <p class="muted-text">Tipo richiedente: <?= V::e($tipoRichiedente ?? 'non disponibile') ?></p>
                <div class="ops-form-row">
                    <label>Data <input type="date" name="dataServizio" value="<?= V::e($form['dataServizio'] ?? '') ?>" required></label>
                    <label>Ora inizio <input type="time" name="oraInizio" value="<?= V::e($form['oraInizio'] ?? '') ?>" required></label>
                </div>
                <label>Ora fine <input type="time" name="oraFine" value="<?= V::e($form['oraFine'] ?? '') ?>" required></label>
                <label>Note
                    <textarea name="note" rows="4"><?= V::e($form['note'] ?? '') ?></textarea>
                </label>
                <button class="btn btn-accent" type="submit">Invia richiesta</button>
            </form>
            <?php else: ?>
                <article class="ops-panel">
                    <h2>Prenotazione bloccata</h2>
                    <p class="muted-text">Questa ghost kitchen non puo ricevere richieste finche lo stato non torna attivo.</p>
                </article>
            <?php endif; ?>
        </div>

        <?php if ($prenotazione !== null): ?>
            <article class="ops-panel ops-result">
                <h2>Riepilogo richiesta #<?= V::e($prenotazione->getIdPrenotazione()) ?></h2>
                <p>Stato: <?= V::e($prenotazione->getStato()) ?> - Importo stimato: &euro; <?= V::e(V::money($prenotazione->getImportoTotale())) ?></p>
                <a class="btn btn-accent" href="<?= V::e(V::url('/pagamento/ghost-kitchen/' . $prenotazione->getIdPrenotazione())) ?>">Vai al pagamento</a>
            </article>
        <?php endif; ?>

        <section class="ops-panel">
            <h2>Disponibilita ghost kitchen</h2>
            <div class="ops-list">
                <?php foreach ($disponibilita as $slot): ?>
                    <div class="ops-list-item">
                        <strong><?= V::e($slot->getData()) ?></strong>
                        <span><?= V::e($slot->getOraInizio()) ?> - <?= V::e($slot->getOraFine()) ?></span>
                        <span class="badge"><?= V::e($slot->getStato()) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($disponibilita === []): ?>
                <p class="muted-text">Nessuno slot pubblicato per questa ghost kitchen.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</section>

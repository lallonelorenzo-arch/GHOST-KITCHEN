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
/** @var array $availabilityPayload */
$ghostKitchenPrenotabile = $ghostKitchenPrenotabile ?? true;
$availabilityPayload = $availabilityPayload ?? [];
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

            <?php if ($ghostKitchenPrenotabile && empty($accessoRichiesto)): ?>
            <form
                class="ops-panel chef-booking-wizard gk-booking-wizard"
                method="post"
                action="<?= V::e(V::url('/prenotazione/ghost-kitchen/' . $ghostKitchen->getId())) ?>"
                data-gk-booking
                data-hour-price="<?= V::e(number_format($ghostKitchen->getPrezzoOrario(), 2, '.', '')) ?>"
                data-gk-name="<?= V::e($ghostKitchen->getNome()) ?>"
            >
                <header class="chef-booking-header">
                    <div>
                        <span>Prenotazione cucina</span>
                        <h2><?= V::e($ghostKitchen->getNome()) ?></h2>
                    </div>
                </header>

                <div class="chef-booking-progress" aria-label="Avanzamento prenotazione">
                    <span class="is-active" data-gk-step-indicator="1"><b>1</b> Data e orario</span>
                    <span data-gk-step-indicator="2"><b>2</b> Conferma</span>
                </div>

                <input type="hidden" name="dataServizio" data-gk-date value="<?= V::e($form['dataServizio'] ?? '') ?>">
                <input type="hidden" name="oraInizio" data-gk-start value="<?= V::e($form['oraInizio'] ?? '') ?>">
                <input type="hidden" name="oraFine" data-gk-end value="<?= V::e($form['oraFine'] ?? '') ?>">

                <section class="chef-booking-step is-active" data-gk-booking-step="1">
                    <div class="chef-step-heading">
                        <span>Step 1 di 2</span>
                        <h3>Quando vuoi prenotare la cucina?</h3>
                        <p>Seleziona un giorno disponibile e orari a ore piene compresi nello slot pubblicato.</p>
                    </div>
                    <div class="wizard-calendar" data-gk-calendar>
                        <div class="wizard-calendar-head">
                            <button type="button" data-gk-calendar-prev aria-label="Mese precedente">&larr;</button>
                            <strong data-gk-calendar-title></strong>
                            <button type="button" data-gk-calendar-next aria-label="Mese successivo">&rarr;</button>
                        </div>
                        <div class="wizard-calendar-weekdays" aria-hidden="true">
                            <span>Lun</span><span>Mar</span><span>Mer</span><span>Gio</span><span>Ven</span><span>Sab</span><span>Dom</span>
                        </div>
                        <div class="wizard-calendar-grid" data-gk-calendar-grid></div>
                    </div>
                    <div class="service-period-picker" data-gk-time-picker hidden>
                        <span>Scegli orario</span>
                        <div class="booking-address-grid">
                            <label>Inizio
                                <select data-gk-start-select></select>
                            </label>
                            <label>Fine
                                <select data-gk-end-select></select>
                            </label>
                        </div>
                    </div>
                    <p class="wizard-inline-error" data-gk-step-error="1" hidden></p>
                </section>

                <section class="chef-booking-step" data-gk-booking-step="2" hidden>
                    <div class="chef-step-heading">
                        <span>Step 2 di 2</span>
                        <h3>Conferma richiesta</h3>
                        <p>Controlla il riepilogo prima di inviare la richiesta al gestore.</p>
                    </div>
                    <dl class="chef-booking-review">
                        <div><dt>Ghost kitchen</dt><dd data-gk-review-name></dd></div>
                        <div><dt>Data e orario</dt><dd data-gk-review-date></dd></div>
                        <div><dt>Durata</dt><dd data-gk-review-hours></dd></div>
                        <div class="is-total"><dt>Totale stimato</dt><dd data-gk-review-total></dd></div>
                        <div class="is-wide"><dt>Note</dt><dd data-gk-review-notes></dd></div>
                    </dl>
                    <label>Note operative
                        <textarea name="note" rows="4" data-gk-notes><?= V::e($form['note'] ?? '') ?></textarea>
                    </label>
                    <p class="wizard-inline-error" data-gk-step-error="2" hidden></p>
                </section>

                <footer class="chef-booking-actions">
                    <button class="btn btn-ghost" type="button" data-gk-prev hidden>Indietro</button>
                    <button class="btn btn-accent" type="button" data-gk-next>Continua</button>
                    <button class="btn btn-accent" type="submit" data-gk-submit hidden>Invia richiesta</button>
                </footer>

                <script type="application/json" data-gk-availability><?= json_encode($availabilityPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
            </form>
            <?php elseif (!$ghostKitchenPrenotabile): ?>
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

        <?php
        $calendarSlots = $disponibilita;
        $calendarTitle = 'Calendario disponibilita ghost kitchen';
        $calendarEmptyText = 'Nessuno slot pubblicato per questa ghost kitchen.';
        $calendarSelectable = $ghostKitchenPrenotabile && empty($accessoRichiesto);
        include __DIR__ . '/partials/booking_calendar.php';
        ?>
    <?php endif; ?>
</section>

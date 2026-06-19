<?php
use ViewHelpers as V;
/** @var EChef $chef */
/** @var mixed $fotoProfilo */
/** @var array $menu */
/** @var array $certificazioni */
/** @var array $disponibilitaChef */
/** @var array $accesso */
/** @var bool $canBookChef */
/** @var bool $chefPrenotabile */
/** @var array $indirizzoSalvato */
/** @var string $bookingCsrfToken */
$image = V::mediaUrl($fotoProfilo ?? null, 'https://images.unsplash.com/photo-1750943082012-efe6d2fd9e45?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200');
$rating = $chef->getValutazioneMedia();
$accesso = $accesso ?? [];
$canBookChef = $canBookChef ?? false;
$chefPrenotabile = $chefPrenotabile ?? false;
$indirizzoSalvato = $indirizzoSalvato ?? ['indirizzo' => '', 'citta' => '', 'provincia' => '', 'numeroCivico' => ''];
$bookingCsrfToken = $bookingCsrfToken ?? '';
$savedAddressComplete = trim((string) ($indirizzoSalvato['indirizzo'] ?? '')) !== ''
    && trim((string) ($indirizzoSalvato['citta'] ?? '')) !== ''
    && EUtente::isProvinciaItaliana((string) ($indirizzoSalvato['provincia'] ?? ''))
    && trim((string) ($indirizzoSalvato['numeroCivico'] ?? '')) !== '';
$availabilityPayload = array_map(
    static fn (EDisponibilitaChef $slot): array => [
        'date' => $slot->getData(),
        'period' => $slot->getFasciaServizio(),
        'start' => substr($slot->getOraInizio(), 0, 5),
        'end' => substr($slot->getOraFine(), 0, 5),
    ],
    $disponibilitaChef ?? []
);
?>
<section class="detail-hero" style="background-image: linear-gradient(0deg, rgba(44,24,16,.9), rgba(44,24,16,.25)), url('<?= V::e($image) ?>')">
    <a class="back-link" href="<?= V::e(V::url('/ricerca/chef')) ?>">Torna alla ricerca</a>
    <div>
        <span class="badge rating-badge"><span class="stars"><?= V::stars($rating) ?></span> <?= V::e($rating) ?> / 5</span>
        <h1><?= V::e($chef->getNome() . ' ' . $chef->getCognome()) ?></h1>
        <p><?= V::e($chef->getSpecializzazione() ?: $chef->getTipologiaCucina()) ?></p>
    </div>
</section>

<section class="section detail-layout chef-booking-page" data-chef-booking>
    <article>
        <h2>Chi sono</h2>
        <p class="lead"><?= V::e($chef->getBiografia() ?: 'Lo chef non ha ancora pubblicato una biografia estesa.') ?></p>
        <div class="detail-chips">
            <span><?= V::e($chef->getTipologiaCucina() ?: 'Cucina non specificata') ?></span>
            <span><?= V::e($chef->getNumeroRecensioni()) ?> recensioni</span>
            <span><?= V::e($chef->getValutazioneMedia()) ?>/5</span>
        </div>

        <div class="menu-section-heading">
            <div>
                <h2>Scegli il menu</h2>
                <p>Seleziona una proposta prima di avviare la prenotazione.</p>
            </div>
            <span data-selected-menu-label>Nessun menu selezionato</span>
        </div>
        <div class="chef-menu-grid" role="radiogroup" aria-label="Menu disponibili">
            <?php foreach ($menu as $menuItem): ?>
                <?php $m = $menuItem['menu']; ?>
                <label class="chef-menu-option">
                    <input
                        type="radio"
                        name="menuPreview"
                        value="<?= V::e((int) $m->getIdMenu()) ?>"
                        data-menu-option
                        data-menu-name="<?= V::e($m->getNome()) ?>"
                        data-menu-price="<?= V::e(number_format($m->getPrezzoPersona(), 2, '.', '')) ?>"
                    >
                    <span class="chef-menu-check" aria-hidden="true"></span>
                    <span class="chef-menu-content">
                        <span class="panel-title">
                            <strong><?= V::e($m->getNome()) ?></strong>
                            <b>&euro; <?= V::e(V::money($m->getPrezzoPersona())) ?> <small>a persona</small></b>
                        </span>
                        <span class="chef-menu-description"><?= V::e($m->getDescrizione()) ?></span>
                        <?php if (($menuItem['piatti'] ?? []) !== []): ?>
                            <span class="chef-menu-dishes">
                                <?php foreach ($menuItem['piatti'] as $piattoData): ?>
                                    <?php $piatto = $piattoData['piatto']; ?>
                                    <span><small><?= V::e(ucfirst($piatto->getCategoria())) ?></small><?= V::e($piatto->getNome()) ?></span>
                                <?php endforeach; ?>
                            </span>
                        <?php endif; ?>
                    </span>
                </label>
            <?php endforeach; ?>
            <?php if ($menu === []): ?>
                <div class="empty-state">Nessun menu pubblicato per questo chef.</div>
            <?php endif; ?>
        </div>

        <h2>Certificazioni</h2>
        <div class="tag-row">
            <?php foreach ($certificazioni as $certificazione): ?>
                <span><?= V::e($certificazione->getTipo() !== '' ? $certificazione->getTipo() : 'Certificazione') ?></span>
            <?php endforeach; ?>
            <?php if ($certificazioni === []): ?><span>Certificazioni non ancora pubblicate</span><?php endif; ?>
        </div>
    </article>

    <aside class="booking-box">
        <span class="booking-label">Esperienza privata</span>
        <p>Menu a partire da</p>
        <strong>&euro; <?= V::e(V::money($chef->getPrezzoBase())) ?></strong>
        <span>Il totale dipende dal menu e dal numero di partecipanti.</span>
        <div class="booking-box-summary" data-booking-box-summary hidden>
            <small>Menu selezionato</small>
            <b data-booking-box-menu></b>
        </div>
        <div class="booking-actions">
            <div class="booking-primary-action">
                <div class="menu-required-popover" data-menu-required-popover hidden role="status">
                    Scegli prima uno dei menu.
                </div>
                <button class="btn btn-accent" type="button" data-chef-booking-start <?= $menu === [] ? 'disabled' : '' ?>>Prenota ora</button>
            </div>
            <button class="btn btn-ghost" type="button" data-modal-open="chef-contact-modal">Contatta Chef</button>
            <a class="btn btn-ghost" href="<?= V::e(V::url('/segnalazione/chef/' . $chef->getIdChef())) ?>">Segnala profilo</a>
        </div>
    </aside>

    <script type="application/json" data-chef-availability><?= json_encode($availabilityPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
</section>

<dialog class="booking-detail-modal booking-alert-modal" id="chef-booking-access-modal" aria-labelledby="chef-booking-access-title">
    <div class="booking-detail-box">
        <header>
            <div>
                <span>Prenotazione chef</span>
                <h2 id="chef-booking-access-title">Non puoi ancora procedere</h2>
            </div>
            <button type="button" class="modal-close-button" data-modal-close aria-label="Chiudi">&times;</button>
        </header>
        <?php if (!$chefPrenotabile): ?>
            <p>Lo chef non è attualmente prenotabile perché le certificazioni richieste non risultano valide.</p>
        <?php elseif (($accesso['isLogged'] ?? false) !== true): ?>
            <p>Accedi come cliente o gestore per completare la prenotazione.</p>
            <a class="btn btn-accent" href="<?= V::e(V::url('/login')) ?>">Accedi</a>
        <?php else: ?>
            <p>Un account chef non può prenotare il servizio di un altro chef. Passa al ruolo gestore, se disponibile.</p>
        <?php endif; ?>
    </div>
</dialog>

<dialog class="chef-booking-dialog" id="chef-booking-dialog" aria-labelledby="chef-booking-title">
    <form
        id="chef-booking-form"
        class="chef-booking-wizard"
        method="post"
        action="<?= V::e(V::url('/prenotazione/chef/' . $chef->getIdChef())) ?>"
        data-chef-booking-form
        data-can-book="<?= $canBookChef && $chefPrenotabile ? '1' : '0' ?>"
    >
        <header class="chef-booking-header">
            <div>
                <span>Prenotazione con</span>
                <h2 id="chef-booking-title"><?= V::e($chef->getNome() . ' ' . $chef->getCognome()) ?></h2>
            </div>
            <button type="button" class="modal-close-button" data-modal-close aria-label="Chiudi prenotazione">&times;</button>
        </header>

        <div class="chef-booking-progress" aria-label="Avanzamento prenotazione">
            <span class="is-active" data-chef-step-indicator="1"><b>1</b> Data</span>
            <span data-chef-step-indicator="2"><b>2</b> Dettagli</span>
            <span data-chef-step-indicator="3"><b>3</b> Conferma</span>
        </div>

        <input type="hidden" name="idMenu" data-booking-menu-id>
        <input type="hidden" name="dataServizio" data-booking-date>
        <input type="hidden" name="fasciaServizio" data-booking-period>
        <input type="hidden" name="csrfToken" value="<?= V::e($bookingCsrfToken) ?>">

        <section class="chef-booking-step is-active" data-chef-booking-step="1">
            <div class="chef-step-heading">
                <span>Step 1 di 3</span>
                <h3>Quando vuoi vivere l’esperienza?</h3>
                <p>Sono selezionabili esclusivamente i giorni pubblicati dallo chef.</p>
            </div>
            <div class="wizard-calendar" data-wizard-calendar>
                <div class="wizard-calendar-head">
                    <button type="button" data-calendar-prev aria-label="Mese precedente">&larr;</button>
                    <strong data-calendar-title></strong>
                    <button type="button" data-calendar-next aria-label="Mese successivo">&rarr;</button>
                </div>
                <div class="wizard-calendar-weekdays" aria-hidden="true">
                    <span>Lun</span><span>Mar</span><span>Mer</span><span>Gio</span><span>Ven</span><span>Sab</span><span>Dom</span>
                </div>
                <div class="wizard-calendar-grid" data-calendar-grid></div>
            </div>
            <div class="service-period-picker" data-period-picker hidden>
                <span>Scegli il servizio</span>
                <div data-period-options></div>
            </div>
            <p class="wizard-inline-error" data-step-error="1" hidden></p>
        </section>

        <section class="chef-booking-step" data-chef-booking-step="2" hidden>
            <div class="chef-step-heading">
                <span>Step 2 di 3</span>
                <h3>Dove si svolgerà il servizio?</h3>
                <p>Inserisci l’indirizzo e le informazioni utili per lo chef.</p>
            </div>

            <label class="saved-address-toggle <?= $savedAddressComplete ? '' : 'is-disabled' ?>">
                <input
                    type="checkbox"
                    data-use-saved-address
                    <?= $savedAddressComplete ? '' : 'disabled' ?>
                >
                <span>
                    <strong>Usa l’indirizzo del profilo</strong>
                    <small><?= $savedAddressComplete
                        ? V::e($indirizzoSalvato['indirizzo'] . ' ' . $indirizzoSalvato['numeroCivico'] . ', ' . $indirizzoSalvato['citta'] . ' (' . $indirizzoSalvato['provincia'] . ')')
                        : 'Completa indirizzo, città, provincia e civico nel profilo per usare questa opzione.' ?></small>
                </span>
            </label>

            <div class="booking-address-grid">
                <label>Indirizzo
                    <input name="indirizzo" maxlength="180" data-address-field="indirizzo" data-saved-value="<?= V::e($indirizzoSalvato['indirizzo'] ?? '') ?>" autocomplete="street-address" required>
                </label>
                <label>Numero civico
                    <input name="numeroCivico" maxlength="20" data-address-field="numeroCivico" data-saved-value="<?= V::e($indirizzoSalvato['numeroCivico'] ?? '') ?>" required>
                </label>
                <label>Città
                    <input name="citta" maxlength="120" data-address-field="citta" data-saved-value="<?= V::e($indirizzoSalvato['citta'] ?? '') ?>" autocomplete="address-level2" required>
                </label>
                <label>Provincia
                    <select name="provincia" data-address-field="provincia" data-saved-value="<?= V::e(strtoupper((string) ($indirizzoSalvato['provincia'] ?? ''))) ?>" autocomplete="address-level1" required>
                        <option value="">Seleziona</option>
                        <?php foreach (EUtente::SIGLE_PROVINCE_ITALIANE as $siglaProvincia): ?>
                            <option value="<?= V::e($siglaProvincia) ?>" <?= strtoupper((string) ($indirizzoSalvato['provincia'] ?? '')) === $siglaProvincia ? 'selected' : '' ?>>
                                <?= V::e($siglaProvincia) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Partecipanti
                    <input type="number" name="numeroPersone" min="1" max="100" value="2" data-booking-guests required>
                </label>
            </div>

            <label>Intolleranze, allergie o richieste speciali
                <textarea name="richiesteSpeciali" rows="4" maxlength="2000" data-booking-requests placeholder="Descrivi ciò che lo chef deve sapere."></textarea>
            </label>

            <fieldset class="wine-pairing-fieldset">
                <legend>Abbinamento vini al menu</legend>
                <label><input type="radio" name="abbinamentoVini" value="1"> Sì, desidero l’abbinamento</label>
                <label><input type="radio" name="abbinamentoVini" value="0" checked> No, grazie</label>
            </fieldset>
            <p class="wizard-inline-error" data-step-error="2" hidden></p>
        </section>

        <section class="chef-booking-step" data-chef-booking-step="3" hidden>
            <div class="chef-step-heading">
                <span>Step 3 di 3</span>
                <h3>Riepilogo e pagamento</h3>
                <p>Controlla attentamente i dati prima di confermare.</p>
            </div>
            <dl class="chef-booking-review">
                <div><dt>Menu</dt><dd data-review-menu></dd></div>
                <div><dt>Data e servizio</dt><dd data-review-date></dd></div>
                <div><dt>Indirizzo</dt><dd data-review-address></dd></div>
                <div><dt>Partecipanti</dt><dd data-review-guests></dd></div>
                <div><dt>Abbinamento vini</dt><dd data-review-wine></dd></div>
                <div class="is-total"><dt>Totale</dt><dd data-review-total></dd></div>
                <div class="is-wide"><dt>Richieste</dt><dd data-review-requests></dd></div>
            </dl>

            <p class="wizard-inline-error" data-step-error="3" hidden></p>
        </section>

        <footer class="chef-booking-actions">
            <button class="btn btn-ghost" type="button" data-chef-booking-prev hidden>Indietro</button>
            <button class="btn btn-accent" type="button" data-chef-booking-next>Continua</button>
            <button class="btn btn-accent" type="submit" data-chef-booking-submit hidden>Conferma e paga</button>
        </footer>
    </form>
</dialog>

<dialog class="booking-detail-modal contact-modal" id="chef-contact-modal" aria-labelledby="chef-contact-title">
    <div class="booking-detail-box">
        <header>
            <div>
                <span>Recapiti chef</span>
                <h2 id="chef-contact-title"><?= V::e($chef->getNome() . ' ' . $chef->getCognome()) ?></h2>
            </div>
            <button type="button" class="modal-close-button" data-modal-close aria-label="Chiudi contatti">&times;</button>
        </header>
        <dl class="contact-detail-list">
            <div>
                <dt>Email</dt>
                <dd><a href="mailto:<?= V::e($chef->getEmail()) ?>"><?= V::e($chef->getEmail() !== '' ? $chef->getEmail() : 'Non disponibile') ?></a></dd>
            </div>
            <div>
                <dt>Telefono</dt>
                <dd><a href="tel:<?= V::e($chef->getTelefono()) ?>"><?= V::e($chef->getTelefono() !== '' ? $chef->getTelefono() : 'Non disponibile') ?></a></dd>
            </div>
            <div>
                <dt>Località</dt>
                <dd><?= V::e($chef->getLocalita() !== '' ? $chef->getLocalita() : 'Non indicata') ?></dd>
            </div>
        </dl>
    </div>
</dialog>

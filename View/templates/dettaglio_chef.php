<?php
use ViewHelpers as V;
/** @var EChef $chef */
/** @var mixed $fotoProfilo */
/** @var array $media */
/** @var array $menu */
/** @var array $certificazioni */
/** @var array $disponibilitaChef */
/** @var array $recensioni */
/** @var array $autoriRecensioni */
/** @var array $accesso */
/** @var bool $canBookChef */
/** @var bool $chefPrenotabile */
/** @var bool $canManageGallery */
/** @var array $indirizzoSalvato */
/** @var string $bookingCsrfToken */
$image = V::mediaUrl($fotoProfilo ?? null, 'https://images.unsplash.com/photo-1750943082012-efe6d2fd9e45?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200');
$media = array_values(array_filter($media ?? [], static fn (EMedia $item): bool => $item->getStato() === EMedia::STATO_ATTIVO));
$recensioni = array_values(array_filter($recensioni ?? [], static fn (ERecensioneChef $recensione): bool => $recensione->isVisibile()));
$autoriRecensioni = $autoriRecensioni ?? [];
$numeroRecensioni = count($recensioni);
$rating = $numeroRecensioni > 0
    ? round(array_sum(array_map(static fn (ERecensioneChef $recensione): int => $recensione->getPunteggio(), $recensioni)) / $numeroRecensioni, 2)
    : 0.0;
$accesso = $accesso ?? [];
$canBookChef = $canBookChef ?? false;
$chefPrenotabile = $chefPrenotabile ?? false;
$canManageGallery = $canManageGallery ?? false;
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
$chefGalleryFallbacks = [
    'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=900',
    'https://images.unsplash.com/photo-1551218808-94e220e084d2?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=900',
    'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=900',
];
$chefGalleryItems = $media !== [] ? $media : $chefGalleryFallbacks;
?>
<section class="detail-hero" style="background-image: linear-gradient(0deg, rgba(44,24,16,.9), rgba(44,24,16,.25)), url('<?= V::e($image) ?>')">
    <a class="back-link" href="<?= V::e(V::url('/ricerca/chef')) ?>">Torna alla ricerca</a>
    <div>
        <a class="badge rating-badge" href="#recensioni-chef"><span class="stars"><?= V::stars($rating) ?></span> <?= V::e($rating) ?> / 5</a>
        <h1><?= V::e($chef->getNome() . ' ' . $chef->getCognome()) ?></h1>
        <p><?= V::e($chef->getSpecializzazione() ?: $chef->getTipologiaCucina()) ?></p>
    </div>
</section>

<section class="section detail-layout chef-booking-page" data-chef-booking>
    <article>
        <nav class="detail-tabs" aria-label="Sezioni chef" data-tabs>
            <button class="is-active" type="button" data-tab-button="chef-menu" aria-controls="chef-menu" aria-selected="true">Menu</button>
            <button type="button" data-tab-button="chef-recensioni" aria-controls="chef-recensioni" aria-selected="false">Recensioni</button>
            <button type="button" data-tab-button="chef-chi-sono" aria-controls="chef-chi-sono" aria-selected="false">Chi Sono</button>
        </nav>

        <section class="detail-tab-panel is-active" id="chef-menu" data-tab-panel>
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
        </section>

        <section class="detail-tab-panel" id="chef-recensioni" data-tab-panel hidden>
            <div class="toolbar">
                <div>
                    <h2>Recensioni</h2>
                    <p><?= V::e($numeroRecensioni) ?> recensioni pubblicate</p>
                </div>
            </div>

            <?php if ($recensioni === []): ?>
                <div class="empty-state">Nessuna recensione pubblicata per questo chef.</div>
            <?php else: ?>
                <div class="ops-list reviews-list compact-reviews">
                    <?php foreach ($recensioni as $recensione): ?>
                        <?php
                        $autore = $recensione->getIdAutore() !== null ? ($autoriRecensioni[(int) $recensione->getIdAutore()] ?? null) : null;
                        $autoreNome = $autore !== null ? trim($autore->getNome() . ' ' . $autore->getCognome()) : '';
                        ?>
                        <article class="ops-panel review-row">
                            <header class="review-row-header">
                                <div>
                                    <div class="review-score">
                                        <span class="stars" aria-label="Valutazione <?= V::e($recensione->getPunteggio()) ?> su 5"><?= V::stars((float) $recensione->getPunteggio()) ?></span>
                                        <strong><?= V::e($recensione->getPunteggio()) ?>/5</strong>
                                    </div>
                                    <?php if ($autoreNome !== ''): ?><p>Recensito da <?= V::e($autoreNome) ?></p><?php endif; ?>
                                    <time datetime="<?= V::e($recensione->getDataRecensione()) ?>"><?= V::e($recensione->getDataRecensione()) ?></time>
                                </div>
                            </header>

                            <?php if ($recensione->getCommento() !== ''): ?>
                                <p class="review-comment"><?= V::e($recensione->getCommento()) ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="detail-tab-panel" id="chef-chi-sono" data-tab-panel hidden>
            <h2>Chi Sono</h2>
            <p class="lead"><?= V::e($chef->getBiografia() ?: 'Lo chef non ha ancora pubblicato una biografia estesa.') ?></p>
            <div class="chef-about-grid">
                <div class="about-card">
                    <span class="detail-info-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M8 21h8"/><path d="M12 17v4"/><path d="M7 4h10v6a5 5 0 0 1-10 0V4Z"/><path d="M5 6H3v3a4 4 0 0 0 4 4"/><path d="M19 6h2v3a4 4 0 0 1-4 4"/></svg>
                    </span>
                    <h3>Certificazioni</h3>
                    <ul class="clean-list">
                        <?php foreach ($certificazioni as $certificazione): ?>
                            <li><?= V::e($certificazione->getTipo() !== '' ? $certificazione->getTipo() : 'Certificazione') ?></li>
                        <?php endforeach; ?>
                        <?php if ($certificazioni === []): ?><li>Certificazioni non ancora pubblicate</li><?php endif; ?>
                    </ul>
                </div>
                <div class="about-card">
                    <span class="detail-info-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </span>
                    <h3>Esperienza</h3>
                    <strong><?= V::e($chef->getAnniEsperienza()) ?>+ anni</strong>
                    <p>di esperienza professionale<?= $chef->getTipologiaCucina() !== '' ? ' in ' . V::e($chef->getTipologiaCucina()) : '' ?></p>
                </div>
            </div>

            <div class="gallery-heading" id="profilo-gallery">
                <h2>Galleria</h2>
                <?php if ($canManageGallery): ?>
                    <form class="gallery-upload-form" method="post" action="<?= V::e(V::url('/dashboard/chef/media')) ?>" enctype="multipart/form-data">
                        <input type="hidden" name="azione" value="carica">
                        <input type="hidden" name="returnTo" value="<?= V::e('/chef/' . (int) $chef->getIdChef() . '#profilo-gallery') ?>">
                        <label class="gallery-add-button" title="Aggiungi foto">
                            <span aria-hidden="true">+</span>
                            <input type="file" name="media" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required onchange="this.form.requestSubmit()">
                        </label>
                    </form>
                <?php endif; ?>
            </div>
            <div class="detail-gallery">
                <?php foreach ($chefGalleryItems as $item): ?>
                    <?php
                    $galleryImage = is_string($item)
                        ? $item
                        : V::mediaUrl($item, 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=900');
                    $galleryAlt = is_object($item) && method_exists($item, 'getDescrizione') && $item->getDescrizione() !== ''
                        ? $item->getDescrizione()
                        : $chef->getNome() . ' ' . $chef->getCognome();
                    ?>
                    <figure class="gallery-item">
                        <button type="button" data-gallery-open data-gallery-src="<?= V::e($galleryImage) ?>" data-gallery-alt="<?= V::e($galleryAlt) ?>">
                            <img src="<?= V::e($galleryImage) ?>" alt="<?= V::e($galleryAlt) ?>" loading="lazy">
                        </button>
                        <?php if ($canManageGallery && is_object($item) && method_exists($item, 'getIdMedia')): ?>
                            <form method="post" action="<?= V::e(V::url('/dashboard/chef/media')) ?>" class="gallery-delete-form">
                                <input type="hidden" name="azione" value="rimuovi">
                                <input type="hidden" name="idMedia" value="<?= V::e((int) $item->getIdMedia()) ?>">
                                <input type="hidden" name="returnTo" value="<?= V::e('/chef/' . (int) $chef->getIdChef() . '#profilo-gallery') ?>">
                                <button type="submit" aria-label="Elimina foto">
                                    <svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>
                                </button>
                            </form>
                        <?php endif; ?>
                    </figure>
                <?php endforeach; ?>
            </div>
        </section>
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

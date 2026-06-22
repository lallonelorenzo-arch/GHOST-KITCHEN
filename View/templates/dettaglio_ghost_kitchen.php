<?php
use ViewHelpers as V;
/** @var EGhostKitchen $ghostKitchen */
/** @var array $attrezzature */
/** @var array $recensioni */
/** @var array $autoriRecensioni */
/** @var array $disponibilitaPubbliche */
/** @var array $availabilityPayload */
/** @var mixed $mediaPrincipale */
/** @var array $media */
/** @var EGestore|null $gestore */
/** @var string|null $tipoRichiedente */
/** @var bool $ghostKitchenPrenotabile */
/** @var bool $canManageGallery */
/** @var string|null $erroreForm */
/** @var string|null $messaggioSuccesso */
$image = V::mediaUrl($mediaPrincipale ?? null, 'https://images.unsplash.com/photo-1767785990437-dfe1fe516fe8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200');
$rating = $ghostKitchen->getValutazioneMedia();
$gestore = $gestore ?? null;
$media = array_values(array_filter($media ?? [], static fn (EMedia $item): bool => $item->getStato() === EMedia::STATO_ATTIVO));
$recensioni = array_values(array_filter($recensioni ?? [], static fn (ERecensioneGhostKitchen $recensione): bool => $recensione->isVisibile()));
$autoriRecensioni = $autoriRecensioni ?? [];
$availabilityPayload = $availabilityPayload ?? [];
$tipoRichiedente = $tipoRichiedente ?? null;
$ghostKitchenPrenotabile = $ghostKitchenPrenotabile ?? false;
$canManageGallery = $canManageGallery ?? false;
$erroreForm = $erroreForm ?? null;
$messaggioSuccesso = $messaggioSuccesso ?? null;
$galleryFallbacks = [
    'https://images.unsplash.com/photo-1556911220-bff31c812dba?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=900',
    'https://images.unsplash.com/photo-1556910103-1c02745aae4d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=900',
    'https://images.unsplash.com/photo-1600891964599-f61ba0e24092?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=900',
];
$galleryItems = $media !== [] ? $media : $galleryFallbacks;
?>
<section class="detail-hero" style="background-image: linear-gradient(0deg, rgba(44,24,16,.9), rgba(44,24,16,.25)), url('<?= V::e($image) ?>')">
    <a class="back-link" href="<?= V::e(V::url('/ricerca/ghost-kitchen')) ?>">Torna alle cucine</a>
    <div>
        <span class="badge rating-badge"><span class="stars"><?= V::stars($rating) ?></span> <?= V::e($rating) ?> / 5</span>
        <h1><?= V::e($ghostKitchen->getNome()) ?></h1>
        <p><?= V::e($ghostKitchen->getIndirizzo() . ', ' . $ghostKitchen->getCitta()) ?></p>
    </div>
</section>

<section class="section detail-layout">
    <article>
        <?php if (!empty($messaggioSuccesso)): ?><div class="notice"><?= V::e($messaggioSuccesso) ?></div><?php endif; ?>
        <?php if (!empty($erroreForm)): ?><div class="alert"><?= V::e($erroreForm) ?></div><?php endif; ?>

        <h2>Descrizione</h2>
        <p class="lead"><?= V::e($ghostKitchen->getDescrizione()) ?></p>
        <div class="detail-info-grid">
            <div class="detail-info-card">
                <span class="detail-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <strong><?= V::e($ghostKitchen->getCapienza()) ?></strong>
                <span>Persone</span>
            </div>
            <div class="detail-info-card">
                <span class="detail-info-icon" aria-hidden="true">
                    m&sup2;
                </span>
                <strong><?= V::e(V::money($ghostKitchen->getMq())) ?></strong>
                <span>Metratura</span>
            </div>
        </div>

        <div class="gallery-heading" id="profilo-gallery">
            <h2>Galleria</h2>
            <?php if ($canManageGallery): ?>
                <form class="gallery-upload-form" method="post" action="<?= V::e(V::url('/dashboard/gestore/media')) ?>" enctype="multipart/form-data">
                    <input type="hidden" name="azione" value="carica">
                    <input type="hidden" name="idGhostKitchen" value="<?= V::e((int) $ghostKitchen->getId()) ?>">
                    <input type="hidden" name="returnTo" value="<?= V::e('/ghost-kitchen/' . (int) $ghostKitchen->getId() . '#profilo-gallery') ?>">
                    <label class="gallery-add-button" title="Aggiungi foto">
                        <span aria-hidden="true">+</span>
                        <input type="file" name="media" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required onchange="this.form.requestSubmit()">
                    </label>
                </form>
            <?php endif; ?>
        </div>
        <div class="detail-gallery">
            <?php foreach ($galleryItems as $item): ?>
                <?php
                $galleryImage = is_string($item)
                    ? $item
                    : V::mediaUrl($item, 'https://images.unsplash.com/photo-1556911220-bff31c812dba?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=900');
                $galleryAlt = is_object($item) && method_exists($item, 'getDescrizione') && $item->getDescrizione() !== ''
                    ? $item->getDescrizione()
                    : $ghostKitchen->getNome();
                ?>
                <figure class="gallery-item">
                    <button type="button" data-gallery-open data-gallery-src="<?= V::e($galleryImage) ?>" data-gallery-alt="<?= V::e($galleryAlt) ?>">
                        <img src="<?= V::e($galleryImage) ?>" alt="<?= V::e($galleryAlt) ?>" loading="lazy">
                    </button>
                    <?php if ($canManageGallery && is_object($item) && method_exists($item, 'getIdMedia')): ?>
                        <form method="post" action="<?= V::e(V::url('/dashboard/gestore/media')) ?>" class="gallery-delete-form">
                            <input type="hidden" name="azione" value="rimuovi">
                            <input type="hidden" name="idGhostKitchen" value="<?= V::e((int) $ghostKitchen->getId()) ?>">
                            <input type="hidden" name="idMedia" value="<?= V::e((int) $item->getIdMedia()) ?>">
                            <input type="hidden" name="returnTo" value="<?= V::e('/ghost-kitchen/' . (int) $ghostKitchen->getId() . '#profilo-gallery') ?>">
                            <button type="submit" aria-label="Elimina foto">
                                <svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>
                            </button>
                        </form>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </div>

        <nav class="detail-tabs" aria-label="Sezioni ghost kitchen" data-tabs>
            <button class="is-active" type="button" data-tab-button="gk-attrezzature" aria-controls="gk-attrezzature" aria-selected="true">Attrezzature</button>
            <button type="button" data-tab-button="gk-recensioni" aria-controls="gk-recensioni" aria-selected="false">Recensioni</button>
        </nav>

        <section class="detail-tab-panel is-active" id="gk-attrezzature" data-tab-panel>
            <h2>Attrezzature</h2>
            <div class="card-grid two compact">
                <?php foreach ($attrezzature as $attrezzatura): ?>
                    <div class="panel equipment-card">
                        <h3><?= V::e($attrezzatura->getNome()) ?></h3>
                        <p><?= V::e($attrezzatura->getCategoria()) ?> - quantita <?= V::e($attrezzatura->getQuantita()) ?></p>
                        <small><?= V::e($attrezzatura->getDescrizione()) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($attrezzature === []): ?><div class="empty-state">Nessuna attrezzatura pubblicata.</div><?php endif; ?>
        </section>

        <section class="detail-tab-panel" id="gk-recensioni" data-tab-panel hidden>
            <h2>Recensioni</h2>
            <?php if ($recensioni === []): ?>
                <div class="empty-state">Nessuna recensione pubblicata per questa ghost kitchen.</div>
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
    </article>
    <aside class="booking-box">
        <span class="booking-label">Prenotazione cucina</span>
        <strong>&euro; <?= V::e(V::money($ghostKitchen->getPrezzoOrario())) ?></strong>
        <span>per ora</span>
        <div class="booking-actions">
            <button class="btn btn-accent" type="button" data-modal-open="gk-booking-dialog">Prenota ora</button>
            <button class="btn btn-ghost" type="button" data-modal-open="gestore-contact-modal">Contatta Gestore</button>
            <a class="btn btn-ghost" href="<?= V::e(V::url('/segnalazione/ghost-kitchen/' . $ghostKitchen->getId())) ?>">Segnala cucina</a>
        </div>
    </aside>
</section>

<dialog class="chef-booking-dialog" id="gk-booking-dialog" aria-labelledby="gk-booking-title">
    <?php if (!$ghostKitchenPrenotabile || $tipoRichiedente === null): ?>
        <div class="booking-detail-box">
            <header>
                <div>
                    <span>Prenotazione cucina</span>
                    <h2 id="gk-booking-title">Non puoi ancora procedere</h2>
                </div>
                <button type="button" class="modal-close-button" data-modal-close aria-label="Chiudi">&times;</button>
            </header>
            <p><?= !$ghostKitchenPrenotabile
                ? 'Questa ghost kitchen non e prenotabile finche stato, gestore e certificazioni non risultano validi.'
                : 'Accedi come cliente o chef per inviare una richiesta di prenotazione.' ?></p>
            <?php if ($tipoRichiedente === null): ?><a class="btn btn-accent" href="<?= V::e(V::url('/login')) ?>">Accedi</a><?php endif; ?>
        </div>
    <?php else: ?>
        <form
            class="chef-booking-wizard gk-booking-wizard"
            method="post"
            action="<?= V::e(V::url('/prenotazione/ghost-kitchen/' . $ghostKitchen->getId())) ?>"
            data-gk-booking
            data-hour-price="<?= V::e(number_format($ghostKitchen->getPrezzoOrario(), 2, '.', '')) ?>"
            data-gk-name="<?= V::e($ghostKitchen->getNome()) ?>"
        >
            <header class="chef-booking-header">
                <div>
                    <span>Prenotazione cucina</span>
                    <h2 id="gk-booking-title"><?= V::e($ghostKitchen->getNome()) ?></h2>
                </div>
                <button type="button" class="modal-close-button" data-modal-close aria-label="Chiudi prenotazione">&times;</button>
            </header>

            <div class="chef-booking-progress" aria-label="Avanzamento prenotazione">
                <span class="is-active" data-gk-step-indicator="1"><b>1</b> Data e orario</span>
                <span data-gk-step-indicator="2"><b>2</b> Conferma</span>
            </div>

            <input type="hidden" name="dataServizio" data-gk-date>
            <input type="hidden" name="oraInizio" data-gk-start>
            <input type="hidden" name="oraFine" data-gk-end>

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
                        <label>Inizio <select data-gk-start-select></select></label>
                        <label>Fine <select data-gk-end-select></select></label>
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
                    <textarea name="note" rows="4" data-gk-notes></textarea>
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
    <?php endif; ?>
</dialog>

<dialog class="booking-detail-modal contact-modal" id="gestore-contact-modal" aria-labelledby="gestore-contact-title">
    <div class="booking-detail-box">
        <header>
            <div>
                <span>Recapiti gestore</span>
                <h2 id="gestore-contact-title"><?= V::e($gestore !== null ? trim($gestore->getNome() . ' ' . $gestore->getCognome()) : 'Gestore') ?></h2>
            </div>
            <button type="button" class="modal-close-button" data-modal-close aria-label="Chiudi contatti">&times;</button>
        </header>
        <dl class="contact-detail-list">
            <div>
                <dt>Email</dt>
                <dd>
                    <?php if ($gestore !== null && $gestore->getEmail() !== ''): ?>
                        <a href="mailto:<?= V::e($gestore->getEmail()) ?>"><?= V::e($gestore->getEmail()) ?></a>
                    <?php else: ?>
                        Non disponibile
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt>Telefono</dt>
                <dd>
                    <?php if ($gestore !== null && $gestore->getTelefono() !== ''): ?>
                        <a href="tel:<?= V::e($gestore->getTelefono()) ?>"><?= V::e($gestore->getTelefono()) ?></a>
                    <?php else: ?>
                        Non disponibile
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt>Ghost kitchen</dt>
                <dd><?= V::e($ghostKitchen->getNome()) ?></dd>
            </div>
        </dl>
    </div>
</dialog>

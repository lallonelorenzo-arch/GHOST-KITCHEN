<?php
use ViewHelpers as V;
/** @var EChef $chef */
/** @var mixed $fotoProfilo */
/** @var array $menu */
/** @var array $certificazioni */
$image = V::mediaUrl($fotoProfilo ?? null, 'https://images.unsplash.com/photo-1750943082012-efe6d2fd9e45?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200');
$rating = $chef->getValutazioneMedia();
?>
<section class="detail-hero" style="background-image: linear-gradient(0deg, rgba(44,24,16,.9), rgba(44,24,16,.25)), url('<?= V::e($image) ?>')">
    <a class="back-link" href="<?= V::e(V::url('/ricerca/chef')) ?>">Torna alla ricerca</a>
    <div>
        <span class="badge rating-badge"><span class="stars"><?= V::stars($rating) ?></span> <?= V::e($rating) ?> / 5</span>
        <h1><?= V::e($chef->getNome() . ' ' . $chef->getCognome()) ?></h1>
        <p><?= V::e($chef->getSpecializzazione() ?: $chef->getTipologiaCucina()) ?></p>
    </div>
</section>

<section class="section detail-layout">
    <article>
        <h2>Chi sono</h2>
        <p class="lead"><?= V::e($chef->getBiografia() ?: 'Lo chef non ha ancora pubblicato una biografia estesa.') ?></p>
        <div class="detail-chips">
            <span><?= V::e($chef->getTipologiaCucina() ?: 'Cucina non specificata') ?></span>
            <span><?= V::e($chef->getNumeroRecensioni()) ?> recensioni</span>
            <span><?= V::e($chef->getValutazioneMedia()) ?>/5</span>
        </div>

        <h2>Menu disponibili</h2>
        <div class="stack">
            <?php foreach ($menu as $menuItem): ?>
                <?php $m = $menuItem['menu']; ?>
                <div class="panel">
                    <div class="panel-title">
                        <h3><?= V::e($m->getNome()) ?></h3>
                        <strong>&euro; <?= V::e(V::money($m->getPrezzoPersona())) ?></strong>
                    </div>
                    <p><?= V::e($m->getDescrizione()) ?></p>
                    <?php if (($menuItem['piatti'] ?? []) !== []): ?>
                        <ul class="clean-list">
                            <?php foreach ($menuItem['piatti'] as $piattoData): ?>
                                <?php $piatto = $piattoData['piatto']; ?>
                                <li><?= V::e($piatto->getCategoria()) ?>: <?= V::e($piatto->getNome()) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if ($menu === []): ?>
                <div class="empty-state">Nessun menu pubblicato per questo chef.</div>
            <?php endif; ?>
        </div>

        <h2>Certificazioni</h2>
        <div class="tag-row">
            <?php foreach ($certificazioni as $certificazione): ?>
                <span><?= V::e(method_exists($certificazione, 'getNome') ? $certificazione->getNome() : 'Certificazione') ?></span>
            <?php endforeach; ?>
            <?php if ($certificazioni === []): ?><span>Certificazioni non ancora pubblicate</span><?php endif; ?>
        </div>
    </article>
    <aside class="booking-box">
        <span class="booking-label">Prenotazione chef</span>
        <p>A partire da</p>
        <strong>&euro; <?= V::e(V::money($chef->getPrezzoBase())) ?></strong>
        <span>per esperienza</span>
        <div class="booking-actions">
            <a class="btn btn-accent" href="<?= V::e(V::url('/prenotazione/chef/' . $chef->getIdChef())) ?>">Prenota ora</a>
            <button class="btn btn-ghost" type="button" data-modal-open="chef-contact-modal">Contatta Chef</button>
            <a class="btn btn-ghost" href="<?= V::e(V::url('/segnalazione/chef/' . $chef->getIdChef())) ?>">Segnala profilo</a>
        </div>
    </aside>
</section>

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
                <dt>Localita</dt>
                <dd><?= V::e($chef->getLocalita() !== '' ? $chef->getLocalita() : 'Non indicata') ?></dd>
            </div>
        </dl>
    </div>
</dialog>

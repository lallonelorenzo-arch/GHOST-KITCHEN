<?php
use ViewHelpers as V;
/** @var EGhostKitchen $ghostKitchen */
/** @var array $attrezzature */
/** @var array $disponibilitaPubbliche */
/** @var mixed $mediaPrincipale */
$image = V::mediaUrl($mediaPrincipale ?? null, 'https://images.unsplash.com/photo-1767785990437-dfe1fe516fe8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200');
$rating = $ghostKitchen->getValutazioneMedia();
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
        <h2>Descrizione</h2>
        <p class="lead"><?= V::e($ghostKitchen->getDescrizione()) ?></p>
        <div class="stats">
            <div><strong><?= V::e($ghostKitchen->getCapienza()) ?></strong><span>Persone</span></div>
            <div><strong><?= V::e($ghostKitchen->getMq()) ?></strong><span>m<sup>2</sup></span></div>
            <div><strong><?= V::e($ghostKitchen->getStato()) ?></strong><span>Stato</span></div>
        </div>

        <h2>Attrezzature</h2>
        <div class="card-grid two compact">
            <?php foreach ($attrezzature as $attrezzatura): ?>
                <div class="panel">
                    <h3><?= V::e($attrezzatura->getNome()) ?></h3>
                    <p><?= V::e($attrezzatura->getCategoria()) ?> - quantita <?= V::e($attrezzatura->getQuantita()) ?></p>
                    <small><?= V::e($attrezzatura->getDescrizione()) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($attrezzature === []): ?><div class="empty-state">Nessuna attrezzatura pubblicata.</div><?php endif; ?>
    </article>
    <aside class="booking-box">
        <span class="booking-label">Prenotazione cucina</span>
        <p>A partire da</p>
        <strong>&euro; <?= V::e(V::money($ghostKitchen->getPrezzoOrario())) ?></strong>
        <span>per ora</span>
        <div class="slots">
            <?php foreach (array_slice($disponibilitaPubbliche, 0, 4) as $slot): ?>
                <div><span><?= V::e($slot->getData()) ?> <?= V::e($slot->getOraInizio()) ?></span><b><?= V::e($slot->getStato()) ?></b></div>
            <?php endforeach; ?>
            <?php if ($disponibilitaPubbliche === []): ?>
                <div><span>Nessuno slot pubblicato</span><b>Da confermare</b></div>
            <?php endif; ?>
        </div>
        <div class="booking-actions">
            <a class="btn btn-accent" href="<?= V::e(V::url('/prenotazione/ghost-kitchen/' . $ghostKitchen->getId())) ?>">Prenota ora</a>
            <a class="btn btn-ghost" href="<?= V::e(V::url('/login')) ?>">Contatta Gestore</a>
            <a class="btn btn-ghost" href="<?= V::e(V::url('/segnalazione/ghost-kitchen/' . $ghostKitchen->getId())) ?>">Segnala cucina</a>
        </div>
    </aside>
</section>

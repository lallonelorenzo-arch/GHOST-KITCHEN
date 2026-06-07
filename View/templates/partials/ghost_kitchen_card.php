<?php
use ViewHelpers as V;
/** @var EGhostKitchen $ghostKitchen */
$kitchenImage = 'https://images.unsplash.com/photo-1767785990437-dfe1fe516fe8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200';
$rating = $ghostKitchen->getValutazioneMedia();
$indirizzo = trim($ghostKitchen->getCitta() . ', ' . $ghostKitchen->getIndirizzo(), ' ,');
?>
<a class="result-card kitchen-card" href="<?= V::e(V::url('/ghost-kitchen/' . (int) $ghostKitchen->getId())) ?>">
    <div class="card-image" style="background-image: url('<?= V::e($kitchenImage) ?>')">
        <span class="price-badge">&euro; <?= V::e(V::money($ghostKitchen->getPrezzoOrario())) ?>/h</span>
        <div class="rating-row">
            <span class="stars" aria-label="Valutazione <?= V::e($rating) ?> su 5"><?= V::stars($rating) ?></span>
            <span><?= V::e($rating) ?> (<?= V::e($ghostKitchen->getNumeroRecensioni()) ?>)</span>
        </div>
    </div>
    <div class="card-body">
        <h3><?= V::e($ghostKitchen->getNome()) ?></h3>
        <span class="location-row">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M12 21s7-5.1 7-11a7 7 0 0 0-14 0c0 5.9 7 11 7 11Z"></path>
                <circle cx="12" cy="10" r="2.6"></circle>
            </svg>
            <?= V::e($indirizzo) ?>
        </span>
        <div class="kitchen-card-stats">
            <span>
                <small>Capacita</small>
                <strong><?= V::e($ghostKitchen->getCapienza()) ?> persone</strong>
            </span>
            <span>
                <small>Metratura</small>
                <strong><?= V::e(V::money($ghostKitchen->getMq())) ?> m&sup2;</strong>
            </span>
            <span>
                <small>Valutazione</small>
                <strong><?= V::e($rating) ?>/5</strong>
            </span>
        </div>
        <div class="result-price-line">A partire da <strong>&euro;<?= V::e(V::money($ghostKitchen->getPrezzoOrario())) ?></strong> /h</div>
    </div>
</a>

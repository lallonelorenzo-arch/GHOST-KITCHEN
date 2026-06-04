<?php
use ViewHelpers as V;
/** @var EGhostKitchen $ghostKitchen */
$kitchenImage = 'https://images.unsplash.com/photo-1767785990437-dfe1fe516fe8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200';
$rating = $ghostKitchen->getValutazioneMedia();
?>
<a class="result-card kitchen-card" href="<?= V::e(V::url('/ghost-kitchen/' . (int) $ghostKitchen->getId())) ?>">
    <div class="card-image wide" style="background-image: url('<?= V::e($kitchenImage) ?>')">
        <span class="price-badge">&euro; <?= V::e(V::money($ghostKitchen->getPrezzoOrario())) ?>/h</span>
    </div>
    <div class="card-body">
        <div class="rating-row">
            <span class="stars" aria-label="Valutazione <?= V::e($rating) ?> su 5"><?= V::stars($rating) ?></span>
            <span><?= V::e($rating) ?>/5</span>
        </div>
        <h3><?= V::e($ghostKitchen->getNome()) ?></h3>
        <p><?= V::e($ghostKitchen->getIndirizzo() . ', ' . $ghostKitchen->getCitta()) ?></p>
        <div class="card-stat-row">
            <span><strong><?= V::e($ghostKitchen->getCapienza()) ?></strong> persone</span>
            <span><strong><?= V::e($ghostKitchen->getMq()) ?></strong> m<sup>2</sup></span>
        </div>
    </div>
</a>

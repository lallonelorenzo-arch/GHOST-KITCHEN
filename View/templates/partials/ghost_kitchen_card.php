<?php
use ViewHelpers as V;
/** @var EGhostKitchen $ghostKitchen */
$kitchenImage = 'https://images.unsplash.com/photo-1767785990437-dfe1fe516fe8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200';
?>
<a class="result-card kitchen-card" href="<?= V::e(V::url('/ghost-kitchen/' . (int) $ghostKitchen->getId())) ?>">
    <div class="card-image wide" style="background-image: url('<?= V::e($kitchenImage) ?>')">
        <span>Euro <?= V::e(V::money($ghostKitchen->getPrezzoOrario())) ?>/h</span>
    </div>
    <div class="card-body">
        <h3><?= V::e($ghostKitchen->getNome()) ?></h3>
        <p><?= V::e($ghostKitchen->getIndirizzo() . ', ' . $ghostKitchen->getCitta()) ?></p>
        <div class="meta">
            <span><?= V::e($ghostKitchen->getCapienza()) ?> persone</span>
            <span><?= V::e($ghostKitchen->getValutazioneMedia()) ?>/5</span>
        </div>
    </div>
</a>

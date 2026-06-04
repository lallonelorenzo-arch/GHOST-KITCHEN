<?php
use ViewHelpers as V;
/** @var EChef $chef */
$chefImage = 'https://images.unsplash.com/photo-1750943082012-efe6d2fd9e45?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800';
?>
<a class="result-card" href="<?= V::e(V::url('/chef/' . (int) $chef->getIdChef())) ?>">
    <div class="card-image" style="background-image: url('<?= V::e($chefImage) ?>')">
        <span>Euro <?= V::e(V::money($chef->getPrezzoBase())) ?></span>
    </div>
    <div class="card-body">
        <h3><?= V::e($chef->getNome() . ' ' . $chef->getCognome()) ?></h3>
        <p><?= V::e($chef->getSpecializzazione() ?: $chef->getTipologiaCucina()) ?></p>
        <div class="meta">
            <span><?= V::e($chef->getTipologiaCucina() ?: 'chef') ?></span>
            <span>Star <?= V::e($chef->getValutazioneMedia()) ?> (<?= V::e($chef->getNumeroRecensioni()) ?>)</span>
        </div>
    </div>
</a>

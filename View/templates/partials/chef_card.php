<?php
use ViewHelpers as V;
/** @var EChef $chef */
$chefImage = 'https://images.unsplash.com/photo-1750943082012-efe6d2fd9e45?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800';
$rating = $chef->getValutazioneMedia();
$localita = $chef->getLocalita();
$descrizione = $chef->getBiografia();
$descrizione = $descrizione !== '' ? $descrizione : 'Chef professionista disponibile per esperienze culinarie personalizzate.';
?>
<a class="result-card" href="<?= V::e(V::url('/chef/' . (int) $chef->getIdChef())) ?>">
    <div class="card-image" style="background-image: url('<?= V::e($chefImage) ?>')">
        <span class="price-badge">&euro; <?= V::e(V::money($chef->getPrezzoBase())) ?></span>
        <div class="rating-row">
            <span class="stars" aria-label="Valutazione <?= V::e($rating) ?> su 5"><?= V::stars($rating) ?></span>
            <span><?= V::e($rating) ?> (<?= V::e($chef->getNumeroRecensioni()) ?>)</span>
        </div>
    </div>
    <div class="card-body">
        <h3><?= V::e($chef->getNome() . ' ' . $chef->getCognome()) ?></h3>
        <p><?= V::e($chef->getSpecializzazione() ?: $chef->getTipologiaCucina()) ?></p>
        <p class="result-description"><?= V::e($descrizione) ?></p>
        <div class="result-card-meta">
            <span class="location-row">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M12 21s7-5.1 7-11a7 7 0 0 0-14 0c0 5.9 7 11 7 11Z"></path>
                    <circle cx="12" cy="10" r="2.6"></circle>
                </svg>
                <?= V::e($localita) ?>
            </span>
            <span class="inline-rating">
                <span class="stars" aria-hidden="true"><?= V::stars($rating) ?></span>
                <?= V::e($rating) ?> (<?= V::e($chef->getNumeroRecensioni()) ?>)
            </span>
        </div>
        <div class="result-price-line">A partire da <strong>&euro;<?= V::e(V::money($chef->getPrezzoBase())) ?></strong> /persona</div>
    </div>
</a>

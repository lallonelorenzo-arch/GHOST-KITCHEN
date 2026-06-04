<?php
use ViewHelpers as V;
/** @var array $ghostKitchen */
/** @var array $filtri */
$numeroGhostKitchen = count($ghostKitchen);
?>
<section class="page-hero">
    <h1>Ghost Kitchen</h1>
    <p>Cucine professionali attrezzate disponibili a ore.</p>
</section>

<section class="section">
    <form class="filter-bar" method="get" action="<?= V::e(V::url('/ricerca/ghost-kitchen')) ?>">
        <input name="localita" value="<?= V::e($filtri['localita'] ?? '') ?>" placeholder="Localita">
        <input name="budgetMax" value="<?= V::e($filtri['budgetMax'] ?? '') ?>" type="number" min="0" placeholder="Prezzo max">
        <input name="valutazioneMin" value="<?= V::e($filtri['valutazioneMin'] ?? '') ?>" type="number" min="0" max="5" placeholder="Valutazione min">
        <button class="btn btn-primary" type="submit">Filtra</button>
    </form>
    <p class="result-count"><?= $numeroGhostKitchen ?> <?= $numeroGhostKitchen === 1 ? 'cucina trovata' : 'cucine trovate' ?></p>
    <div class="card-grid two">
        <?php foreach ($ghostKitchen as $ghostKitchenItem): ?>
            <?php $ghostKitchen = $ghostKitchenItem; ?>
            <?php require __DIR__ . '/partials/ghost_kitchen_card.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php if ($numeroGhostKitchen === 0): ?>
        <div class="empty-state">Nessuna ghost kitchen trovata. Prova a modificare i filtri.</div>
    <?php endif; ?>
</section>

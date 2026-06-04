<?php
use ViewHelpers as V;
/** @var array $chef */
/** @var array $filtri */
$numeroChef = count($chef);
?>
<section class="page-hero">
    <h1>Trova il tuo Chef</h1>
    <p>Scopri chef professionisti per ogni occasione e gusto culinario.</p>
</section>

<section class="section">
    <form class="filter-bar" method="get" action="<?= V::e(V::url('/ricerca/chef')) ?>">
        <input name="tipologiaCucina" value="<?= V::e($filtri['tipologiaCucina'] ?? '') ?>" placeholder="Tipo cucina">
        <input name="budgetMax" value="<?= V::e($filtri['budgetMax'] ?? '') ?>" type="number" min="0" placeholder="Budget max">
        <input name="valutazioneMin" value="<?= V::e($filtri['valutazioneMin'] ?? '') ?>" type="number" min="0" max="5" placeholder="Valutazione min">
        <button class="btn btn-primary" type="submit">Filtra</button>
    </form>
    <p class="result-count"><?= $numeroChef ?> chef <?= $numeroChef === 1 ? 'trovato' : 'trovati' ?></p>
    <div class="card-grid three">
        <?php foreach ($chef as $chefItem): ?>
            <?php $chef = $chefItem; ?>
            <?php require __DIR__ . '/partials/chef_card.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php if ($numeroChef === 0): ?>
        <div class="empty-state">Nessun chef trovato. Prova a modificare i filtri.</div>
    <?php endif; ?>
</section>

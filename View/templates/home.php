<?php
use ViewHelpers as V;
/** @var array $chefInEvidenza */
/** @var array $ghostKitchenInEvidenza */
/** @var array $cucine */
$hero = 'https://images.unsplash.com/photo-1750943081248-833d71a2ab8e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1920';
?>
<section class="hero" style="background-image: linear-gradient(90deg, rgba(44,24,16,.92), rgba(44,24,16,.55), rgba(44,24,16,.1)), url('<?= V::e($hero) ?>')">
    <div class="hero-content">
        <h1>L'arte culinaria <span>a casa tua</span></h1>
        <p>Prenota chef professionisti per eventi esclusivi o affitta una ghost kitchen per le tue creazioni culinarie.</p>
        <div class="actions">
            <a class="btn btn-accent" href="<?= V::e(V::url('/ricerca/chef')) ?>">Trova uno Chef</a>
            <a class="btn btn-light" href="<?= V::e(V::url('/ricerca/ghost-kitchen')) ?>">Esplora Ghost Kitchen</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-heading">
        <div>
            <h2>Chef in Evidenza</h2>
            <p>I migliori chef professionisti selezionati dal database reale.</p>
        </div>
        <a href="<?= V::e(V::url('/ricerca/chef')) ?>">Vedi tutti</a>
    </div>
    <div class="card-grid four">
        <?php foreach ($chefInEvidenza as $chef): ?>
            <?php require __DIR__ . '/partials/chef_card.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php if ($chefInEvidenza === []): ?>
        <div class="empty-state">Nessuno chef in evidenza disponibile.</div>
    <?php endif; ?>
</section>

<section class="section muted">
    <div class="center-heading">
        <h2>Esplora per Cucina</h2>
        <p>Filtra gli chef in base alle preferenze culinarie.</p>
    </div>
    <div class="pill-grid">
        <?php foreach ($cucine as $cucina): ?>
            <a class="pill-card" href="<?= V::e(V::url('/ricerca/chef', ['tipologiaCucina' => strtolower($cucina)])) ?>">
                <strong><?= V::e($cucina) ?></strong>
                <span>cerca chef</span>
            </a>
        <?php endforeach; ?>
    </div>
    <?php if ($cucine === []): ?>
        <div class="empty-state">Nessuna categoria cucina disponibile.</div>
    <?php endif; ?>
</section>

<section class="section dark-band">
    <div>
        <h2>Ghost Kitchen Professionali</h2>
        <p>Cucine attrezzate per ore o giornate, collegate ai dati reali del progetto.</p>
        <a class="btn btn-accent" href="<?= V::e(V::url('/ricerca/ghost-kitchen')) ?>">Scopri le Ghost Kitchen</a>
    </div>
    <div class="card-grid">
        <?php foreach ($ghostKitchenInEvidenza as $ghostKitchen): ?>
            <?php require __DIR__ . '/partials/ghost_kitchen_card.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php if ($ghostKitchenInEvidenza === []): ?>
        <div class="empty-state">Nessuna ghost kitchen in evidenza disponibile.</div>
    <?php endif; ?>
</section>

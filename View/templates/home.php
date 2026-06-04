<?php
use ViewHelpers as V;
/** @var array $chefInEvidenza */
/** @var array $ghostKitchenInEvidenza */
/** @var array $cucine */
?>
<section class="hero brand-hero">
    <div class="hero-content">
        <span class="eyebrow">Chef privati e cucine professionali</span>
        <h1>Ghost Kitchen per esperienze food <span>memorabili</span></h1>
        <p>Prenota chef selezionati e cucine attrezzate con un'esperienza piu decisa, veloce e costruita intorno al mondo food.</p>
        <div class="actions">
            <a class="btn btn-accent" href="<?= V::e(V::url('/ricerca/chef')) ?>">Trova uno Chef</a>
            <a class="btn btn-light" href="<?= V::e(V::url('/ricerca/ghost-kitchen')) ?>">Esplora Ghost Kitchen</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-heading">
        <div>
            <h2>Chef in evidenza</h2>
            <p>Professionisti disponibili per cene private, degustazioni e servizi personalizzati.</p>
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
        <h2>Esplora per cucina</h2>
        <p>Parti dal tipo di esperienza che hai in mente e restringi la ricerca in pochi passaggi.</p>
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
        <h2>Ghost kitchen professionali</h2>
        <p>Spazi attrezzati per produzione, delivery, eventi e preparazioni ad alta intensita operativa.</p>
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

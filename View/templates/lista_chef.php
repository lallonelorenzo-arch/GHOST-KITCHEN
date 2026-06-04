<?php
use ViewHelpers as V;
/** @var array $chef */
/** @var array $filtri */
$numeroChef = count($chef);
$tipologiaSelezionata = (string) ($filtri['tipologiaCucina'] ?? '');
$budgetSelezionato = ((float) ($filtri['budgetMax'] ?? 0) > 0) ? (string) $filtri['budgetMax'] : '';
$valutazioneSelezionata = ((int) ($filtri['valutazioneMin'] ?? 0) > 0) ? (string) $filtri['valutazioneMin'] : '';
?>
<section class="page-hero">
    <h1>Trova il tuo Chef</h1>
    <p>Scopri chef professionisti per ogni occasione e gusto culinario.</p>
</section>

<section class="section">
    <form class="figma-filter-panel" method="get" action="<?= V::e(V::url('/ricerca/chef')) ?>">
        <div class="filter-search-row">
            <label class="search-field" aria-label="Cerca chef per cucina o specialita">
                <span class="search-icon" aria-hidden="true">&#128269;</span>
                <input name="tipologiaCucina" value="<?= V::e($tipologiaSelezionata) ?>" placeholder="Cerca per cucina o specialita...">
            </label>
            <button class="filter-button" type="button" data-filter-toggle aria-expanded="true">
                <span aria-hidden="true">&#9661;</span>
                Filtri
                <span class="filter-chevron" aria-hidden="true">&#8963;</span>
            </button>
        </div>

        <div class="filter-collapsible" data-filter-panel>
            <div class="filter-divider"></div>
            <div class="filter-select-grid">
                <label>Tipo di Cucina
                    <select name="tipologiaCucinaPreset" data-filter-copy-to="tipologiaCucina">
                        <option value="">Tutte</option>
                        <?php foreach (['Italiana', 'Mediterranea', 'Giapponese', 'Fusion', 'Vegetariana'] as $tipologia): ?>
                            <option value="<?= V::e($tipologia) ?>" <?= strcasecmp($tipologiaSelezionata, $tipologia) === 0 ? 'selected' : '' ?>><?= V::e($tipologia) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Fascia di Prezzo
                    <select name="budgetMax">
                        <option value="" <?= $budgetSelezionato === '' ? 'selected' : '' ?>>Tutti</option>
                        <option value="50" <?= $budgetSelezionato === '50' ? 'selected' : '' ?>>Fino a 50 euro</option>
                        <option value="100" <?= $budgetSelezionato === '100' ? 'selected' : '' ?>>Fino a 100 euro</option>
                        <option value="200" <?= $budgetSelezionato === '200' ? 'selected' : '' ?>>Fino a 200 euro</option>
                    </select>
                </label>
                <label>Valutazione
                    <select name="valutazioneMin">
                        <option value="" <?= $valutazioneSelezionata === '' ? 'selected' : '' ?>>Tutte</option>
                        <option value="3" <?= $valutazioneSelezionata === '3' ? 'selected' : '' ?>>Da 3 stelle</option>
                        <option value="4" <?= $valutazioneSelezionata === '4' ? 'selected' : '' ?>>Da 4 stelle</option>
                        <option value="5" <?= $valutazioneSelezionata === '5' ? 'selected' : '' ?>>5 stelle</option>
                    </select>
                </label>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" type="submit">Applica filtri</button>
            </div>
        </div>
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

<?php
use ViewHelpers as V;
/** @var array $ghostKitchen */
/** @var array $filtri */
$numeroGhostKitchen = count($ghostKitchen);
$localitaSelezionata = (string) ($filtri['localita'] ?? '');
$budgetSelezionato = ((float) ($filtri['budgetMax'] ?? 0) > 0) ? (string) $filtri['budgetMax'] : '';
$valutazioneSelezionata = ((int) ($filtri['valutazioneMin'] ?? 0) > 0) ? (string) $filtri['valutazioneMin'] : '';
?>
<section class="page-hero">
    <h1>Ghost Kitchen</h1>
    <p>Trova spazi attrezzati per produzione, test menu e servizi temporanei.</p>
</section>

<section class="section">
    <form class="filter-shell" method="get" action="<?= V::e(V::url('/ricerca/ghost-kitchen')) ?>">
        <div class="filter-search-row">
            <label class="search-field" aria-label="Cerca ghost kitchen per citta o indirizzo">
                <span class="search-icon" aria-hidden="true">&#128269;</span>
                <input name="localita" value="<?= V::e($localitaSelezionata) ?>" placeholder="Cerca citta, zona o indirizzo">
            </label>
            <button class="filter-button" type="button" data-filter-toggle aria-expanded="true">
                Filtri
                <span class="filter-chevron" aria-hidden="true">&#8963;</span>
            </button>
        </div>

        <div class="filter-collapsible" data-filter-panel>
            <div class="filter-divider"></div>
            <div class="filter-select-grid">
                <label>Localita
                    <select name="localitaPreset" data-filter-copy-to="localita">
                        <option value="">Tutte</option>
                        <?php foreach (['Roma', 'Milano', 'Torino', 'Napoli', 'Bologna'] as $localita): ?>
                            <option value="<?= V::e($localita) ?>" <?= strcasecmp($localitaSelezionata, $localita) === 0 ? 'selected' : '' ?>><?= V::e($localita) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Fascia di Prezzo
                    <select name="budgetMax">
                        <option value="" <?= $budgetSelezionato === '' ? 'selected' : '' ?>>Tutti</option>
                        <option value="30" <?= $budgetSelezionato === '30' ? 'selected' : '' ?>>Fino a 30 euro/ora</option>
                        <option value="60" <?= $budgetSelezionato === '60' ? 'selected' : '' ?>>Fino a 60 euro/ora</option>
                        <option value="100" <?= $budgetSelezionato === '100' ? 'selected' : '' ?>>Fino a 100 euro/ora</option>
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

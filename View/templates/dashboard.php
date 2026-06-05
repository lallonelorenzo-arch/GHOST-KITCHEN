<?php
use ViewHelpers as V;
/** @var array $filtri */
/** @var array $statistiche */
/** @var string|null $messaggioAccesso */
/** @var string|null $messaggioFiltro */
$filtri = $filtri ?? ['dataDa' => '', 'dataA' => '', 'tipoPrenotazione' => 'tutte', 'periodo' => 'personalizzato'];
$statistiche = $statistiche ?? [];
$prenotazioni = $statistiche['prenotazioni'] ?? [];
$pagamenti = $statistiche['pagamenti'] ?? [];
$recensioni = $statistiche['recensioni'] ?? [];
$moderazione = $statistiche['moderazione'] ?? [];
$periodoAttivo = (string) ($filtri['periodo'] ?? 'personalizzato');
$periodiRapidi = [
    'mese' => 'Mensile',
    'trimestre' => 'Trimestrale',
    'anno' => 'Annuale',
];
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Dashboard</h1>
    <p>Una vista sintetica su prenotazioni, pagamenti, recensioni e attivita da presidiare.</p>
</section>

<section class="section ops-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <?php if (!empty($messaggioFiltro)): ?>
        <div class="alert"><?= V::e($messaggioFiltro) ?></div>
    <?php endif; ?>

    <form class="ops-panel ops-form dashboard-filter-panel" method="get" action="<?= V::e(V::url('/dashboard')) ?>">
        <div class="toolbar">
            <div>
                <h2>Filtri statistiche</h2>
                <p>Restringono le statistiche per data servizio, pagamenti collegati alle prenotazioni e tipologia di prenotazione.</p>
            </div>
        </div>

        <div class="period-shortcuts" aria-label="Periodo statistiche">
            <?php foreach ($periodiRapidi as $value => $label): ?>
                <a class="btn <?= $periodoAttivo === $value ? 'btn-primary' : 'btn-ghost' ?>" href="<?= V::e(V::url('/dashboard', ['periodo' => $value, 'tipoPrenotazione' => $filtri['tipoPrenotazione'] ?? 'tutte'])) ?>"><?= V::e($label) ?></a>
            <?php endforeach; ?>
        </div>

        <input type="hidden" name="periodo" value="personalizzato">
        <div class="ops-form-row">
            <label>Dal giorno
                <input type="date" name="dataDa" value="<?= V::e($filtri['dataDa'] ?? '') ?>">
                <span class="filter-help">Prima data da includere nel calcolo.</span>
            </label>
            <label>Al giorno
                <input type="date" name="dataA" value="<?= V::e($filtri['dataA'] ?? '') ?>">
                <span class="filter-help">Deve essere uguale o successiva alla data iniziale.</span>
            </label>
        </div>
        <label>Tipologia da analizzare
            <select name="tipoPrenotazione">
                <?php foreach (['tutte' => 'Tutte le prenotazioni', 'chef' => 'Solo prenotazioni chef', 'ghost_kitchen' => 'Solo prenotazioni ghost kitchen'] as $value => $label): ?>
                    <option value="<?= V::e($value) ?>" <?= ($filtri['tipoPrenotazione'] ?? 'tutte') === $value ? 'selected' : '' ?>><?= V::e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <span class="filter-help">Il filtro incide su prenotazioni, pagamenti e recensioni collegate.</span>
        </label>
        <div class="filter-actions">
            <button class="btn btn-primary" type="submit">Aggiorna dashboard</button>
            <a class="btn btn-ghost" href="<?= V::e(V::url('/dashboard')) ?>">Azzera filtri</a>
        </div>
    </form>

    <div class="ops-grid dashboard-grid">
        <article class="ops-panel">
            <h2>Prenotazioni</h2>
            <dl class="ops-meta">
                <div><dt>Totali</dt><dd><?= V::e($prenotazioni['prenotazioniTotali'] ?? 0) ?></dd></div>
                <div><dt>Chef</dt><dd><?= V::e($prenotazioni['prenotazioniChef'] ?? 0) ?></dd></div>
                <div><dt>Cucine</dt><dd><?= V::e($prenotazioni['prenotazioniGhostKitchen'] ?? 0) ?></dd></div>
            </dl>
        </article>

        <article class="ops-panel">
            <h2>Pagamenti</h2>
            <dl class="ops-meta">
                <div><dt>Volume</dt><dd>&euro; <?= V::e(V::money((float) ($pagamenti['volumePagamenti'] ?? 0))) ?></dd></div>
                <div><dt>Rimborsi</dt><dd><?= V::e($pagamenti['numeroRimborsi'] ?? 0) ?></dd></div>
                <div><dt>Volume rimborsi</dt><dd>&euro; <?= V::e(V::money((float) ($pagamenti['volumeRimborsi'] ?? 0))) ?></dd></div>
            </dl>
        </article>

        <article class="ops-panel">
            <h2>Recensioni</h2>
            <dl class="ops-meta">
                <div><dt>Chef</dt><dd><?= V::e($recensioni['recensioniChef'] ?? 0) ?></dd></div>
                <div><dt>Cucine</dt><dd><?= V::e($recensioni['recensioniGhostKitchen'] ?? 0) ?></dd></div>
                <div><dt>Top chef</dt><dd><?= V::e($recensioni['chefConValutazioneMigliore']['nome'] ?? 'n/d') ?></dd></div>
            </dl>
        </article>

        <article class="ops-panel">
            <h2>Moderazione</h2>
            <dl class="ops-meta">
                <div><dt>Segnalazioni</dt><dd><?= V::e($moderazione['segnalazioniAperte'] ?? 0) ?></dd></div>
                <div><dt>Certificazioni</dt><dd><?= V::e($moderazione['certificazioniInAttesa'] ?? 0) ?></dd></div>
                <div><dt>Stato</dt><dd>operativo</dd></div>
            </dl>
        </article>
    </div>

    <div class="ops-grid dashboard-grid">
        <section class="ops-panel">
            <h2>Chef piu prenotati</h2>
            <div class="ops-list">
                <?php foreach (($prenotazioni['chefPiuPrenotati'] ?? []) as $item): ?>
                    <div class="ops-list-item">
                        <strong><?= V::e($item['nome'] ?? 'Chef') ?></strong>
                        <span>#<?= V::e($item['idChef'] ?? '') ?></span>
                        <span class="badge"><?= V::e($item['prenotazioni'] ?? 0) ?> prenotazioni</span>
                    </div>
                <?php endforeach; ?>
                <?php if (($prenotazioni['chefPiuPrenotati'] ?? []) === []): ?>
                    <div class="empty-state">Nessun dato chef disponibile per i filtri selezionati.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="ops-panel">
            <h2>Ghost kitchen piu prenotate</h2>
            <div class="ops-list">
                <?php foreach (($prenotazioni['ghostKitchenPiuPrenotate'] ?? []) as $item): ?>
                    <div class="ops-list-item">
                        <strong><?= V::e($item['nome'] ?? 'Ghost kitchen') ?></strong>
                        <span>#<?= V::e($item['idGhostKitchen'] ?? '') ?></span>
                        <span class="badge"><?= V::e($item['prenotazioni'] ?? 0) ?> prenotazioni</span>
                    </div>
                <?php endforeach; ?>
                <?php if (($prenotazioni['ghostKitchenPiuPrenotate'] ?? []) === []): ?>
                    <div class="empty-state">Nessun dato ghost kitchen disponibile per i filtri selezionati.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</section>

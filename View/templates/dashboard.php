<?php
use ViewHelpers as V;
/** @var array $filtri */
/** @var array $statistiche */
/** @var string|null $messaggioAccesso */
$filtri = $filtri ?? ['dataDa' => '', 'dataA' => '', 'tipoPrenotazione' => 'tutte'];
$statistiche = $statistiche ?? [];
$prenotazioni = $statistiche['prenotazioni'] ?? [];
$pagamenti = $statistiche['pagamenti'] ?? [];
$recensioni = $statistiche['recensioni'] ?? [];
$moderazione = $statistiche['moderazione'] ?? [];
?>
<section class="page-hero compact-hero uc-page-hero">
    <span class="badge">UC14</span>
    <h1>Dashboard</h1>
    <p>Statistiche operative su prenotazioni, pagamenti, recensioni e moderazione.</p>
</section>

<section class="section uc-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <form class="uc-panel uc-form" method="get" action="<?= V::e(V::url('/dashboard')) ?>">
        <h2>Filtri</h2>
        <div class="uc-form-row">
            <label>Data da <input type="date" name="dataDa" value="<?= V::e($filtri['dataDa'] ?? '') ?>"></label>
            <label>Data a <input type="date" name="dataA" value="<?= V::e($filtri['dataA'] ?? '') ?>"></label>
        </div>
        <label>Tipo prenotazione
            <select name="tipoPrenotazione">
                <?php foreach (['tutte' => 'Tutte', 'chef' => 'Chef', 'ghost_kitchen' => 'Ghost kitchen'] as $value => $label): ?>
                    <option value="<?= V::e($value) ?>" <?= ($filtri['tipoPrenotazione'] ?? 'tutte') === $value ? 'selected' : '' ?>><?= V::e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn btn-primary" type="submit">Aggiorna</button>
    </form>

    <div class="actions">
        <a class="btn btn-ghost" href="<?= V::e(V::url('/moderazione')) ?>">Moderazione</a>
        <a class="btn btn-ghost" href="<?= V::e(V::url('/certificazioni')) ?>">Certificazioni</a>
    </div>

    <div class="uc-grid dashboard-grid">
        <article class="uc-panel">
            <h2>Prenotazioni</h2>
            <dl class="uc-meta">
                <div><dt>Totali</dt><dd><?= V::e($prenotazioni['prenotazioniTotali'] ?? 0) ?></dd></div>
                <div><dt>Chef</dt><dd><?= V::e($prenotazioni['prenotazioniChef'] ?? 0) ?></dd></div>
                <div><dt>Cucine</dt><dd><?= V::e($prenotazioni['prenotazioniGhostKitchen'] ?? 0) ?></dd></div>
            </dl>
        </article>

        <article class="uc-panel">
            <h2>Pagamenti</h2>
            <dl class="uc-meta">
                <div><dt>Volume</dt><dd>&euro; <?= V::e(V::money((float) ($pagamenti['volumePagamenti'] ?? 0))) ?></dd></div>
                <div><dt>Rimborsi</dt><dd><?= V::e($pagamenti['numeroRimborsi'] ?? 0) ?></dd></div>
                <div><dt>Volume rimborsi</dt><dd>&euro; <?= V::e(V::money((float) ($pagamenti['volumeRimborsi'] ?? 0))) ?></dd></div>
            </dl>
        </article>

        <article class="uc-panel">
            <h2>Recensioni</h2>
            <dl class="uc-meta">
                <div><dt>Chef</dt><dd><?= V::e($recensioni['recensioniChef'] ?? 0) ?></dd></div>
                <div><dt>Cucine</dt><dd><?= V::e($recensioni['recensioniGhostKitchen'] ?? 0) ?></dd></div>
                <div><dt>Top chef</dt><dd><?= V::e($recensioni['chefConValutazioneMigliore']['nome'] ?? 'n/d') ?></dd></div>
            </dl>
        </article>

        <article class="uc-panel">
            <h2>Moderazione</h2>
            <dl class="uc-meta">
                <div><dt>Segnalazioni</dt><dd><?= V::e($moderazione['segnalazioniAperte'] ?? 0) ?></dd></div>
                <div><dt>Certificazioni</dt><dd><?= V::e($moderazione['certificazioniInAttesa'] ?? 0) ?></dd></div>
                <div><dt>Stato</dt><dd>operativo</dd></div>
            </dl>
        </article>
    </div>

    <section class="uc-panel">
        <h2>Ghost kitchen piu prenotate</h2>
        <div class="uc-list">
            <?php foreach (($prenotazioni['ghostKitchenPiuPrenotate'] ?? []) as $item): ?>
                <div class="uc-list-item">
                    <strong><?= V::e($item['nome'] ?? 'Ghost kitchen') ?></strong>
                    <span>#<?= V::e($item['idGhostKitchen'] ?? '') ?></span>
                    <span class="badge"><?= V::e($item['prenotazioni'] ?? 0) ?> prenotazioni</span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</section>

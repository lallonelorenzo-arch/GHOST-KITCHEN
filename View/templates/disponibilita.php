<?php
use ViewHelpers as V;
/** @var array $accesso */
/** @var array|null $calendarioChef */
/** @var array|null $calendarioGhostKitchen */
/** @var int $idGhostKitchen */
/** @var string|null $messaggioAccesso */
/** @var string|null $messaggioGestore */
?>
<section class="page-hero compact-hero uc-page-hero">
    <span class="badge">UC6</span>
    <h1>Gestione disponibilita</h1>
    <p>Aggiungi slot liberi per chef o ghost kitchen.</p>
</section>

<section class="section uc-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <div class="uc-grid">
        <form class="uc-panel uc-form" method="post" action="<?= V::e(V::url('/disponibilita/chef')) ?>">
            <h2>Nuova disponibilita chef</h2>
            <div class="uc-form-row">
                <label>Data <input type="date" name="data" required></label>
                <label>Ora inizio <input type="time" name="oraInizio" required></label>
            </div>
            <label>Ora fine <input type="time" name="oraFine" required></label>
            <button class="btn btn-accent" type="submit">Aggiungi slot chef</button>
        </form>

        <form class="uc-panel uc-form" method="post" action="<?= V::e(V::url('/disponibilita/ghost-kitchen')) ?>">
            <h2>Nuova disponibilita ghost kitchen</h2>
            <?php if (!empty($messaggioGestore)): ?><p class="uc-muted"><?= V::e($messaggioGestore) ?></p><?php endif; ?>
            <label>ID ghost kitchen <input type="number" name="idGhostKitchen" min="1" value="<?= V::e($idGhostKitchen ?? 0) ?>" required></label>
            <div class="uc-form-row">
                <label>Data <input type="date" name="data" required></label>
                <label>Ora inizio <input type="time" name="oraInizio" required></label>
            </div>
            <label>Ora fine <input type="time" name="oraFine" required></label>
            <button class="btn btn-accent" type="submit">Aggiungi slot cucina</button>
        </form>
    </div>

    <div class="uc-grid">
        <section class="uc-panel">
            <h2>Calendario chef</h2>
            <?php $slots = $calendarioChef['disponibilita'] ?? []; ?>
            <?php if ($slots === []): ?><p class="uc-muted">Nessun calendario chef disponibile per il ruolo corrente.</p><?php endif; ?>
            <div class="uc-list">
                <?php foreach ($slots as $slot): ?>
                    <div class="uc-list-item"><strong><?= V::e($slot->getData()) ?></strong><span><?= V::e($slot->getOraInizio()) ?> - <?= V::e($slot->getOraFine()) ?></span><span class="badge"><?= V::e($slot->getStato()) ?></span></div>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="uc-panel">
            <h2>Calendario ghost kitchen</h2>
            <?php $slots = $calendarioGhostKitchen['disponibilita'] ?? []; ?>
            <?php if ($slots === []): ?><p class="uc-muted">Inserisci un ID ghost kitchen nella query o nel form per visualizzare il calendario.</p><?php endif; ?>
            <div class="uc-list">
                <?php foreach ($slots as $slot): ?>
                    <div class="uc-list-item"><strong><?= V::e($slot->getData()) ?></strong><span><?= V::e($slot->getOraInizio()) ?> - <?= V::e($slot->getOraFine()) ?></span><span class="badge"><?= V::e($slot->getStato()) ?></span></div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</section>

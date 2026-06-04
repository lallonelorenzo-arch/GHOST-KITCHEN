<?php
use ViewHelpers as V;
/** @var array $accesso */
/** @var array|null $calendarioChef */
/** @var array|null $calendarioGhostKitchen */
/** @var int $idGhostKitchen */
/** @var array $ghostKitchenGestore */
/** @var string|null $messaggioAccesso */
/** @var string|null $messaggioGestore */
$ghostKitchenGestore = $ghostKitchenGestore ?? [];
$ruoli = is_array(($accesso ?? [])['ruoli'] ?? null) ? $accesso['ruoli'] : [];
$isChef = in_array('chef', $ruoli, true);
$isGestore = in_array('gestore', $ruoli, true);
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Gestione disponibilita</h1>
    <p>Pubblica finestre orarie utilizzabili per chef e cucine gestite.</p>
</section>

<section class="section ops-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <div class="ops-grid">
        <?php if ($isChef): ?>
            <form class="ops-panel ops-form" method="post" action="<?= V::e(V::url('/disponibilita/chef')) ?>">
                <h2>Nuova disponibilita chef</h2>
                <div class="ops-form-row">
                    <label>Data <input type="date" name="data" required></label>
                    <label>Ora inizio <input type="time" name="oraInizio" required></label>
                </div>
                <label>Ora fine <input type="time" name="oraFine" required></label>
                <button class="btn btn-accent" type="submit">Aggiungi slot chef</button>
            </form>
        <?php endif; ?>

        <?php if ($isGestore): ?>
            <form class="ops-panel ops-form" method="post" action="<?= V::e(V::url('/disponibilita/ghost-kitchen')) ?>">
                <h2>Nuova disponibilita ghost kitchen</h2>
                <?php if (!empty($messaggioGestore)): ?><p class="muted-text"><?= V::e($messaggioGestore) ?></p><?php endif; ?>
                <?php if ($ghostKitchenGestore !== []): ?>
                    <label>Ghost kitchen
                        <select name="idGhostKitchen" required>
                            <option value="">Seleziona cucina</option>
                            <?php foreach ($ghostKitchenGestore as $cucina): ?>
                                <option value="<?= V::e($cucina->getId()) ?>" <?= (int) ($idGhostKitchen ?? 0) === (int) $cucina->getId() ? 'selected' : '' ?>>
                                    <?= V::e($cucina->getNome()) ?> - <?= V::e($cucina->getCitta()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                <?php endif; ?>
                <div class="ops-form-row">
                    <label>Data <input type="date" name="data" required></label>
                    <label>Ora inizio <input type="time" name="oraInizio" required></label>
                </div>
                <label>Ora fine <input type="time" name="oraFine" required></label>
                <button class="btn btn-accent" type="submit">Aggiungi slot cucina</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="ops-grid">
        <?php if ($isChef): ?>
            <section class="ops-panel">
                <h2>Calendario chef</h2>
                <?php $slots = $calendarioChef['disponibilita'] ?? []; ?>
                <?php if ($slots === []): ?><p class="muted-text">Nessuna disponibilita chef pubblicata.</p><?php endif; ?>
                <div class="ops-list">
                    <?php foreach ($slots as $slot): ?>
                        <div class="ops-list-item"><strong><?= V::e($slot->getData()) ?></strong><span><?= V::e($slot->getOraInizio()) ?> - <?= V::e($slot->getOraFine()) ?></span><span class="badge"><?= V::e($slot->getStato()) ?></span></div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
        <?php if ($isGestore): ?>
            <section class="ops-panel">
                <h2>Calendario ghost kitchen</h2>
                <?php $slots = $calendarioGhostKitchen['disponibilita'] ?? []; ?>
                <?php if ($slots === []): ?><p class="muted-text"><?= $ghostKitchenGestore === [] ? 'Nessuna ghost kitchen collegata al tuo profilo.' : 'Seleziona una ghost kitchen per visualizzare il calendario.' ?></p><?php endif; ?>
                <div class="ops-list">
                    <?php foreach ($slots as $slot): ?>
                        <div class="ops-list-item"><strong><?= V::e($slot->getData()) ?></strong><span><?= V::e($slot->getOraInizio()) ?> - <?= V::e($slot->getOraFine()) ?></span><span class="badge"><?= V::e($slot->getStato()) ?></span></div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>

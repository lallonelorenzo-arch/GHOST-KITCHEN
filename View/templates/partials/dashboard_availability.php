<?php
use ViewHelpers as V;
/** @var string $availabilityRole */
/** @var array $availabilitySlots */
/** @var array $availabilityGhostKitchens */
/** @var array $availabilityByGhostKitchen */
$availabilityRole = $availabilityRole ?? 'chef';
$availabilitySlots = $availabilitySlots ?? [];
$availabilityGhostKitchens = $availabilityGhostKitchens ?? [];
$availabilityByGhostKitchen = $availabilityByGhostKitchen ?? [];
?>
<section class="dashboard-management">
    <div class="management-heading">
        <div>
            <h2>Disponibilita</h2>
            <p>Pubblica e consulta gli slot disponibili direttamente dalla dashboard.</p>
        </div>
    </div>

    <?php if ($availabilityRole === 'chef'): ?>
        <form class="ops-panel ops-form dashboard-inline-form" method="post" action="<?= V::e(V::url('/disponibilita/chef')) ?>">
            <h3>Nuova disponibilita chef</h3>
            <p class="muted-text">Scegli il giorno e una o entrambe le fasce di servizio.</p>
            <label>Data <input type="date" name="data" min="<?= V::e(date('Y-m-d')) ?>" required></label>
            <div class="service-period-options">
                <label>
                    <input type="checkbox" name="fasce[]" value="pranzo">
                    <span><strong>Pranzo</strong><small>12:00 - 15:00</small></span>
                </label>
                <label>
                    <input type="checkbox" name="fasce[]" value="cena">
                    <span><strong>Cena</strong><small>19:00 - 23:00</small></span>
                </label>
            </div>
            <button class="btn btn-accent" type="submit">Aggiungi disponibilita</button>
        </form>
        <section class="ops-panel">
            <?php
            $calendarSlots = $availabilitySlots;
            $calendarTitle = 'Calendario chef';
            $calendarEmptyText = 'Nessuna disponibilita pubblicata.';
            $calendarSelectable = false;
            include __DIR__ . '/booking_calendar.php';
            ?>
            <div class="availability-actions-list">
                <?php foreach ($availabilitySlots as $slot): ?>
                    <div>
                        <span><?= V::e($slot->getData()) ?> - <?= V::e(ucfirst($slot->getFasciaServizio())) ?> - <?= V::e($slot->getStato()) ?></span>
                        <?php if ($slot->getStato() !== 'occupata'): ?>
                            <?php $action = $slot->getStato() === 'libera' ? 'blocca' : 'libera'; ?>
                            <form method="post" action="<?= V::e(V::url('/disponibilita/chef/' . $slot->getIdDisponibilitaChef() . '/' . $action)) ?>">
                                <button class="btn btn-ghost" type="submit"><?= $action === 'blocca' ? 'Blocca' : 'Riapri' ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php else: ?>
        <?php if ($availabilityGhostKitchens === []): ?>
            <div class="empty-state">Crea una Ghost Kitchen prima di pubblicare disponibilita.</div>
        <?php endif; ?>
        <?php foreach ($availabilityGhostKitchens as $cucina): ?>
            <?php $idCucina = (int) $cucina->getId(); ?>
            <article class="ops-panel availability-kitchen-block">
                <form class="ops-form dashboard-inline-form" method="post" action="<?= V::e(V::url('/disponibilita/ghost-kitchen')) ?>">
                    <input type="hidden" name="idGhostKitchen" value="<?= V::e($idCucina) ?>">
                    <h3><?= V::e($cucina->getNome()) ?></h3>
                    <div class="ops-form-row">
                        <label>Data <input type="date" name="data" required></label>
                        <label>Ora inizio <input type="time" name="oraInizio" required></label>
                    </div>
                    <label>Ora fine <input type="time" name="oraFine" required></label>
                    <button class="btn btn-accent" type="submit">Aggiungi slot</button>
                </form>
                <?php
                $calendarSlots = $availabilityByGhostKitchen[$idCucina] ?? [];
                $calendarTitle = 'Calendario ' . $cucina->getNome();
                $calendarEmptyText = 'Nessuna disponibilita pubblicata per questa cucina.';
                $calendarSelectable = false;
                include __DIR__ . '/booking_calendar.php';
                ?>
                <div class="availability-actions-list">
                    <?php foreach (($availabilityByGhostKitchen[$idCucina] ?? []) as $slot): ?>
                        <div>
                            <span><?= V::e($slot->getData()) ?> - <?= V::e(substr($slot->getOraInizio(), 0, 5)) ?>-<?= V::e(substr($slot->getOraFine(), 0, 5)) ?> - <?= V::e($slot->getStato()) ?></span>
                            <?php if ($slot->getStato() !== 'occupata'): ?>
                                <?php $action = $slot->getStato() === 'libera' ? 'blocca' : 'libera'; ?>
                                <form method="post" action="<?= V::e(V::url('/disponibilita/ghost-kitchen/' . $slot->getIdDisponibilitaGhostKitchen() . '/' . $action)) ?>">
                                    <button class="btn btn-ghost" type="submit"><?= $action === 'blocca' ? 'Blocca' : 'Riapri' ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

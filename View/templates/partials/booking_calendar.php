<?php
use ViewHelpers as V;
/** @var array $calendarSlots */
/** @var string $calendarTitle */
/** @var string $calendarEmptyText */
/** @var bool $calendarSelectable */
$calendarSlots = $calendarSlots ?? [];
$calendarTitle = $calendarTitle ?? 'Calendario';
$calendarEmptyText = $calendarEmptyText ?? 'Nessuno slot disponibile.';
$calendarSelectable = $calendarSelectable ?? false;
$monthFormatter = static function (string $monthKey): string {
    $date = DateTimeImmutable::createFromFormat('!Y-m', $monthKey);
    return $date !== false ? $date->format('m/Y') : $monthKey;
};
$dayFormatter = static function (DateTimeImmutable $date): string {
    $labels = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
    return $labels[((int) $date->format('N')) - 1] . ' ' . $date->format('d');
};
$slotsByMonth = [];
foreach ($calendarSlots as $slot) {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $slot->getData());
    if ($date === false) {
        continue;
    }
    $slotsByMonth[$date->format('Y-m')][$slot->getData()][] = $slot;
}
ksort($slotsByMonth);
?>
<section class="booking-calendar" data-booking-calendar>
    <div class="booking-calendar-header">
        <h2><?= V::e($calendarTitle) ?></h2>
        <div class="booking-calendar-legend" aria-label="Legenda stati disponibilita">
            <span><i class="calendar-dot is-free"></i>Libera</span>
            <span><i class="calendar-dot is-busy"></i>Occupata</span>
            <span><i class="calendar-dot is-blocked"></i>Bloccata</span>
        </div>
    </div>

    <?php if ($slotsByMonth === []): ?>
        <p class="muted-text"><?= V::e($calendarEmptyText) ?></p>
    <?php endif; ?>

    <?php foreach ($slotsByMonth as $monthKey => $slotsByDate): ?>
        <?php
        $firstDay = DateTimeImmutable::createFromFormat('!Y-m-d', $monthKey . '-01');
        if ($firstDay === false) {
            continue;
        }
        $daysInMonth = (int) $firstDay->format('t');
        $offset = ((int) $firstDay->format('N')) - 1;
        ?>
        <article class="booking-calendar-month">
            <h3><?= V::e($monthFormatter($monthKey)) ?></h3>
            <div class="calendar-weekdays" aria-hidden="true">
                <span>Lun</span><span>Mar</span><span>Mer</span><span>Gio</span><span>Ven</span><span>Sab</span><span>Dom</span>
            </div>
            <div class="calendar-grid">
                <?php for ($blank = 0; $blank < $offset; $blank++): ?>
                    <div class="calendar-day is-empty" aria-hidden="true"></div>
                <?php endfor; ?>
                <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                    <?php
                    $date = $firstDay->setDate((int) $firstDay->format('Y'), (int) $firstDay->format('m'), $day);
                    $dateKey = $date->format('Y-m-d');
                    $daySlots = $slotsByDate[$dateKey] ?? [];
                    usort($daySlots, static fn ($a, $b): int => strcmp($a->getOraInizio(), $b->getOraInizio()));
                    ?>
                    <div class="calendar-day <?= $daySlots === [] ? 'has-no-slots' : 'has-slots' ?>">
                        <time datetime="<?= V::e($dateKey) ?>"><?= V::e($dayFormatter($date)) ?></time>
                        <?php foreach ($daySlots as $slot): ?>
                            <?php
                            $isFree = $slot->getStato() === 'libera';
                            $tag = $calendarSelectable && $isFree ? 'button' : 'span';
                            $attrs = $tag === 'button'
                                ? ' type="button" aria-pressed="false" data-slot-select data-date="' . V::e($slot->getData()) . '" data-start="' . V::e($slot->getOraInizio()) . '" data-end="' . V::e($slot->getOraFine()) . '"'
                                : '';
                            $slotLabel = method_exists($slot, 'getFasciaServizio')
                                ? ucfirst($slot->getFasciaServizio())
                                : substr($slot->getOraInizio(), 0, 5) . '-' . substr($slot->getOraFine(), 0, 5);
                            ?>
                            <<?= $tag ?> class="calendar-slot status-<?= V::e($slot->getStato()) ?>"<?= $attrs ?>>
                                <strong><?= V::e($slotLabel) ?></strong>
                                <small><?= V::e($slot->getStato()) ?></small>
                            </<?= $tag ?>>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>

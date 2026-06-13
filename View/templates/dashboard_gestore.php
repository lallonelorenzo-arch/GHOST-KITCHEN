<?php
use ViewHelpers as V;
/** @var array $accesso */
/** @var array $metriche */
/** @var string $tabAttiva */
/** @var array $fatturatoMensile */
/** @var array $prenotazioniSettimanali */
/** @var array $prossimePrenotazioni */
/** @var array $prenotazioniTabella */
/** @var array $richiestePrenotazione */
/** @var array $ghostKitchenGestore */
/** @var array $disponibilitaGhostKitchen */
/** @var array $attrezzatureGhostKitchen */
/** @var string $filtroRichieste */
$nome = trim((string) ($accesso['nome'] ?? ''));
$nome = $nome !== '' ? $nome : 'Gestore';
$filtroRichieste = (string) ($filtroRichieste ?? 'tutte');
$tabs = [
    'panoramica' => 'Panoramica',
    'prenotazioni' => 'Prenotazioni ricevute',
    'richieste' => 'Richieste',
    'ghost_kitchen' => 'Ghost Kitchen',
    'disponibilita' => 'Disponibilita',
];
$fatturatoMensile = $fatturatoMensile ?? [];
$prenotazioniSettimanali = $prenotazioniSettimanali ?? [];
$prossimePrenotazioni = $prossimePrenotazioni ?? [];
$prenotazioniTabella = $prenotazioniTabella ?? [];
$richiestePrenotazione = $richiestePrenotazione ?? [];
$ghostKitchenGestore = $ghostKitchenGestore ?? [];
$disponibilitaGhostKitchen = $disponibilitaGhostKitchen ?? [];
$attrezzatureGhostKitchen = $attrezzatureGhostKitchen ?? [];
$maxFatturatoRaw = max(1, ...array_map(static fn (array $p): float => (float) $p['value'], $fatturatoMensile));
$maxFatturato = max(1000, (int) ceil($maxFatturatoRaw / 1000) * 1000);
$maxSettimana = max(1, ...array_map(static fn (array $p): int => (int) $p['value'], $prenotazioniSettimanali));
$linePoints = [];
$lineChartPoints = [];
foreach ($fatturatoMensile as $index => $point) {
    $x = 58 + ($index * (560 / max(1, count($fatturatoMensile) - 1)));
    $y = 238 - (((float) $point['value'] / $maxFatturato) * 198);
    $linePoints[] = round($x, 2) . ',' . round($y, 2);
    $lineChartPoints[] = ['x' => $x, 'y' => $y, 'point' => $point];
}
$activeRevenueIndex = min(3, max(0, count($lineChartPoints) - 1));
$activeRevenuePoint = $lineChartPoints[$activeRevenueIndex] ?? null;
$requestFilters = [
    'tutte' => 'Tutte',
    'in_attesa' => 'In attesa',
    'accettate' => 'Accettate',
    'rifiutate' => 'Rifiutate',
];
$richiesteFiltrate = array_values(array_filter($richiestePrenotazione, static function (array $item) use ($filtroRichieste): bool {
    $stato = (string) ($item['stato'] ?? '');
    if ($filtroRichieste === 'tutte') {
        return true;
    }
    if ($filtroRichieste === 'accettate') {
        return in_array($stato, ['accettata', 'pagata', 'completata'], true);
    }
    if ($filtroRichieste === 'rifiutate') {
        return $stato === 'rifiutata';
    }

    return $stato === 'in_attesa';
}));
$mesi = [1 => 'gen', 'feb', 'mar', 'apr', 'mag', 'giu', 'lug', 'ago', 'set', 'ott', 'nov', 'dic'];
$formatData = static function (string $data) use ($mesi): string {
    $ts = strtotime($data);
    return $ts === false ? $data : date('j', $ts) . ' ' . ($mesi[(int) date('n', $ts)] ?? date('m', $ts));
};
$formatDataLunga = static function (string $data) use ($mesi): string {
    $ts = strtotime($data);
    $giorni = [1 => 'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'];
    return $ts === false ? $data : ($giorni[(int) date('N', $ts)] ?? '') . ' ' . date('j', $ts) . ' ' . ($mesi[(int) date('n', $ts)] ?? date('m', $ts)) . ' ' . date('Y', $ts);
};
$formatOra = static fn (string $ora): string => substr($ora, 0, 5);
$statusLabels = [
    'in_attesa' => 'In Attesa',
    'accettata' => 'Confermata',
    'pagata' => 'Confermata',
    'completata' => 'Confermata',
    'rifiutata' => 'Rifiutata',
    'cancellata' => 'Cancellata',
];
?>
<section class="chef-dashboard-hero">
    <h1>Dashboard Gestore</h1>
    <p>Benvenuto, <?= V::e($nome) ?>!</p>
</section>

<section class="section chef-dashboard">
    <nav class="chef-dashboard-tabs" aria-label="Sezioni dashboard gestore">
        <?php foreach ($tabs as $key => $label): ?>
            <a class="<?= $tabAttiva === $key ? 'is-active' : '' ?>" href="<?= V::e(V::url('/dashboard', ['ruolo' => 'gestore', 'tab' => $key])) ?>">
                <?= V::e($label) ?>
                <?php if ($key === 'richieste' && (int) ($metriche['richiesteInAttesa'] ?? 0) > 0): ?>
                    <span class="nav-badge"><?= V::e(min((int) $metriche['richiesteInAttesa'], 99)) ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if ($tabAttiva === 'panoramica'): ?>
    <div class="chef-metric-grid">
        <article class="chef-metric-card">
            <span class="metric-icon warm"><svg viewBox="0 0 24 24"><path d="M3 21h18"></path><path d="M5 21V7l8-4v18"></path><path d="M19 21V11l-6-4"></path><path d="M9 9h1M9 13h1M9 17h1M15 13h1M15 17h1"></path></svg></span>
            <strong><?= V::e($metriche['ghostKitchenTotali'] ?? 0) ?></strong>
            <p>Ghost Kitchen</p>
        </article>
        <article class="chef-metric-card">
            <span class="metric-icon sage metric-euro" aria-hidden="true">&euro;</span>
            <strong>&euro;<?= V::e(V::money((float) ($metriche['fatturatoMese'] ?? 0))) ?></strong>
            <p>Fatturato Mese</p>
        </article>
        <article class="chef-metric-card">
            <span class="metric-icon gold"><svg viewBox="0 0 24 24"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.8 1-6.1-4.4-4.3 6.1-.9L12 3Z"></path></svg></span>
            <strong><?= V::e($metriche['valutazioneMedia'] ?? 0) ?></strong>
            <p>Valutazione Media</p>
        </article>
        <article class="chef-metric-card">
            <span class="metric-icon blue"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"></circle><path d="M12 8v5l3 2"></path></svg></span>
            <strong><?= V::e((string) round((float) ($metriche['oreOccupate'] ?? 0))) ?></strong>
            <p>Ore Occupate</p>
        </article>
    </div>

    <div class="chef-dashboard-panels">
        <section class="chef-dashboard-panel chart-panel">
            <h2>Fatturato Mensile</h2>
            <svg class="line-chart revenue-chart" viewBox="0 0 660 300" role="img" aria-label="Fatturato mensile">
                <?php for ($tick = 0; $tick <= 4; $tick++): ?>
                    <?php $value = ($maxFatturato / 4) * $tick; ?>
                    <?php $y = 238 - (($value / $maxFatturato) * 198); ?>
                    <line class="chart-grid-line" x1="58" y1="<?= V::e($y) ?>" x2="620" y2="<?= V::e($y) ?>"></line>
                    <text class="chart-y-label" x="42" y="<?= V::e($y + 5) ?>"><?= V::e((string) (int) $value) ?></text>
                <?php endfor; ?>
                <?php foreach ($lineChartPoints as $point): ?>
                    <line class="chart-grid-line vertical" x1="<?= V::e($point['x']) ?>" y1="40" x2="<?= V::e($point['x']) ?>" y2="238"></line>
                <?php endforeach; ?>
                <line class="chart-axis" x1="58" y1="40" x2="58" y2="238"></line>
                <line class="chart-axis" x1="58" y1="238" x2="620" y2="238"></line>
                <polyline points="<?= V::e(implode(' ', $linePoints)) ?>"></polyline>
                <?php foreach ($lineChartPoints as $index => $point): ?>
                    <circle class="chart-point <?= $index === $activeRevenueIndex ? 'is-active' : '' ?>" tabindex="0" data-chart-point data-label="<?= V::e($point['point']['label']) ?>" data-value="<?= V::e(V::money((float) $point['point']['value'])) ?>" data-x="<?= V::e((string) $point['x']) ?>" data-y="<?= V::e((string) $point['y']) ?>" cx="<?= V::e($point['x']) ?>" cy="<?= V::e($point['y']) ?>" r="<?= $index === $activeRevenueIndex ? '6' : '5' ?>"></circle>
                    <text class="chart-x-label" x="<?= V::e($point['x']) ?>" y="270"><?= V::e($point['point']['label']) ?></text>
                <?php endforeach; ?>
                <?php if ($activeRevenuePoint !== null): ?>
                    <line class="chart-cursor" data-revenue-cursor x1="<?= V::e($activeRevenuePoint['x']) ?>" y1="40" x2="<?= V::e($activeRevenuePoint['x']) ?>" y2="238"></line>
                    <g class="chart-tooltip" data-revenue-tooltip transform="translate(<?= V::e(min(478, max(78, $activeRevenuePoint['x'] + 16))) ?> <?= V::e(max(58, $activeRevenuePoint['y'] - 16)) ?>)">
                        <rect width="150" height="76" rx="10"></rect>
                        <text data-revenue-label x="16" y="28"><?= V::e($activeRevenuePoint['point']['label']) ?></text>
                        <text data-revenue-value class="chart-tooltip-value" x="16" y="56">Ricavo: &euro;<?= V::e(V::money((float) $activeRevenuePoint['point']['value'])) ?></text>
                    </g>
                <?php endif; ?>
            </svg>
        </section>
        <section class="chef-dashboard-panel chart-panel">
            <h2>Prenotazioni Settimanali</h2>
            <div class="bar-chart-shell">
                <div class="bar-y-axis" aria-hidden="true">
                    <?php foreach ([12, 9, 6, 3, 0] as $tick): ?>
                        <span><?= V::e((string) $tick) ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="bar-chart" role="img" aria-label="Prenotazioni settimanali">
                    <?php foreach ($prenotazioniSettimanali as $point): ?>
                        <span tabindex="0" data-bar-point data-label="<?= V::e($point['label']) ?>" data-value="<?= V::e((string) (int) $point['value']) ?>">
                            <i style="height: <?= V::e(28 + (((int) $point['value'] / max(12, $maxSettimana)) * 188)) ?>px"></i>
                            <b><?= V::e($point['label']) ?></b>
                        </span>
                    <?php endforeach; ?>
                </div>
                <div class="bar-tooltip" data-bar-tooltip hidden>
                    <strong data-bar-label></strong>
                    <span data-bar-value></span>
                </div>
            </div>
        </section>
    </div>

    <section class="chef-dashboard-panel upcoming-panel">
        <header>
            <h2>Prossime Prenotazioni</h2>
            <a href="<?= V::e(V::url('/dashboard', ['ruolo' => 'gestore', 'tab' => 'prenotazioni'])) ?>">Vedi tutte &rarr;</a>
        </header>
        <div class="upcoming-list">
            <?php foreach ($prossimePrenotazioni as $item): ?>
                <?php $prenotazione = $item['prenotazione']; ?>
                <article>
                    <span>
                        <strong><?= V::e($item['nome']) ?></strong>
                        <small><?= V::e($item['descrizione']) ?></small>
                    </span>
                    <time>
                        <strong><?= V::e($formatData($prenotazione->getDataServizio())) ?></strong>
                        <small><?= V::e($formatOra($prenotazione->getOraInizio())) ?></small>
                    </time>
                    <em class="request-status status-<?= V::e($item['stato']) ?>"><?= V::e($statusLabels[$item['stato']] ?? $item['stato']) ?></em>
                </article>
            <?php endforeach; ?>
            <?php if ($prossimePrenotazioni === []): ?>
                <div class="empty-state">Nessuna prenotazione imminente.</div>
            <?php endif; ?>
        </div>
    </section>
    <?php elseif ($tabAttiva === 'prenotazioni'): ?>
        <section class="chef-dashboard-panel booking-table-panel">
            <div class="booking-table-wrap">
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>Richiedente</th>
                            <th>Ghost Kitchen</th>
                            <th>Data</th>
                            <th>Dettagli</th>
                            <th>Totale</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($prenotazioniTabella as $item): ?>
                        <?php $prenotazione = $item['prenotazione']; ?>
                        <tr>
                            <td><a class="table-client-link" href="<?= V::e(V::url('/utente/' . (int) $item['richiedenteId'])) ?>"><?= V::e($item['richiedenteNome']) ?></a></td>
                            <td><?= V::e($item['ghostKitchen']) ?></td>
                            <td><strong><?= V::e(date('j/n/Y', strtotime($prenotazione->getDataServizio()) ?: time())) ?></strong><span><?= V::e($formatOra($prenotazione->getOraInizio())) ?></span></td>
                            <td><?= V::e($item['dettagli']) ?></td>
                            <td><strong class="table-total">&euro;<?= V::e(V::money($prenotazione->getImportoTotale())) ?></strong></td>
                            <td><span class="request-status status-<?= V::e($item['stato']) ?>"><?= V::e($statusLabels[$item['stato']] ?? $item['stato']) ?></span></td>
                            <td><button class="table-detail-link" type="button" data-modal-open="gk-booking-detail-<?= V::e((int) $prenotazione->getIdPrenotazione()) ?>">Dettagli</button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php foreach ($prenotazioniTabella as $item): ?>
                <?php $prenotazione = $item['prenotazione']; ?>
                <?php $modalId = 'gk-booking-detail-' . (int) $prenotazione->getIdPrenotazione(); ?>
                <dialog class="booking-detail-modal" id="<?= V::e($modalId) ?>" aria-labelledby="<?= V::e($modalId) ?>-title">
                    <div class="booking-detail-box">
                        <header>
                            <div>
                                <span>Prenotazione #<?= V::e((int) $prenotazione->getIdPrenotazione()) ?></span>
                                <h2 id="<?= V::e($modalId) ?>-title"><?= V::e($item['ghostKitchen']) ?></h2>
                            </div>
                            <button type="button" class="modal-close-button" data-modal-close aria-label="Chiudi dettaglio">&times;</button>
                        </header>
                        <div class="booking-detail-grid">
                            <section>
                                <h3>Richiedente</h3>
                                <dl>
                                    <div><dt>Nome</dt><dd><?= V::e($item['richiedenteNome']) ?></dd></div>
                                    <div><dt>Email</dt><dd><?= V::e($item['richiedenteEmail'] !== '' ? $item['richiedenteEmail'] : 'Non indicata') ?></dd></div>
                                    <div><dt>Telefono</dt><dd><?= V::e($item['richiedenteTelefono'] !== '' ? $item['richiedenteTelefono'] : 'Non indicato') ?></dd></div>
                                    <div><dt>Localita</dt><dd><?= V::e($item['richiedenteLocalita'] !== '' ? $item['richiedenteLocalita'] : 'Non indicata') ?></dd></div>
                                </dl>
                            </section>
                            <section>
                                <h3>Servizio</h3>
                                <dl>
                                    <div><dt>Data</dt><dd><?= V::e($formatDataLunga($prenotazione->getDataServizio())) ?></dd></div>
                                    <div><dt>Orario</dt><dd><?= V::e($formatOra($prenotazione->getOraInizio())) ?> - <?= V::e($formatOra($prenotazione->getOraFine())) ?></dd></div>
                                    <div><dt>Spazio</dt><dd><?= V::e($item['indirizzoGhostKitchen'] !== '' ? $item['indirizzoGhostKitchen'] : 'Non disponibile') ?></dd></div>
                                    <div><dt>Tipo</dt><dd><?= V::e($prenotazione->getTipoRichiedente()) ?></dd></div>
                                    <div><dt>Totale</dt><dd>&euro;<?= V::e(V::money($prenotazione->getImportoTotale())) ?></dd></div>
                                    <div><dt>Stato</dt><dd><span class="request-status status-<?= V::e($item['stato']) ?>"><?= V::e($statusLabels[$item['stato']] ?? $item['stato']) ?></span></dd></div>
                                </dl>
                            </section>
                        </div>
                        <section class="booking-detail-notes">
                            <h3>Note</h3>
                            <p><?= V::e($prenotazione->getNote() !== '' ? $prenotazione->getNote() : 'Nessuna nota aggiuntiva.') ?></p>
                        </section>
                    </div>
                </dialog>
            <?php endforeach; ?>
            <?php if ($prenotazioniTabella === []): ?>
                <div class="empty-state">Nessuna prenotazione ricevuta.</div>
            <?php endif; ?>
        </section>
    <?php elseif ($tabAttiva === 'disponibilita'): ?>
        <?php
        $availabilityRole = 'gestore';
        $availabilityGhostKitchens = $ghostKitchenGestore;
        $availabilityByGhostKitchen = $disponibilitaGhostKitchen;
        include __DIR__ . '/partials/dashboard_availability.php';
        ?>
    <?php elseif ($tabAttiva === 'richieste'): ?>
        <section class="dashboard-requests-panel">
            <header class="requests-header">
                <div>
                    <h1>Richieste di Prenotazione</h1>
                    <p><?= V::e((int) ($metriche['richiesteInAttesa'] ?? 0)) ?> richieste in attesa di risposta</p>
                </div>
                <nav class="requests-tabs" aria-label="Filtro richieste">
                    <?php foreach ($requestFilters as $key => $label): ?>
                        <a class="<?= $filtroRichieste === $key ? 'is-active' : '' ?>" href="<?= V::e(V::url('/dashboard', ['ruolo' => 'gestore', 'tab' => 'richieste', 'filtro' => $key])) ?>"><?= V::e($label) ?></a>
                    <?php endforeach; ?>
                </nav>
            </header>
            <div class="request-list">
                <?php foreach ($richiesteFiltrate as $index => $item): ?>
                    <?php $richiesta = $item['prenotazione']; ?>
                    <?php $isOpen = $index === 0 && $item['stato'] === 'in_attesa'; ?>
                    <article class="request-card <?= $item['stato'] === 'in_attesa' ? 'is-pending' : '' ?> <?= $isOpen ? 'is-open' : '' ?>">
                        <div class="request-card-head" role="button" tabindex="0" data-request-toggle aria-expanded="<?= $isOpen ? 'true' : 'false' ?>">
                            <div class="request-main">
                                <span class="request-avatar"><?= V::e($item['iniziali']) ?></span>
                                <div class="request-copy">
                                    <h2><?= V::e($item['nomeRichiedente']) ?> <span class="request-status status-<?= V::e($item['stato']) ?>"><?= V::e($statusLabels[$item['stato']] ?? $item['stato']) ?></span></h2>
                                    <p><?= V::e($item['descrizione']) ?></p>
                                </div>
                            </div>
                            <div class="request-summary">
                                <strong><?= V::e($formatData($richiesta->getDataServizio())) ?> &middot; <?= V::e($formatOra($richiesta->getOraInizio())) ?></strong>
                                <span><?= V::e($richiesta->getTipoRichiedente()) ?> &middot; &euro;<?= V::e(V::money($richiesta->getImportoTotale())) ?></span>
                            </div>
                            <span class="request-chevron" aria-hidden="true">
                                <svg viewBox="0 0 20 20" focusable="false"><path d="m5.5 7.5 4.5 4.5 4.5-4.5"></path></svg>
                            </span>
                        </div>
                        <div class="request-card-detail" <?= $isOpen ? '' : 'hidden' ?>>
                            <div class="request-detail-grid">
                                <span><svg viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="15" rx="2"></rect><path d="M8 3v4M16 3v4M4 10h16"></path></svg><?= V::e($formatDataLunga($richiesta->getDataServizio())) ?> alle <?= V::e($formatOra($richiesta->getOraInizio())) ?></span>
                                <span><svg viewBox="0 0 24 24"><path d="M12 21s7-5.2 7-11a7 7 0 0 0-14 0c0 5.8 7 11 7 11Z"></path><circle cx="12" cy="10" r="2.5"></circle></svg><?= V::e($item['indirizzo']) ?></span>
                                <span><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-8 0v2"></path><circle cx="12" cy="7" r="4"></circle></svg><?= V::e($richiesta->getTipoRichiedente()) ?></span>
                                <span><svg viewBox="0 0 24 24"><path d="M15 6.5A5 5 0 1 0 15 17M4 10h8M4 14h8"></path></svg>Budget: &euro;<?= V::e(V::money($richiesta->getImportoTotale())) ?></span>
                            </div>
                            <div class="request-message">
                                <span>Messaggio del richiedente</span>
                                <p>"<?= V::e($item['messaggio']) ?>"</p>
                            </div>
                            <small class="request-received"><?= V::e($item['ricevuta']) ?></small>
                            <?php if ($item['stato'] === 'in_attesa'): ?>
                                <div class="request-actions">
                                    <form method="post" action="<?= V::e(V::url('/richieste/ghost-kitchen/' . $richiesta->getIdPrenotazione() . '/accetta')) ?>">
                                        <button class="btn request-accept" type="submit">Accetta richiesta</button>
                                    </form>
                                    <form method="post" action="<?= V::e(V::url('/richieste/ghost-kitchen/' . $richiesta->getIdPrenotazione() . '/rifiuta')) ?>">
                                        <button class="btn request-refuse" type="submit">Rifiuta</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <p class="request-locked">Richiesta gia <?= V::e(strtolower($statusLabels[$item['stato']] ?? $item['stato'])) ?>.</p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if ($richiesteFiltrate === []): ?>
                    <div class="empty-state">Nessuna richiesta trovata.</div>
                <?php endif; ?>
            </div>
        </section>
    <?php elseif ($tabAttiva === 'ghost_kitchen'): ?>
        <section class="dashboard-management">
            <div class="management-heading">
                <div>
                    <h2>Le mie Ghost Kitchen</h2>
                    <p>Crea e gestisci spazi, prezzi, pubblicazione e attrezzature.</p>
                </div>
            </div>
            <form class="ops-panel ops-form management-form" method="post" action="<?= V::e(V::url('/dashboard/gestore/ghost-kitchen')) ?>">
                <input type="hidden" name="azione" value="crea">
                <h3>Nuova Ghost Kitchen</h3>
                <div class="ops-form-row">
                    <label>Nome <input type="text" name="nome" required></label>
                    <label>Prezzo orario <input type="number" name="prezzoOrario" min="0" step="0.01" required></label>
                </div>
                <label>Descrizione <textarea name="descrizione" rows="4" required></textarea></label>
                <div class="ops-form-row">
                    <label>Indirizzo <input type="text" name="indirizzo" required></label>
                    <label>Citta <input type="text" name="citta" required></label>
                </div>
                <div class="ops-form-row management-three-columns">
                    <label>CAP <input type="text" name="cap" required></label>
                    <label>Capienza <input type="number" name="capienza" min="1" required></label>
                    <label>Metri quadri <input type="number" name="mq" min="1" step="0.01" required></label>
                </div>
                <button class="btn btn-accent" type="submit">Crea Ghost Kitchen</button>
            </form>
            <div class="management-card-list">
            <?php foreach ($ghostKitchenGestore as $ghostKitchen): ?>
                <?php $idGhostKitchen = (int) $ghostKitchen->getId(); ?>
                <article class="ops-panel management-composite-card">
                    <form class="ops-form management-form" method="post" action="<?= V::e(V::url('/dashboard/gestore/ghost-kitchen')) ?>">
                        <input type="hidden" name="idGhostKitchen" value="<?= V::e($idGhostKitchen) ?>">
                        <div class="management-card-heading">
                            <h3><?= V::e($ghostKitchen->getNome()) ?></h3>
                            <span class="request-status status-<?= V::e($ghostKitchen->getStato()) ?>"><?= V::e($ghostKitchen->getStato()) ?></span>
                        </div>
                        <div class="ops-form-row">
                            <label>Nome <input type="text" name="nome" value="<?= V::e($ghostKitchen->getNome()) ?>" required></label>
                            <label>Prezzo orario <input type="number" name="prezzoOrario" min="0" step="0.01" value="<?= V::e($ghostKitchen->getPrezzoOrario()) ?>" required></label>
                        </div>
                        <label>Descrizione <textarea name="descrizione" rows="4" required><?= V::e($ghostKitchen->getDescrizione()) ?></textarea></label>
                        <div class="ops-form-row">
                            <label>Indirizzo <input type="text" name="indirizzo" value="<?= V::e($ghostKitchen->getIndirizzo()) ?>" required></label>
                            <label>Citta <input type="text" name="citta" value="<?= V::e($ghostKitchen->getCitta()) ?>" required></label>
                        </div>
                        <div class="ops-form-row management-three-columns">
                            <label>CAP <input type="text" name="cap" value="<?= V::e($ghostKitchen->getCap()) ?>" required></label>
                            <label>Capienza <input type="number" name="capienza" min="1" value="<?= V::e($ghostKitchen->getCapienza()) ?>" required></label>
                            <label>Metri quadri <input type="number" name="mq" min="1" step="0.01" value="<?= V::e($ghostKitchen->getMq()) ?>" required></label>
                        </div>
                        <div class="actions">
                            <button class="btn btn-accent" type="submit" name="azione" value="aggiorna">Salva dati</button>
                            <?php if ($ghostKitchen->getStato() === EGhostKitchen::STATO_ATTIVA): ?>
                                <button class="btn btn-ghost" type="submit" name="azione" value="rimuovi">Rimuovi dalla pubblicazione</button>
                            <?php else: ?>
                                <button class="btn btn-ghost" type="submit" name="azione" value="pubblica">Pubblica</button>
                            <?php endif; ?>
                            <a class="btn btn-ghost" href="<?= V::e(V::url('/ghost-kitchen/' . $idGhostKitchen)) ?>">Pagina pubblica</a>
                        </div>
                    </form>

                    <div class="equipment-management">
                        <h3>Attrezzature</h3>
                        <?php foreach (($attrezzatureGhostKitchen[$idGhostKitchen] ?? []) as $attrezzatura): ?>
                            <form class="equipment-row" method="post" action="<?= V::e(V::url('/dashboard/gestore/attrezzatura')) ?>">
                                <input type="hidden" name="idGhostKitchen" value="<?= V::e($idGhostKitchen) ?>">
                                <input type="hidden" name="idAttrezzatura" value="<?= V::e((int) $attrezzatura->getIdAttrezzatura()) ?>">
                                <input type="text" name="nome" value="<?= V::e($attrezzatura->getNome()) ?>" aria-label="Nome attrezzatura" required>
                                <input type="text" name="categoria" value="<?= V::e($attrezzatura->getCategoria()) ?>" aria-label="Categoria attrezzatura" required>
                                <input type="text" name="descrizione" value="<?= V::e($attrezzatura->getDescrizione()) ?>" aria-label="Descrizione attrezzatura">
                                <input type="number" name="quantita" min="0" value="<?= V::e($attrezzatura->getQuantita()) ?>" aria-label="Quantita" required>
                                <button class="btn btn-ghost" type="submit" name="azione" value="aggiorna">Salva</button>
                                <button class="btn btn-ghost" type="submit" name="azione" value="rimuovi">Rimuovi</button>
                            </form>
                        <?php endforeach; ?>
                        <form class="equipment-row equipment-create-row" method="post" action="<?= V::e(V::url('/dashboard/gestore/attrezzatura')) ?>">
                            <input type="hidden" name="azione" value="crea">
                            <input type="hidden" name="idGhostKitchen" value="<?= V::e($idGhostKitchen) ?>">
                            <input type="text" name="nome" placeholder="Nuova attrezzatura" required>
                            <input type="text" name="categoria" placeholder="Categoria" required>
                            <input type="text" name="descrizione" placeholder="Descrizione">
                            <input type="number" name="quantita" min="0" value="1" required>
                            <button class="btn btn-accent" type="submit">Aggiungi</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if ($ghostKitchenGestore === []): ?>
                <div class="empty-state">Nessuna ghost kitchen collegata al tuo profilo.</div>
            <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</section>

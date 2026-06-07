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
/** @var array $statisticheGestore */
/** @var array $ghostKitchenGestore */
/** @var string $filtroRichieste */
$nome = trim((string) ($accesso['nome'] ?? ''));
$nome = $nome !== '' ? $nome : 'Gestore';
$filtroRichieste = (string) ($filtroRichieste ?? 'tutte');
$tabs = [
    'panoramica' => 'Panoramica',
    'prenotazioni' => 'Prenotazioni',
    'richieste' => 'Richieste',
    'ghost_kitchen' => 'Ghost Kitchen',
    'calendario' => 'Calendario',
    'statistiche' => 'Statistiche',
];
$fatturatoMensile = $fatturatoMensile ?? [];
$prenotazioniSettimanali = $prenotazioniSettimanali ?? [];
$prossimePrenotazioni = $prossimePrenotazioni ?? [];
$prenotazioniTabella = $prenotazioniTabella ?? [];
$richiestePrenotazione = $richiestePrenotazione ?? [];
$statisticheGestore = $statisticheGestore ?? [];
$ghostKitchenGestore = $ghostKitchenGestore ?? [];
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
$statiStatistiche = $statisticheGestore['stati'] ?? [];
$maxStati = max(1, ...array_values(array_map(static fn ($value): int => (int) $value, $statiStatistiche)));
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
                            <span class="request-chevron" aria-hidden="true"><?= $isOpen ? '&#8963;' : '&#8964;' ?></span>
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
        <section class="ghost-kitchen-management-grid">
            <?php foreach ($ghostKitchenGestore as $ghostKitchen): ?>
                <article class="ghost-kitchen-management-card">
                    <header>
                        <h2><?= V::e($ghostKitchen->getNome()) ?></h2>
                        <span class="request-status status-<?= V::e($ghostKitchen->getStato()) ?>"><?= V::e($ghostKitchen->getStato()) ?></span>
                    </header>
                    <p><?= V::e($ghostKitchen->getDescrizione()) ?></p>
                    <dl>
                        <div><dt>Indirizzo</dt><dd><?= V::e($ghostKitchen->getIndirizzo()) ?>, <?= V::e($ghostKitchen->getCitta()) ?></dd></div>
                        <div><dt>Prezzo</dt><dd>&euro;<?= V::e(V::money($ghostKitchen->getPrezzoOrario())) ?>/h</dd></div>
                        <div><dt>Capienza</dt><dd><?= V::e($ghostKitchen->getCapienza()) ?> persone</dd></div>
                        <div><dt>Valutazione</dt><dd><?= V::e($ghostKitchen->getValutazioneMedia()) ?> / 5</dd></div>
                    </dl>
                </article>
            <?php endforeach; ?>
            <?php if ($ghostKitchenGestore === []): ?>
                <div class="empty-state">Nessuna ghost kitchen collegata al tuo profilo.</div>
            <?php endif; ?>
        </section>
    <?php elseif ($tabAttiva === 'statistiche'): ?>
        <section class="chef-stats-layout">
            <div class="chef-metric-grid chef-stats-grid">
                <article class="chef-metric-card">
                    <span class="metric-icon sage" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19V5"></path><path d="M4 19h16"></path><path d="M8 16l3-4 3 2 5-7"></path></svg></span>
                    <strong><?= V::e((string) round((float) ($statisticheGestore['tassoConferma'] ?? 0))) ?>%</strong>
                    <p>Tasso Conferma</p>
                </article>
                <article class="chef-metric-card">
                    <span class="metric-icon warm" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 12h18"></path><path d="M5 12v7h14v-7"></path><path d="M7 12V8a5 5 0 0 1 10 0v4"></path></svg></span>
                    <strong><?= V::e(number_format((float) ($statisticheGestore['orePrenotate'] ?? 0), 0, ',', '.')) ?></strong>
                    <p>Ore Prenotate</p>
                </article>
                <article class="chef-metric-card">
                    <span class="metric-icon gold" aria-hidden="true">&euro;</span>
                    <strong>&euro;<?= V::e(V::money((float) ($statisticheGestore['importoMedio'] ?? 0))) ?></strong>
                    <p>Importo Medio</p>
                </article>
                <article class="chef-metric-card">
                    <span class="metric-icon blue" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"></circle><path d="M12 8v5l3 2"></path></svg></span>
                    <strong><?= V::e(number_format((float) ($statisticheGestore['durataMedia'] ?? 0), 1, ',', '.')) ?>h</strong>
                    <p>Durata Media</p>
                </article>
            </div>
            <div class="chef-dashboard-panels">
                <section class="chef-dashboard-panel chart-panel">
                    <h2>Andamento Ricavi</h2>
                    <svg class="line-chart revenue-chart" viewBox="0 0 660 300" role="img" aria-label="Andamento ricavi">
                        <?php for ($tick = 0; $tick <= 4; $tick++): ?>
                            <?php $value = ($maxFatturato / 4) * $tick; ?>
                            <?php $y = 238 - (($value / $maxFatturato) * 198); ?>
                            <line class="chart-grid-line" x1="58" y1="<?= V::e($y) ?>" x2="620" y2="<?= V::e($y) ?>"></line>
                            <text class="chart-y-label" x="42" y="<?= V::e($y + 5) ?>"><?= V::e((string) (int) $value) ?></text>
                        <?php endfor; ?>
                        <line class="chart-axis" x1="58" y1="40" x2="58" y2="238"></line>
                        <line class="chart-axis" x1="58" y1="238" x2="620" y2="238"></line>
                        <polyline points="<?= V::e(implode(' ', $linePoints)) ?>"></polyline>
                        <?php foreach ($lineChartPoints as $point): ?>
                            <circle cx="<?= V::e($point['x']) ?>" cy="<?= V::e($point['y']) ?>" r="5"></circle>
                            <text class="chart-x-label" x="<?= V::e($point['x']) ?>" y="270"><?= V::e($point['point']['label']) ?></text>
                        <?php endforeach; ?>
                    </svg>
                </section>
                <section class="chef-dashboard-panel">
                    <h2>Stato Prenotazioni</h2>
                    <div class="status-stat-list">
                        <?php foreach ($statiStatistiche as $stato => $totale): ?>
                            <div>
                                <span><?= V::e($statusLabels[$stato] ?? $stato) ?></span>
                                <i><b style="width: <?= V::e((string) (((int) $totale / $maxStati) * 100)) ?>%"></b></i>
                                <strong><?= V::e((string) (int) $totale) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </section>
    <?php else: ?>
        <section class="chef-dashboard-panel">
            <h2><?= V::e($tabs[$tabAttiva] ?? 'Sezione') ?></h2>
            <p class="muted-text">Questo pannello verra configurato nel prossimo passaggio.</p>
        </section>
    <?php endif; ?>
</section>

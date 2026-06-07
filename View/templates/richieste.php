<?php
use ViewHelpers as V;
/** @var array $richiestePrenotazione */
/** @var int $richiesteInAttesa */
/** @var string $filtroRichieste */
/** @var string|null $messaggioAccesso */
$richiestePrenotazione = $richiestePrenotazione ?? [];
$richiesteInAttesa = (int) ($richiesteInAttesa ?? 0);
$filtroRichieste = (string) ($filtroRichieste ?? 'tutte');
$tabs = [
    'tutte' => 'Tutte',
    'in_attesa' => 'In attesa',
    'accettata' => 'Accettate',
    'rifiutata' => 'Rifiutate',
];
$statusLabels = [
    'in_attesa' => 'In attesa',
    'accettata' => 'Accettata',
    'rifiutata' => 'Rifiutata',
    'pagata' => 'Accettata',
    'completata' => 'Accettata',
    'cancellata' => 'Cancellata',
];
$mesi = [1 => 'gen', 'feb', 'mar', 'apr', 'mag', 'giu', 'lug', 'ago', 'set', 'ott', 'nov', 'dic'];
$formatData = static function (string $data) use ($mesi): string {
    $ts = strtotime($data);
    if ($ts === false) {
        return $data;
    }
    return date('j', $ts) . ' ' . ($mesi[(int) date('n', $ts)] ?? date('m', $ts));
};
$formatOra = static fn (string $ora): string => substr($ora, 0, 5);
?>
<section class="section requests-page">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <header class="requests-header">
        <div>
            <h1>Richieste di Prenotazione</h1>
            <p><?= V::e($richiesteInAttesa) ?> <?= $richiesteInAttesa === 1 ? 'richiesta' : 'richieste' ?> in attesa di risposta</p>
        </div>
        <nav class="requests-tabs" aria-label="Filtra richieste">
            <?php foreach ($tabs as $key => $label): ?>
                <a class="<?= $filtroRichieste === $key ? 'is-active' : '' ?>" href="<?= V::e(V::url('/richieste', ['stato' => $key])) ?>"><?= V::e($label) ?></a>
            <?php endforeach; ?>
        </nav>
    </header>

    <div class="request-list">
        <?php foreach ($richiestePrenotazione as $item): ?>
            <?php
                $richiesta = $item['prenotazione'];
                $stato = (string) $item['stato'];
                $label = $statusLabels[$stato] ?? $stato;
                $isPending = $stato === 'in_attesa';
            ?>
            <article class="request-row <?= $isPending ? 'is-pending' : '' ?>">
                <div class="request-main">
                    <span class="request-avatar"><?= V::e($item['iniziali']) ?></span>
                    <div class="request-copy">
                        <h2>
                            <?= V::e($item['nomeRichiedente']) ?>
                            <span class="request-status status-<?= V::e($stato) ?>"><?= V::e($label) ?></span>
                        </h2>
                        <p><?= V::e($item['descrizione']) ?></p>
                    </div>
                </div>
                <div class="request-summary">
                    <strong><?= V::e($formatData($richiesta->getDataServizio())) ?> &middot; <?= V::e($formatOra($richiesta->getOraInizio())) ?></strong>
                    <span><?= V::e($richiesta->getNumeroPersone()) ?> ospiti &middot; &euro;<?= V::e(V::money($richiesta->getImportoTotale())) ?></span>
                </div>
                <?php if ($isPending): ?>
                    <div class="request-actions">
                        <form method="post" action="<?= V::e(V::url('/richieste/chef/' . $richiesta->getIdPrenotazione() . '/accetta')) ?>">
                            <button class="btn btn-accent btn-small" type="submit">Accetta</button>
                        </form>
                        <form method="post" action="<?= V::e(V::url('/richieste/chef/' . $richiesta->getIdPrenotazione() . '/rifiuta')) ?>">
                            <input name="motivo" placeholder="Motivo">
                            <button class="btn btn-ghost btn-small" type="submit">Rifiuta</button>
                        </form>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
        <?php if ($richiestePrenotazione === []): ?>
            <div class="empty-state">Nessuna richiesta per il filtro selezionato.</div>
        <?php endif; ?>
    </div>
</section>

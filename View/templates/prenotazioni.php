<?php
use ViewHelpers as V;
/** @var array $prenotazioni */
/** @var string|null $messaggioAccesso */
$prenotazioni = $prenotazioni ?? [];
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Le mie prenotazioni</h1>
    <p>Consulta servizi chef e ghost kitchen prenotati dal tuo account.</p>
</section>

<section class="section ops-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php elseif ($prenotazioni === []): ?>
        <div class="empty-state">Nessuna prenotazione trovata.</div>
    <?php else: ?>
        <div class="ops-list">
            <?php foreach ($prenotazioni as $item): ?>
                <?php
                $tipo = (string) ($item['tipo'] ?? '');
                $prenotazione = $item['prenotazione'];
                $tipoUrl = $tipo === 'ghost_kitchen' ? 'ghost-kitchen' : 'chef';
                $stato = $prenotazione->getStato();
                $canPay = in_array($stato, ['in_attesa', 'accettata'], true);
                $canCancel = !in_array($stato, ['completata', 'cancellata', 'rifiutata'], true);
                $canReview = $stato === 'completata';
                ?>
                <article class="ops-panel booking-row">
                    <div>
                        <span class="badge neutral"><?= V::e($tipo === 'ghost_kitchen' ? 'Ghost kitchen' : 'Chef') ?></span>
                        <h2>#<?= V::e($prenotazione->getIdPrenotazione()) ?> - <?= V::e($prenotazione->getDataServizio()) ?></h2>
                        <p><?= V::e($prenotazione->getOraInizio()) ?> - <?= V::e($prenotazione->getOraFine()) ?></p>
                    </div>
                    <dl class="ops-meta">
                        <div><dt>Stato</dt><dd><?= V::e($stato) ?></dd></div>
                        <div><dt>Importo</dt><dd>&euro; <?= V::e(V::money((float) $prenotazione->getImportoTotale())) ?></dd></div>
                        <div><dt>Tipo</dt><dd><?= V::e($tipo === 'ghost_kitchen' ? 'Ghost kitchen' : 'Chef') ?></dd></div>
                    </dl>
                    <div class="actions booking-actions-inline">
                        <?php if ($canPay): ?>
                            <a class="btn btn-accent" href="<?= V::e(V::url('/pagamento/' . $tipoUrl . '/' . $prenotazione->getIdPrenotazione())) ?>">Paga</a>
                        <?php endif; ?>
                        <?php if ($canCancel): ?>
                            <a class="btn btn-ghost" href="<?= V::e(V::url('/cancellazione/' . $tipoUrl . '/' . $prenotazione->getIdPrenotazione())) ?>">Cancella</a>
                        <?php endif; ?>
                        <?php if ($canReview): ?>
                            <a class="btn btn-ghost" href="<?= V::e(V::url('/recensione/' . $tipoUrl . '/' . $prenotazione->getIdPrenotazione())) ?>">Recensisci</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

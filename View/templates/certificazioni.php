<?php
use ViewHelpers as V;
/** @var array $certificazioni */
/** @var array $certificazioniInScadenza */
/** @var array $ownerCertificazioni */
/** @var array $riepilogoCertificazioni */
/** @var string|null $messaggioAccesso */
$certificazioni = $certificazioni ?? [];
$certificazioniInScadenza = $certificazioniInScadenza ?? [];
$ownerCertificazioni = $ownerCertificazioni ?? [];
$riepilogoCertificazioni = $riepilogoCertificazioni ?? ['totale' => 0, 'in_attesa' => 0, 'approvata' => 0, 'rifiutata' => 0];
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Certificazioni</h1>
    <p>Controlla documenti approvati, rifiutati e in attesa, con attenzione alle scadenze operative.</p>
</section>

<section class="section ops-flow certifications-page">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <div class="ops-grid certifications-summary">
        <article class="ops-panel moderation-stat"><span>Totali</span><strong><?= V::e($riepilogoCertificazioni['totale'] ?? 0) ?></strong></article>
        <article class="ops-panel moderation-stat"><span>In attesa</span><strong><?= V::e($riepilogoCertificazioni['in_attesa'] ?? 0) ?></strong></article>
        <article class="ops-panel moderation-stat"><span>Approvate</span><strong><?= V::e($riepilogoCertificazioni['approvata'] ?? 0) ?></strong></article>
        <article class="ops-panel moderation-stat"><span>Rifiutate</span><strong><?= V::e($riepilogoCertificazioni['rifiutata'] ?? 0) ?></strong></article>
    </div>

    <section class="ops-panel certifications-alert-panel">
        <div class="toolbar">
            <div>
                <h2>Scadenze da monitorare</h2>
                <p>Il sistema segnala le certificazioni approvate con data di scadenza nei prossimi 90 giorni.</p>
            </div>
        </div>

        <?php if ($certificazioniInScadenza === []): ?>
            <div class="empty-state">Nessuna certificazione in scadenza nei prossimi 90 giorni.</div>
        <?php else: ?>
            <div class="ops-list">
                <?php foreach ($certificazioniInScadenza as $item): ?>
                    <?php $certificazione = $item['certificazione']; ?>
                    <article class="ops-list-item certification-expiry-item">
                        <strong><?= V::e($certificazione->getTipo()) ?> - scadenza <?= V::e($item['scadenza'] ?? 'n/d') ?></strong>
                        <span><?= V::e($item['ownerLabel'] ?? 'Owner #' . $certificazione->getIdOwner()) ?></span>
                        <a class="btn btn-primary btn-small" href="<?= V::e(V::url('/certificazioni/' . $certificazione->getIdCertificazione())) ?>">Dettaglio</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="ops-panel admin-directory-panel">
        <div class="toolbar">
            <div>
                <h2>Tutte le certificazioni</h2>
                <p>L'admin puo approvare, rifiutare o rimettere in attesa certificazioni di chef e ghost kitchen dopo il controllo manuale.</p>
            </div>
        </div>

        <?php if ($certificazioni === []): ?>
            <div class="empty-state">Nessuna certificazione caricata.</div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table certifications-table">
                    <thead>
                    <tr><th>ID</th><th>Owner</th><th>Tipo</th><th>Caricamento</th><th>Validazione</th><th>Scadenza</th><th>Stato</th><th>Azioni</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($certificazioni as $certificazione): ?>
                        <?php
                        $ownerKey = $certificazione->getTipoOwner() . ':' . (string) $certificazione->getIdOwner();
                        $owner = $ownerCertificazioni[$ownerKey] ?? null;
                        if ($owner instanceof EGhostKitchen) {
                            $ownerLabel = 'Ghost kitchen: ' . $owner->getNome();
                        } elseif ($owner !== null && method_exists($owner, 'getNome') && method_exists($owner, 'getCognome')) {
                            $ownerLabel = 'Chef: ' . trim($owner->getNome() . ' ' . $owner->getCognome());
                        } else {
                            $ownerLabel = $certificazione->getTipoOwner() . ' #' . $certificazione->getIdOwner();
                        }
                        ?>
                        <tr>
                            <td>#<?= V::e($certificazione->getIdCertificazione()) ?></td>
                            <td><strong><?= V::e($ownerLabel) ?></strong></td>
                            <td><?= V::e($certificazione->getTipo()) ?></td>
                            <td><?= V::e($certificazione->getDataCaricamento()) ?></td>
                            <td><?= V::e($certificazione->getDataValidazione() !== '' ? $certificazione->getDataValidazione() : 'n/d') ?></td>
                            <td><?= V::e($certificazione->getDataScadenza() !== '' ? $certificazione->getDataScadenza() : 'n/d') ?></td>
                            <td><span class="status-pill neutral"><?= V::e(str_replace('_', ' ', $certificazione->getStato())) ?></span></td>
                            <td>
                                <div class="admin-row-actions certification-actions">
                                    <a class="btn btn-ghost btn-small" href="<?= V::e(V::url('/certificazioni/' . $certificazione->getIdCertificazione())) ?>">Dettaglio</a>
                                    <?php if ($certificazione->getStato() !== ECertificazione::STATO_RIFIUTATA): ?>
                                        <form method="post" action="<?= V::e(V::url('/certificazioni/' . $certificazione->getIdCertificazione() . '/rifiuta')) ?>"><button class="btn btn-danger btn-small" type="submit">Rifiuta</button></form>
                                    <?php endif; ?>
                                    <?php if ($certificazione->getStato() !== ECertificazione::STATO_IN_ATTESA): ?>
                                        <form method="post" action="<?= V::e(V::url('/certificazioni/' . $certificazione->getIdCertificazione() . '/in-attesa')) ?>"><button class="btn btn-ghost btn-small" type="submit">In attesa</button></form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>

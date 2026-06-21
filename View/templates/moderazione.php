<?php
use ViewHelpers as V;
/** @var array $segnalazioniModerazione */
/** @var array $riepilogoModerazione */
/** @var string|null $messaggioAccesso */
$segnalazioniModerazione = $segnalazioniModerazione ?? [];
$riepilogoModerazione = $riepilogoModerazione ?? ['totale' => 0, 'recensioni' => 0, 'profili' => 0, 'contenuti' => 0];
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Moderazione</h1>
    <p>Gestisci segnalazioni, recensioni e profili con un flusso guidato.</p>
</section>

<section class="section ops-flow moderation-page">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <div class="ops-grid moderation-summary-grid">
        <article class="ops-panel moderation-stat">
            <span>Da valutare</span>
            <strong><?= V::e($riepilogoModerazione['totale'] ?? 0) ?></strong>
        </article>
        <article class="ops-panel moderation-stat">
            <span>Recensioni</span>
            <strong><?= V::e($riepilogoModerazione['recensioni'] ?? 0) ?></strong>
        </article>
        <article class="ops-panel moderation-stat">
            <span>Profili</span>
            <strong><?= V::e($riepilogoModerazione['profili'] ?? 0) ?></strong>
        </article>
        <article class="ops-panel moderation-stat">
            <span>Altri contenuti</span>
            <strong><?= V::e($riepilogoModerazione['contenuti'] ?? 0) ?></strong>
        </article>
    </div>

    <section class="ops-panel moderation-board">
        <div class="toolbar">
            <div>
                <h2>Segnalazioni aperte</h2>
                <p>Le segnalazioni aperte sono da valutare; prendendole in carico passano in valutazione per indicare che l'admin le sta gestendo.</p>
            </div>
        </div>

        <?php if ($segnalazioniModerazione === []): ?>
            <div class="empty-state moderation-empty">
                Nessuna segnalazione aperta. La coda di moderazione e pulita.
            </div>
        <?php endif; ?>

        <div class="moderation-list">
            <?php foreach ($segnalazioniModerazione as $scheda): ?>
                <?php
                $segnalazione = $scheda['segnalazione'];
                $idSegnalazione = $segnalazione->getIdSegnalazione();
                $idTarget = $segnalazione->getIdTarget();
                $stato = $segnalazione->getStato();
                ?>
                <article class="moderation-card">
                    <header class="moderation-card-header">
                        <div>
                            <span class="badge neutral"><?= V::e($scheda['targetLabel'] ?? 'Contenuto') ?></span>
                            <h3><?= V::e($segnalazione->getMotivo() !== '' ? $segnalazione->getMotivo() : 'Segnalazione #' . $idSegnalazione) ?></h3>
                            <p><?= V::e($scheda['targetSummary'] ?? '') ?></p>
                        </div>
                        <div class="moderation-meta">
                            <span>#<?= V::e($idSegnalazione) ?></span>
                            <?php if ($stato === 'aperta'): ?><span>Segnalazione aperta da valutare</span><?php endif; ?>
                            <span class="status-pill <?= $stato === 'aperta' ? '' : 'neutral' ?>"><?= V::e(str_replace('_', ' ', $stato)) ?></span>
                        </div>
                    </header>

                    <?php if ($segnalazione->getDescrizione() !== ''): ?>
                        <p class="moderation-description"><?= V::e($segnalazione->getDescrizione()) ?></p>
                    <?php endif; ?>

                    <dl class="moderation-details">
                        <div><dt>Segnalante</dt><dd>#<?= V::e($segnalazione->getIdSegnalante() ?? 'n/d') ?></dd></div>
                        <div><dt>Target</dt><dd><?= V::e($segnalazione->getTipoTarget()) ?> #<?= V::e($idTarget ?? 'n/d') ?></dd></div>
                        <div><dt>Data</dt><dd><?= V::e($segnalazione->getDataSegnalazione() !== '' ? $segnalazione->getDataSegnalazione() : 'n/d') ?></dd></div>
                    </dl>

                    <div class="moderation-actions">
                        <?php if ($stato === 'aperta'): ?>
                            <form method="post" action="<?= V::e(V::url('/moderazione/segnalazione/' . $idSegnalazione . '/prendi')) ?>">
                                <button class="btn btn-primary" type="submit">Prendi in carico e metti in valutazione</button>
                            </form>
                        <?php endif; ?>

                        <?php if (($scheda['isRecensione'] ?? false) && $idTarget !== null): ?>
                            <form method="post" action="<?= V::e(V::url('/moderazione/recensione/' . $idTarget . '/nascondi')) ?>">
                                <button class="btn btn-ghost" type="submit">Nascondi recensione</button>
                            </form>
                            <form method="post" action="<?= V::e(V::url('/moderazione/recensione/' . $idTarget . '/rimuovi')) ?>">
                                <button class="btn btn-danger" type="submit">Rimuovi recensione</button>
                            </form>
                        <?php endif; ?>

                        <?php if (($scheda['isProfilo'] ?? false) && $idTarget !== null): ?>
                            <form method="post" action="<?= V::e(V::url('/moderazione/profilo/' . $idTarget . '/sospendi')) ?>">
                                <button class="btn btn-ghost" type="submit">Sospendi profilo</button>
                            </form>
                            <form method="post" action="<?= V::e(V::url('/moderazione/profilo/' . $idTarget . '/banna')) ?>">
                                <button class="btn btn-danger" type="submit">Banna profilo</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <form class="moderation-close-form" method="post" action="<?= V::e(V::url('/moderazione/segnalazione/' . $idSegnalazione . '/chiudi')) ?>">
                        <label>Decisione finale
                            <select name="esito" required>
                                <option value="risolta">Risolta: intervento effettuato</option>
                                <option value="respinta">Respinta: segnalazione non fondata</option>
                                <option value="archiviata">Archiviata: chiusa senza ulteriori azioni</option>
                            </select>
                        </label>
                        <label>Nota interna
                            <textarea name="noteAdmin" rows="2" placeholder="Motiva brevemente la decisione per tracciarla."></textarea>
                        </label>
                        <button class="btn btn-accent" type="submit">Chiudi segnalazione</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

</section>

<?php
use ViewHelpers as V;
/** @var array $segnalazioni */
/** @var string|null $messaggioAccesso */
$segnalazioni = $segnalazioni ?? [];
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Moderazione</h1>
    <p>Prendi in carico segnalazioni e applica interventi su contenuti o profili.</p>
</section>

<section class="section ops-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <section class="ops-panel">
        <h2>Segnalazioni aperte</h2>
        <?php if ($segnalazioni === []): ?><p class="muted-text">Nessuna segnalazione aperta.</p><?php endif; ?>
        <div class="ops-list">
            <?php foreach ($segnalazioni as $segnalazione): ?>
                <article class="ops-list-item ops-request">
                    <strong>#<?= V::e($segnalazione->getIdSegnalazione()) ?> - <?= V::e($segnalazione->getMotivo()) ?></strong>
                    <span><?= V::e($segnalazione->getTipoTarget()) ?> #<?= V::e($segnalazione->getIdTarget()) ?> - <?= V::e($segnalazione->getStato()) ?></span>
                    <p class="muted-text"><?= V::e($segnalazione->getDescrizione()) ?></p>
                    <form method="post" action="<?= V::e(V::url('/moderazione/segnalazione/' . $segnalazione->getIdSegnalazione() . '/prendi')) ?>">
                        <button class="btn btn-primary" type="submit">Prendi in carico</button>
                    </form>
                    <form method="post" action="<?= V::e(V::url('/moderazione/segnalazione/' . $segnalazione->getIdSegnalazione() . '/chiudi')) ?>">
                        <select name="esito" required>
                            <option value="risolta">Risolta</option>
                            <option value="archiviata">Archiviata</option>
                            <option value="respinta">Respinta</option>
                        </select>
                        <input name="noteAdmin" placeholder="Note per la decisione">
                        <button class="btn btn-accent" type="submit">Chiudi</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="ops-grid">
        <form class="ops-panel ops-form" method="post" action="<?= V::e(V::url('/moderazione/recensione/1/nascondi')) ?>" data-dynamic-action="/moderazione/recensione/{id}/{azione}">
            <h2>Modera recensione</h2>
            <label>ID recensione <input name="dynamicId" type="number" min="1" required></label>
            <label>Azione
                <select name="dynamicAction" required>
                    <option value="nascondi">Nascondi</option>
                    <option value="rimuovi">Rimuovi</option>
                    <option value="ripristina">Ripristina</option>
                </select>
            </label>
            <button class="btn btn-accent" type="submit">Applica</button>
        </form>

        <form class="ops-panel ops-form" method="post" action="<?= V::e(V::url('/moderazione/profilo/1/sospendi')) ?>" data-dynamic-action="/moderazione/profilo/{id}/{azione}">
            <h2>Modera profilo</h2>
            <label>ID utente <input name="dynamicId" type="number" min="1" required></label>
            <label>Azione
                <select name="dynamicAction" required>
                    <option value="sospendi">Sospendi</option>
                    <option value="banna">Banna</option>
                    <option value="riattiva">Riattiva</option>
                </select>
            </label>
            <button class="btn btn-accent" type="submit">Applica</button>
        </form>
    </div>
</section>

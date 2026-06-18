<?php
use ViewHelpers as V;
/** @var string|null $titoloPagina */
/** @var string|null $descrizionePagina */
/** @var array $recensioni */
/** @var array $filtri */
/** @var array $opzioniTipologiaCucina */
/** @var bool $vistaAdmin */
/** @var string|null $messaggioAccesso */
$recensioni = $recensioni ?? [];
$filtri = $filtri ?? ['ordinamento' => 'recenti', 'tipo' => 'tutte', 'stato' => 'tutti', 'tipologiaCucina' => ''];
$opzioniTipologiaCucina = $opzioniTipologiaCucina ?? [];
$vistaAdmin = (bool) ($vistaAdmin ?? false);
$actionPath = $vistaAdmin ? '/recensioni' : '/mie-recensioni';
$titoloPagina = $titoloPagina ?? ($vistaAdmin ? 'Tutte le recensioni' : 'Le mie recensioni');
$descrizionePagina = $descrizionePagina ?? 'Consulta le recensioni registrate sulla piattaforma.';
$tipoLabel = static fn (string $tipo): string => $tipo === 'ghost_kitchen' ? 'Ghost kitchen' : 'Chef';
$statoClass = static fn (string $stato): string => $stato === 'visibile' ? '' : 'neutral';
?>
<section class="page-hero compact-hero ops-hero">
    <h1><?= V::e($titoloPagina) ?></h1>
    <p><?= V::e($descrizionePagina) ?></p>
</section>

<section class="section ops-flow reviews-page">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <form class="ops-panel ops-form reviews-filter-panel" method="get" action="<?= V::e(V::url($actionPath)) ?>">
        <div class="toolbar">
            <div>
                <h2>Filtri</h2>
                <p>Ordina e restringi l'elenco in base al tipo di contenuto e alla valutazione.</p>
            </div>
            <div class="actions">
                <a class="btn btn-ghost" href="<?= V::e(V::url($actionPath)) ?>">Reset</a>
                <button class="btn btn-accent" type="submit">Applica</button>
            </div>
        </div>
        <div class="filter-select-grid">
            <label>Ordina
                <select name="ordinamento">
                    <option value="recenti" <?= ($filtri['ordinamento'] ?? '') === 'recenti' ? 'selected' : '' ?>>Piu recenti</option>
                    <option value="valutazioni_alte" <?= ($filtri['ordinamento'] ?? '') === 'valutazioni_alte' ? 'selected' : '' ?>>Valutazioni piu alte</option>
                    <option value="valutazioni_basse" <?= ($filtri['ordinamento'] ?? '') === 'valutazioni_basse' ? 'selected' : '' ?>>Valutazioni piu basse</option>
                    <option value="cucina" <?= ($filtri['ordinamento'] ?? '') === 'cucina' ? 'selected' : '' ?>>Per tipologia cucina</option>
                </select>
            </label>
            <label>Tipo
                <select name="tipo">
                    <option value="tutte" <?= ($filtri['tipo'] ?? '') === 'tutte' ? 'selected' : '' ?>>Tutte</option>
                    <option value="chef" <?= ($filtri['tipo'] ?? '') === 'chef' ? 'selected' : '' ?>>Chef</option>
                    <option value="ghost_kitchen" <?= ($filtri['tipo'] ?? '') === 'ghost_kitchen' ? 'selected' : '' ?>>Ghost kitchen</option>
                </select>
            </label>
            <label>Tipologia cucina
                <select name="tipologiaCucina">
                    <option value="">Tutte</option>
                    <?php foreach ($opzioniTipologiaCucina as $tipologia): ?>
                        <option value="<?= V::e($tipologia) ?>" <?= strcasecmp((string) ($filtri['tipologiaCucina'] ?? ''), (string) $tipologia) === 0 ? 'selected' : '' ?>>
                            <?= V::e($tipologia) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php if ($vistaAdmin): ?>
                <label>Stato
                    <select name="stato">
                        <option value="tutti" <?= ($filtri['stato'] ?? '') === 'tutti' ? 'selected' : '' ?>>Tutti</option>
                        <option value="visibile" <?= ($filtri['stato'] ?? '') === 'visibile' ? 'selected' : '' ?>>Visibili</option>
                        <option value="nascosta" <?= ($filtri['stato'] ?? '') === 'nascosta' ? 'selected' : '' ?>>Nascoste</option>
                        <option value="rimossa" <?= ($filtri['stato'] ?? '') === 'rimossa' ? 'selected' : '' ?>>Rimosse</option>
                    </select>
                </label>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($recensioni === []): ?>
        <div class="empty-state">Nessuna recensione trovata.</div>
    <?php else: ?>
        <div class="ops-list reviews-list">
            <?php foreach ($recensioni as $item): ?>
                <?php
                $tipo = (string) ($item['tipoTarget'] ?? 'chef');
                $idRecensione = (int) ($item['idRecensione'] ?? 0);
                $stato = (string) ($item['stato'] ?? '');
                $targetPath = $tipo === 'ghost_kitchen' ? '/ghost-kitchen/' : '/chef/';
                ?>
                <article class="ops-panel review-row">
                    <header class="review-row-header">
                        <div>
                            <span class="badge neutral"><?= V::e($tipoLabel($tipo)) ?></span>
                            <h2><?= V::e($item['targetNome'] ?? 'Target non disponibile') ?></h2>
                            <p><?= V::e($item['targetDettaglio'] ?? '') ?></p>
                        </div>
                        <div class="review-score">
                            <strong><?= V::e($item['punteggio'] ?? 0) ?>/5</strong>
                            <span class="status-pill <?= V::e($statoClass($stato)) ?>"><?= V::e($stato) ?></span>
                        </div>
                    </header>

                    <p class="review-comment"><?= V::e($item['commento'] ?? '') ?></p>

                    <dl class="ops-meta">
                        <div><dt>ID recensione</dt><dd>#<?= V::e($idRecensione) ?></dd></div>
                        <div><dt>Prenotazione</dt><dd>#<?= V::e($item['idPrenotazione'] ?? '') ?></dd></div>
                        <div><dt>Data</dt><dd><?= V::e($item['dataRecensione'] ?? '') ?></dd></div>
                        <?php if (($item['tipologiaCucina'] ?? '') !== ''): ?>
                            <div><dt>Cucina</dt><dd><?= V::e($item['tipologiaCucina']) ?></dd></div>
                        <?php endif; ?>
                        <?php if ($vistaAdmin): ?>
                            <div><dt>Autore</dt><dd><?= V::e(($item['autoreNome'] ?? '') . ' (' . ($item['autoreEmail'] ?? '') . ')') ?></dd></div>
                        <?php endif; ?>
                    </dl>

                    <div class="actions review-actions">
                        <a class="btn btn-ghost" href="<?= V::e(V::url($targetPath . (int) ($item['idTarget'] ?? 0))) ?>">Apri target</a>
                        <?php if (!$vistaAdmin): ?>
                            <a class="btn btn-ghost" href="<?= V::e(V::url('/segnalazione/recensione/' . $idRecensione)) ?>">Segnala</a>
                        <?php else: ?>
                            <?php if ($stato !== 'nascosta'): ?>
                                <form method="post" action="<?= V::e(V::url('/moderazione/recensione/' . $idRecensione . '/nascondi')) ?>">
                                    <button class="btn btn-ghost" type="submit">Nascondi</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($stato !== 'visibile'): ?>
                                <form method="post" action="<?= V::e(V::url('/moderazione/recensione/' . $idRecensione . '/ripristina')) ?>">
                                    <button class="btn btn-primary" type="submit">Ripristina</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($stato !== 'rimossa'): ?>
                                <form method="post" action="<?= V::e(V::url('/moderazione/recensione/' . $idRecensione . '/rimuovi')) ?>">
                                    <button class="btn btn-danger" type="submit">Rimuovi</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

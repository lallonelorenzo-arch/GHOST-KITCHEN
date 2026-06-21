<?php
use ViewHelpers as V;
/** @var string|null $titoloPagina */
/** @var string|null $descrizionePagina */
/** @var array $recensioni */
/** @var array $filtri */
/** @var bool $vistaAdmin */
/** @var string|null $messaggioAccesso */
$recensioni = $recensioni ?? [];
$filtri = $filtri ?? ['ordinamento' => 'recenti', 'tipo' => 'tutte', 'stato' => 'tutti', 'tipologiaCucina' => ''];
$vistaAdmin = (bool) ($vistaAdmin ?? false);
$actionPath = $vistaAdmin ? '/recensioni' : '/mie-recensioni';
$titoloPagina = $titoloPagina ?? ($vistaAdmin ? 'Tutte le recensioni' : 'Le mie recensioni');
$descrizionePagina = $descrizionePagina ?? 'Consulta le recensioni registrate sulla piattaforma.';
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
                </select>
            </label>
            <label>Tipo
                <select name="tipo">
                    <option value="tutte" <?= ($filtri['tipo'] ?? '') === 'tutte' ? 'selected' : '' ?>>Tutte</option>
                    <option value="chef" <?= ($filtri['tipo'] ?? '') === 'chef' ? 'selected' : '' ?>>Chef</option>
                    <option value="ghost_kitchen" <?= ($filtri['tipo'] ?? '') === 'ghost_kitchen' ? 'selected' : '' ?>>Ghost kitchen</option>
                </select>
            </label>
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
                $punteggio = (int) ($item['punteggio'] ?? 0);
                $commento = trim((string) ($item['commento'] ?? ''));
                ?>
                <article class="ops-panel review-row">
                    <header class="review-row-header">
                        <div>
                            <div class="review-score">
                                <span class="stars" aria-label="Valutazione <?= V::e($punteggio) ?> su 5"><?= V::stars((float) $punteggio) ?></span>
                                <strong><?= V::e($punteggio) ?>/5</strong>
                            </div>
                            <h2>
                                <a href="<?= V::e(V::url($targetPath . (int) ($item['idTarget'] ?? 0))) ?>">
                                    <?= V::e($item['targetNome'] ?? 'Target non disponibile') ?>
                                </a>
                            </h2>
                            <p>Recensito da <?= V::e(($item['autoreNome'] ?? '') !== '' ? $item['autoreNome'] : 'utente #' . (int) ($item['idAutore'] ?? 0)) ?></p>
                            <?php if (($item['targetDettaglio'] ?? '') !== ''): ?>
                                <p><?= V::e($item['targetDettaglio']) ?></p>
                            <?php endif; ?>
                            <time datetime="<?= V::e((string) ($item['dataRecensione'] ?? '')) ?>"><?= V::e($item['dataRecensione'] ?? '') ?></time>
                        </div>
                    </header>

                    <?php if ($commento !== ''): ?>
                        <p class="review-comment"><?= V::e($commento) ?></p>
                    <?php endif; ?>

                    <?php if ($vistaAdmin): ?>
                        <div class="actions review-actions">
                            <?php if ($stato !== 'nascosta'): ?>
                                <form method="post" action="<?= V::e(V::url('/moderazione/recensione/' . $idRecensione . '/nascondi')) ?>">
                                    <input type="hidden" name="ritorno" value="/recensioni">
                                    <button class="btn btn-ghost" type="submit">Nascondi</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($stato !== 'visibile'): ?>
                                <form method="post" action="<?= V::e(V::url('/moderazione/recensione/' . $idRecensione . '/ripristina')) ?>">
                                    <input type="hidden" name="ritorno" value="/recensioni">
                                    <button class="btn btn-primary" type="submit">Ripristina</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($stato !== 'rimossa'): ?>
                                <form method="post" action="<?= V::e(V::url('/moderazione/recensione/' . $idRecensione . '/rimuovi')) ?>">
                                    <input type="hidden" name="ritorno" value="/recensioni">
                                    <button class="btn btn-danger" type="submit">Rimuovi</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

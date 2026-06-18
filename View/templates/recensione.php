<?php
use ViewHelpers as V;
/** @var string|null $tipoTarget */
/** @var EPrenotazione|null $prenotazione */
/** @var array $form */
/** @var ERecensione|null $recensione */
/** @var array|null $targetRecensione */
/** @var string|null $messaggioAccesso */
/** @var string|null $erroreForm */
/** @var string|null $messaggioSuccesso */
/** @var bool|null $recensioneBloccata */
$tipoTarget = $tipoTarget ?? 'chef';
$prenotazione = $prenotazione ?? null;
$recensione = $recensione ?? null;
$targetRecensione = $targetRecensione ?? null;
$recensioneBloccata = (bool) ($recensioneBloccata ?? false);
$form = $form ?? [];
$tipoSlug = $tipoTarget === 'ghost_kitchen' ? 'ghost-kitchen' : 'chef';
$idPrenotazione = $prenotazione !== null ? (int) $prenotazione->getIdPrenotazione() : (int) ($idPrenotazione ?? ($form['idPrenotazione'] ?? 0));
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Recensione</h1>
    <p>Condividi una valutazione utile dopo un servizio completato e collegato al tuo profilo.</p>
</section>

<section class="section ops-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>
    <?php if (!empty($erroreForm)): ?><div class="alert"><?= V::e($erroreForm) ?></div><?php endif; ?>
    <?php if (!empty($messaggioSuccesso)): ?><div class="notice"><?= V::e($messaggioSuccesso) ?></div><?php endif; ?>

    <div class="ops-grid">
        <article class="ops-panel">
            <h2>Prenotazione</h2>
            <?php if ($prenotazione !== null): ?>
                <dl class="ops-meta">
                    <div><dt>ID</dt><dd>#<?= V::e($prenotazione->getIdPrenotazione()) ?></dd></div>
                    <?php if (is_array($targetRecensione)): ?>
                        <div><dt><?= V::e((string) ($targetRecensione['label'] ?? 'Target')) ?></dt><dd><?= V::e((string) ($targetRecensione['nome'] ?? 'Non disponibile')) ?></dd></div>
                        <?php foreach (($targetRecensione['dettagli'] ?? []) as $label => $value): ?>
                            <div><dt><?= V::e((string) $label) ?></dt><dd><?= V::e((string) $value) ?></dd></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div><dt>Tipo</dt><dd><?= V::e($tipoTarget) ?></dd></div>
                    <?php endif; ?>
                    <div><dt>Stato</dt><dd><?= V::e($prenotazione->getStato()) ?></dd></div>
                </dl>
            <?php else: ?>
                <p class="muted-text">Nessuna prenotazione recensibile caricata.</p>
            <?php endif; ?>
            <?php if ($recensione !== null): ?>
                <div class="notice">Recensione #<?= V::e($recensione->getIdRecensione()) ?> pubblicata.</div>
            <?php endif; ?>
        </article>

        <?php if (!$recensioneBloccata): ?>
            <form class="ops-panel ops-form" method="post" action="<?= V::e(V::url('/recensione/' . $tipoSlug . '/' . $idPrenotazione)) ?>">
                <h2>Valutazione</h2>
                <label>Punteggio
                    <select name="punteggio" required>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= (int) ($form['punteggio'] ?? 5) === $i ? 'selected' : '' ?>><?= $i ?> stelle</option>
                        <?php endfor; ?>
                    </select>
                </label>
                <label>Commento
                    <textarea name="commento" rows="5" required><?= V::e($form['commento'] ?? '') ?></textarea>
                </label>
                <button class="btn btn-accent" type="submit">Pubblica recensione</button>
            </form>
        <?php endif; ?>
    </div>
</section>

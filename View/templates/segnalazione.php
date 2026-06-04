<?php
use ViewHelpers as V;
/** @var string|null $tipoTarget */
/** @var int|null $idTarget */
/** @var mixed $target */
/** @var array $form */
/** @var ESegnalazione|null $segnalazione */
/** @var string|null $messaggioAccesso */
/** @var string|null $erroreForm */
/** @var string|null $messaggioSuccesso */
$tipoTarget = $tipoTarget ?? ($form['tipoTarget'] ?? 'utente');
$idTarget = (int) ($idTarget ?? ($form['idTarget'] ?? 0));
$target = $target ?? null;
$segnalazione = $segnalazione ?? null;
$form = $form ?? [];
$tipoSlug = $tipoTarget === 'ghost_kitchen' ? 'ghost-kitchen' : $tipoTarget;
?>
<section class="page-hero compact-hero uc-page-hero">
    <span class="badge">UC11</span>
    <h1>Segnalazione</h1>
    <p>Invia una segnalazione moderabile dall'amministratore.</p>
</section>

<section class="section uc-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>
    <?php if (!empty($erroreForm)): ?><div class="alert"><?= V::e($erroreForm) ?></div><?php endif; ?>
    <?php if (!empty($messaggioSuccesso)): ?><div class="notice"><?= V::e($messaggioSuccesso) ?></div><?php endif; ?>

    <div class="uc-grid">
        <article class="uc-panel">
            <h2>Target</h2>
            <dl class="uc-meta">
                <div><dt>Tipo</dt><dd><?= V::e($tipoTarget) ?></dd></div>
                <div><dt>ID</dt><dd>#<?= V::e($idTarget) ?></dd></div>
                <div><dt>Stato</dt><dd><?= $target !== null ? 'trovato' : 'non caricato' ?></dd></div>
            </dl>
            <?php if ($segnalazione !== null): ?>
                <div class="notice">Segnalazione #<?= V::e($segnalazione->getIdSegnalazione()) ?> aperta.</div>
            <?php endif; ?>
        </article>

        <form class="uc-panel uc-form" method="post" action="<?= V::e(V::url('/segnalazione/' . $tipoSlug . '/' . $idTarget)) ?>">
            <h2>Dettagli</h2>
            <label>Motivo
                <input name="motivo" value="<?= V::e($form['motivo'] ?? '') ?>" required>
            </label>
            <label>Descrizione
                <textarea name="descrizione" rows="5"><?= V::e($form['descrizione'] ?? '') ?></textarea>
            </label>
            <button class="btn btn-accent" type="submit">Invia segnalazione</button>
        </form>
    </div>
</section>

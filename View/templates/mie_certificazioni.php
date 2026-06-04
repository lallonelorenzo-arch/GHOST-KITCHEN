<?php
use ViewHelpers as V;
/** @var array $certificazioni */
/** @var string|null $messaggioAccesso */
$certificazioni = $certificazioni ?? [];
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Le mie certificazioni</h1>
    <p>Carica documenti professionali e segui lo stato della revisione.</p>
</section>

<section class="section ops-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?></div>
    <?php else: ?>
        <form class="ops-panel ops-form" method="post" action="<?= V::e(V::url('/mie-certificazioni')) ?>" enctype="multipart/form-data">
            <h2>Nuova certificazione</h2>
            <label>Tipo certificazione <input name="tipo" maxlength="80" required></label>
            <label>File <input type="file" name="certificazione" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp" required></label>
            <button class="btn btn-accent" type="submit">Carica certificazione</button>
        </form>

        <section class="ops-panel">
            <h2>Certificazioni caricate</h2>
            <?php if ($certificazioni === []): ?>
                <p class="muted-text">Nessuna certificazione caricata.</p>
            <?php endif; ?>
            <div class="ops-list">
                <?php foreach ($certificazioni as $certificazione): ?>
                    <article class="ops-list-item ops-request">
                        <strong><?= V::e($certificazione->getTipo()) ?></strong>
                        <span>Caricata il <?= V::e($certificazione->getDataCaricamento()) ?></span>
                        <span class="badge"><?= V::e($certificazione->getStato()) ?></span>
                        <?php if ($certificazione->getNoteAdmin() !== ''): ?>
                            <p><?= V::e($certificazione->getNoteAdmin()) ?></p>
                        <?php endif; ?>
                        <?php if ($certificazione->getPathFile() !== ''): ?>
                            <a class="btn btn-ghost" href="<?= V::e(V::url($certificazione->getPathFile())) ?>" target="_blank" rel="noopener">Apri file</a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>

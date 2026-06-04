<?php
use ViewHelpers as V;
/** @var EChef $chef */
/** @var mixed $fotoProfilo */
/** @var array $menu */
/** @var array $certificazioni */
$image = V::mediaUrl($fotoProfilo ?? null, 'https://images.unsplash.com/photo-1750943082012-efe6d2fd9e45?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200');
?>
<section class="detail-hero" style="background-image: linear-gradient(0deg, rgba(44,24,16,.9), rgba(44,24,16,.25)), url('<?= V::e($image) ?>')">
    <a class="back-link" href="<?= V::e(V::url('/ricerca/chef')) ?>">Torna alla ricerca</a>
    <div>
        <span class="badge">Valutazione <?= V::e($chef->getValutazioneMedia()) ?> / 5</span>
        <h1><?= V::e($chef->getNome() . ' ' . $chef->getCognome()) ?></h1>
        <p><?= V::e($chef->getSpecializzazione() ?: $chef->getTipologiaCucina()) ?></p>
    </div>
</section>

<section class="section detail-layout">
    <article>
        <h2>Chi sono</h2>
        <p class="lead"><?= V::e($chef->getBiografia() ?: 'Profilo chef disponibile nel database.') ?></p>

        <h2>Menu disponibili</h2>
        <div class="stack">
            <?php foreach ($menu as $menuItem): ?>
                <?php $m = $menuItem['menu']; ?>
                <div class="panel">
                    <div class="panel-title">
                        <h3><?= V::e($m->getNome()) ?></h3>
                        <strong>Euro <?= V::e(V::money($m->getPrezzoPersona())) ?></strong>
                    </div>
                    <p><?= V::e($m->getDescrizione()) ?></p>
                    <?php if (($menuItem['piatti'] ?? []) !== []): ?>
                        <ul class="clean-list">
                            <?php foreach ($menuItem['piatti'] as $piattoData): ?>
                                <?php $piatto = $piattoData['piatto']; ?>
                                <li><?= V::e($piatto->getCategoria()) ?>: <?= V::e($piatto->getNome()) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if ($menu === []): ?>
                <div class="empty-state">Nessun menu pubblicato per questo chef.</div>
            <?php endif; ?>
        </div>

        <h2>Certificazioni</h2>
        <div class="tag-row">
            <?php foreach ($certificazioni as $certificazione): ?>
                <span><?= V::e(method_exists($certificazione, 'getNome') ? $certificazione->getNome() : 'Certificazione') ?></span>
            <?php endforeach; ?>
            <?php if ($certificazioni === []): ?><span>Nessuna certificazione approvata visibile.</span><?php endif; ?>
        </div>
    </article>
    <aside class="booking-box">
        <p>A partire da</p>
        <strong>Euro <?= V::e(V::money($chef->getPrezzoBase())) ?></strong>
        <span>per esperienza</span>
        <a class="btn btn-accent" href="<?= V::e(V::url('/prenotazione/placeholder')) ?>">Prenota Ora</a>
        <a class="btn btn-ghost" href="<?= V::e(V::url('/login')) ?>">Contatta Chef</a>
    </aside>
</section>

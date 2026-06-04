<?php
use ViewHelpers as V;
/** @var string $titolo */
/** @var string $messaggio */
/** @var bool $successo */
/** @var string $ritorno */
$ritorno = $ritorno ?? '/';
$cta = str_starts_with($ritorno, '/profilo') ? 'Torna al profilo' : 'Continua';
?>
<section class="section error-page ops-esito">
    <div class="esito-card">
        <span class="badge"><?= !empty($successo) ? 'OK' : 'Attenzione' ?></span>
        <h1><?= V::e($titolo ?? 'Esito operazione') ?></h1>
        <p><?= V::e($messaggio ?? '') ?></p>
        <a class="btn btn-accent" href="<?= V::e(V::url($ritorno)) ?>"><?= V::e($cta) ?></a>
    </div>
</section>

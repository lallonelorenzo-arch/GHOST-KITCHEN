<?php
use ViewHelpers as V;
/** @var string $titolo */
/** @var string $messaggio */
/** @var bool $successo */
/** @var string $ritorno */
?>
<section class="section error-page ops-esito">
    <span class="badge"><?= !empty($successo) ? 'OK' : 'Attenzione' ?></span>
    <h1><?= V::e($titolo ?? 'Esito operazione') ?></h1>
    <p><?= V::e($messaggio ?? '') ?></p>
    <a class="btn btn-accent" href="<?= V::e(V::url($ritorno ?? '/')) ?>">Continua</a>
</section>

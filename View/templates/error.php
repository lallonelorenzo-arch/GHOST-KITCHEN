<?php
use ViewHelpers as V;
/** @var int $status */
/** @var string $title */
/** @var string $message */
?>
<section class="section error-page">
    <span class="badge"><?= V::e($status) ?></span>
    <h1><?= V::e($title) ?></h1>
    <p><?= V::e($message) ?></p>
    <a class="btn btn-accent" href="<?= V::e(V::url('/')) ?>">Torna alla home</a>
</section>

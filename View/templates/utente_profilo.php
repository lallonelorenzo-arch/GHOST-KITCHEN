<?php
use ViewHelpers as V;
/** @var EUtente $utenteProfilo */
?>
<section class="section client-profile-page">
    <a class="back-link-inline" href="<?= V::e(V::url('/dashboard', ['tab' => 'prenotazioni'])) ?>">&larr; Torna alle prenotazioni</a>
    <section class="client-profile-card">
        <div class="client-profile-avatar">
            <?= V::e(strtoupper(substr($utenteProfilo->getNome(), 0, 1) . substr($utenteProfilo->getCognome(), 0, 1))) ?>
        </div>
        <div>
            <h1><?= V::e(trim($utenteProfilo->getNome() . ' ' . $utenteProfilo->getCognome())) ?></h1>
            <p><?= V::e($utenteProfilo->getLocalita() !== '' ? $utenteProfilo->getLocalita() : 'Localita non indicata') ?></p>
        </div>
        <dl class="client-profile-fields">
            <div><dt>Email</dt><dd><?= V::e($utenteProfilo->getEmail()) ?></dd></div>
            <div><dt>Telefono</dt><dd><?= V::e($utenteProfilo->getTelefono() !== '' ? $utenteProfilo->getTelefono() : 'n/d') ?></dd></div>
            <div class="is-wide"><dt>Note profilo</dt><dd><?= V::e($utenteProfilo->getBiografia() !== '' ? $utenteProfilo->getBiografia() : 'Nessuna informazione aggiuntiva.') ?></dd></div>
        </dl>
    </section>
</section>

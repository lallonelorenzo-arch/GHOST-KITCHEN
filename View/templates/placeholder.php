<?php
use ViewHelpers as V;
?>
<section class="section error-page">
    <span class="badge">In lavorazione</span>
    <h1>Prenotazione non ancora collegata</h1>
    <p>Il flusso di prenotazione sara collegato ai Control dedicati nella prossima fase.</p>
    <a class="btn btn-accent" href="<?= V::e(V::url('/')) ?>">Torna alla home</a>
</section>

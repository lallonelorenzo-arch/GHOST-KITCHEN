<?php
use ViewHelpers as V;
?>
<section class="section error-page">
    <span class="badge neutral">Servizio non disponibile</span>
    <h1>Seleziona una risorsa valida</h1>
    <p>Per completare una prenotazione apri il profilo di uno chef o il dettaglio di una ghost kitchen e usa il pulsante dedicato.</p>
    <a class="btn btn-accent" href="<?= V::e(V::url('/')) ?>">Torna alla home</a>
</section>

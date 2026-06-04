<?php
use ViewHelpers as V;
?>
<section class="page-hero">
    <h1>Ricerca</h1>
    <p>Scegli cosa cercare: chef privati o ghost kitchen professionali.</p>
</section>

<section class="section split">
    <form class="search-panel" method="get" action="<?= V::e(V::url('/ricerca/chef')) ?>">
        <h2>Trova il tuo Chef</h2>
        <label>Tipo di cucina
            <input name="tipologiaCucina" type="text" placeholder="giapponese, fusion, mediterranea">
        </label>
        <label>Budget massimo
            <input name="budgetMax" type="number" min="0" step="1" placeholder="250">
        </label>
        <label>Valutazione minima
            <input name="valutazioneMin" type="number" min="0" max="5" step="1" placeholder="4">
        </label>
        <button class="btn btn-accent" type="submit">Cerca Chef</button>
    </form>

    <form class="search-panel" method="get" action="<?= V::e(V::url('/ricerca/ghost-kitchen')) ?>">
        <h2>Cerca Ghost Kitchen</h2>
        <label>Localita
            <input name="localita" type="text" placeholder="Roma">
        </label>
        <label>Prezzo orario massimo
            <input name="budgetMax" type="number" min="0" step="1" placeholder="60">
        </label>
        <label>Valutazione minima
            <input name="valutazioneMin" type="number" min="0" max="5" step="1" placeholder="4">
        </label>
        <button class="btn btn-accent" type="submit">Cerca Cucine</button>
    </form>
</section>

<?php
use ViewHelpers as V;
/** @var EChef|null $chef */
/** @var array $menuDisponibili */
/** @var array $disponibilitaChef */
/** @var array $form */
/** @var string|null $messaggioAccesso */
/** @var string|null $erroreForm */
/** @var string|null $messaggioSuccesso */
/** @var EPrenotazioneChef|null $prenotazione */
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Prenotazione chef</h1>
    <p>Scegli menu, data e dettagli del servizio: la richiesta verra inviata allo chef per conferma.</p>
</section>

<section class="section ops-flow">
    <?php if (!empty($errore)): ?>
        <div class="alert"><?= V::e($errore) ?></div>
    <?php elseif ($chef !== null): ?>
        <?php if (!empty($messaggioAccesso)): ?>
            <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
        <?php endif; ?>
        <?php if (!empty($erroreForm)): ?>
            <div class="alert"><?= V::e($erroreForm) ?></div>
        <?php endif; ?>
        <?php if (!empty($messaggioSuccesso)): ?>
            <div class="notice"><?= V::e($messaggioSuccesso) ?></div>
        <?php endif; ?>

        <div class="ops-grid">
            <article class="ops-panel">
                <h2><?= V::e($chef->getNome() . ' ' . $chef->getCognome()) ?></h2>
                <p><?= V::e($chef->getSpecializzazione()) ?></p>
                <dl class="ops-meta">
                    <div><dt>Cucina</dt><dd><?= V::e($chef->getTipologiaCucina()) ?></dd></div>
                    <div><dt>Prezzo base</dt><dd>&euro; <?= V::e(V::money($chef->getPrezzoBase())) ?></dd></div>
                    <div><dt>Valutazione</dt><dd><?= V::e($chef->getValutazioneMedia()) ?>/5</dd></div>
                </dl>
            </article>

            <form class="ops-panel ops-form" method="post" action="<?= V::e(V::url('/prenotazione/chef/' . $chef->getIdChef())) ?>">
                <h2>Dati richiesta</h2>
                <label>Menu
                    <select name="idMenu" required>
                        <option value="">Seleziona menu</option>
                        <?php foreach ($menuDisponibili as $menu): ?>
                            <option value="<?= V::e($menu->getIdMenu()) ?>" <?= (string) ($form['idMenu'] ?? '') === (string) $menu->getIdMenu() ? 'selected' : '' ?>>
                                <?= V::e($menu->getNome()) ?> - &euro; <?= V::e(V::money($menu->getPrezzoPersona())) ?> a persona
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <?php if ($menuDisponibili === []): ?>
                    <p class="muted-text">Nessun menu disponibile per questo chef.</p>
                <?php endif; ?>
                <div class="ops-form-row">
                    <label>Data <input type="date" name="dataServizio" value="<?= V::e($form['dataServizio'] ?? '') ?>" required></label>
                    <label>Persone <input type="number" name="numeroPersone" min="1" value="<?= V::e($form['numeroPersone'] ?? '2') ?>" required></label>
                </div>
                <div class="ops-form-row">
                    <label>Ora inizio <input type="time" name="oraInizio" value="<?= V::e($form['oraInizio'] ?? '') ?>" required></label>
                    <label>Ora fine <input type="time" name="oraFine" value="<?= V::e($form['oraFine'] ?? '') ?>" required></label>
                </div>
                <label>Indirizzo servizio
                    <input name="indirizzoServizio" value="<?= V::e($form['indirizzoServizio'] ?? '') ?>" required>
                </label>
                <label>Richieste speciali
                    <textarea name="richiesteSpeciali" rows="3"><?= V::e($form['richiesteSpeciali'] ?? '') ?></textarea>
                </label>
                <label>Note interne
                    <textarea name="note" rows="2"><?= V::e($form['note'] ?? '') ?></textarea>
                </label>
                <button class="btn btn-accent" type="submit">Invia richiesta</button>
            </form>
        </div>

        <?php if ($prenotazione !== null): ?>
            <article class="ops-panel ops-result">
                <h2>Riepilogo richiesta #<?= V::e($prenotazione->getIdPrenotazione()) ?></h2>
                <p>Stato: <?= V::e($prenotazione->getStato()) ?> - Importo stimato: &euro; <?= V::e(V::money($prenotazione->getImportoTotale())) ?></p>
                <a class="btn btn-accent" href="<?= V::e(V::url('/pagamento/chef/' . $prenotazione->getIdPrenotazione())) ?>">Vai al pagamento</a>
            </article>
        <?php endif; ?>

        <section class="ops-panel">
            <h2>Disponibilita chef</h2>
            <div class="ops-list">
                <?php foreach ($disponibilitaChef as $slot): ?>
                    <div class="ops-list-item">
                        <strong><?= V::e($slot->getData()) ?></strong>
                        <span><?= V::e($slot->getOraInizio()) ?> - <?= V::e($slot->getOraFine()) ?></span>
                        <span class="badge"><?= V::e($slot->getStato()) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($disponibilitaChef === []): ?>
                <p class="muted-text">Nessuno slot pubblicato per questo chef.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</section>

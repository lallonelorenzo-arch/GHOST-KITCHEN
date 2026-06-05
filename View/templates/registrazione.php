<?php
use ViewHelpers as V;
/** @var array $input */
/** @var string|null $errore */
$input = $input ?? [];
$errore = $errore ?? null;
$ruoliSelezionati = $input['ruoli'] ?? [];
if (!is_array($ruoliSelezionati)) {
    $ruoliSelezionati = [$ruoliSelezionati];
}
$isChecked = static fn (string $ruolo): string => in_array($ruolo, $ruoliSelezionati, true) ? ' checked' : '';
$value = static fn (string $key): string => V::e((string) ($input[$key] ?? ''));
?>
<section class="page-hero compact-hero">
    <h1>Registrazione</h1>
    <p>Crea un account cliente, chef, gestore o un profilo professionale con piu ruoli.</p>
</section>

<section class="section auth-section registration-section">
    <form class="auth-card registration-card" method="post" action="<?= V::e(V::url('/registrazione')) ?>" enctype="multipart/form-data">
        <?php if (!empty($errore)): ?>
            <div class="alert"><?= V::e($errore) ?></div>
        <?php endif; ?>

        <fieldset class="registration-fieldset">
            <legend>Dati account</legend>
            <div class="ops-form-row">
                <label>Nome
                    <input name="nome" value="<?= $value('nome') ?>" required autocomplete="given-name">
                </label>
                <label>Cognome
                    <input name="cognome" value="<?= $value('cognome') ?>" required autocomplete="family-name">
                </label>
            </div>
            <label>Email
                <input name="email" type="email" value="<?= $value('email') ?>" required autocomplete="email">
            </label>
            <div class="ops-form-row">
                <label>Telefono
                    <input name="telefono" value="<?= $value('telefono') ?>" required autocomplete="tel">
                </label>
                <label>Localita
                    <input name="localita" value="<?= $value('localita') ?>" autocomplete="address-level2">
                </label>
            </div>
            <div class="ops-form-row">
                <label>Password
                    <span class="password-field">
                        <input name="password" type="password" required autocomplete="new-password" minlength="8">
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Mostra password">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </span>
                </label>
                <label>Conferma password
                    <span class="password-field">
                        <input name="confermaPassword" type="password" required autocomplete="new-password" minlength="8">
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Mostra password">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </span>
                </label>
            </div>
        </fieldset>

        <fieldset class="registration-fieldset">
            <legend>Tipo di account</legend>
            <div class="registration-role-grid">
                <label class="registration-role-option">
                    <input type="checkbox" name="ruoli[]" value="cliente"<?= $isChecked('cliente') ?>>
                    <span><strong>Cliente</strong><small>Puo cercare e prenotare chef o ghost kitchen.</small></span>
                </label>
                <label class="registration-role-option">
                    <input type="checkbox" name="ruoli[]" value="chef"<?= $isChecked('chef') ?>>
                    <span><strong>Chef</strong><small>Richiede verifica admin e certificazioni approvate.</small></span>
                </label>
                <label class="registration-role-option">
                    <input type="checkbox" name="ruoli[]" value="gestore"<?= $isChecked('gestore') ?>>
                    <span><strong>Gestore</strong><small>Puo gestire ghost kitchen; le strutture saranno validate tramite certificazioni.</small></span>
                </label>
            </div>
        </fieldset>

        <fieldset class="registration-fieldset professional-fieldset">
            <legend>Dati chef</legend>
            <p class="muted-text">Compila questa sezione se hai selezionato il ruolo chef. L'admin dovra approvare certificazioni e profilo prima della prenotabilita.</p>
            <label>Biografia professionale
                <textarea name="biografiaChef" rows="3"><?= $value('biografiaChef') ?></textarea>
            </label>
            <div class="ops-form-row">
                <label>Specializzazione
                    <input name="specializzazione" value="<?= $value('specializzazione') ?>" placeholder="es. Cucina mediterranea">
                </label>
                <label>Tipologia cucina
                    <input name="tipologiaCucina" value="<?= $value('tipologiaCucina') ?>" placeholder="es. italiana, fusion, giapponese">
                </label>
            </div>
            <div class="ops-form-row">
                <label>Prezzo base
                    <input name="prezzoBase" type="number" step="0.01" min="0" value="<?= $value('prezzoBase') ?>">
                </label>
                <label>Anni esperienza
                    <input name="anniEsperienza" type="number" min="0" value="<?= $value('anniEsperienza') ?>">
                </label>
            </div>
            <div class="ops-form-row">
                <label>Tipo certificazione
                    <input name="tipoCertificazione" value="<?= $value('tipoCertificazione') ?>" placeholder="es. HACCP Livello 3">
                </label>
                <label>Certificazioni
                    <input name="certificazioni[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.webp">
                </label>
            </div>
        </fieldset>

        <label>Biografia personale o note
            <textarea name="biografia" rows="3"><?= $value('biografia') ?></textarea>
        </label>

        <button class="btn btn-accent" type="submit">Crea account</button>
        <p class="auth-switch">Hai gia un account? <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></p>
    </form>
</section>

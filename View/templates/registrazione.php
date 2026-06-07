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
$initialStep = 1;
$erroreLower = strtolower((string) $errore);
if ($errore !== null && str_contains($erroreLower, 'ghost kitchen')) {
    $initialStep = 4;
} elseif ($errore !== null && str_contains($erroreLower, 'chef')) {
    $initialStep = 3;
} elseif ($errore !== null && (str_contains($erroreLower, 'cliente') || str_contains($erroreLower, 'tipo di account') || str_contains($erroreLower, 'ruoli'))) {
    $initialStep = 2;
}
?>
<section class="page-hero compact-hero">
    <h1>Registrazione</h1>
    <p>Crea un account cliente oppure un profilo professionale come chef, gestore o entrambi.</p>
</section>

<section class="section auth-section registration-section">
    <form class="auth-card registration-card registration-wizard" method="post" action="<?= V::e(V::url('/registrazione')) ?>" enctype="multipart/form-data" data-registration-form data-registration-wizard data-initial-step="<?= V::e($initialStep) ?>">
        <?php if (!empty($errore)): ?>
            <div class="alert"><?= V::e($errore) ?></div>
        <?php endif; ?>

        <div class="wizard-progress" aria-label="Avanzamento registrazione">
            <span class="is-active" data-step-indicator="1">Account</span>
            <span data-step-indicator="2">Ruolo</span>
            <span data-step-indicator="3">Chef</span>
            <span data-step-indicator="4">Ghost kitchen</span>
            <span data-step-indicator="5">Conferma</span>
        </div>

        <fieldset class="registration-fieldset registration-step is-active" data-registration-step="1">
            <legend>Dati account</legend>
            <p class="muted-text">Partiamo dalle informazioni essenziali per creare il tuo accesso.</p>
            <div class="ops-form-row">
                <label>Nome
                    <input name="nome" value="<?= $value('nome') ?>" required maxlength="100" autocomplete="given-name">
                </label>
                <label>Cognome
                    <input name="cognome" value="<?= $value('cognome') ?>" required maxlength="100" autocomplete="family-name">
                </label>
            </div>
            <label>Email
                <input name="email" type="email" value="<?= $value('email') ?>" required autocomplete="email">
            </label>
            <div class="ops-form-row">
                <label>Telefono
                    <input name="telefono" value="<?= $value('telefono') ?>" required maxlength="30" autocomplete="tel">
                </label>
                <label>Localita
                    <input name="localita" value="<?= $value('localita') ?>" autocomplete="address-level2">
                </label>
            </div>
            <div class="ops-form-row">
                <label>Password
                    <span class="password-field">
                        <input name="password" type="password" required autocomplete="new-password" minlength="8" maxlength="128">
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Mostra password">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </span>
                </label>
                <label>Conferma password
                    <span class="password-field">
                        <input name="confermaPassword" type="password" required autocomplete="new-password" minlength="8" maxlength="128">
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Mostra password">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </span>
                </label>
            </div>
            <div class="wizard-actions">
                <span></span>
                <button class="btn btn-accent" type="button" data-wizard-next>Continua</button>
            </div>
        </fieldset>

        <fieldset class="registration-fieldset registration-step" data-registration-step="2" hidden>
            <legend>Tipo di account</legend>
            <p class="muted-text">Cliente e ruoli professionali sono alternativi. Chef e gestore possono essere selezionati insieme.</p>
            <div class="registration-role-grid">
                <label class="registration-role-option">
                    <input type="checkbox" name="ruoli[]" value="cliente" data-role-client<?= $isChecked('cliente') ?>>
                    <span><strong>Cliente</strong><small>Puo cercare e prenotare chef o ghost kitchen.</small></span>
                </label>
                <label class="registration-role-option">
                    <input type="checkbox" name="ruoli[]" value="chef" data-role-professional<?= $isChecked('chef') ?>>
                    <span><strong>Chef</strong><small>Richiede verifica admin e certificazioni approvate.</small></span>
                </label>
                <label class="registration-role-option">
                    <input type="checkbox" name="ruoli[]" value="gestore" data-role-professional<?= $isChecked('gestore') ?>>
                    <span><strong>Gestore</strong><small>Puo gestire ghost kitchen; le strutture saranno validate tramite certificazioni.</small></span>
                </label>
            </div>
            <div class="wizard-actions">
                <button class="btn btn-ghost" type="button" data-wizard-prev>Indietro</button>
                <button class="btn btn-accent" type="button" data-wizard-next>Continua</button>
            </div>
        </fieldset>

        <fieldset class="registration-fieldset professional-fieldset registration-step" data-registration-step="3" hidden>
            <legend>Dati chef</legend>
            <p class="muted-text">Compila questa sezione se hai selezionato il ruolo chef. L'admin dovra approvare certificazioni e profilo prima della prenotabilita.</p>
            <label>Biografia professionale
                <textarea name="biografiaChef" rows="3"><?= $value('biografiaChef') ?></textarea>
            </label>
            <div class="ops-form-row">
                <label>Specializzazione
                    <input name="specializzazione" value="<?= $value('specializzazione') ?>" maxlength="150" placeholder="es. Cucina mediterranea">
                </label>
                <label>Tipologia cucina
                    <input name="tipologiaCucina" value="<?= $value('tipologiaCucina') ?>" maxlength="80" placeholder="es. italiana, fusion, giapponese">
                </label>
            </div>
            <div class="ops-form-row">
                <label>Prezzo base
                    <input name="prezzoBase" type="number" step="0.01" min="0" max="10000" value="<?= $value('prezzoBase') ?>">
                </label>
                <label>Anni esperienza
                    <input name="anniEsperienza" type="number" min="0" max="80" value="<?= $value('anniEsperienza') ?>">
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
            <div class="wizard-actions">
                <button class="btn btn-ghost" type="button" data-wizard-prev>Indietro</button>
                <button class="btn btn-accent" type="button" data-wizard-next>Continua</button>
            </div>
        </fieldset>

        <fieldset class="registration-fieldset professional-fieldset registration-step" data-registration-step="4" hidden>
            <legend>Dati ghost kitchen</legend>
            <p class="muted-text">Compila questa sezione se hai selezionato il ruolo gestore. La cucina restera sospesa finche il profilo non sara verificato.</p>
            <label>Nome ghost kitchen
                <input name="ghostKitchenNome" value="<?= $value('ghostKitchenNome') ?>" maxlength="150" placeholder="es. Milano Isola Lab">
            </label>
            <label>Descrizione
                <textarea name="ghostKitchenDescrizione" rows="3" placeholder="Descrivi spazio, postazioni e uso ideale"><?= $value('ghostKitchenDescrizione') ?></textarea>
            </label>
            <div class="ops-form-row">
                <label>Indirizzo
                    <input name="ghostKitchenIndirizzo" value="<?= $value('ghostKitchenIndirizzo') ?>" maxlength="255" placeholder="Via e numero civico">
                </label>
                <label>Citta
                    <input name="ghostKitchenCitta" value="<?= $value('ghostKitchenCitta') ?>" maxlength="100" placeholder="es. Milano">
                </label>
            </div>
            <div class="ops-form-row">
                <label>CAP
                    <input name="ghostKitchenCap" value="<?= $value('ghostKitchenCap') ?>" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" placeholder="20100">
                </label>
                <label>Prezzo orario
                    <input name="ghostKitchenPrezzoOrario" type="number" step="0.01" min="1" max="1000" value="<?= $value('ghostKitchenPrezzoOrario') ?>">
                </label>
            </div>
            <div class="ops-form-row">
                <label>Capienza
                    <input name="ghostKitchenCapienza" type="number" min="1" max="500" value="<?= $value('ghostKitchenCapienza') ?>">
                </label>
                <label>Metri quadri
                    <input name="ghostKitchenMq" type="number" step="0.01" min="1" max="5000" value="<?= $value('ghostKitchenMq') ?>">
                </label>
            </div>
            <div class="wizard-actions">
                <button class="btn btn-ghost" type="button" data-wizard-prev>Indietro</button>
                <button class="btn btn-accent" type="button" data-wizard-next>Continua</button>
            </div>
        </fieldset>

        <fieldset class="registration-fieldset registration-step" data-registration-step="5" hidden>
            <legend>Conferma</legend>
            <p class="muted-text">Aggiungi eventuali note personali e completa la registrazione.</p>
            <label>Biografia personale o note
                <textarea name="biografia" rows="3"><?= $value('biografia') ?></textarea>
            </label>
            <div class="registration-review">
                <div>
                    <strong>Account</strong>
                    <span>I dati inseriti verranno usati per accesso e comunicazioni.</span>
                </div>
                <div>
                    <strong>Ruoli</strong>
                    <span>Cliente da solo, oppure chef e gestore anche insieme.</span>
                </div>
            </div>
            <div class="wizard-actions">
                <button class="btn btn-ghost" type="button" data-wizard-prev>Indietro</button>
                <button class="btn btn-accent" type="submit">Crea account</button>
            </div>
        </fieldset>
        <p class="auth-switch">Hai gia un account? <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></p>
    </form>
</section>

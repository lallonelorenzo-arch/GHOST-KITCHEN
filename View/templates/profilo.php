<?php
use ViewHelpers as V;
/** @var array $accesso */
/** @var string|null $messaggioAccesso */
/** @var array $storicoPagamenti */
/** @var string $section */
/** @var bool $isEditing */
$accesso = $accesso ?? [];
$storicoPagamenti = $storicoPagamenti ?? [];
$nome = trim((string) (($accesso['nome'] ?? '') . ' ' . ($accesso['cognome'] ?? '')));
$nome = $nome !== '' ? $nome : 'Profilo utente';
$email = (string) ($accesso['email'] ?? '');
$ruoli = is_array($accesso['ruoli'] ?? null) ? $accesso['ruoli'] : [];
$isAdmin = in_array('admin', $ruoli, true) || in_array('amministratore', $ruoli, true);
$isChef = in_array('chef', $ruoli, true);
$isGestore = in_array('gestore', $ruoli, true);
$fotoProfilo = (string) ($accesso['fotoProfilo'] ?? '');
$iniziali = strtoupper(substr((string) ($accesso['nome'] ?? 'G'), 0, 1) . substr((string) ($accesso['cognome'] ?? 'K'), 0, 1));
$iniziali = trim($iniziali) !== '' ? $iniziali : 'GK';
$telefono = trim((string) ($accesso['telefono'] ?? $accesso['phone'] ?? ''));
$indirizzo = trim((string) ($accesso['indirizzo'] ?? $accesso['via'] ?? ''));
$citta = trim((string) ($accesso['citta'] ?? ''));
$provincia = trim((string) ($accesso['provincia'] ?? ''));
$numeroCivico = trim((string) ($accesso['numeroCivico'] ?? ''));
$bio = trim((string) ($accesso['biografia'] ?? $accesso['bio'] ?? $accesso['descrizione'] ?? $accesso['descrizioneChef'] ?? ''));
$ruoliLabel = $ruoli !== [] ? implode(', ', array_map('ucfirst', $ruoli)) : 'Nessun ruolo assegnato';
$section = (string) ($section ?? 'profilo');
$isEditing = (bool) ($isEditing ?? false);
$profileFields = [
    'Nome completo' => $nome,
    'Email' => $email !== '' ? $email : 'Non disponibile',
    'Telefono' => $telefono !== '' ? $telefono : 'Non disponibile',
    'Indirizzo' => trim($indirizzo . ' ' . $numeroCivico) ?: 'Non disponibile',
    'Città' => $citta !== '' ? $citta : 'Non disponibile',
    'Provincia' => $provincia !== '' ? $provincia : 'Non disponibile',
    'Ruolo' => $ruoliLabel,
    'Biografia' => $bio !== '' ? $bio : 'Non disponibile',
];
$profileNav = [
    ['label' => 'Profilo', 'href' => '/profilo', 'active' => $section === 'profilo'],
    ['label' => 'Sicurezza', 'href' => '/profilo?section=sicurezza', 'active' => $section === 'sicurezza'],
    ['label' => 'Notifiche', 'href' => '/profilo?section=notifiche', 'active' => $section === 'notifiche'],
    ['label' => 'Pagamenti', 'href' => '/profilo?section=pagamenti', 'active' => $section === 'pagamenti'],
];
?>
<section class="profile-page">
    <div class="profile-title">
        <div>
            <span class="eyebrow profile-eyebrow">Area personale</span>
            <h1>Profilo account</h1>
            <p>Gestisci informazioni personali e collegamenti operativi disponibili per i tuoi ruoli.</p>
        </div>
    </div>

    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Vai al login</a></div>
    <?php else: ?>
        <div class="account-shell">
            <aside class="account-sidebar" aria-label="Menu profilo">
                <div class="account-card">
                    <span class="profile-avatar" aria-hidden="true">
                        <?php if ($fotoProfilo !== ''): ?>
                            <img src="<?= V::e(V::url($fotoProfilo)) ?>" alt="">
                        <?php else: ?>
                            <?= V::e($iniziali) ?>
                        <?php endif; ?>
                    </span>
                    <h2><?= V::e($nome) ?></h2>
                    <div class="role-stack">
                        <?php foreach ($ruoli as $ruolo): ?>
                            <span class="role-pill"><?= V::e(ucfirst((string) $ruolo)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <form class="profile-photo-form" method="post" action="<?= V::e(V::url('/profilo')) ?>" enctype="multipart/form-data">
                        <input type="hidden" name="azione" value="foto">
                        <label>Foto profilo <input type="file" name="fotoProfilo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required></label>
                        <button class="btn btn-ghost" type="submit">Aggiorna foto</button>
                    </form>
                </div>
                <nav class="account-nav">
                    <?php foreach ($profileNav as $item): ?>
                        <a class="<?= $item['active'] ? 'is-active' : '' ?>" href="<?= V::e(V::url($item['href'])) ?>">
                            <?= V::e($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </aside>

            <section class="account-main">
                <?php if ($section === 'profilo'): ?>
                <article class="account-panel">
                    <div class="account-panel-head">
                        <div>
                            <span class="eyebrow profile-eyebrow">Account</span>
                            <h2>Informazioni personali</h2>
                        </div>
                        <?php if (!$isEditing): ?>
                            <a class="btn btn-accent" href="<?= V::e(V::url('/profilo', ['edit' => '1'])) ?>">Modifica</a>
                        <?php endif; ?>
                    </div>

                    <?php if ($isEditing): ?>
                        <form class="profile-edit-form" method="post" action="<?= V::e(V::url('/profilo')) ?>">
                            <label>Nome
                                <input name="nome" value="<?= V::e((string) ($accesso['nome'] ?? '')) ?>" required>
                            </label>
                            <label>Cognome
                                <input name="cognome" value="<?= V::e((string) ($accesso['cognome'] ?? '')) ?>" required>
                            </label>
                            <label>Email
                                <input type="email" name="email" value="<?= V::e($email) ?>" required>
                            </label>
                            <label>Telefono
                                <input name="telefono" value="<?= V::e($telefono) ?>">
                            </label>
                            <label>Indirizzo
                                <input name="indirizzo" maxlength="180" value="<?= V::e($indirizzo) ?>" autocomplete="street-address">
                            </label>
                            <label>Numero civico
                                <input name="numeroCivico" maxlength="20" value="<?= V::e($numeroCivico) ?>">
                            </label>
                            <label>Città
                                <input name="citta" maxlength="120" value="<?= V::e($citta) ?>" autocomplete="address-level2">
                            </label>
                            <label>Provincia
                                <select name="provincia" autocomplete="address-level1">
                                    <option value="">Seleziona</option>
                                    <?php foreach (EUtente::SIGLE_PROVINCE_ITALIANE as $siglaProvincia): ?>
                                        <option value="<?= V::e($siglaProvincia) ?>" <?= strtoupper($provincia) === $siglaProvincia ? 'selected' : '' ?>>
                                            <?= V::e($siglaProvincia) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>Ruolo
                                <input value="<?= V::e($ruoliLabel) ?>" disabled>
                            </label>
                            <label class="is-wide">Biografia
                                <textarea name="biografia" rows="5"><?= V::e($bio) ?></textarea>
                            </label>
                            <div class="form-actions is-wide">
                                <a class="btn btn-ghost" href="<?= V::e(V::url('/profilo')) ?>">Annulla</a>
                                <button class="btn btn-accent" type="submit">Salva modifiche</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <dl class="profile-fields">
                            <?php foreach ($profileFields as $label => $value): ?>
                                <div class="<?= $label === 'Biografia' ? 'is-wide' : '' ?>">
                                    <dt><?= V::e($label) ?></dt>
                                    <dd><?= V::e($value) ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>
                    <?php endif; ?>
                </article>
                <?php elseif ($section === 'sicurezza'): ?>
                    <article class="account-panel">
                        <div class="account-panel-head">
                            <div>
                                <span class="eyebrow profile-eyebrow">Sicurezza</span>
                                <h2>Cambia password</h2>
                            </div>
                        </div>
                        <form class="profile-edit-form" method="post" action="<?= V::e(V::url('/profilo')) ?>">
                            <input type="hidden" name="azione" value="password">
                            <label class="is-wide">Password attuale
                                <span class="password-field">
                                    <input type="password" name="passwordAttuale" autocomplete="current-password" required>
                                    <button type="button" class="password-toggle" data-password-toggle aria-label="Mostra password">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </span>
                            </label>
                            <label>Nuova password
                                <span class="password-field">
                                    <input type="password" name="nuovaPassword" autocomplete="new-password" minlength="8" required>
                                    <button type="button" class="password-toggle" data-password-toggle aria-label="Mostra password">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </span>
                            </label>
                            <label>Conferma nuova password
                                <span class="password-field">
                                    <input type="password" name="confermaPassword" autocomplete="new-password" minlength="8" required>
                                    <button type="button" class="password-toggle" data-password-toggle aria-label="Mostra password">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </span>
                            </label>
                            <div class="form-actions is-wide">
                                <button class="btn btn-accent" type="submit">Aggiorna password</button>
                            </div>
                        </form>
                    </article>
                <?php elseif ($section === 'notifiche'): ?>
                    <article class="account-panel">
                        <div class="account-panel-head">
                            <div>
                                <span class="eyebrow profile-eyebrow">Notifiche</span>
                                <h2>Preferenze notifiche</h2>
                            </div>
                        </div>
                        <div class="empty-state">La gestione delle notifiche non e ancora attiva.</div>
                    </article>
                <?php elseif ($section === 'pagamenti'): ?>
                    <article class="account-panel">
                        <div class="account-panel-head">
                            <div>
                                <span class="eyebrow profile-eyebrow">Pagamenti</span>
                                <h2>Storico transazioni</h2>
                            </div>
                        </div>

                        <section class="payment-history">
                            <?php if ($storicoPagamenti === []): ?>
                                <p>Nessuna transazione registrata.</p>
                            <?php else: ?>
                                <div class="transaction-list">
                                    <?php foreach ($storicoPagamenti as $item): ?>
                                        <?php $pagamento = $item['pagamento']; ?>
                                        <div class="transaction-item">
                                            <span>
                                                <strong><?= V::e($item['descrizione']) ?></strong>
                                                <small><?= V::e($item['data'] !== '' ? $item['data'] : $pagamento->getStato()) ?></small>
                                            </span>
                                            <b>&euro;<?= V::e(V::money($pagamento->getImporto())) ?></b>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    </article>
                <?php endif; ?>
            </section>
        </div>
    <?php endif; ?>
</section>

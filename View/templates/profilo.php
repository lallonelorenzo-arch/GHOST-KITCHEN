<?php
use ViewHelpers as V;
/** @var array $accesso */
/** @var string|null $messaggioAccesso */
$accesso = $accesso ?? [];
$nome = trim((string) (($accesso['nome'] ?? '') . ' ' . ($accesso['cognome'] ?? '')));
$nome = $nome !== '' ? $nome : 'Profilo utente';
$email = (string) ($accesso['email'] ?? '');
$ruoloAttivo = (string) ($accesso['ruoloAttivo'] ?? '');
$ruoli = $accesso['ruoli'] ?? [];
$iniziali = strtoupper(substr((string) ($accesso['nome'] ?? 'G'), 0, 1) . substr((string) ($accesso['cognome'] ?? 'K'), 0, 1));
$iniziali = trim($iniziali) !== '' ? $iniziali : 'GK';
?>
<section class="page-hero compact-hero">
    <div class="profile-hero">
        <span class="profile-avatar" aria-hidden="true"><?= V::e($iniziali) ?></span>
        <div>
            <span class="eyebrow">Area personale</span>
            <h1><?= V::e($nome) ?></h1>
            <p>Gestisci il tuo accesso, le attivita operative e le sezioni collegate al ruolo corrente.</p>
        </div>
        <?php if ($ruoloAttivo !== ''): ?>
            <span class="badge"><?= V::e($ruoloAttivo) ?></span>
        <?php endif; ?>
    </div>
</section>

<section class="section ops-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Vai al login</a></div>
    <?php else: ?>
        <div class="profile-layout">
            <article class="profile-panel">
                <h2>Dettagli account</h2>
                <dl class="profile-list">
                    <div><dt>Nome</dt><dd><?= V::e($nome) ?></dd></div>
                    <div><dt>Email</dt><dd><?= V::e($email !== '' ? $email : 'Non disponibile') ?></dd></div>
                    <div><dt>Ruolo attivo</dt><dd><?= V::e($ruoloAttivo !== '' ? $ruoloAttivo : 'Non definito') ?></dd></div>
                    <div><dt>Ruoli</dt><dd><?= V::e($ruoli !== [] ? implode(', ', $ruoli) : 'Nessun ruolo assegnato') ?></dd></div>
                </dl>
            </article>

            <article class="profile-panel">
                <div class="toolbar">
                    <div>
                        <h2>Azioni rapide</h2>
                        <p>Collegamenti alle aree operative disponibili nell'applicazione.</p>
                    </div>
                </div>
                <div class="quick-links">
                    <a class="quick-link" href="<?= V::e(V::url('/disponibilita')) ?>">
                        <strong>Disponibilita</strong>
                        <span>Aggiungi slot per chef o ghost kitchen.</span>
                    </a>
                    <a class="quick-link" href="<?= V::e(V::url('/richieste')) ?>">
                        <strong>Richieste</strong>
                        <span>Gestisci prenotazioni in attesa di risposta.</span>
                    </a>
                    <a class="quick-link" href="<?= V::e(V::url('/dashboard')) ?>">
                        <strong>Dashboard</strong>
                        <span>Controlla metriche, pagamenti e moderazione.</span>
                    </a>
                    <a class="quick-link" href="<?= V::e(V::url('/certificazioni')) ?>">
                        <strong>Certificazioni</strong>
                        <span>Verifica documenti e richieste degli chef.</span>
                    </a>
                </div>
            </article>
        </div>
    <?php endif; ?>
</section>

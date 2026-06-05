<?php
/** @var string $contentTemplate */
/** @var array|null $utenteCorrente */
/** @var string $currentPath */
use ViewHelpers as V;
$currentPath = (string) ($currentPath ?? '/');
$isActive = static fn (string $path): string => ($path === '/' ? $currentPath === '/' : str_starts_with($currentPath, $path)) ? ' is-active' : '';
$utenteNome = $utenteCorrente !== null ? trim((string) (($utenteCorrente['nome'] ?? '') . ' ' . ($utenteCorrente['cognome'] ?? ''))) : '';
$utenteNome = $utenteNome !== '' ? $utenteNome : 'Account';
$utenteRuolo = $utenteCorrente !== null ? (string) ($utenteCorrente['ruolo'] ?? 'utente') : '';
$utenteRuoli = $utenteCorrente !== null && is_array($utenteCorrente['ruoli'] ?? null) ? $utenteCorrente['ruoli'] : [];
$isAdminUser = in_array('admin', $utenteRuoli, true) || in_array('amministratore', $utenteRuoli, true);
$isChefUser = in_array('chef', $utenteRuoli, true);
$isGestoreUser = in_array('gestore', $utenteRuoli, true);
$fotoProfilo = $utenteCorrente !== null ? (string) ($utenteCorrente['fotoProfilo'] ?? '') : '';
$iniziali = '';
if ($utenteCorrente !== null) {
    $iniziali = strtoupper(substr((string) ($utenteCorrente['nome'] ?? 'A'), 0, 1) . substr((string) ($utenteCorrente['cognome'] ?? ''), 0, 1));
    $iniziali = trim($iniziali) !== '' ? $iniziali : 'GK';
}
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ghost Kitchen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= V::e(V::asset('css/app.css')) ?>">
    <script>window.GK_BASE_URL = <?= json_encode((string) ($GLOBALS['view_base_url'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;</script>
    <script defer src="<?= V::e(V::asset('js/app.js')) ?>?v=<?= V::e((string) @filemtime(dirname(__DIR__, 2) . '/public/assets/js/app.js')) ?>"></script>
</head>
<body>
<header class="site-header">
    <nav class="navbar">
        <a class="brand" href="<?= V::e(V::url('/')) ?>">
            <span class="brand-icon" aria-hidden="true">
                <svg viewBox="0 0 32 32" focusable="false">
                    <path d="M10 14c-2.2-.4-3.8-2.1-3.8-4.2 0-2.4 2-4.3 4.6-4.3.8 0 1.6.2 2.2.5 1-1.7 2.8-2.8 5-2.8 3 0 5.4 2.1 5.7 4.8 1.6.6 2.7 2 2.7 3.8 0 2.1-1.6 3.8-3.7 4.2v8.5H10V14Z"/>
                    <path d="M10 18h12M10 22h12"/>
                </svg>
            </span>
            <span>Ghost Kitchen</span>
        </a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-label="Apri menu">Menu</button>
        <div class="nav-links" data-nav-links>
            <?php if (!$isAdminUser): ?>
                <a class="<?= V::e(trim($isActive('/ricerca/chef'))) ?>" href="<?= V::e(V::url('/ricerca/chef')) ?>">Trova Chef</a>
                <a class="<?= V::e(trim($isActive('/ricerca/ghost-kitchen'))) ?>" href="<?= V::e(V::url('/ricerca/ghost-kitchen')) ?>">Ghost Kitchen</a>
            <?php endif; ?>
            <?php if ($utenteCorrente !== null): ?>
                <?php if (!$isAdminUser): ?>
                    <a class="<?= V::e(trim($isActive('/prenotazioni'))) ?>" href="<?= V::e(V::url('/prenotazioni')) ?>">Le mie prenotazioni</a>
                <?php endif; ?>
                <?php if ($isChefUser || $isGestoreUser): ?>
                    <a class="<?= V::e(trim($isActive('/disponibilita'))) ?>" href="<?= V::e(V::url('/disponibilita')) ?>">Disponibilita</a>
                    <a class="<?= V::e(trim($isActive('/richieste'))) ?>" href="<?= V::e(V::url('/richieste')) ?>">Richieste</a>
                <?php endif; ?>
                <?php if ($isChefUser): ?>
                    <a class="<?= V::e(trim($isActive('/mie-certificazioni'))) ?>" href="<?= V::e(V::url('/mie-certificazioni')) ?>">Le mie certificazioni</a>
                <?php endif; ?>
                <?php if ($isAdminUser): ?>
                    <a class="<?= V::e(trim($isActive('/dashboard'))) ?>" href="<?= V::e(V::url('/dashboard')) ?>">Dashboard</a>
                    <a class="<?= V::e(trim($isActive('/moderazione'))) ?>" href="<?= V::e(V::url('/moderazione')) ?>">Moderazione</a>
                    <a class="<?= V::e(trim($isActive('/utenti'))) ?>" href="<?= V::e(V::url('/utenti')) ?>">Utenti</a>
                    <a class="<?= V::e(trim($isActive('/certificazioni'))) ?>" href="<?= V::e(V::url('/certificazioni')) ?>">Certificazioni</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="nav-user">
            <?php if ($utenteCorrente !== null): ?>
                <a class="user-menu" href="<?= V::e(V::url('/profilo')) ?>" aria-label="Apri profilo utente">
                    <span class="user-avatar" aria-hidden="true">
                        <?php if ($fotoProfilo !== ''): ?>
                            <img src="<?= V::e(V::url($fotoProfilo)) ?>" alt="">
                        <?php else: ?>
                            <?= V::e($iniziali) ?>
                        <?php endif; ?>
                    </span>
                    <span class="user-copy">
                        <strong><?= V::e($utenteNome) ?></strong>
                        <span><?= V::e($utenteRuolo !== '' ? $utenteRuolo : 'account') ?></span>
                    </span>
                </a>
                <a class="btn btn-ghost" href="<?= V::e(V::url('/logout')) ?>">Logout</a>
            <?php else: ?>
                <a class="btn btn-ghost" href="<?= V::e(V::url('/registrazione')) ?>">Registrati</a>
                <a class="btn btn-ghost" href="<?= V::e(V::url('/login')) ?>">Accedi</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main>
    <?php require $contentTemplate; ?>
</main>
</body>
</html>

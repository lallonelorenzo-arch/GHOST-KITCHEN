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
    <link rel="stylesheet" href="<?= V::e(V::asset('css/app.css')) ?>?v=<?= V::e((string) @filemtime(dirname(__DIR__, 2) . '/public/assets/css/app.css')) ?>">
    <script>window.GK_BASE_URL = <?= json_encode((string) ($GLOBALS['view_base_url'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;</script>
    <script defer src="<?= V::e(V::asset('js/app.js')) ?>?v=<?= V::e((string) @filemtime(dirname(__DIR__, 2) . '/public/assets/js/app.js')) ?>"></script>
</head>
<body>
<header class="site-header">
    <nav class="navbar">
        <a class="brand" href="<?= V::e(V::url('/')) ?>">
            <img class="brand-logo" src="<?= V::e(V::asset('img/ghost-kitchen-logo-final.png')) ?>" alt="Ghost Kitchen">
        </a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-label="Apri menu">Menu</button>
        <div class="nav-links" data-nav-links>
            <a class="<?= V::e(trim($isActive('/ricerca/chef'))) ?>" href="<?= V::e(V::url('/ricerca/chef')) ?>">Trova Chef</a>
            <a class="<?= V::e(trim($isActive('/ricerca/ghost-kitchen'))) ?>" href="<?= V::e(V::url('/ricerca/ghost-kitchen')) ?>">Ghost Kitchen</a>
            <?php if ($utenteCorrente !== null): ?>
                <a class="<?= V::e(trim($isActive('/prenotazioni'))) ?>" href="<?= V::e(V::url('/prenotazioni')) ?>">Le mie prenotazioni</a>
                <?php if ($isChefUser || $isGestoreUser): ?>
                    <a class="<?= V::e(trim($isActive('/disponibilita'))) ?>" href="<?= V::e(V::url('/disponibilita')) ?>">Disponibilita</a>
                    <a class="<?= V::e(trim($isActive('/richieste'))) ?>" href="<?= V::e(V::url('/richieste')) ?>">Richieste</a>
                <?php endif; ?>
                <?php if ($isChefUser): ?>
                    <a class="<?= V::e(trim($isActive('/mie-certificazioni'))) ?>" href="<?= V::e(V::url('/mie-certificazioni')) ?>">Le mie certificazioni</a>
                <?php endif; ?>
                <?php if ($isAdminUser): ?>
                    <a class="<?= V::e(trim($isActive('/dashboard'))) ?>" href="<?= V::e(V::url('/dashboard')) ?>">Dashboard</a>
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
                <a class="btn btn-ghost" href="<?= V::e(V::url('/login')) ?>">Accedi</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main>
    <?php require $contentTemplate; ?>
</main>
<footer class="footer">
    <span><img class="footer-logo" src="<?= V::e(V::asset('img/ghost-kitchen-logo-final.png')) ?>" alt="Ghost Kitchen"></span>
    <span>Prenotazioni, cucine e chef in un unico spazio operativo.</span>
</footer>
</body>
</html>

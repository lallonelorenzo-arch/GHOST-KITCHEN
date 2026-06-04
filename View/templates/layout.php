<?php
/** @var string $contentTemplate */
/** @var array|null $utenteCorrente */
use ViewHelpers as V;
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ghost Kitchen</title>
    <link rel="stylesheet" href="<?= V::e(V::asset('css/app.css')) ?>">
    <script defer src="<?= V::e(V::asset('js/app.js')) ?>"></script>
</head>
<body>
<header class="site-header">
    <nav class="navbar">
        <a class="brand" href="<?= V::e(V::url('/')) ?>">
            <span class="brand-icon">GK</span>
            <span>Ghost Kitchen</span>
        </a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Apri menu">Menu</button>
        <div class="nav-links" data-nav-links>
            <a href="<?= V::e(V::url('/ricerca/chef')) ?>">Trova Chef</a>
            <a href="<?= V::e(V::url('/ricerca/ghost-kitchen')) ?>">Ghost Kitchen</a>
        </div>
        <div class="nav-user">
            <?php if ($utenteCorrente !== null): ?>
                <span class="user-chip"><?= V::e(trim(($utenteCorrente['nome'] ?? '') . ' ' . ($utenteCorrente['cognome'] ?? ''))) ?></span>
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
    <span>Ghost Kitchen</span>
    <span>Interfaccia Figma adattata a ECFV PHP</span>
</footer>
</body>
</html>

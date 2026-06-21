<?php
use ViewHelpers as V;
/** @var array $clienti */
/** @var array $chef */
/** @var array $gestori */
/** @var array $ghostKitchen */
/** @var array $gestoriGhostKitchen */
/** @var array $riepilogoUtenti */
/** @var array $filtriUtenti */
/** @var array $accesso */
/** @var string|null $messaggioAccesso */
$clienti = $clienti ?? [];
$chef = $chef ?? [];
$gestori = $gestori ?? [];
$ghostKitchen = $ghostKitchen ?? [];
$gestoriGhostKitchen = $gestoriGhostKitchen ?? [];
$riepilogoUtenti = $riepilogoUtenti ?? ['clienti' => 0, 'chef' => 0, 'gestori' => 0, 'ghostKitchen' => 0];
$filtriUtenti = $filtriUtenti ?? ['q' => '', 'tipo' => 'tutti', 'stato' => 'tutti'];
$accesso = $accesso ?? [];
$idAdminCorrente = isset($accesso['idUtente']) ? (int) $accesso['idUtente'] : null;
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Utenti</h1>
    <p>Consulta clienti, chef e ghost kitchen registrati nel sistema.</p>
</section>

<section class="section ops-flow admin-users-page">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>

    <div class="ops-grid admin-users-summary">
        <article class="ops-panel moderation-stat">
            <span>Clienti</span>
            <strong><?= V::e($riepilogoUtenti['clienti'] ?? 0) ?></strong>
        </article>
        <article class="ops-panel moderation-stat">
            <span>Chef</span>
            <strong><?= V::e($riepilogoUtenti['chef'] ?? 0) ?></strong>
        </article>
        <article class="ops-panel moderation-stat">
            <span>Gestori</span>
            <strong><?= V::e($riepilogoUtenti['gestori'] ?? 0) ?></strong>
        </article>
        <article class="ops-panel moderation-stat">
            <span>Ghost kitchen</span>
            <strong><?= V::e($riepilogoUtenti['ghostKitchen'] ?? 0) ?></strong>
        </article>
    </div>

    <form class="ops-panel ops-form reviews-filter-panel" method="get" action="<?= V::e(V::url('/utenti')) ?>">
        <div class="toolbar">
            <div>
                <h2>Filtri utenti</h2>
                <p>Sospendi blocca temporaneamente l'account; banna indica un blocco piu severo finche l'admin non lo riattiva.</p>
            </div>
            <div class="actions">
                <a class="btn btn-ghost" href="<?= V::e(V::url('/utenti')) ?>">Reset</a>
                <button class="btn btn-accent" type="submit">Cerca</button>
            </div>
        </div>
        <div class="filter-select-grid">
            <label>Ricerca
                <input name="q" value="<?= V::e($filtriUtenti['q'] ?? '') ?>" placeholder="Nome, email, telefono o citta">
            </label>
            <label>Tipo
                <select name="tipo">
                    <?php foreach (['tutti' => 'Tutti', 'cliente' => 'Clienti', 'chef' => 'Chef', 'gestore' => 'Gestori', 'ghost_kitchen' => 'Ghost kitchen'] as $value => $label): ?>
                        <option value="<?= V::e($value) ?>" <?= ($filtriUtenti['tipo'] ?? 'tutti') === $value ? 'selected' : '' ?>><?= V::e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Stato
                <select name="stato">
                    <?php foreach (['tutti' => 'Tutti', 'attivo' => 'Attivo', 'sospeso' => 'Sospeso', 'bannato' => 'Bannato', 'attiva' => 'GK attiva', 'sospesa' => 'GK sospesa', 'non_disponibile' => 'GK non disponibile'] as $value => $label): ?>
                        <option value="<?= V::e($value) ?>" <?= ($filtriUtenti['stato'] ?? 'tutti') === $value ? 'selected' : '' ?>><?= V::e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
    </form>

    <section class="ops-panel admin-directory-panel">
        <div class="toolbar">
            <div>
                <h2>Clienti registrati</h2>
                <p>Utenti che possono effettuare prenotazioni e gestire i propri pagamenti.</p>
            </div>
        </div>

        <?php if ($clienti === []): ?>
            <div class="empty-state">Nessun cliente registrato.</div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Telefono</th><th>Stato</th><th>Azioni</th></tr></thead>
                    <tbody>
                    <?php foreach ($clienti as $cliente): ?>
                        <tr>
                            <td>#<?= V::e($cliente->getIdCliente()) ?></td>
                            <td><strong><?= V::e(trim($cliente->getNome() . ' ' . $cliente->getCognome())) ?></strong></td>
                            <td><?= V::e($cliente->getEmail()) ?></td>
                            <td><?= V::e($cliente->getTelefono() !== '' ? $cliente->getTelefono() : 'n/d') ?></td>
                            <td><span class="status-pill neutral"><?= V::e($cliente->getStato()) ?></span></td>
                            <td>
                                <div class="admin-row-actions">
                                    <?php if ($cliente->getStato() !== EUtente::STATO_SOSPESO): ?>
                                        <form method="post" action="<?= V::e(V::url('/utenti/utente/' . $cliente->getIdCliente() . '/sospendi')) ?>"><button class="btn btn-ghost btn-small" type="submit">Sospendi</button></form>
                                    <?php endif; ?>
                                    <?php if ($cliente->getStato() !== EUtente::STATO_ATTIVO): ?>
                                        <form method="post" action="<?= V::e(V::url('/utenti/utente/' . $cliente->getIdCliente() . '/riattiva')) ?>"><button class="btn btn-primary btn-small" type="submit">Riattiva</button></form>
                                    <?php endif; ?>
                                    <?php if ($cliente->getStato() !== EUtente::STATO_BANNATO): ?>
                                        <form method="post" action="<?= V::e(V::url('/utenti/utente/' . $cliente->getIdCliente() . '/banna')) ?>"><button class="btn btn-danger btn-small" type="submit">Banna</button></form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="ops-panel admin-directory-panel">
        <div class="toolbar">
            <div>
                <h2>Chef registrati</h2>
                <p>Profili professionali presenti nella piattaforma, con stato verifica e dati principali.</p>
            </div>
        </div>

        <?php if ($chef === []): ?>
            <div class="empty-state">Nessuno chef registrato.</div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Cucina</th><th>Prezzo</th><th>Verifica</th><th>Stato</th><th>Azioni</th></tr></thead>
                    <tbody>
                    <?php foreach ($chef as $item): ?>
                        <tr>
                            <td>#<?= V::e($item->getIdChef()) ?></td>
                            <td><strong><?= V::e(trim($item->getNome() . ' ' . $item->getCognome())) ?></strong></td>
                            <td><?= V::e($item->getEmail()) ?></td>
                            <td><?= V::e($item->getTipologiaCucina() !== '' ? $item->getTipologiaCucina() : 'n/d') ?></td>
                            <td><?= V::e(number_format($item->getPrezzoBase(), 2, ',', '.')) ?> euro</td>
                            <td><span class="status-pill neutral"><?= V::e(str_replace('_', ' ', $item->getStatoVerifica())) ?></span></td>
                            <td><span class="status-pill neutral"><?= V::e($item->getStato()) ?></span></td>
                            <td>
                                <div class="admin-row-actions">
                                    <?php if ($item->getIdChef() !== $idAdminCorrente && $item->getStato() !== EUtente::STATO_SOSPESO): ?>
                                        <form method="post" action="<?= V::e(V::url('/utenti/utente/' . $item->getIdChef() . '/sospendi')) ?>"><button class="btn btn-ghost btn-small" type="submit">Sospendi</button></form>
                                    <?php endif; ?>
                                    <?php if ($item->getStato() !== EUtente::STATO_ATTIVO): ?>
                                        <form method="post" action="<?= V::e(V::url('/utenti/utente/' . $item->getIdChef() . '/riattiva')) ?>"><button class="btn btn-primary btn-small" type="submit">Riattiva</button></form>
                                    <?php endif; ?>
                                    <?php if ($item->getIdChef() !== $idAdminCorrente && $item->getStato() !== EUtente::STATO_BANNATO): ?>
                                        <form method="post" action="<?= V::e(V::url('/utenti/utente/' . $item->getIdChef() . '/banna')) ?>"><button class="btn btn-danger btn-small" type="submit">Banna</button></form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="ops-panel admin-directory-panel">
        <div class="toolbar">
            <div>
                <h2>Gestori registrati</h2>
                <p>Profili che possono gestire ghost kitchen. I nuovi gestori restano in attesa finche l'admin non li verifica.</p>
            </div>
        </div>

        <?php if ($gestori === []): ?>
            <div class="empty-state">Nessun gestore registrato.</div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Telefono</th><th>Verifica</th><th>Stato</th><th>Azioni</th></tr></thead>
                    <tbody>
                    <?php foreach ($gestori as $gestore): ?>
                        <tr>
                            <td>#<?= V::e($gestore->getIdGestore()) ?></td>
                            <td><strong><?= V::e(trim($gestore->getNome() . ' ' . $gestore->getCognome())) ?></strong></td>
                            <td><?= V::e($gestore->getEmail()) ?></td>
                            <td><?= V::e($gestore->getTelefono() !== '' ? $gestore->getTelefono() : 'n/d') ?></td>
                            <td><span class="status-pill neutral"><?= V::e(str_replace('_', ' ', $gestore->getStatoVerifica())) ?></span></td>
                            <td><span class="status-pill neutral"><?= V::e($gestore->getStato()) ?></span></td>
                            <td>
                                <div class="admin-row-actions">
                                    <?php if ($gestore->getStatoVerifica() !== EGestore::STATO_VERIFICA_VERIFICATO): ?>
                                        <form method="post" action="<?= V::e(V::url('/utenti/gestore/' . $gestore->getIdGestore() . '/approva')) ?>"><button class="btn btn-primary btn-small" type="submit">Approva</button></form>
                                    <?php endif; ?>
                                    <?php if ($gestore->getStatoVerifica() !== EGestore::STATO_VERIFICA_RIFIUTATO): ?>
                                        <form method="post" action="<?= V::e(V::url('/utenti/gestore/' . $gestore->getIdGestore() . '/rifiuta')) ?>"><button class="btn btn-danger btn-small" type="submit">Rifiuta</button></form>
                                    <?php endif; ?>
                                    <?php if ($gestore->getStatoVerifica() !== EGestore::STATO_VERIFICA_SOSPESO): ?>
                                        <form method="post" action="<?= V::e(V::url('/utenti/gestore/' . $gestore->getIdGestore() . '/sospendi-verifica')) ?>"><button class="btn btn-ghost btn-small" type="submit">Sospendi verifica</button></form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="ops-panel admin-directory-panel">
        <div class="toolbar">
            <div>
                <h2>Ghost kitchen registrate</h2>
                <p>Cucine disponibili nel sistema con gestore collegato e dati operativi principali.</p>
            </div>
        </div>

        <?php if ($ghostKitchen === []): ?>
            <div class="empty-state">Nessuna ghost kitchen registrata.</div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>Nome</th><th>Gestore</th><th>Citta</th><th>Prezzo</th><th>Capienza</th><th>Stato</th><th>Azioni</th></tr></thead>
                    <tbody>
                    <?php foreach ($ghostKitchen as $item): ?>
                        <?php
                        $gestore = $item->getIdGestore() !== null ? ($gestoriGhostKitchen[(int) $item->getIdGestore()] ?? null) : null;
                        $nomeGestore = $gestore !== null ? trim($gestore->getNome() . ' ' . $gestore->getCognome()) : 'n/d';
                        ?>
                        <tr>
                            <td>#<?= V::e($item->getId()) ?></td>
                            <td><strong><?= V::e($item->getNome()) ?></strong></td>
                            <td><?= V::e($nomeGestore) ?></td>
                            <td><?= V::e($item->getCitta()) ?></td>
                            <td><?= V::e(number_format($item->getPrezzoOrario(), 2, ',', '.')) ?> euro/h</td>
                            <td><?= V::e($item->getCapienza()) ?></td>
                            <td><span class="status-pill neutral"><?= V::e(str_replace('_', ' ', $item->getStato())) ?></span></td>
                            <td>
                                <div class="admin-row-actions">
                                    <?php if ($item->getStato() !== EGhostKitchen::STATO_ATTIVA): ?>
                                        <form method="post" action="<?= V::e(V::url('/utenti/ghost-kitchen/' . $item->getId() . '/attiva')) ?>"><button class="btn btn-primary btn-small" type="submit">Attiva</button></form>
                                    <?php endif; ?>
                                    <?php if ($item->getStato() !== EGhostKitchen::STATO_SOSPESA): ?>
                                        <form method="post" action="<?= V::e(V::url('/utenti/ghost-kitchen/' . $item->getId() . '/sospendi')) ?>"><button class="btn btn-ghost btn-small" type="submit">Sospendi</button></form>
                                    <?php endif; ?>
                                    <?php if ($item->getStato() !== EGhostKitchen::STATO_NON_DISPONIBILE): ?>
                                        <form method="post" action="<?= V::e(V::url('/utenti/ghost-kitchen/' . $item->getId() . '/non-disponibile')) ?>"><button class="btn btn-danger btn-small" type="submit">Non disponibile</button></form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>

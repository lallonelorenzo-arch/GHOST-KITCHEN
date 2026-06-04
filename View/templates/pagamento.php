<?php
use ViewHelpers as V;
/** @var string|null $tipoPrenotazione */
/** @var int|null $idPrenotazione */
/** @var string|null $tipoPagamento */
/** @var float|null $importo */
/** @var array $metodiDisponibili */
/** @var array $form */
/** @var EPagamento|null $pagamento */
/** @var string|null $messaggioAccesso */
/** @var string|null $erroreForm */
/** @var string|null $messaggioSuccesso */
$tipoPrenotazione = $tipoPrenotazione ?? 'chef';
$tipoSlug = $tipoPrenotazione === 'ghost_kitchen' ? 'ghost-kitchen' : 'chef';
$form = $form ?? [];
$metodiDisponibili = $metodiDisponibili ?? [];
?>
<section class="page-hero compact-hero ops-hero">
    <h1>Pagamento</h1>
    <p>Controlla riepilogo, importo e metodo prima di confermare la transazione.</p>
</section>

<section class="section ops-flow">
    <?php if (!empty($messaggioAccesso)): ?>
        <div class="alert"><?= V::e($messaggioAccesso) ?> <a href="<?= V::e(V::url('/login')) ?>">Accedi</a></div>
    <?php endif; ?>
    <?php if (!empty($erroreForm)): ?><div class="alert"><?= V::e($erroreForm) ?></div><?php endif; ?>
    <?php if (!empty($messaggioSuccesso)): ?><div class="notice"><?= V::e($messaggioSuccesso) ?></div><?php endif; ?>

    <div class="ops-grid">
        <article class="ops-panel">
            <h2>Riepilogo</h2>
            <dl class="ops-meta">
                <div><dt>Prenotazione</dt><dd>#<?= V::e($idPrenotazione ?? '') ?></dd></div>
                <div><dt>Tipo</dt><dd><?= V::e($tipoPrenotazione) ?></dd></div>
                <div><dt>Importo</dt><dd>&euro; <?= V::e(V::money((float) ($importo ?? 0))) ?></dd></div>
            </dl>
            <?php if ($pagamento !== null): ?>
                <div class="notice">Pagamento #<?= V::e($pagamento->getIdPagamento()) ?> - <?= V::e($pagamento->getStato()) ?></div>
            <?php endif; ?>
        </article>

        <form class="ops-panel ops-form" method="post" action="<?= V::e(V::url('/pagamento/' . $tipoSlug . '/' . (int) $idPrenotazione)) ?>">
            <h2>Dati pagamento</h2>
            <label>Tipo pagamento
                <select name="tipoPagamento" required>
                    <?php foreach (['totale' => 'Totale', 'caparra' => 'Caparra', 'saldo' => 'Saldo', 'penale' => 'Penale'] as $value => $label): ?>
                        <option value="<?= V::e($value) ?>" <?= ($form['tipoPagamento'] ?? $tipoPagamento ?? 'totale') === $value ? 'selected' : '' ?>><?= V::e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Metodo pagamento
                <select name="idMetodoPagamento" required>
                    <option value="">Seleziona metodo</option>
                    <?php foreach ($metodiDisponibili as $metodo): ?>
                        <option value="<?= V::e($metodo->getIdMetodoPagamento()) ?>" <?= (string) ($form['idMetodoPagamento'] ?? '') === (string) $metodo->getIdMetodoPagamento() ? 'selected' : '' ?>>
                            <?= V::e($metodo->getTipo()) ?> <?= $metodo->getCircuito() !== '' ? '- ' . V::e($metodo->getCircuito()) : '' ?> <?= $metodo->getUltimeQuattroCifre() !== '' ? '**** ' . V::e($metodo->getUltimeQuattroCifre()) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php if ($metodiDisponibili === []): ?>
                <p class="muted-text">Nessun metodo di pagamento attivo disponibile per questa prenotazione.</p>
            <?php endif; ?>
            <button class="btn btn-accent" type="submit">Conferma pagamento</button>
        </form>
    </div>
</section>

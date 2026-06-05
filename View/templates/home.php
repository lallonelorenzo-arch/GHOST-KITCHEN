<?php
use ViewHelpers as V;
/** @var array $chefInEvidenza */
/** @var array $ghostKitchenInEvidenza */
/** @var array $cucine */
$hero = 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=90&w=2400';
?>
<section class="hero" style="background-image: linear-gradient(90deg, rgba(44,24,16,.96), rgba(44,24,16,.72), rgba(44,24,16,.28)), url('<?= V::e($hero) ?>')">
    <div class="hero-content">
        <h1>L'arte culinaria <span>a casa tua</span></h1>
        <p>Prenota chef professionisti per eventi esclusivi o affitta una ghost kitchen per le tue creazioni culinarie.</p>
        <div class="actions">
            <a class="btn btn-accent hero-cta" href="<?= V::e(V::url('/ricerca/chef')) ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m16 16 4 4"></path>
                </svg>
                Trova uno Chef
                <span aria-hidden="true">&rarr;</span>
            </a>
            <a class="btn btn-light hero-cta" href="<?= V::e(V::url('/ricerca/ghost-kitchen')) ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M8 11c-1.8-.3-3.1-1.7-3.1-3.4 0-1.9 1.7-3.5 3.8-3.5.7 0 1.3.2 1.8.4.8-1.4 2.3-2.3 4.1-2.3 2.5 0 4.4 1.7 4.7 3.9 1.3.5 2.2 1.7 2.2 3.1 0 1.7-1.3 3.1-3.1 3.4v7H8v-7.6Z"></path>
                    <path d="M8 15h10M8 18h10"></path>
                </svg>
                Esplora Ghost Kitchen
            </a>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-heading">
        <div>
            <h2>Chef in evidenza</h2>
            <p>Professionisti disponibili per cene private, degustazioni e servizi personalizzati.</p>
        </div>
        <a href="<?= V::e(V::url('/ricerca/chef')) ?>">Vedi tutti</a>
    </div>
    <div class="card-grid four">
        <?php foreach ($chefInEvidenza as $chef): ?>
            <?php require __DIR__ . '/partials/chef_card.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php if ($chefInEvidenza === []): ?>
        <div class="empty-state">Nessuno chef in evidenza disponibile.</div>
    <?php endif; ?>
</section>

<section class="section muted">
    <div class="center-heading">
        <h2>Esplora per cucina</h2>
        <p>Parti dal tipo di esperienza che hai in mente e restringi la ricerca in pochi passaggi.</p>
    </div>
    <div class="pill-grid">
        <?php foreach ($cucine as $cucina): ?>
            <a class="pill-card" href="<?= V::e(V::url('/ricerca/chef', ['tipologiaCucina' => strtolower($cucina)])) ?>">
                <strong><?= V::e($cucina) ?></strong>
                <span>cerca chef</span>
            </a>
        <?php endforeach; ?>
    </div>
    <?php if ($cucine === []): ?>
        <div class="empty-state">Nessuna categoria cucina disponibile.</div>
    <?php endif; ?>
</section>

<section class="section dark-band ghost-kitchen-feature">
    <div class="ghost-kitchen-copy">
        <h2>Ghost Kitchen<br>Professionali</h2>
        <p>Affitta cucine professionali completamente attrezzate per ore o giornate. Perfette per chef, catering e progetti culinari.</p>
        <ul class="feature-list">
            <li>Attrezzature professionali certificate</li>
            <li>Disponibilita flessibile a fasce orarie</li>
            <li>Igiene e sicurezza garantite</li>
            <li>Posizioni strategiche in tutta Italia</li>
        </ul>
        <a class="btn btn-accent feature-cta" href="<?= V::e(V::url('/ricerca/ghost-kitchen')) ?>">Scopri le Ghost Kitchen <span aria-hidden="true">&rarr;</span></a>
    </div>
    <div class="ghost-kitchen-photo" role="img" aria-label="Chef al lavoro in una cucina professionale"></div>
</section>

<section class="section how-it-works">
    <div class="center-heading">
        <h2>Come Funziona</h2>
        <p>Prenotare uno chef o una ghost kitchen e semplice e veloce</p>
    </div>
    <div class="steps-grid">
        <article class="step-card">
            <div class="step-icon">
                <span class="step-number">1</span>
                <svg viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                    <circle cx="21" cy="21" r="12"></circle>
                    <path d="m30 30 8 8"></path>
                </svg>
            </div>
            <h3>Cerca e Scopri</h3>
            <p>Esplora profili di chef e ghost kitchen, leggi recensioni e confronta prezzi</p>
        </article>
        <article class="step-card">
            <div class="step-icon">
                <span class="step-number">2</span>
                <svg viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                    <rect x="12" y="14" width="24" height="26" rx="3"></rect>
                    <path d="M17 8v8M31 8v8M12 22h24"></path>
                </svg>
            </div>
            <h3>Prenota</h3>
            <p>Scegli data e orario, personalizza il menu o seleziona gli equipaggiamenti necessari</p>
        </article>
        <article class="step-card">
            <div class="step-icon">
                <span class="step-number">3</span>
                <svg viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                    <path d="M16 25c-3.3-.6-5.6-3.1-5.6-6.2 0-3.6 3-6.4 6.8-6.4 1.2 0 2.4.3 3.3.8 1.5-2.6 4.2-4.2 7.5-4.2 4.5 0 8.1 3.1 8.5 7.1 2.4.9 4.1 3 4.1 5.6 0 3.1-2.3 5.6-5.6 6.2v10.7H16V25Z"></path>
                    <path d="M16 31h19M16 36h19"></path>
                </svg>
            </div>
            <h3>Goditi l'Esperienza</h3>
            <p>Rilassati mentre lo chef prepara o inizia a creare nella tua ghost kitchen</p>
        </article>
    </div>
</section>

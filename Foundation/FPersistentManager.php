<?php
declare(strict_types=1);

require_once __DIR__ . '/../Entity/EUtente.php';
require_once __DIR__ . '/../Entity/EChef.php';
require_once __DIR__ . '/../Entity/EGhostKitchen.php';
require_once __DIR__ . '/../Entity/EMedia.php';
require_once __DIR__ . '/../Entity/EMenu.php';
require_once __DIR__ . '/../Entity/EPiatto.php';
require_once __DIR__ . '/../Entity/EAttrezzatura.php';
require_once __DIR__ . '/../Entity/ECertificazione.php';
require_once __DIR__ . '/../Entity/ECliente.php';
require_once __DIR__ . '/../Entity/EPrenotazioneChef.php';
require_once __DIR__ . '/../Entity/EPrenotazioneGhostKitchen.php';
require_once __DIR__ . '/../Entity/EPagamento.php';
require_once __DIR__ . '/../Entity/ECancellazione.php';
require_once __DIR__ . '/../Entity/ERimborso.php';
require_once __DIR__ . '/../Entity/ERecensioneChef.php';
require_once __DIR__ . '/../Entity/ERecensioneGhostKitchen.php';
require_once __DIR__ . '/../Entity/ESegnalazione.php';

/**
 * PersistentManager fittizio per test di UC1.
 * Non usa DB, SQL o sessioni reali.
 */
class FPersistentManager
{
    /**
     * @return EChef[]
     */
    public static function cercaChef(
        string $localita,
        string $tipologiaCucina,
        float $budgetMax,
        int $valutazioneMin
    ): array {
        $chefDisponibili = [
            new EChef(
                1,
                'Marco',
                'Rossi',
                'marco.rossi@example.com',
                'hash-marco',
                '+39061234567',
                EUtente::STATO_ATTIVO,
                'Chef specializzato in cucina giapponese contemporanea.',
                'Sushi chef',
                'sushi',
                90.0,
                8,
                EChef::STATO_VERIFICA_VERIFICATO,
                4.7,
                52
            ),
            new EChef(
                2,
                'Laura',
                'Bianchi',
                'laura.bianchi@example.com',
                'hash-laura',
                '+39069876543',
                EUtente::STATO_ATTIVO,
                'Chef per eventi privati e degustazioni.',
                'Chef fusion',
                'fusion',
                110.0,
                10,
                EChef::STATO_VERIFICA_VERIFICATO,
                4.8,
                41
            ),
            new EChef(
                3,
                'Giulia',
                'Verdi',
                'giulia.verdi@example.com',
                'hash-giulia',
                '+39065551234',
                EUtente::STATO_ATTIVO,
                'Chef orientata a menu mediterranei e stagionali.',
                'Chef mediterranea',
                'mediterranea',
                75.0,
                6,
                EChef::STATO_VERIFICA_VERIFICATO,
                4.2,
                19
            )
        ];

        $mappaLocalitaChef = [
            1 => 'roma',
            2 => 'milano',
            3 => 'roma'
        ];

        $localita = strtolower(trim($localita));
        $tipologiaCucina = strtolower(trim($tipologiaCucina));

        $risultati = [];

        foreach ($chefDisponibili as $chef) {
            $idChef = $chef->getIdChef();
            $localitaChef = $idChef !== null && isset($mappaLocalitaChef[$idChef]) ? $mappaLocalitaChef[$idChef] : '';

            if ($localita !== '' && $localitaChef !== $localita) {
                continue;
            }

            if (
                $tipologiaCucina !== '' &&
                strtolower($chef->getTipologiaCucina()) !== $tipologiaCucina &&
                strtolower($chef->getSpecializzazione()) !== $tipologiaCucina
            ) {
                continue;
            }

            if ($budgetMax > 0 && $chef->getPrezzoBase() > $budgetMax) {
                continue;
            }

            if ($chef->getValutazioneMedia() < $valutazioneMin) {
                continue;
            }

            $risultati[] = $chef;
        }

        return $risultati;
    }

    /**
     * @return EGhostKitchen[]
     */
    public static function cercaGhostKitchen(
        string $localita,
        float $budgetMax,
        int $valutazioneMin
    ): array {
        $ghostKitchenDisponibili = [
            new EGhostKitchen(
                101,
                11,
                'Ghost Roma Centro',
                'Spazio attrezzato per chef e piccoli team.',
                'Via Nazionale 10',
                'Roma',
                '00184',
                35.0,
                20,
                80.0,
                EGhostKitchen::STATO_ATTIVA,
                4.5,
                34
            ),
            new EGhostKitchen(
                102,
                12,
                'Milano Lab Kitchen',
                'Cucina professionale per delivery e catering.',
                'Via Torino 20',
                'Milano',
                '20123',
                55.0,
                30,
                120.0,
                EGhostKitchen::STATO_ATTIVA,
                4.9,
                27
            ),
            new EGhostKitchen(
                103,
                13,
                'Trastevere Food Hub',
                'Laboratorio culinario condiviso per eventi.',
                'Viale Trastevere 50',
                'Roma',
                '00153',
                48.0,
                16,
                65.0,
                EGhostKitchen::STATO_ATTIVA,
                4.1,
                18
            )
        ];

        $localita = strtolower(trim($localita));
        $risultati = [];

        foreach ($ghostKitchenDisponibili as $ghostKitchen) {
            if ($localita !== '' && strtolower($ghostKitchen->getCitta()) !== $localita) {
                continue;
            }

            if ($budgetMax > 0 && $ghostKitchen->getPrezzoOrario() > $budgetMax) {
                continue;
            }

            if ($ghostKitchen->getValutazioneMedia() < $valutazioneMin) {
                continue;
            }

            $risultati[] = $ghostKitchen;
        }

        return $risultati;
    }

    public static function getMediaPrincipale(
        string $tipoOwner,
        int $idOwner
    ): ?EMedia {
        $tipoOwner = strtolower(trim($tipoOwner));

        $mediaDisponibili = [
            new EMedia(
                201,
                EMedia::OWNER_CHEF,
                1,
                EMedia::TIPO_MEDIA_FOTO_PROFILO,
                'chef-marco.jpg',
                '/media/chef/chef-marco.jpg',
                'image/jpeg',
                'Foto profilo chef Marco Rossi',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            ),
            new EMedia(
                202,
                EMedia::OWNER_GHOST_KITCHEN,
                101,
                EMedia::TIPO_MEDIA_FOTO_AMBIENTE,
                'ghost-roma-centro.jpg',
                '/media/ghost-kitchen/ghost-roma-centro.jpg',
                'image/jpeg',
                'Immagine principale Ghost Roma Centro',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            )
        ];

        foreach ($mediaDisponibili as $media) {
            if ($media->getTipoOwner() === $tipoOwner && $media->getIdOwner() === $idOwner) {
                return $media;
            }
        }

        return null;
    }

    public static function loadChef(int $idChef): ?EChef
    {
        foreach (self::getChefDataset() as $chef) {
            if ($chef->getIdChef() === $idChef) {
                return $chef;
            }
        }

        return null;
    }

    /**
     * @return EMenu[]
     */
    public static function loadMenuByChef(int $idChef): array
    {
        $menuDisponibili = [
            new EMenu(
                301,
                1,
                'Percorso Sushi Signature',
                'Menu degustazione con sushi, nigiri e uramaki.',
                55.0,
                true
            ),
            new EMenu(
                302,
                1,
                'Japanese Dinner Experience',
                'Menu completo per cene private con portate giapponesi.',
                75.0,
                true
            ),
            new EMenu(
                303,
                2,
                'Fusion Experience',
                'Menu fusion per eventi aziendali e privati.',
                68.0,
                true
            )
        ];

        $risultati = [];

        foreach ($menuDisponibili as $menu) {
            if ($menu->getIdChef() === $idChef) {
                $risultati[] = $menu;
            }
        }

        return $risultati;
    }

    /**
     * @return EPiatto[]
     */
    public static function loadPiattiByMenu(int $idMenu): array
    {
        $piattiDisponibili = [
            new EPiatto(
                401,
                301,
                'Nigiri Selection',
                EPiatto::CATEGORIA_SECONDO,
                'Selezione di nigiri con pesce fresco.',
                'Riso, salmone, tonno, ricciola',
                'Pesce, soia',
                0.0,
                1
            ),
            new EPiatto(
                402,
                301,
                'Uramaki Crunch',
                EPiatto::CATEGORIA_SECONDO,
                'Uramaki croccante con gambero e avocado.',
                'Riso, gambero, avocado, panko',
                'Crostacei, glutine',
                4.5,
                2
            ),
            new EPiatto(
                403,
                302,
                'Miso Soup',
                EPiatto::CATEGORIA_PRIMO,
                'Zuppa miso con tofu e wakame.',
                'Miso, tofu, alga wakame',
                'Soia',
                0.0,
                1
            ),
            new EPiatto(
                404,
                302,
                'Mochi Trio',
                EPiatto::CATEGORIA_DOLCE,
                'Tris di mochi artigianali.',
                'Farina di riso, matcha, mango, cioccolato',
                'Può contenere latte',
                3.0,
                2
            ),
            new EPiatto(
                405,
                303,
                'Tataki Fusion',
                EPiatto::CATEGORIA_SECONDO,
                'Tataki rivisitato in chiave fusion.',
                'Manzo, sesamo, salsa ponzu',
                'Soia, sesamo',
                6.0,
                1
            )
        ];

        $risultati = [];

        foreach ($piattiDisponibili as $piatto) {
            if ($piatto->getIdMenu() === $idMenu) {
                $risultati[] = $piatto;
            }
        }

        return $risultati;
    }

    /**
     * @return ECertificazione[]
     */
    public static function loadCertificazioniApprovateByChef(int $idChef): array
    {
        $certificazioni = [
            new ECertificazione(
                501,
                1,
                'HACCP',
                'haccp-marco-rossi.pdf',
                '/certificazioni/haccp-marco-rossi.pdf',
                ECertificazione::STATO_APPROVATA,
                '2026-04-10',
                '2026-04-15',
                'Certificazione verificata.'
            ),
            new ECertificazione(
                502,
                1,
                'Somministrazione alimenti',
                'sab-marco-rossi.pdf',
                '/certificazioni/sab-marco-rossi.pdf',
                ECertificazione::STATO_APPROVATA,
                '2026-04-12',
                '2026-04-18',
                'Documento approvato.'
            ),
            new ECertificazione(
                503,
                2,
                'HACCP',
                'haccp-laura-bianchi.pdf',
                '/certificazioni/haccp-laura-bianchi.pdf',
                ECertificazione::STATO_APPROVATA,
                '2026-03-02',
                '2026-03-08',
                'Documento approvato.'
            )
        ];

        $risultati = [];

        foreach ($certificazioni as $certificazione) {
            if (
                $certificazione->getIdChef() === $idChef &&
                $certificazione->getStato() === ECertificazione::STATO_APPROVATA
            ) {
                $risultati[] = $certificazione;
            }
        }

        return $risultati;
    }

    /**
     * @return EMedia[]
     */
    public static function getMediaByOwner(string $tipoOwner, int $idOwner): array
    {
        $tipoOwner = strtolower(trim($tipoOwner));
        $risultati = [];

        foreach (self::getMediaDataset() as $media) {
            if ($media->getTipoOwner() === $tipoOwner && $media->getIdOwner() === $idOwner) {
                $risultati[] = $media;
            }
        }

        return $risultati;
    }

    /**
     * @return EChef[]
     */
    private static function getChefDataset(): array
    {
        return [
            new EChef(
                1,
                'Marco',
                'Rossi',
                'marco.rossi@example.com',
                'hash-marco',
                '+39061234567',
                EUtente::STATO_ATTIVO,
                'Chef specializzato in cucina giapponese contemporanea.',
                'Sushi chef',
                'sushi',
                90.0,
                8,
                EChef::STATO_VERIFICA_VERIFICATO,
                4.7,
                52
            ),
            new EChef(
                2,
                'Laura',
                'Bianchi',
                'laura.bianchi@example.com',
                'hash-laura',
                '+39069876543',
                EUtente::STATO_ATTIVO,
                'Chef per eventi privati e degustazioni.',
                'Chef fusion',
                'fusion',
                110.0,
                10,
                EChef::STATO_VERIFICA_VERIFICATO,
                4.8,
                41
            ),
            new EChef(
                3,
                'Giulia',
                'Verdi',
                'giulia.verdi@example.com',
                'hash-giulia',
                '+39065551234',
                EUtente::STATO_ATTIVO,
                'Chef orientata a menu mediterranei e stagionali.',
                'Chef mediterranea',
                'mediterranea',
                75.0,
                6,
                EChef::STATO_VERIFICA_VERIFICATO,
                4.2,
                19
            )
        ];
    }

    /**
     * @return EMedia[]
     */
    private static function getMediaDataset(): array
    {
        return [
            new EMedia(
                201,
                EMedia::OWNER_CHEF,
                1,
                EMedia::TIPO_MEDIA_FOTO_PROFILO,
                'chef-marco.jpg',
                '/media/chef/chef-marco.jpg',
                'image/jpeg',
                'Foto profilo chef Marco Rossi',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            ),
            new EMedia(
                202,
                EMedia::OWNER_GHOST_KITCHEN,
                101,
                EMedia::TIPO_MEDIA_FOTO_AMBIENTE,
                'ghost-roma-centro.jpg',
                '/media/ghost-kitchen/ghost-roma-centro.jpg',
                'image/jpeg',
                'Immagine principale Ghost Roma Centro',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            ),
            new EMedia(
                203,
                EMedia::OWNER_PIATTO,
                401,
                EMedia::TIPO_MEDIA_FOTO_PIATTO,
                'nigiri-selection.jpg',
                '/media/piatti/nigiri-selection.jpg',
                'image/jpeg',
                'Foto del piatto Nigiri Selection',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            ),
            new EMedia(
                204,
                EMedia::OWNER_PIATTO,
                402,
                EMedia::TIPO_MEDIA_FOTO_PIATTO,
                'uramaki-crunch.jpg',
                '/media/piatti/uramaki-crunch.jpg',
                'image/jpeg',
                'Foto del piatto Uramaki Crunch',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            ),
            new EMedia(
                205,
                EMedia::OWNER_PIATTO,
                404,
                EMedia::TIPO_MEDIA_FOTO_PIATTO,
                'mochi-trio.jpg',
                '/media/piatti/mochi-trio.jpg',
                'image/jpeg',
                'Foto del piatto Mochi Trio',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            )
        ];
    }

    public static function loadPrenotazioneChef(int $idPrenotazione): ?EPrenotazioneChef
    {
        foreach (self::getPrenotazioniChefDataset() as $prenotazione) {
            if ($prenotazione->getIdPrenotazione() === $idPrenotazione) {
                return $prenotazione;
            }
        }

        return null;
    }

    public static function loadPrenotazioneGhostKitchen(int $idPrenotazione): ?EPrenotazioneGhostKitchen
    {
        foreach (self::getPrenotazioniGhostKitchenDataset() as $prenotazione) {
            if ($prenotazione->getIdPrenotazione() === $idPrenotazione) {
                return $prenotazione;
            }
        }

        return null;
    }

    public static function loadPagamentoByPrenotazione(string $tipoPrenotazione, int $idPrenotazione): ?EPagamento
    {
        $tipoPrenotazione = strtolower(trim($tipoPrenotazione));

        foreach (self::getPagamentiDataset() as $pagamento) {
            if (
                $pagamento->getTipoPrenotazione() === $tipoPrenotazione &&
                $pagamento->getIdPrenotazione() === $idPrenotazione
            ) {
                return $pagamento;
            }
        }

        return null;
    }

    public static function calcolaRimborsoStimato(string $tipoPrenotazione, int $idPrenotazione): array
    {
        $pagamento = self::loadPagamentoByPrenotazione($tipoPrenotazione, $idPrenotazione);

        if ($pagamento === null) {
            return [
                'trovato' => false,
                'messaggio' => 'Pagamento non trovato per la prenotazione indicata.'
            ];
        }

        $motivoPolicy = 'cancellazione con largo anticipo';
        $percentualeRimborso = 0.8;

        if ($idPrenotazione === 1002 || $idPrenotazione === 2002) {
            $motivoPolicy = 'cancellazione tardiva con penale';
            $percentualeRimborso = 0.35;
        }

        if ($tipoPrenotazione === ECancellazione::PRENOTAZIONE_GHOST_KITCHEN && $idPrenotazione === 2002) {
            $motivoPolicy = 'danni ghost kitchen simulati';
            $percentualeRimborso = 0.0;
        }

        $importoPagato = $pagamento->getImporto();
        $importoRimborsabile = round($importoPagato * $percentualeRimborso, 2);
        $penale = round($importoPagato - $importoRimborsabile, 2);

        return [
            'trovato' => true,
            'tipoPrenotazione' => $tipoPrenotazione,
            'idPrenotazione' => $idPrenotazione,
            'idPagamento' => $pagamento->getIdPagamento(),
            'importoPagato' => $importoPagato,
            'percentualeRimborso' => $percentualeRimborso,
            'penale' => $penale,
            'importoRimborsabile' => $importoRimborsabile,
            'criterioFittizio' => $motivoPolicy
        ];
    }

    public static function storeCancellazione(ECancellazione $cancellazione): ECancellazione
    {
        if ($cancellazione->getIdCancellazione() === null) {
            $cancellazione->setIdCancellazione(9001);
        }

        return $cancellazione;
    }

    public static function storeRimborso(ERimborso $rimborso): ERimborso
    {
        if ($rimborso->getIdRimborso() === null) {
            $rimborso->setIdRimborso(9101);
        }

        return $rimborso;
    }

    public static function updatePrenotazioneChef(EPrenotazioneChef $prenotazione): EPrenotazioneChef
    {
        return $prenotazione;
    }

    public static function updatePrenotazioneGhostKitchen(EPrenotazioneGhostKitchen $prenotazione): EPrenotazioneGhostKitchen
    {
        return $prenotazione;
    }

    public static function updatePagamento(EPagamento $pagamento): EPagamento
    {
        return $pagamento;
    }

    public static function verificaPrenotazioneRecensibile(string $tipoTarget, int $idPrenotazione, int $idAutore): array
    {
        $tipoTarget = strtolower(trim($tipoTarget));
        $prenotazione = $tipoTarget === 'chef'
            ? self::loadPrenotazioneChef($idPrenotazione)
            : self::loadPrenotazioneGhostKitchen($idPrenotazione);

        if ($prenotazione === null) {
            return ['recensibile' => false, 'motivo' => 'Prenotazione non trovata.'];
        }

        if ($prenotazione->getIdRichiedente() !== $idAutore) {
            return ['recensibile' => false, 'motivo' => 'Autore non associato alla prenotazione.'];
        }

        if ($prenotazione->getStato() !== EPrenotazione::STATO_COMPLETATA) {
            return ['recensibile' => false, 'motivo' => 'Prenotazione non completata.'];
        }

        return ['recensibile' => true, 'motivo' => 'Prenotazione completata e recensibile.'];
    }

    public static function storeRecensioneChef(ERecensioneChef $recensione): ERecensioneChef
    {
        if ($recensione->getIdRecensione() === null) {
            $recensione->setIdRecensione(9201);
        }

        return $recensione;
    }

    public static function storeRecensioneGhostKitchen(ERecensioneGhostKitchen $recensione): ERecensioneGhostKitchen
    {
        if ($recensione->getIdRecensione() === null) {
            $recensione->setIdRecensione(9301);
        }

        return $recensione;
    }

    public static function aggiornaValutazioneChef(int $idChef): array
    {
        return [
            'idChef' => $idChef,
            'valutazioneMediaAggiornata' => 4.75,
            'numeroRecensioni' => 53
        ];
    }

    public static function aggiornaValutazioneGhostKitchen(int $idGhostKitchen): array
    {
        return [
            'idGhostKitchen' => $idGhostKitchen,
            'valutazioneMediaAggiornata' => 4.55,
            'numeroRecensioni' => 35
        ];
    }

    public static function loadUtente(int $idUtente): ?EUtente
    {
        foreach (self::getUtentiDataset() as $utente) {
            if ($utente->getIdUtente() === $idUtente) {
                return $utente;
            }
        }

        return null;
    }

    public static function loadTargetSegnalazione(string $tipoTarget, int $idTarget)
    {
        $tipoTarget = strtolower(trim($tipoTarget));

        if ($tipoTarget === ESegnalazione::TARGET_UTENTE) {
            return self::loadUtente($idTarget);
        }

        if ($tipoTarget === ESegnalazione::TARGET_CHEF) {
            return self::loadChef($idTarget);
        }

        if ($tipoTarget === ESegnalazione::TARGET_GHOST_KITCHEN) {
            return self::loadGhostKitchen($idTarget);
        }

        if ($tipoTarget === ESegnalazione::TARGET_RECENSIONE) {
            return self::loadRecensione($idTarget);
        }

        if ($tipoTarget === ESegnalazione::TARGET_MENU) {
            foreach (self::loadMenuByChef(1) as $menu) {
                if ($menu->getIdMenu() === $idTarget) {
                    return $menu;
                }
            }
        }

        return null;
    }

    public static function storeSegnalazione(ESegnalazione $segnalazione): ESegnalazione
    {
        if ($segnalazione->getIdSegnalazione() === null) {
            $segnalazione->setIdSegnalazione(9401);
        }

        return $segnalazione;
    }

    /**
     * @return ECertificazione[]
     */
    public static function loadCertificazioniInAttesa(): array
    {
        return array_values(array_filter(
            self::getCertificazioniValidazioneDataset(),
            static fn (ECertificazione $certificazione): bool => $certificazione->getStato() === ECertificazione::STATO_IN_ATTESA
        ));
    }

    public static function loadCertificazione(int $idCertificazione): ?ECertificazione
    {
        foreach (self::getCertificazioniValidazioneDataset() as $certificazione) {
            if ($certificazione->getIdCertificazione() === $idCertificazione) {
                return $certificazione;
            }
        }

        return null;
    }

    public static function updateCertificazione(ECertificazione $certificazione): ECertificazione
    {
        return $certificazione;
    }

    /**
     * @return ESegnalazione[]
     */
    public static function loadSegnalazioniDaModerare(): array
    {
        return self::getSegnalazioniDataset();
    }

    public static function loadSegnalazione(int $idSegnalazione): ?ESegnalazione
    {
        foreach (self::getSegnalazioniDataset() as $segnalazione) {
            if ($segnalazione->getIdSegnalazione() === $idSegnalazione) {
                return $segnalazione;
            }
        }

        return null;
    }

    public static function updateSegnalazione(ESegnalazione $segnalazione): ESegnalazione
    {
        return $segnalazione;
    }

    public static function loadRecensione(int $idRecensione): ?ERecensione
    {
        foreach (self::getRecensioniDataset() as $recensione) {
            if ($recensione->getIdRecensione() === $idRecensione) {
                return $recensione;
            }
        }

        return null;
    }

    public static function updateRecensione(ERecensione $recensione): ERecensione
    {
        return $recensione;
    }

    public static function updateUtente(EUtente $utente): EUtente
    {
        return $utente;
    }

    public static function loadGhostKitchen(int $idGhostKitchen): ?EGhostKitchen
    {
        foreach (self::getGhostKitchenDataset() as $ghostKitchen) {
            if ($ghostKitchen->getId() === $idGhostKitchen) {
                return $ghostKitchen;
            }
        }

        return null;
    }

    public static function getStatisticheDashboard(array $filtri): array
    {
        return [
            'filtriApplicati' => $filtri,
            'prenotazioni' => self::getStatistichePrenotazioni($filtri),
            'pagamenti' => self::getStatistichePagamenti($filtri),
            'recensioni' => self::getStatisticheRecensioni($filtri),
            'moderazione' => self::getStatisticheModerazione($filtri)
        ];
    }

    public static function getStatistichePrenotazioni(array $filtri): array
    {
        return [
            'prenotazioniTotali' => 42,
            'prenotazioniChef' => 25,
            'prenotazioniGhostKitchen' => 17,
            'ghostKitchenPiuPrenotate' => [
                ['idGhostKitchen' => 101, 'nome' => 'Ghost Roma Centro', 'prenotazioni' => 11],
                ['idGhostKitchen' => 102, 'nome' => 'Milano Lab Kitchen', 'prenotazioni' => 6]
            ]
        ];
    }

    public static function getStatistichePagamenti(array $filtri): array
    {
        return [
            'volumePagamenti' => 12840.50,
            'numeroRimborsi' => 3,
            'volumeRimborsi' => 410.00
        ];
    }

    public static function getStatisticheRecensioni(array $filtri): array
    {
        return [
            'chefConValutazioneMigliore' => [
                'idChef' => 2,
                'nome' => 'Laura Bianchi',
                'valutazioneMedia' => 4.8
            ],
            'recensioniChef' => 53,
            'recensioniGhostKitchen' => 35
        ];
    }

    public static function getStatisticheModerazione(array $filtri): array
    {
        return [
            'segnalazioniAperte' => 2,
            'certificazioniInAttesa' => 2
        ];
    }

    /**
     * @return EPrenotazioneChef[]
     */
    private static function getPrenotazioniChefDataset(): array
    {
        return [
            new EPrenotazioneChef(1001, 10, '2026-05-01', '2026-05-10', '19:00', '23:00', EPrenotazione::STATO_COMPLETATA, 300.0, 'Cena privata completata.', 1, 301, 'Via Roma 4, Roma', 6, ''),
            new EPrenotazioneChef(1002, 10, '2026-05-15', '2026-05-30', '20:00', '23:00', EPrenotazione::STATO_PAGATA, 450.0, 'Prenotazione cancellabile.', 2, 303, 'Via Milano 8, Roma', 8, '')
        ];
    }

    /**
     * @return EPrenotazioneGhostKitchen[]
     */
    private static function getPrenotazioniGhostKitchenDataset(): array
    {
        return [
            new EPrenotazioneGhostKitchen(2001, 1, '2026-05-01', '2026-05-12', '09:00', '15:00', EPrenotazione::STATO_COMPLETATA, 210.0, 'Slot completato.', 101, EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CHEF),
            new EPrenotazioneGhostKitchen(2002, 10, '2026-05-14', '2026-05-25', '10:00', '18:00', EPrenotazione::STATO_PAGATA, 280.0, 'Prenotazione cancellabile.', 101, EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CLIENTE)
        ];
    }

    /**
     * @return EPagamento[]
     */
    private static function getPagamentiDataset(): array
    {
        return [
            new EPagamento(8001, 1002, EPagamento::PRENOTAZIONE_CHEF, 701, 450.0, EPagamento::TIPO_TOTALE, EPagamento::STATO_COMPLETATO, 'TX-CHEF-1002', '2026-05-15'),
            new EPagamento(8002, 2002, EPagamento::PRENOTAZIONE_GHOST_KITCHEN, 701, 280.0, EPagamento::TIPO_TOTALE, EPagamento::STATO_COMPLETATO, 'TX-GK-2002', '2026-05-14')
        ];
    }

    /**
     * @return ERecensione[]
     */
    private static function getRecensioniDataset(): array
    {
        return [
            new ERecensioneChef(3001, 10, 5, 'Servizio preciso e menu ottimo.', '2026-05-11', ERecensione::STATO_VISIBILE, 1, 1001),
            new ERecensioneGhostKitchen(3002, 1, 4, 'Cucina ben attrezzata.', '2026-05-13', ERecensione::STATO_VISIBILE, 101, 2001)
        ];
    }

    /**
     * @return ESegnalazione[]
     */
    private static function getSegnalazioniDataset(): array
    {
        return [
            new ESegnalazione(4001, 10, ESegnalazione::TARGET_RECENSIONE, 3001, 'Commento non conforme', 'La recensione contiene dettagli da verificare.', ESegnalazione::STATO_APERTA, '2026-05-17'),
            new ESegnalazione(4002, 1, ESegnalazione::TARGET_UTENTE, 10, 'Comportamento scorretto', 'Utente da verificare dopo mancata presenza.', ESegnalazione::STATO_IN_VALUTAZIONE, '2026-05-18')
        ];
    }

    /**
     * @return ECertificazione[]
     */
    private static function getCertificazioniValidazioneDataset(): array
    {
        return [
            new ECertificazione(6001, 1, 'HACCP', 'haccp-marco-rinnovo.pdf', '/certificazioni/pending/haccp-marco-rinnovo.pdf', ECertificazione::STATO_IN_ATTESA, '2026-05-16'),
            new ECertificazione(6002, 2, 'Sicurezza alimentare', 'sicurezza-laura.pdf', '/certificazioni/pending/sicurezza-laura.pdf', ECertificazione::STATO_IN_ATTESA, '2026-05-17')
        ];
    }

    /**
     * @return EUtente[]
     */
    private static function getUtentiDataset(): array
    {
        return [
            new ECliente(10, 'Anna', 'Neri', 'anna.neri@example.com', 'hash-anna', '+39061112222', EUtente::STATO_ATTIVO),
            new EChef(1, 'Marco', 'Rossi', 'marco.rossi@example.com', 'hash-marco', '+39061234567', EUtente::STATO_ATTIVO, 'Chef specializzato in cucina giapponese contemporanea.', 'Sushi chef', 'sushi', 90.0, 8, EChef::STATO_VERIFICA_VERIFICATO, 4.7, 52)
        ];
    }

    /**
     * @return EGhostKitchen[]
     */
    private static function getGhostKitchenDataset(): array
    {
        return [
            new EGhostKitchen(101, 11, 'Ghost Roma Centro', 'Spazio attrezzato per chef e piccoli team.', 'Via Nazionale 10', 'Roma', '00184', 35.0, 20, 80.0, EGhostKitchen::STATO_ATTIVA, 4.5, 34),
            new EGhostKitchen(102, 12, 'Milano Lab Kitchen', 'Cucina professionale per delivery e catering.', 'Via Torino 20', 'Milano', '20123', 55.0, 30, 120.0, EGhostKitchen::STATO_ATTIVA, 4.9, 27)
        ];
    }
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/../Entity/EUtente.php';
require_once __DIR__ . '/../Entity/ECliente.php';
require_once __DIR__ . '/../Entity/EChef.php';
require_once __DIR__ . '/../Entity/EGestore.php';
require_once __DIR__ . '/../Entity/EGhostKitchen.php';
require_once __DIR__ . '/../Entity/EMedia.php';
require_once __DIR__ . '/../Entity/EMenu.php';
require_once __DIR__ . '/../Entity/EPiatto.php';
require_once __DIR__ . '/../Entity/EAttrezzatura.php';
require_once __DIR__ . '/../Entity/ECertificazione.php';
require_once __DIR__ . '/../Entity/EDisponibilitaChef.php';
require_once __DIR__ . '/../Entity/EDisponibilitaGhostKitchen.php';
require_once __DIR__ . '/../Entity/EPrenotazione.php';
require_once __DIR__ . '/../Entity/EPrenotazioneChef.php';
require_once __DIR__ . '/../Entity/EPrenotazioneGhostKitchen.php';
require_once __DIR__ . '/../Entity/EPagamento.php';
require_once __DIR__ . '/../Entity/EMetodoPagamento.php';
require_once __DIR__ . '/../Entity/ECancellazione.php';
require_once __DIR__ . '/../Entity/ERimborso.php';
require_once __DIR__ . '/../Entity/ERecensione.php';
require_once __DIR__ . '/../Entity/ERecensioneChef.php';
require_once __DIR__ . '/../Entity/ERecensioneGhostKitchen.php';
require_once __DIR__ . '/../Entity/ESegnalazione.php';

class FPersistentManager
{
    private static $initialized = false;

    /** @var ECliente[] */
    private static $clienti = [];
    /** @var EChef[] */
    private static $chef = [];
    /** @var EGestore[] */
    private static $gestori = [];
    /** @var EGhostKitchen[] */
    private static $ghostKitchens = [];
    /** @var EMenu[] */
    private static $menu = [];
    /** @var EPiatto[] */
    private static $piatti = [];
    /** @var EMedia[] */
    private static $media = [];
    /** @var EAttrezzatura[] */
    private static $attrezzature = [];
    /** @var ECertificazione[] */
    private static $certificazioni = [];
    /** @var EDisponibilitaChef[] */
    private static $disponibilitaChef = [];
    /** @var EDisponibilitaGhostKitchen[] */
    private static $disponibilitaGhostKitchen = [];
    /** @var EPrenotazioneChef[] */
    private static $prenotazioniChef = [];
    /** @var EPrenotazioneGhostKitchen[] */
    private static $prenotazioniGhostKitchen = [];
    /** @var EMetodoPagamento[] */
    private static $metodiPagamento = [];
    /** @var EPagamento[] */
    private static $pagamenti = [];
    /** @var ECancellazione[] */
    private static $cancellazioni = [];
    /** @var ERimborso[] */
    private static $rimborsi = [];
    /** @var ERecensione[] */
    private static $recensioni = [];
    /** @var ESegnalazione[] */
    private static $segnalazioni = [];

    private static $nextIdDisponibilitaChef = 800;
    private static $nextIdDisponibilitaGhostKitchen = 900;
    private static $nextIdPrenotazioneChef = 1000;
    private static $nextIdPrenotazioneGhostKitchen = 1100;
    private static $nextIdPagamento = 1200;
    private static $nextIdCancellazione = 9001;
    private static $nextIdRimborso = 9101;
    private static $nextIdRecensioneChef = 9201;
    private static $nextIdRecensioneGhostKitchen = 9301;
    private static $nextIdSegnalazione = 9401;

    private static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$clienti = [
            new ECliente(10, 'Anna', 'Neri', 'anna.neri@example.com', 'hash-anna', '+39061230001', EUtente::STATO_ATTIVO),
            new ECliente(11, 'Luca', 'Galli', 'luca.galli@example.com', 'hash-luca', '+39061230002', EUtente::STATO_ATTIVO)
        ];

        self::$chef = [
            new EChef(1, 'Marco', 'Rossi', 'marco.rossi@example.com', 'hash-marco', '+39061234567', EUtente::STATO_ATTIVO, 'Chef giapponese.', 'Sushi chef', 'sushi', 90.0, 8, EChef::STATO_VERIFICA_VERIFICATO, 4.7, 52),
            new EChef(2, 'Laura', 'Bianchi', 'laura.bianchi@example.com', 'hash-laura', '+39069876543', EUtente::STATO_ATTIVO, 'Chef fusion.', 'Chef fusion', 'fusion', 110.0, 10, EChef::STATO_VERIFICA_VERIFICATO, 4.8, 41),
            new EChef(3, 'Giulia', 'Verdi', 'giulia.verdi@example.com', 'hash-giulia', '+39065551234', EUtente::STATO_ATTIVO, 'Chef mediterranea.', 'Chef mediterranea', 'mediterranea', 75.0, 6, EChef::STATO_VERIFICA_VERIFICATO, 4.2, 19)
        ];

        self::$gestori = [
            new EGestore(21, 'Paolo', 'Romani', 'paolo.romani@example.com', 'hash-paolo', '+39061239991', EUtente::STATO_ATTIVO),
            new EGestore(22, 'Sara', 'Conti', 'sara.conti@example.com', 'hash-sara', '+39061239992', EUtente::STATO_ATTIVO)
        ];

        self::$ghostKitchens = [
            new EGhostKitchen(101, 21, 'Ghost Roma Centro', 'Spazio attrezzato.', 'Via Nazionale 10', 'Roma', '00184', 35.0, 20, 80.0, EGhostKitchen::STATO_ATTIVA, 4.5, 34),
            new EGhostKitchen(102, 22, 'Milano Lab Kitchen', 'Cucina professionale.', 'Via Torino 20', 'Milano', '20123', 55.0, 30, 120.0, EGhostKitchen::STATO_ATTIVA, 4.9, 27),
            new EGhostKitchen(103, 21, 'Trastevere Food Hub', 'Laboratorio condiviso.', 'Viale Trastevere 50', 'Roma', '00153', 48.0, 16, 65.0, EGhostKitchen::STATO_ATTIVA, 4.1, 18)
        ];

        self::$menu = [
            new EMenu(301, 1, 'Percorso Sushi Signature', 'Menu degustazione sushi.', 55.0, true),
            new EMenu(302, 1, 'Japanese Dinner Experience', 'Menu completo.', 75.0, true),
            new EMenu(303, 2, 'Fusion Experience', 'Menu fusion.', 68.0, true)
        ];

        self::$piatti = [
            new EPiatto(401, 301, 'Nigiri Selection', EPiatto::CATEGORIA_SECONDO, 'Selezione nigiri.', 'Riso, pesce', 'Pesce, soia', 0.0, 1),
            new EPiatto(402, 301, 'Uramaki Crunch', EPiatto::CATEGORIA_SECONDO, 'Uramaki croccante.', 'Riso, gambero', 'Crostacei, glutine', 4.5, 2),
            new EPiatto(403, 302, 'Miso Soup', EPiatto::CATEGORIA_PRIMO, 'Zuppa miso.', 'Miso, tofu', 'Soia', 0.0, 1),
            new EPiatto(404, 302, 'Mochi Trio', EPiatto::CATEGORIA_DOLCE, 'Tris di mochi.', 'Farina di riso, matcha', 'Puo contenere latte', 3.0, 2)
        ];

        self::$media = [
            new EMedia(201, EMedia::OWNER_CHEF, 1, EMedia::TIPO_MEDIA_FOTO_PROFILO, 'chef-marco.jpg', '/media/chef/chef-marco.jpg', 'image/jpeg', 'Foto profilo chef', '2026-05-18', 0, EMedia::STATO_ATTIVO),
            new EMedia(202, EMedia::OWNER_GHOST_KITCHEN, 101, EMedia::TIPO_MEDIA_FOTO_AMBIENTE, 'ghost-roma-centro.jpg', '/media/ghost-kitchen/ghost-roma-centro.jpg', 'image/jpeg', 'Immagine principale', '2026-05-18', 0, EMedia::STATO_ATTIVO),
            new EMedia(203, EMedia::OWNER_PIATTO, 401, EMedia::TIPO_MEDIA_FOTO_PIATTO, 'nigiri-selection.jpg', '/media/piatti/nigiri-selection.jpg', 'image/jpeg', 'Foto Nigiri Selection', '2026-05-18', 0, EMedia::STATO_ATTIVO),
            new EMedia(204, EMedia::OWNER_PIATTO, 402, EMedia::TIPO_MEDIA_FOTO_PIATTO, 'uramaki-crunch.jpg', '/media/piatti/uramaki-crunch.jpg', 'image/jpeg', 'Foto Uramaki Crunch', '2026-05-18', 0, EMedia::STATO_ATTIVO),
            new EMedia(205, EMedia::OWNER_PIATTO, 404, EMedia::TIPO_MEDIA_FOTO_PIATTO, 'mochi-trio.jpg', '/media/piatti/mochi-trio.jpg', 'image/jpeg', 'Foto Mochi Trio', '2026-05-18', 0, EMedia::STATO_ATTIVO),
            new EMedia(206, EMedia::OWNER_GHOST_KITCHEN, 101, EMedia::TIPO_MEDIA_PLANIMETRIA, 'ghost-roma-planimetria.jpg', '/media/ghost-kitchen/ghost-roma-planimetria.jpg', 'image/jpeg', 'Planimetria', '2026-05-18', 1, EMedia::STATO_ATTIVO),
            new EMedia(207, EMedia::OWNER_GHOST_KITCHEN, 102, EMedia::TIPO_MEDIA_FOTO_AMBIENTE, 'ghost-milano.jpg', '/media/ghost-kitchen/ghost-milano.jpg', 'image/jpeg', 'Ambiente Milano', '2026-05-18', 0, EMedia::STATO_ATTIVO)
        ];

        self::$attrezzature = [
            new EAttrezzatura(601, 101, 'Forno combinato', 'cottura', 'Forno professionale', 2),
            new EAttrezzatura(602, 101, 'Abbattitore', 'conservazione', 'Abbattitore rapido', 1),
            new EAttrezzatura(603, 102, 'Piano induzione', 'cottura', 'Piano a 6 fuochi', 3)
        ];

        self::$certificazioni = [
            new ECertificazione(501, 1, 'HACCP', 'haccp-marco.pdf', '/certificazioni/haccp-marco.pdf', ECertificazione::STATO_APPROVATA, '2026-04-10', '2026-04-15', 'Verificata'),
            new ECertificazione(502, 1, 'Somministrazione alimenti', 'sab-marco-rossi.pdf', '/certificazioni/sab-marco-rossi.pdf', ECertificazione::STATO_APPROVATA, '2026-04-12', '2026-04-18', 'Documento approvato.'),
            new ECertificazione(503, 2, 'HACCP', 'haccp-laura-bianchi.pdf', '/certificazioni/haccp-laura-bianchi.pdf', ECertificazione::STATO_APPROVATA, '2026-03-02', '2026-03-08', 'Documento approvato.'),
            new ECertificazione(6001, 1, 'HACCP', 'haccp-marco-rinnovo.pdf', '/certificazioni/pending/haccp-marco-rinnovo.pdf', ECertificazione::STATO_IN_ATTESA, '2026-05-16'),
            new ECertificazione(6002, 2, 'Sicurezza alimentare', 'sicurezza-laura.pdf', '/certificazioni/pending/sicurezza-laura.pdf', ECertificazione::STATO_IN_ATTESA, '2026-05-17')
        ];

        self::$disponibilitaChef = [
            new EDisponibilitaChef(701, 1, '2026-06-10', '18:00', '22:00', EDisponibilitaChef::STATO_LIBERA),
            new EDisponibilitaChef(702, 2, '2026-06-11', '19:00', '23:00', EDisponibilitaChef::STATO_LIBERA)
        ];

        self::$disponibilitaGhostKitchen = [
            new EDisponibilitaGhostKitchen(801, 101, '2026-06-12', '10:00', '14:00', EDisponibilitaGhostKitchen::STATO_LIBERA),
            new EDisponibilitaGhostKitchen(802, 102, '2026-06-13', '15:00', '20:00', EDisponibilitaGhostKitchen::STATO_LIBERA)
        ];

        self::$prenotazioniChef = [
            new EPrenotazioneChef(901, 10, '2026-05-19', '2026-06-10', '18:00', '22:00', EPrenotazione::STATO_IN_ATTESA, 220.0, 'Richiesta iniziale', 1, 301, 'Via Appia 12, Roma', 4, 'No crostacei'),
            new EPrenotazioneChef(1001, 10, '2026-05-01', '2026-05-10', '19:00', '23:00', EPrenotazione::STATO_COMPLETATA, 300.0, 'Cena privata completata.', 1, 301, 'Via Roma 4, Roma', 6, ''),
            new EPrenotazioneChef(1002, 10, '2026-05-15', '2026-05-30', '20:00', '23:00', EPrenotazione::STATO_PAGATA, 450.0, 'Prenotazione cancellabile.', 2, 303, 'Via Milano 8, Roma', 8, '')
        ];

        self::$prenotazioniGhostKitchen = [
            new EPrenotazioneGhostKitchen(902, 1, '2026-05-19', '2026-06-12', '10:00', '14:00', EPrenotazione::STATO_IN_ATTESA, 140.0, 'Uso per prep delivery', 101, EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CHEF),
            new EPrenotazioneGhostKitchen(2001, 1, '2026-05-01', '2026-05-12', '09:00', '15:00', EPrenotazione::STATO_COMPLETATA, 210.0, 'Slot completato.', 101, EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CHEF),
            new EPrenotazioneGhostKitchen(2002, 10, '2026-05-14', '2026-05-25', '10:00', '18:00', EPrenotazione::STATO_PAGATA, 280.0, 'Prenotazione cancellabile.', 101, EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CLIENTE)
        ];

        self::$metodiPagamento = [
            new EMetodoPagamento(1001, 10, EMetodoPagamento::TIPO_CARTA, 'Anna Neri', 'VISA', '4242', 12, 2028, true),
            new EMetodoPagamento(1002, 1, EMetodoPagamento::TIPO_PAYPAL, 'Marco Rossi', 'PAYPAL', '', 0, 0, true)
        ];

        self::$pagamenti = [
            new EPagamento(1101, 901, EPagamento::PRENOTAZIONE_CHEF, 1001, 44.0, EPagamento::TIPO_CAPARRA, EPagamento::STATO_IN_ATTESA, 'TX-SEED-1101', '2026-05-19'),
            new EPagamento(8001, 1002, EPagamento::PRENOTAZIONE_CHEF, 1001, 450.0, EPagamento::TIPO_TOTALE, EPagamento::STATO_COMPLETATO, 'TX-CHEF-1002', '2026-05-15'),
            new EPagamento(8002, 2002, EPagamento::PRENOTAZIONE_GHOST_KITCHEN, 1001, 280.0, EPagamento::TIPO_TOTALE, EPagamento::STATO_COMPLETATO, 'TX-GK-2002', '2026-05-14')
        ];

        self::$recensioni = [
            new ERecensioneChef(3001, 10, 5, 'Servizio preciso e menu ottimo.', '2026-05-11', ERecensione::STATO_VISIBILE, 1, 1001),
            new ERecensioneGhostKitchen(3002, 1, 4, 'Cucina ben attrezzata.', '2026-05-13', ERecensione::STATO_VISIBILE, 101, 2001)
        ];

        self::$segnalazioni = [
            new ESegnalazione(4001, 10, ESegnalazione::TARGET_RECENSIONE, 3001, 'Commento non conforme', 'La recensione contiene dettagli da verificare.', ESegnalazione::STATO_APERTA, '2026-05-17'),
            new ESegnalazione(4002, 1, ESegnalazione::TARGET_UTENTE, 10, 'Comportamento scorretto', 'Utente da verificare dopo mancata presenza.', ESegnalazione::STATO_IN_VALUTAZIONE, '2026-05-18')
        ];

        self::$cancellazioni = [
            new ECancellazione(7001, 1002, ECancellazione::PRENOTAZIONE_CHEF, 10, 'Cancellazione simulabile', '2026-05-18', 292.50, 157.50, ECancellazione::STATO_RICHIESTA)
        ];

        self::$rimborsi = [
            new ERimborso(7101, 8001, 7001, 157.50, 'Rimborso simulabile', ERimborso::STATO_RICHIESTO, '2026-05-18')
        ];

        self::$initialized = true;
    }

    public static function cercaChef(string $localita, string $tipologiaCucina, float $budgetMax, int $valutazioneMin): array
    {
        self::init();
        $mappaLocalitaChef = [1 => 'roma', 2 => 'milano', 3 => 'roma'];
        $localita = strtolower(trim($localita));
        $tipologiaCucina = strtolower(trim($tipologiaCucina));
        $risultati = [];

        foreach (self::$chef as $chef) {
            $idChef = $chef->getIdChef();
            $localitaChef = $idChef !== null && isset($mappaLocalitaChef[$idChef]) ? $mappaLocalitaChef[$idChef] : '';
            if ($localita !== '' && $localitaChef !== $localita) {
                continue;
            }
            if ($tipologiaCucina !== '' && strtolower($chef->getTipologiaCucina()) !== $tipologiaCucina && strtolower($chef->getSpecializzazione()) !== $tipologiaCucina) {
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

    public static function cercaGhostKitchen(string $localita, float $budgetMax, int $valutazioneMin): array
    {
        self::init();
        $localita = strtolower(trim($localita));
        $risultati = [];

        foreach (self::$ghostKitchens as $ghostKitchen) {
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

    public static function getMediaPrincipale(string $tipoOwner, int $idOwner): ?EMedia
    {
        self::init();
        $mediaOwner = self::getMediaByOwner($tipoOwner, $idOwner);
        return $mediaOwner[0] ?? null;
    }

    public static function loadChef(int $idChef): ?EChef
    {
        self::init();
        foreach (self::$chef as $chef) {
            if ($chef->getIdChef() === $idChef) {
                return $chef;
            }
        }
        return null;
    }

    public static function loadCliente(int $idCliente): ?ECliente
    {
        self::init();
        foreach (self::$clienti as $cliente) {
            if ($cliente->getIdCliente() === $idCliente) {
                return $cliente;
            }
        }
        return null;
    }

    public static function loadUtente(int $idUtente): ?EUtente
    {
        self::init();
        foreach ([self::$clienti, self::$chef, self::$gestori] as $gruppo) {
            foreach ($gruppo as $utente) {
                if ($utente->getIdUtente() === $idUtente) {
                    return $utente;
                }
            }
        }
        return null;
    }

    public static function loadGhostKitchen(int $idGhostKitchen): ?EGhostKitchen
    {
        self::init();
        foreach (self::$ghostKitchens as $ghostKitchen) {
            if ($ghostKitchen->getId() === $idGhostKitchen) {
                return $ghostKitchen;
            }
        }
        return null;
    }

    public static function loadMenuByChef(int $idChef): array
    {
        self::init();
        return array_values(array_filter(self::$menu, static fn (EMenu $menu): bool => $menu->getIdChef() === $idChef));
    }

    public static function loadMenu(int $idMenu): ?EMenu
    {
        self::init();
        foreach (self::$menu as $menu) {
            if ($menu->getIdMenu() === $idMenu) {
                return $menu;
            }
        }
        return null;
    }

    public static function loadPiattiByMenu(int $idMenu): array
    {
        self::init();
        return array_values(array_filter(self::$piatti, static fn (EPiatto $piatto): bool => $piatto->getIdMenu() === $idMenu));
    }

    public static function loadCertificazioniApprovateByChef(int $idChef): array
    {
        self::init();
        return array_values(array_filter(self::$certificazioni, static fn (ECertificazione $c): bool => $c->getIdChef() === $idChef && $c->getStato() === ECertificazione::STATO_APPROVATA));
    }

    public static function getMediaByOwner(string $tipoOwner, int $idOwner): array
    {
        self::init();
        $tipoOwner = strtolower(trim($tipoOwner));
        return array_values(array_filter(self::$media, static fn (EMedia $m): bool => $m->getTipoOwner() === $tipoOwner && $m->getIdOwner() === $idOwner));
    }

    public static function loadAttrezzatureByGhostKitchen(int $idGhostKitchen): array
    {
        self::init();
        return array_values(array_filter(self::$attrezzature, static fn (EAttrezzatura $a): bool => $a->getIdGhostKitchen() === $idGhostKitchen));
    }

    public static function loadDisponibilitaGhostKitchen(int $idGhostKitchen): array
    {
        self::init();
        return array_values(array_filter(self::$disponibilitaGhostKitchen, static fn (EDisponibilitaGhostKitchen $d): bool => $d->getIdGhostKitchen() === $idGhostKitchen));
    }

    public static function loadDisponibilitaGhostKitchenById(int $idDisponibilitaGhostKitchen): ?EDisponibilitaGhostKitchen
    {
        self::init();
        foreach (self::$disponibilitaGhostKitchen as $disponibilita) {
            if ($disponibilita->getIdDisponibilitaGhostKitchen() === $idDisponibilitaGhostKitchen) {
                return $disponibilita;
            }
        }
        return null;
    }

    public static function loadDisponibilitaChef(int $idChef): array
    {
        self::init();
        return array_values(array_filter(self::$disponibilitaChef, static fn (EDisponibilitaChef $d): bool => $d->getIdChef() === $idChef));
    }

    public static function loadDisponibilitaChefById(int $idDisponibilitaChef): ?EDisponibilitaChef
    {
        self::init();
        foreach (self::$disponibilitaChef as $disponibilita) {
            if ($disponibilita->getIdDisponibilitaChef() === $idDisponibilitaChef) {
                return $disponibilita;
            }
        }
        return null;
    }

    public static function verificaDisponibilitaChef(int $idChef, string $data, string $oraInizio, string $oraFine): bool
    {
        self::init();
        foreach (self::$disponibilitaChef as $disponibilita) {
            if ($disponibilita->getIdChef() === $idChef && $disponibilita->getData() === $data && $disponibilita->getOraInizio() === $oraInizio && $disponibilita->getOraFine() === $oraFine && $disponibilita->getStato() === EDisponibilitaChef::STATO_LIBERA) {
                return true;
            }
        }

        return false;
    }

    public static function verificaDisponibilitaGhostKitchen(int $idGhostKitchen, string $data, string $oraInizio, string $oraFine): bool
    {
        self::init();
        foreach (self::$disponibilitaGhostKitchen as $disponibilita) {
            if ($disponibilita->getIdGhostKitchen() === $idGhostKitchen && $disponibilita->getData() === $data && $disponibilita->getOraInizio() === $oraInizio && $disponibilita->getOraFine() === $oraFine && $disponibilita->getStato() === EDisponibilitaGhostKitchen::STATO_LIBERA) {
                return true;
            }
        }

        return false;
    }

    public static function storePrenotazioneChef(EPrenotazioneChef $prenotazione): EPrenotazioneChef
    {
        self::init();
        if ($prenotazione->getIdPrenotazione() === null) {
            $prenotazione->setIdPrenotazione(self::$nextIdPrenotazioneChef++);
        }
        self::$prenotazioniChef[] = $prenotazione;
        return $prenotazione;
    }

    public static function storePrenotazioneGhostKitchen(EPrenotazioneGhostKitchen $prenotazione): EPrenotazioneGhostKitchen
    {
        self::init();
        if ($prenotazione->getIdPrenotazione() === null) {
            $prenotazione->setIdPrenotazione(self::$nextIdPrenotazioneGhostKitchen++);
        }
        self::$prenotazioniGhostKitchen[] = $prenotazione;
        return $prenotazione;
    }

    public static function storeDisponibilitaChef(EDisponibilitaChef $disponibilita): EDisponibilitaChef
    {
        self::init();
        if ($disponibilita->getIdDisponibilitaChef() === null) {
            $disponibilita->setIdDisponibilitaChef(self::$nextIdDisponibilitaChef++);
        }
        self::$disponibilitaChef[] = $disponibilita;
        return $disponibilita;
    }

    public static function storeDisponibilitaGhostKitchen(EDisponibilitaGhostKitchen $disponibilita): EDisponibilitaGhostKitchen
    {
        self::init();
        if ($disponibilita->getIdDisponibilitaGhostKitchen() === null) {
            $disponibilita->setIdDisponibilitaGhostKitchen(self::$nextIdDisponibilitaGhostKitchen++);
        }
        self::$disponibilitaGhostKitchen[] = $disponibilita;
        return $disponibilita;
    }

    public static function updateDisponibilitaChef(EDisponibilitaChef $disponibilita): EDisponibilitaChef
    {
        self::init();
        foreach (self::$disponibilitaChef as $index => $item) {
            if ($item->getIdDisponibilitaChef() === $disponibilita->getIdDisponibilitaChef()) {
                self::$disponibilitaChef[$index] = $disponibilita;
                break;
            }
        }
        return $disponibilita;
    }

    public static function updateDisponibilitaGhostKitchen(EDisponibilitaGhostKitchen $disponibilita): EDisponibilitaGhostKitchen
    {
        self::init();
        foreach (self::$disponibilitaGhostKitchen as $index => $item) {
            if ($item->getIdDisponibilitaGhostKitchen() === $disponibilita->getIdDisponibilitaGhostKitchen()) {
                self::$disponibilitaGhostKitchen[$index] = $disponibilita;
                break;
            }
        }
        return $disponibilita;
    }

    public static function loadRichiestePrenotazioneChef(int $idChef): array
    {
        self::init();
        return array_values(array_filter(self::$prenotazioniChef, static fn (EPrenotazioneChef $p): bool => $p->getIdChef() === $idChef && $p->getStato() === EPrenotazione::STATO_IN_ATTESA));
    }

    public static function loadRichiestePrenotazioneGhostKitchenByGestore(int $idGestore): array
    {
        self::init();
        $idsGhostKitchen = array_map(static fn (EGhostKitchen $g): ?int => $g->getId(), array_filter(self::$ghostKitchens, static fn (EGhostKitchen $g): bool => $g->getIdGestore() === $idGestore));
        return array_values(array_filter(self::$prenotazioniGhostKitchen, static fn (EPrenotazioneGhostKitchen $p): bool => in_array($p->getIdGhostKitchen(), $idsGhostKitchen, true) && $p->getStato() === EPrenotazione::STATO_IN_ATTESA));
    }

    public static function loadPrenotazioneChef(int $idPrenotazione): ?EPrenotazioneChef
    {
        self::init();
        foreach (self::$prenotazioniChef as $prenotazione) {
            if ($prenotazione->getIdPrenotazione() === $idPrenotazione) {
                return $prenotazione;
            }
        }
        return null;
    }

    public static function loadPrenotazioneGhostKitchen(int $idPrenotazione): ?EPrenotazioneGhostKitchen
    {
        self::init();
        foreach (self::$prenotazioniGhostKitchen as $prenotazione) {
            if ($prenotazione->getIdPrenotazione() === $idPrenotazione) {
                return $prenotazione;
            }
        }
        return null;
    }

    public static function updatePrenotazioneChef(EPrenotazioneChef $prenotazione): EPrenotazioneChef
    {
        self::init();
        foreach (self::$prenotazioniChef as $index => $item) {
            if ($item->getIdPrenotazione() === $prenotazione->getIdPrenotazione()) {
                self::$prenotazioniChef[$index] = $prenotazione;
                break;
            }
        }
        return $prenotazione;
    }

    public static function updatePrenotazioneGhostKitchen(EPrenotazioneGhostKitchen $prenotazione): EPrenotazioneGhostKitchen
    {
        self::init();
        foreach (self::$prenotazioniGhostKitchen as $index => $item) {
            if ($item->getIdPrenotazione() === $prenotazione->getIdPrenotazione()) {
                self::$prenotazioniGhostKitchen[$index] = $prenotazione;
                break;
            }
        }
        return $prenotazione;
    }

    public static function calcolaImportoPagamento(string $tipoPrenotazione, int $idPrenotazione, string $tipoPagamento): float
    {
        self::init();
        $tipoPrenotazione = strtolower(trim($tipoPrenotazione));
        $tipoPagamento = strtolower(trim($tipoPagamento));

        $prenotazione = $tipoPrenotazione === 'chef'
            ? self::loadPrenotazioneChef($idPrenotazione)
            : self::loadPrenotazioneGhostKitchen($idPrenotazione);

        if ($prenotazione === null) {
            throw new InvalidArgumentException('Prenotazione non trovata per calcolo importo.');
        }

        $totale = $prenotazione->getImportoTotale();
        if ($tipoPagamento === EPagamento::TIPO_CAPARRA) {
            return round($totale * 0.20, 2);
        }
        if ($tipoPagamento === EPagamento::TIPO_SALDO) {
            return round($totale * 0.80, 2);
        }
        if ($tipoPagamento === EPagamento::TIPO_PENALE) {
            return round($totale * 0.10, 2);
        }

        return $totale;
    }

    public static function loadMetodiPagamentoByUtente(int $idUtente): array
    {
        self::init();
        return array_values(array_filter(self::$metodiPagamento, static fn (EMetodoPagamento $m): bool => $m->getIdUtente() === $idUtente && $m->isAttivo()));
    }

    public static function loadMetodoPagamento(int $idMetodoPagamento): ?EMetodoPagamento
    {
        self::init();
        foreach (self::$metodiPagamento as $metodo) {
            if ($metodo->getIdMetodoPagamento() === $idMetodoPagamento) {
                return $metodo;
            }
        }
        return null;
    }

    public static function storePagamento(EPagamento $pagamento): EPagamento
    {
        self::init();
        if ($pagamento->getIdPagamento() === null) {
            $pagamento->setIdPagamento(self::$nextIdPagamento++);
        }
        self::$pagamenti[] = $pagamento;
        return $pagamento;
    }

    public static function updatePagamento(EPagamento $pagamento): EPagamento
    {
        self::init();
        foreach (self::$pagamenti as $index => $item) {
            if ($item->getIdPagamento() === $pagamento->getIdPagamento()) {
                self::$pagamenti[$index] = $pagamento;
                break;
            }
        }
        return $pagamento;
    }

    public static function loadPagamentoByPrenotazione(string $tipoPrenotazione, int $idPrenotazione): ?EPagamento
    {
        self::init();
        $tipoPrenotazione = strtolower(trim($tipoPrenotazione));

        foreach (self::$pagamenti as $pagamento) {
            if ($pagamento->getTipoPrenotazione() === $tipoPrenotazione && $pagamento->getIdPrenotazione() === $idPrenotazione) {
                return $pagamento;
            }
        }

        return null;
    }

    public static function calcolaRimborsoStimato(string $tipoPrenotazione, int $idPrenotazione): array
    {
        self::init();
        $pagamento = self::loadPagamentoByPrenotazione($tipoPrenotazione, $idPrenotazione);

        if ($pagamento === null) {
            return [
                'trovato' => false,
                'messaggio' => 'Pagamento non trovato per la prenotazione indicata.'
            ];
        }

        $tipoPrenotazione = strtolower(trim($tipoPrenotazione));
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
        self::init();
        if ($cancellazione->getIdCancellazione() === null) {
            $cancellazione->setIdCancellazione(self::$nextIdCancellazione++);
        }
        self::$cancellazioni[] = $cancellazione;
        return $cancellazione;
    }

    public static function storeRimborso(ERimborso $rimborso): ERimborso
    {
        self::init();
        if ($rimborso->getIdRimborso() === null) {
            $rimborso->setIdRimborso(self::$nextIdRimborso++);
        }
        self::$rimborsi[] = $rimborso;
        return $rimborso;
    }

    public static function verificaPrenotazioneRecensibile(string $tipoTarget, int $idPrenotazione, int $idAutore): array
    {
        self::init();
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
        self::init();
        if ($recensione->getIdRecensione() === null) {
            $recensione->setIdRecensione(self::$nextIdRecensioneChef++);
        }
        self::$recensioni[] = $recensione;
        return $recensione;
    }

    public static function storeRecensioneGhostKitchen(ERecensioneGhostKitchen $recensione): ERecensioneGhostKitchen
    {
        self::init();
        if ($recensione->getIdRecensione() === null) {
            $recensione->setIdRecensione(self::$nextIdRecensioneGhostKitchen++);
        }
        self::$recensioni[] = $recensione;
        return $recensione;
    }

    public static function aggiornaValutazioneChef(int $idChef): array
    {
        self::init();
        return [
            'idChef' => $idChef,
            'valutazioneMediaAggiornata' => 4.75,
            'numeroRecensioni' => 53
        ];
    }

    public static function aggiornaValutazioneGhostKitchen(int $idGhostKitchen): array
    {
        self::init();
        return [
            'idGhostKitchen' => $idGhostKitchen,
            'valutazioneMediaAggiornata' => 4.55,
            'numeroRecensioni' => 35
        ];
    }

    public static function loadTargetSegnalazione(string $tipoTarget, int $idTarget)
    {
        self::init();
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
            return self::loadMenu($idTarget);
        }

        return null;
    }

    public static function storeSegnalazione(ESegnalazione $segnalazione): ESegnalazione
    {
        self::init();
        if ($segnalazione->getIdSegnalazione() === null) {
            $segnalazione->setIdSegnalazione(self::$nextIdSegnalazione++);
        }
        self::$segnalazioni[] = $segnalazione;
        return $segnalazione;
    }

    public static function loadCertificazioniInAttesa(): array
    {
        self::init();
        return array_values(array_filter(self::$certificazioni, static fn (ECertificazione $certificazione): bool => $certificazione->getStato() === ECertificazione::STATO_IN_ATTESA));
    }

    public static function loadCertificazione(int $idCertificazione): ?ECertificazione
    {
        self::init();
        foreach (self::$certificazioni as $certificazione) {
            if ($certificazione->getIdCertificazione() === $idCertificazione) {
                return $certificazione;
            }
        }
        return null;
    }

    public static function updateCertificazione(ECertificazione $certificazione): ECertificazione
    {
        self::init();
        foreach (self::$certificazioni as $index => $item) {
            if ($item->getIdCertificazione() === $certificazione->getIdCertificazione()) {
                self::$certificazioni[$index] = $certificazione;
                break;
            }
        }
        return $certificazione;
    }

    public static function loadSegnalazioniDaModerare(): array
    {
        self::init();
        return self::$segnalazioni;
    }

    public static function loadSegnalazione(int $idSegnalazione): ?ESegnalazione
    {
        self::init();
        foreach (self::$segnalazioni as $segnalazione) {
            if ($segnalazione->getIdSegnalazione() === $idSegnalazione) {
                return $segnalazione;
            }
        }
        return null;
    }

    public static function updateSegnalazione(ESegnalazione $segnalazione): ESegnalazione
    {
        self::init();
        foreach (self::$segnalazioni as $index => $item) {
            if ($item->getIdSegnalazione() === $segnalazione->getIdSegnalazione()) {
                self::$segnalazioni[$index] = $segnalazione;
                break;
            }
        }
        return $segnalazione;
    }

    public static function loadRecensione(int $idRecensione): ?ERecensione
    {
        self::init();
        foreach (self::$recensioni as $recensione) {
            if ($recensione->getIdRecensione() === $idRecensione) {
                return $recensione;
            }
        }
        return null;
    }

    public static function updateRecensione(ERecensione $recensione): ERecensione
    {
        self::init();
        foreach (self::$recensioni as $index => $item) {
            if ($item->getIdRecensione() === $recensione->getIdRecensione()) {
                self::$recensioni[$index] = $recensione;
                break;
            }
        }
        return $recensione;
    }

    public static function updateUtente(EUtente $utente): EUtente
    {
        self::init();

        if ($utente instanceof ECliente) {
            return self::updateUtenteInGruppo(self::$clienti, $utente);
        }

        if ($utente instanceof EChef) {
            return self::updateUtenteInGruppo(self::$chef, $utente);
        }

        if ($utente instanceof EGestore) {
            return self::updateUtenteInGruppo(self::$gestori, $utente);
        }

        return $utente;
    }

    private static function updateUtenteInGruppo(array &$gruppo, EUtente $utente): EUtente
    {
        foreach ($gruppo as $index => $item) {
            if ($item->getIdUtente() === $utente->getIdUtente()) {
                $gruppo[$index] = $utente;
                break;
            }
        }

        return $utente;
    }

    public static function getStatisticheDashboard(array $filtri): array
    {
        self::init();
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
        self::init();
        $prenotazioniChef = count(self::$prenotazioniChef);
        $prenotazioniGhostKitchen = count(self::$prenotazioniGhostKitchen);

        return [
            'prenotazioniTotali' => $prenotazioniChef + $prenotazioniGhostKitchen,
            'prenotazioniChef' => $prenotazioniChef,
            'prenotazioniGhostKitchen' => $prenotazioniGhostKitchen,
            'ghostKitchenPiuPrenotate' => [
                ['idGhostKitchen' => 101, 'nome' => 'Ghost Roma Centro', 'prenotazioni' => 11],
                ['idGhostKitchen' => 102, 'nome' => 'Milano Lab Kitchen', 'prenotazioni' => 6]
            ]
        ];
    }

    public static function getStatistichePagamenti(array $filtri): array
    {
        self::init();
        $volumePagamenti = 0.0;
        foreach (self::$pagamenti as $pagamento) {
            $volumePagamenti += $pagamento->getImporto();
        }

        return [
            'volumePagamenti' => round($volumePagamenti, 2),
            'numeroRimborsi' => count(self::$rimborsi),
            'volumeRimborsi' => 410.00
        ];
    }

    public static function getStatisticheRecensioni(array $filtri): array
    {
        self::init();
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
        self::init();
        return [
            'segnalazioniAperte' => count(array_filter(self::$segnalazioni, static fn (ESegnalazione $s): bool => $s->getStato() === ESegnalazione::STATO_APERTA)),
            'certificazioniInAttesa' => count(self::loadCertificazioniInAttesa())
        ];
    }
}

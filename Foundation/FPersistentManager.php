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

class FPersistentManager
{
    private static bool $initialized = false;

    /** @var ECliente[] */
    private static array $clienti = [];
    /** @var EChef[] */
    private static array $chef = [];
    /** @var EGestore[] */
    private static array $gestori = [];
    /** @var EGhostKitchen[] */
    private static array $ghostKitchens = [];
    /** @var EMenu[] */
    private static array $menu = [];
    /** @var EPiatto[] */
    private static array $piatti = [];
    /** @var EMedia[] */
    private static array $media = [];
    /** @var EAttrezzatura[] */
    private static array $attrezzature = [];
    /** @var ECertificazione[] */
    private static array $certificazioni = [];
    /** @var EDisponibilitaChef[] */
    private static array $disponibilitaChef = [];
    /** @var EDisponibilitaGhostKitchen[] */
    private static array $disponibilitaGhostKitchen = [];
    /** @var EPrenotazioneChef[] */
    private static array $prenotazioniChef = [];
    /** @var EPrenotazioneGhostKitchen[] */
    private static array $prenotazioniGhostKitchen = [];
    /** @var EMetodoPagamento[] */
    private static array $metodiPagamento = [];
    /** @var EPagamento[] */
    private static array $pagamenti = [];

    private static int $nextIdDisponibilitaChef = 800;
    private static int $nextIdDisponibilitaGhostKitchen = 900;
    private static int $nextIdPrenotazioneChef = 1000;
    private static int $nextIdPrenotazioneGhostKitchen = 1100;
    private static int $nextIdPagamento = 1200;

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
            new EChef(2, 'Laura', 'Bianchi', 'laura.bianchi@example.com', 'hash-laura', '+39069876543', EUtente::STATO_ATTIVO, 'Chef fusion.', 'Chef fusion', 'fusion', 110.0, 10, EChef::STATO_VERIFICA_VERIFICATO, 4.8, 41)
        ];

        self::$gestori = [
            new EGestore(21, 'Paolo', 'Romani', 'paolo.romani@example.com', 'hash-paolo', '+39061239991', EUtente::STATO_ATTIVO),
            new EGestore(22, 'Sara', 'Conti', 'sara.conti@example.com', 'hash-sara', '+39061239992', EUtente::STATO_ATTIVO)
        ];

        self::$ghostKitchens = [
            new EGhostKitchen(101, 21, 'Ghost Roma Centro', 'Spazio attrezzato.', 'Via Nazionale 10', 'Roma', '00184', 35.0, 20, 80.0, EGhostKitchen::STATO_ATTIVA, 4.5, 34),
            new EGhostKitchen(102, 22, 'Milano Lab Kitchen', 'Cucina professionale.', 'Via Torino 20', 'Milano', '20123', 55.0, 30, 120.0, EGhostKitchen::STATO_ATTIVA, 4.9, 27)
        ];

        self::$menu = [
            new EMenu(301, 1, 'Percorso Sushi Signature', 'Menu degustazione sushi.', 55.0, true),
            new EMenu(302, 1, 'Japanese Dinner Experience', 'Menu completo.', 75.0, true),
            new EMenu(303, 2, 'Fusion Experience', 'Menu fusion.', 68.0, true)
        ];

        self::$piatti = [
            new EPiatto(401, 301, 'Nigiri Selection', EPiatto::CATEGORIA_SECONDO, 'Selezione nigiri.', 'Riso, pesce', 'Pesce, soia', 0.0, 1),
            new EPiatto(402, 301, 'Uramaki Crunch', EPiatto::CATEGORIA_SECONDO, 'Uramaki croccante.', 'Riso, gambero', 'Crostacei, glutine', 4.5, 2),
            new EPiatto(403, 302, 'Miso Soup', EPiatto::CATEGORIA_PRIMO, 'Zuppa miso.', 'Miso, tofu', 'Soia', 0.0, 1)
        ];

        self::$media = [
            new EMedia(201, EMedia::OWNER_CHEF, 1, EMedia::TIPO_MEDIA_FOTO_PROFILO, 'chef-marco.jpg', '/media/chef/chef-marco.jpg', 'image/jpeg', 'Foto profilo chef', '2026-05-18', 0, EMedia::STATO_ATTIVO),
            new EMedia(202, EMedia::OWNER_GHOST_KITCHEN, 101, EMedia::TIPO_MEDIA_FOTO_AMBIENTE, 'ghost-roma-centro.jpg', '/media/ghost-kitchen/ghost-roma-centro.jpg', 'image/jpeg', 'Immagine principale', '2026-05-18', 0, EMedia::STATO_ATTIVO),
            new EMedia(206, EMedia::OWNER_GHOST_KITCHEN, 101, EMedia::TIPO_MEDIA_PLANIMETRIA, 'ghost-roma-planimetria.jpg', '/media/ghost-kitchen/ghost-roma-planimetria.jpg', 'image/jpeg', 'Planimetria', '2026-05-18', 1, EMedia::STATO_ATTIVO),
            new EMedia(207, EMedia::OWNER_GHOST_KITCHEN, 102, EMedia::TIPO_MEDIA_FOTO_AMBIENTE, 'ghost-milano.jpg', '/media/ghost-kitchen/ghost-milano.jpg', 'image/jpeg', 'Ambiente Milano', '2026-05-18', 0, EMedia::STATO_ATTIVO)
        ];

        self::$attrezzature = [
            new EAttrezzatura(601, 101, 'Forno combinato', 'cottura', 'Forno professionale', 2),
            new EAttrezzatura(602, 101, 'Abbattitore', 'conservazione', 'Abbattitore rapido', 1),
            new EAttrezzatura(603, 102, 'Piano induzione', 'cottura', 'Piano a 6 fuochi', 3)
        ];

        self::$certificazioni = [
            new ECertificazione(501, 1, 'HACCP', 'haccp-marco.pdf', '/certificazioni/haccp-marco.pdf', ECertificazione::STATO_APPROVATA, '2026-04-10', '2026-04-15', 'Verificata')
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
            new EPrenotazioneChef(901, 10, '2026-05-19', '2026-06-10', '18:00', '22:00', EPrenotazione::STATO_IN_ATTESA, 220.0, 'Richiesta iniziale', 1, 301, 'Via Appia 12, Roma', 4, 'No crostacei')
        ];

        self::$prenotazioniGhostKitchen = [
            new EPrenotazioneGhostKitchen(902, 1, '2026-05-19', '2026-06-12', '10:00', '14:00', EPrenotazione::STATO_IN_ATTESA, 140.0, 'Uso per prep delivery', 101, EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CHEF)
        ];

        self::$metodiPagamento = [
            new EMetodoPagamento(1001, 10, EMetodoPagamento::TIPO_CARTA, 'Anna Neri', 'VISA', '4242', 12, 2028, true),
            new EMetodoPagamento(1002, 1, EMetodoPagamento::TIPO_PAYPAL, 'Marco Rossi', 'PAYPAL', '', 0, 0, true)
        ];

        self::$pagamenti = [
            new EPagamento(1101, 901, EPagamento::PRENOTAZIONE_CHEF, 1001, 44.0, EPagamento::TIPO_CAPARRA, EPagamento::STATO_IN_ATTESA, 'TX-SEED-1101', '2026-05-19')
        ];

        self::$initialized = true;
    }

    public static function cercaChef(string $localita, string $tipologiaCucina, float $budgetMax, int $valutazioneMin): array
    {
        self::init();
        $mappaLocalitaChef = [1 => 'roma', 2 => 'milano'];
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
            if (
                $disponibilita->getIdChef() === $idChef &&
                $disponibilita->getData() === $data &&
                $disponibilita->getOraInizio() === $oraInizio &&
                $disponibilita->getOraFine() === $oraFine &&
                $disponibilita->getStato() === EDisponibilitaChef::STATO_LIBERA
            ) {
                return true;
            }
        }

        return false;
    }

    public static function verificaDisponibilitaGhostKitchen(int $idGhostKitchen, string $data, string $oraInizio, string $oraFine): bool
    {
        self::init();
        foreach (self::$disponibilitaGhostKitchen as $disponibilita) {
            if (
                $disponibilita->getIdGhostKitchen() === $idGhostKitchen &&
                $disponibilita->getData() === $data &&
                $disponibilita->getOraInizio() === $oraInizio &&
                $disponibilita->getOraFine() === $oraFine &&
                $disponibilita->getStato() === EDisponibilitaGhostKitchen::STATO_LIBERA
            ) {
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
}

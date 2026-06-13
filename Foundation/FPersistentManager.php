<?php
declare(strict_types=1);

require_once __DIR__ . '/FConnectionDB.php';
require_once __DIR__ . '/FSession.php';
require_once __DIR__ . '/FUtente.php';
require_once __DIR__ . '/FCliente.php';
require_once __DIR__ . '/FChef.php';
require_once __DIR__ . '/FGestore.php';
require_once __DIR__ . '/FAmministratore.php';
require_once __DIR__ . '/FGhostKitchen.php';
require_once __DIR__ . '/FAttrezzatura.php';
require_once __DIR__ . '/FMenu.php';
require_once __DIR__ . '/FPiatto.php';
require_once __DIR__ . '/FMedia.php';
require_once __DIR__ . '/FCertificazione.php';
require_once __DIR__ . '/FDisponibilitaChef.php';
require_once __DIR__ . '/FDisponibilitaGhostKitchen.php';
require_once __DIR__ . '/FPrenotazione.php';
require_once __DIR__ . '/FPrenotazioneChef.php';
require_once __DIR__ . '/FPrenotazioneGhostKitchen.php';
require_once __DIR__ . '/FMetodoPagamento.php';
require_once __DIR__ . '/FPagamento.php';
require_once __DIR__ . '/FCancellazione.php';
require_once __DIR__ . '/FRimborso.php';
require_once __DIR__ . '/FRecensione.php';
require_once __DIR__ . '/FRecensioneChef.php';
require_once __DIR__ . '/FRecensioneGhostKitchen.php';
require_once __DIR__ . '/FSegnalazione.php';
require_once __DIR__ . '/FStatisticheDashboard.php';
require_once __DIR__ . '/FRegistrazione.php';

/*
 * Foundation ibrida controllata:
 * FConnectionDB e Singleton per la risorsa tecnica condivisa PDO;
 * FSession resta statica e incapsula la sessione PHP;
 * FPersistentManager e una facciata statica verso la persistenza;
 * le classi specifiche come FUtente sono mapper/persistor statici senza stato proprio.
 */
class FPersistentManager
{
    private function __construct()
    {
        // Facciata statica: non istanziabile.
    }

    public static function getConnection(): PDO
    {
        return FConnectionDB::getInstance()->getConnection();
    }

    public static function existUtente(int $idUtente): bool
    {
        return FUtente::exist($idUtente);
    }

    public static function loadUtente(int $idUtente): ?EUtente
    {
        return FUtente::load($idUtente);
    }

    public static function loadUtenteByEmail(string $email): ?EUtente
    {
        return FUtente::loadByEmail($email);
    }

    public static function storeUtente(EUtente $utente): EUtente|false
    {
        return self::storeAndReturn($utente, static fn (EUtente $entity): bool|int => FUtente::store($entity), 'setIdUtente');
    }

    public static function updateUtente(EUtente $utente): EUtente|false
    {
        return self::updateAndReturn($utente, static fn (EUtente $entity): bool => FUtente::update($entity));
    }

    public static function deleteUtente(int $idUtente): bool
    {
        return FUtente::delete($idUtente);
    }

    public static function emailUtenteExists(string $email): bool
    {
        return FUtente::emailExists($email);
    }

    public static function registraAccount(EUtente $utente, array $ruoli, array $chefData = [], array $certificazioni = [], array $ghostKitchenData = []): int|false
    {
        return FRegistrazione::registra($utente, $ruoli, $chefData, $certificazioni, $ghostKitchenData);
    }

    public static function verificaCredenziali(string $email, string $password): ?EUtente
    {
        return FUtente::verificaCredenziali($email, $password);
    }

    public static function getRuoliUtente(int $idUtente): array
    {
        return FUtente::getRuoli($idUtente);
    }

    public static function login(string $email, string $password): bool
    {
        $utente = self::verificaCredenziali($email, $password);
        if ($utente === null || $utente->getIdUtente() === null) {
            return false;
        }

        $ruoli = self::getRuoliUtente((int) $utente->getIdUtente());
        FSession::login([
            'idUtente' => $utente->getIdUtente(),
            'email' => $utente->getEmail(),
            'nome' => $utente->getNome(),
            'cognome' => $utente->getCognome(),
            'fotoProfilo' => $utente->getFotoProfilo(),
        ], $ruoli);

        return true;
    }

    public static function loadCliente(int $idCliente): ?ECliente { return FCliente::load($idCliente); }
    public static function loadClientiRegistrati(): array { return FCliente::loadAll(); }
    public static function loadChef(int $idChef): ?EChef { return FChef::load($idChef); }
    public static function loadChefRegistrati(): array { return FChef::loadAll(); }
    public static function loadGestore(int $idGestore): ?EGestore { return FGestore::load($idGestore); }
    public static function loadGestoriRegistrati(): array { return FGestore::loadAll(); }
    public static function loadAmministratore(int $idAmministratore): ?EAmministratore { return FAmministratore::load($idAmministratore); }

    public static function storeCliente(ECliente $cliente): ECliente|false { return self::storeAndReturn($cliente, static fn (ECliente $entity): bool|int => FCliente::store($entity), 'setIdCliente'); }
    public static function storeChef(EChef $chef): EChef|false { return self::storeAndReturn($chef, static fn (EChef $entity): bool|int => FChef::store($entity), 'setIdChef'); }
    public static function updateChef(EChef $chef): EChef|false { return self::updateAndReturn($chef, static fn (EChef $entity): bool => FChef::update($entity)); }
    public static function storeGestore(EGestore $gestore): EGestore|false { return self::storeAndReturn($gestore, static fn (EGestore $entity): bool|int => FGestore::store($entity), 'setIdGestore'); }
    public static function updateGestore(EGestore $gestore): EGestore|false { return self::updateAndReturn($gestore, static fn (EGestore $entity): bool => FGestore::update($entity)); }
    public static function storeAmministratore(EAmministratore $amministratore): EAmministratore|false { return self::storeAndReturn($amministratore, static fn (EAmministratore $entity): bool|int => FAmministratore::store($entity), 'setIdAmministratore'); }

    public static function loadGhostKitchen(int $idGhostKitchen): ?EGhostKitchen { return FGhostKitchen::load($idGhostKitchen); }
    public static function loadGhostKitchenRegistrate(): array { return FGhostKitchen::loadAll(); }
    public static function loadGhostKitchenByGestore(int $idGestore): array { return FGhostKitchen::loadByGestore($idGestore); }
    public static function storeGhostKitchen(EGhostKitchen $ghostKitchen): EGhostKitchen|false { return self::storeAndReturn($ghostKitchen, static fn (EGhostKitchen $entity): bool|int => FGhostKitchen::store($entity), 'setId'); }
    public static function updateGhostKitchen(EGhostKitchen $ghostKitchen): EGhostKitchen|false { return self::updateAndReturn($ghostKitchen, static fn (EGhostKitchen $entity): bool => FGhostKitchen::update($entity)); }
    public static function deleteGhostKitchen(int $idGhostKitchen): bool { return FGhostKitchen::delete($idGhostKitchen); }

    public static function loadAttrezzatura(int $idAttrezzatura): ?EAttrezzatura { return FAttrezzatura::load($idAttrezzatura); }
    public static function loadMenu(int $idMenu): ?EMenu { return FMenu::load($idMenu); }
    public static function loadPiatto(int $idPiatto): ?EPiatto { return FPiatto::load($idPiatto); }
    public static function loadMedia(int $idMedia): ?EMedia { return FMedia::load($idMedia); }
    public static function loadCertificazione(int $idCertificazione): ?ECertificazione { return FCertificazione::load($idCertificazione); }
    public static function loadDisponibilitaChefById(int $idDisponibilita): ?EDisponibilitaChef { return FDisponibilitaChef::load($idDisponibilita); }
    public static function loadDisponibilitaGhostKitchenById(int $idDisponibilita): ?EDisponibilitaGhostKitchen { return FDisponibilitaGhostKitchen::load($idDisponibilita); }
    public static function loadMetodoPagamento(int $idMetodoPagamento): ?EMetodoPagamento { return FMetodoPagamento::load($idMetodoPagamento); }
    public static function loadPagamento(int $idPagamento): ?EPagamento { return FPagamento::load($idPagamento); }
    public static function loadCancellazione(int $idCancellazione): ?ECancellazione { return FCancellazione::load($idCancellazione); }
    public static function loadRimborso(int $idRimborso): ?ERimborso { return FRimborso::load($idRimborso); }
    public static function loadSegnalazione(int $idSegnalazione): ?ESegnalazione { return FSegnalazione::load($idSegnalazione); }
    public static function loadPrenotazioneChef(int $idPrenotazione): ?EPrenotazioneChef { return FPrenotazioneChef::load($idPrenotazione); }
    public static function loadPrenotazioneGhostKitchen(int $idPrenotazione): ?EPrenotazioneGhostKitchen { return FPrenotazioneGhostKitchen::load($idPrenotazione); }
    public static function loadRecensioneChef(int $idRecensione): ?ERecensioneChef { return FRecensioneChef::load($idRecensione); }
    public static function loadRecensioneGhostKitchen(int $idRecensione): ?ERecensioneGhostKitchen { return FRecensioneGhostKitchen::load($idRecensione); }

    public static function loadMenuByChef(int $idChef): array { return FMenu::loadByChef($idChef); }
    public static function loadRecensioniByChef(int $idChef): array { return FRecensioneChef::loadByChef($idChef); }
    public static function loadPiattiByMenu(int $idMenu): array { return FPiatto::loadByMenu($idMenu); }
    public static function getMediaByOwner(string $tipoOwner, int $idOwner): array { return FMedia::loadByOwner($tipoOwner, $idOwner); }
    public static function getMediaPrincipale(string $tipoOwner, int $idOwner): ?EMedia { return FMedia::getPrincipale($tipoOwner, $idOwner); }
    public static function loadCertificazioniApprovateByChef(int $idChef): array
    {
        return array_values(array_filter(FCertificazione::loadByChef($idChef), static fn (ECertificazione $c): bool => $c->getStato() === ECertificazione::STATO_APPROVATA));
    }
    public static function loadCertificazioniByChef(int $idChef): array { return FCertificazione::loadByChef($idChef); }
    public static function loadCertificazioniByGhostKitchen(int $idGhostKitchen): array { return FCertificazione::loadByGhostKitchen($idGhostKitchen); }
    public static function loadCertificazioniInAttesa(): array { return FCertificazione::loadByStato(ECertificazione::STATO_IN_ATTESA); }
    public static function loadTutteCertificazioni(): array { return FCertificazione::loadAllCertificazioni(); }
    public static function loadCertificazioniHaccpInScadenza(int $giorni = 90): array { return FCertificazione::loadCertificazioniInScadenza($giorni); }
    public static function loadCertificazioniInScadenza(int $giorni = 90): array { return FCertificazione::loadCertificazioniInScadenza($giorni); }
    public static function chefHaCertificazioniInRegola(int $idChef): bool { return FCertificazione::chefHaCertificazioniInRegola($idChef); }
    public static function ghostKitchenHaCertificazioniInRegola(int $idGhostKitchen): bool { return FCertificazione::ghostKitchenHaCertificazioniInRegola($idGhostKitchen); }
    public static function loadAttrezzatureByGhostKitchen(int $idGhostKitchen): array { return FAttrezzatura::loadByGhostKitchen($idGhostKitchen); }
    public static function loadDisponibilitaChef(int $idChef): array { return FDisponibilitaChef::loadByChef($idChef); }
    public static function loadDisponibilitaGhostKitchen(int $idGhostKitchen): array { return FDisponibilitaGhostKitchen::loadByGhostKitchen($idGhostKitchen); }
    public static function loadDisponibilitaChefBySlot(int $idChef, string $data, string $oraInizio, string $oraFine): ?EDisponibilitaChef { return FDisponibilitaChef::loadBySlot($idChef, $data, $oraInizio, $oraFine); }
    public static function loadDisponibilitaGhostKitchenBySlot(int $idGhostKitchen, string $data, string $oraInizio, string $oraFine): ?EDisponibilitaGhostKitchen { return FDisponibilitaGhostKitchen::loadBySlot($idGhostKitchen, $data, $oraInizio, $oraFine); }
    public static function loadMetodiPagamentoByUtente(int $idUtente): array { return FMetodoPagamento::loadByUtente($idUtente); }
    public static function loadPagamentiByUtente(int $idUtente): array { return FPagamento::loadByUtente($idUtente); }
    public static function loadSegnalazioniDaModerare(): array { return FSegnalazione::loadByStato(ESegnalazione::STATO_APERTA); }

    public static function storeAttrezzatura(EAttrezzatura $entity): EAttrezzatura|false { return self::storeAndReturn($entity, static fn (EAttrezzatura $item): bool|int => FAttrezzatura::store($item), 'setIdAttrezzatura'); }
    public static function storeMenu(EMenu $entity): EMenu|false { return self::storeAndReturn($entity, static fn (EMenu $item): bool|int => FMenu::store($item), 'setIdMenu'); }
    public static function storePiatto(EPiatto $entity): EPiatto|false { return self::storeAndReturn($entity, static fn (EPiatto $item): bool|int => FPiatto::store($item), 'setIdPiatto'); }
    public static function storeMedia(EMedia $entity): EMedia|false { return self::storeAndReturn($entity, static fn (EMedia $item): bool|int => FMedia::store($item), 'setIdMedia'); }
    public static function storeCertificazione(ECertificazione $entity): ECertificazione|false { return self::storeAndReturn($entity, static fn (ECertificazione $item): bool|int => FCertificazione::store($item), 'setIdCertificazione'); }
    public static function storeDisponibilitaChef(EDisponibilitaChef $entity): EDisponibilitaChef|false { return self::storeAndReturn($entity, static fn (EDisponibilitaChef $item): bool|int => FDisponibilitaChef::store($item), 'setIdDisponibilitaChef'); }
    public static function storeDisponibilitaGhostKitchen(EDisponibilitaGhostKitchen $entity): EDisponibilitaGhostKitchen|false { return self::storeAndReturn($entity, static fn (EDisponibilitaGhostKitchen $item): bool|int => FDisponibilitaGhostKitchen::store($item), 'setIdDisponibilitaGhostKitchen'); }
    public static function storeMetodoPagamento(EMetodoPagamento $entity): EMetodoPagamento|false { return self::storeAndReturn($entity, static fn (EMetodoPagamento $item): bool|int => FMetodoPagamento::store($item), 'setIdMetodoPagamento'); }
    public static function storePagamento(EPagamento $entity): EPagamento|false { return self::storeAndReturn($entity, static fn (EPagamento $item): bool|int => FPagamento::store($item), 'setIdPagamento'); }
    public static function storeCancellazione(ECancellazione $entity): ECancellazione|false { return self::storeAndReturn($entity, static fn (ECancellazione $item): bool|int => FCancellazione::store($item), 'setIdCancellazione'); }
    public static function storeRimborso(ERimborso $entity): ERimborso|false { return self::storeAndReturn($entity, static fn (ERimborso $item): bool|int => FRimborso::store($item), 'setIdRimborso'); }
    public static function storeSegnalazione(ESegnalazione $entity): ESegnalazione|false { return self::storeAndReturn($entity, static fn (ESegnalazione $item): bool|int => FSegnalazione::store($item), 'setIdSegnalazione'); }
    public static function storePrenotazioneChef(EPrenotazioneChef $entity): EPrenotazioneChef|false { return self::storeAndReturn($entity, static fn (EPrenotazioneChef $item): bool|int => FPrenotazioneChef::store($item), 'setIdPrenotazione'); }
    public static function storePrenotazioneGhostKitchen(EPrenotazioneGhostKitchen $entity): EPrenotazioneGhostKitchen|false { return self::storeAndReturn($entity, static fn (EPrenotazioneGhostKitchen $item): bool|int => FPrenotazioneGhostKitchen::store($item), 'setIdPrenotazione'); }
    public static function storeRecensioneChef(ERecensioneChef $entity): ERecensioneChef|false { return self::storeAndReturn($entity, static fn (ERecensioneChef $item): bool|int => FRecensioneChef::store($item), 'setIdRecensione'); }
    public static function storeRecensioneGhostKitchen(ERecensioneGhostKitchen $entity): ERecensioneGhostKitchen|false { return self::storeAndReturn($entity, static fn (ERecensioneGhostKitchen $item): bool|int => FRecensioneGhostKitchen::store($item), 'setIdRecensione'); }

    public static function updateAttrezzatura(EAttrezzatura $entity): EAttrezzatura|false { return self::updateAndReturn($entity, static fn (EAttrezzatura $item): bool => FAttrezzatura::update($item)); }
    public static function deleteAttrezzatura(int $idAttrezzatura): bool { return FAttrezzatura::delete($idAttrezzatura); }
    public static function updateMenu(EMenu $entity): EMenu|false { return self::updateAndReturn($entity, static fn (EMenu $item): bool => FMenu::update($item)); }
    public static function updatePiatto(EPiatto $entity): EPiatto|false { return self::updateAndReturn($entity, static fn (EPiatto $item): bool => FPiatto::update($item)); }
    public static function deletePiatto(int $idPiatto): bool { return FPiatto::delete($idPiatto); }
    public static function updateMedia(EMedia $entity): EMedia|false { return self::updateAndReturn($entity, static fn (EMedia $item): bool => FMedia::update($item)); }
    public static function updateCertificazione(ECertificazione $entity): ECertificazione|false { return self::updateAndReturn($entity, static fn (ECertificazione $item): bool => FCertificazione::update($item)); }
    public static function updateDisponibilitaChef(EDisponibilitaChef $entity): EDisponibilitaChef|false { return self::updateAndReturn($entity, static fn (EDisponibilitaChef $item): bool => FDisponibilitaChef::update($item)); }
    public static function updateDisponibilitaGhostKitchen(EDisponibilitaGhostKitchen $entity): EDisponibilitaGhostKitchen|false { return self::updateAndReturn($entity, static fn (EDisponibilitaGhostKitchen $item): bool => FDisponibilitaGhostKitchen::update($item)); }
    public static function updateMetodoPagamento(EMetodoPagamento $entity): EMetodoPagamento|false { return self::updateAndReturn($entity, static fn (EMetodoPagamento $item): bool => FMetodoPagamento::update($item)); }
    public static function updatePagamento(EPagamento $entity): EPagamento|false { return self::updateAndReturn($entity, static fn (EPagamento $item): bool => FPagamento::update($item)); }
    public static function updateCancellazione(ECancellazione $entity): ECancellazione|false { return self::updateAndReturn($entity, static fn (ECancellazione $item): bool => FCancellazione::update($item)); }
    public static function updateRimborso(ERimborso $entity): ERimborso|false { return self::updateAndReturn($entity, static fn (ERimborso $item): bool => FRimborso::update($item)); }
    public static function updateSegnalazione(ESegnalazione $entity): ESegnalazione|false { return self::updateAndReturn($entity, static fn (ESegnalazione $item): bool => FSegnalazione::update($item)); }
    public static function updatePrenotazioneChef(EPrenotazioneChef $entity): EPrenotazioneChef|false { return self::updateAndReturn($entity, static fn (EPrenotazioneChef $item): bool => FPrenotazioneChef::update($item)); }
    public static function updatePrenotazioneGhostKitchen(EPrenotazioneGhostKitchen $entity): EPrenotazioneGhostKitchen|false { return self::updateAndReturn($entity, static fn (EPrenotazioneGhostKitchen $item): bool => FPrenotazioneGhostKitchen::update($item)); }
    public static function updateRecensioneChef(ERecensioneChef $entity): ERecensioneChef|false { return self::updateAndReturn($entity, static fn (ERecensioneChef $item): bool => FRecensioneChef::update($item)); }
    public static function updateRecensioneGhostKitchen(ERecensioneGhostKitchen $entity): ERecensioneGhostKitchen|false { return self::updateAndReturn($entity, static fn (ERecensioneGhostKitchen $item): bool => FRecensioneGhostKitchen::update($item)); }

    public static function cercaChef(string $localita, string $tipologiaCucina, float $budgetMax, int $valutazioneMin): array { return FChef::search($localita, $tipologiaCucina, $budgetMax, $valutazioneMin); }
    public static function cercaGhostKitchen(string $localita, float $budgetMax, int $valutazioneMin): array { return FGhostKitchen::search($localita, $budgetMax, $valutazioneMin); }
    public static function verificaDisponibilitaChef(int $idChef, string $data, string $oraInizio, string $oraFine): bool { return FDisponibilitaChef::verificaDisponibilita($idChef, $data, $oraInizio, $oraFine); }
    public static function verificaDisponibilitaGhostKitchen(int $idGhostKitchen, string $data, string $oraInizio, string $oraFine): bool { return FDisponibilitaGhostKitchen::verificaDisponibilita($idGhostKitchen, $data, $oraInizio, $oraFine); }
    public static function loadPagamentoByPrenotazione(string $tipoPrenotazione, int $idPrenotazione): ?EPagamento { return FPagamento::loadByPrenotazione($tipoPrenotazione, $idPrenotazione); }
    public static function calcolaImportoPagamento(string $tipoPrenotazione, int $idPrenotazione, string $tipoPagamento): float { return FPagamento::calcolaImporto($tipoPrenotazione, $idPrenotazione, $tipoPagamento); }
    public static function calcolaRimborsoStimato(string $tipoPrenotazione, int $idPrenotazione): array { return FCancellazione::calcolaRimborsoStimato($tipoPrenotazione, $idPrenotazione); }
    public static function verificaPrenotazioneRecensibile(string $tipoTarget, int $idPrenotazione, int $idAutore): array
    {
        return strtolower(trim($tipoTarget)) === 'chef'
            ? FPrenotazioneChef::verificaRecensibile($idPrenotazione, $idAutore)
            : FPrenotazioneGhostKitchen::verificaRecensibile($idPrenotazione, $idAutore);
    }
    public static function aggiornaValutazioneChef(int $idChef): array { return FRecensioneChef::aggiornaValutazioneChef($idChef); }
    public static function aggiornaValutazioneGhostKitchen(int $idGhostKitchen): array { return FRecensioneGhostKitchen::aggiornaValutazioneGhostKitchen($idGhostKitchen); }
    public static function loadTargetSegnalazione(string $tipoTarget, int $idTarget): mixed { return FSegnalazione::loadTarget($tipoTarget, $idTarget); }
    public static function getStatisticheDashboard(array $filtri): array { return FStatisticheDashboard::getStatisticheDashboard($filtri); }
    public static function loadRichiestePrenotazioneChef(int $idChef): array { return FPrenotazioneChef::loadRichieste($idChef); }
    public static function loadPrenotazioniRicevuteChef(int $idChef): array { return FPrenotazioneChef::loadByChef($idChef); }
    public static function loadRichiestePrenotazioneGhostKitchenByGestore(int $idGestore): array { return FPrenotazioneGhostKitchen::loadRichiesteByGestore($idGestore); }
    public static function loadPrenotazioniRicevuteGhostKitchenByGestore(int $idGestore): array { return FPrenotazioneGhostKitchen::loadByGestore($idGestore); }
    public static function loadPrenotazioniChefByRichiedente(int $idUtente): array { return FPrenotazioneChef::loadByRichiedente($idUtente); }
    public static function loadPrenotazioniGhostKitchenByRichiedente(int $idUtente): array { return FPrenotazioneGhostKitchen::loadByRichiedente($idUtente); }
    public static function loadRecensione(int $idRecensione): ?ERecensione { return FRecensione::load($idRecensione); }
    public static function updateRecensione(ERecensione $recensione): ERecensione|false { return self::updateAndReturn($recensione, static fn (ERecensione $entity): bool => FRecensione::update($entity)); }

    private static function storeAndReturn(object $entity, callable $storeCallback, string $idSetter): object|false
    {
        $result = $storeCallback($entity);

        if ($result === false) {
            return false;
        }

        if (is_int($result) && $result > 0 && method_exists($entity, $idSetter)) {
            $entity->{$idSetter}($result);
        }

        return $entity;
    }

    private static function updateAndReturn(object $entity, callable $updateCallback): object|false
    {
        $result = $updateCallback($entity);

        return $result === true ? $entity : false;
    }
}

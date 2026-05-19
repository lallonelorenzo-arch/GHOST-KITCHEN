<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CModerazione
{
    public static function visualizzaContenutiDaModerare(): array
    {
        return [
            'segnalazioni' => FPersistentManager::loadSegnalazioniDaModerare(),
            'azioni' => [
                'prendiInCarico' => '/Moderazione/prendiInCaricoSegnalazione',
                'moderaRecensione' => '/Moderazione/moderaRecensione',
                'moderaProfilo' => '/Moderazione/moderaProfilo',
                'chiudiSegnalazione' => '/Moderazione/chiudiSegnalazione'
            ]
        ];
    }

    public static function prendiInCaricoSegnalazione(int $idSegnalazione): array
    {
        self::validaId($idSegnalazione, 'ID segnalazione non valido.');
        $segnalazione = FPersistentManager::loadSegnalazione($idSegnalazione);

        if ($segnalazione === null) {
            return ['errore' => 'Segnalazione non trovata.'];
        }

        $segnalazione->prendiInCarico();
        $segnalazione = FPersistentManager::updateSegnalazione($segnalazione);

        return [
            'segnalazione' => $segnalazione,
            'messaggio' => 'Segnalazione presa in carico.'
        ];
    }

    public static function moderaRecensione(int $idRecensione, string $azione): array
    {
        self::validaId($idRecensione, 'ID recensione non valido.');
        $azione = strtolower(trim($azione));
        if (!in_array($azione, ['nascondi', 'rimuovi', 'ripristina'], true)) {
            throw new InvalidArgumentException('Azione recensione non valida.');
        }

        $recensione = FPersistentManager::loadRecensione($idRecensione);
        if ($recensione === null) {
            return ['errore' => 'Recensione non trovata.'];
        }

        $recensione->{$azione}();
        $recensione = FPersistentManager::updateRecensione($recensione);

        return [
            'recensione' => $recensione,
            'messaggio' => 'Recensione moderata.'
        ];
    }

    public static function moderaProfilo(int $idUtente, string $azione): array
    {
        self::validaId($idUtente, 'ID utente non valido.');
        $azione = strtolower(trim($azione));
        $mappaStati = [
            'sospendi' => EUtente::STATO_SOSPESO,
            'banna' => EUtente::STATO_BANNATO,
            'riattiva' => EUtente::STATO_ATTIVO
        ];
        if (!isset($mappaStati[$azione])) {
            throw new InvalidArgumentException('Azione profilo non valida.');
        }

        $utente = FPersistentManager::loadUtente($idUtente);
        if ($utente === null) {
            return ['errore' => 'Utente non trovato.'];
        }

        $utente->setStato($mappaStati[$azione]);
        $utente = FPersistentManager::updateUtente($utente);

        return [
            'utente' => $utente,
            'messaggio' => 'Profilo moderato.'
        ];
    }

    public static function chiudiSegnalazione(int $idSegnalazione, string $esito, string $noteAdmin = ''): array
    {
        self::validaId($idSegnalazione, 'ID segnalazione non valido.');
        $esito = strtolower(trim($esito));
        if (!in_array($esito, ['risolta', 'archiviata', 'respinta'], true)) {
            throw new InvalidArgumentException('Esito segnalazione non valido.');
        }

        $segnalazione = FPersistentManager::loadSegnalazione($idSegnalazione);
        if ($segnalazione === null) {
            return ['errore' => 'Segnalazione non trovata.'];
        }

        if ($segnalazione->getStato() === ESegnalazione::STATO_APERTA && $esito === 'risolta') {
            $segnalazione->prendiInCarico();
        }

        if ($esito === 'risolta') {
            $segnalazione->risolvi();
        } elseif ($esito === 'respinta') {
            $segnalazione->respingi();
        } else {
            try {
                $segnalazione->archivia();
            } catch (InvalidArgumentException $e) {
                $segnalazione->setStato(ESegnalazione::STATO_ARCHIVIATA);
            }
        }

        $segnalazione->setNoteAdmin($noteAdmin);
        $segnalazione->setDataGestione(date('Y-m-d'));
        $segnalazione = FPersistentManager::updateSegnalazione($segnalazione);

        return [
            'segnalazione' => $segnalazione,
            'messaggio' => 'Segnalazione chiusa.'
        ];
    }

    private static function validaId(int $id, string $messaggio): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException($messaggio);
        }
    }
}

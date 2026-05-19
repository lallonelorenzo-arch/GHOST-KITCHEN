<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CValidazioneCertificazioni
{
    public static function visualizzaCertificazioniInAttesa(): array
    {
        return [
            'certificazioni' => FPersistentManager::loadCertificazioniInAttesa(),
            'azioni' => [
                'dettaglio' => '/ValidazioneCertificazioni/visualizzaDettaglioCertificazione',
                'approva' => '/ValidazioneCertificazioni/approvaCertificazione',
                'rifiuta' => '/ValidazioneCertificazioni/rifiutaCertificazione'
            ]
        ];
    }

    public static function visualizzaDettaglioCertificazione(int $idCertificazione): array
    {
        self::validaId($idCertificazione);
        $certificazione = FPersistentManager::loadCertificazione($idCertificazione);

        if ($certificazione === null) {
            return ['errore' => 'Certificazione non trovata.'];
        }

        return [
            'certificazione' => $certificazione,
            'chef' => $certificazione->getIdChef() !== null ? FPersistentManager::loadChef((int) $certificazione->getIdChef()) : null
        ];
    }

    public static function approvaCertificazione(int $idCertificazione, string $noteAdmin = ''): array
    {
        return self::aggiornaStatoCertificazione($idCertificazione, ECertificazione::STATO_APPROVATA, $noteAdmin, 'Certificazione approvata.');
    }

    public static function rifiutaCertificazione(int $idCertificazione, string $noteAdmin = ''): array
    {
        return self::aggiornaStatoCertificazione($idCertificazione, ECertificazione::STATO_RIFIUTATA, $noteAdmin, 'Certificazione rifiutata.');
    }

    private static function aggiornaStatoCertificazione(int $idCertificazione, string $stato, string $noteAdmin, string $messaggio): array
    {
        self::validaId($idCertificazione);
        $certificazione = FPersistentManager::loadCertificazione($idCertificazione);

        if ($certificazione === null) {
            return ['errore' => 'Certificazione non trovata.'];
        }

        $certificazione->setStato($stato);
        $certificazione->setNoteAdmin($noteAdmin);
        $certificazione->setDataValidazione(date('Y-m-d'));
        $certificazione = FPersistentManager::updateCertificazione($certificazione);

        return [
            'certificazione' => $certificazione,
            'messaggio' => $messaggio
        ];
    }

    private static function validaId(int $idCertificazione): void
    {
        if ($idCertificazione <= 0) {
            throw new InvalidArgumentException('ID certificazione non valido.');
        }
    }
}

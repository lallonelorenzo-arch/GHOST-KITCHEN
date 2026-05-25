<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CValidazioneCertificazioni
{
    public function visualizzaCertificazioniInAttesa(): array
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

    public function visualizzaDettaglioCertificazione(int $idCertificazione): array
    {
        $this->validaId($idCertificazione);
        $certificazione = FPersistentManager::loadCertificazione($idCertificazione);

        if ($certificazione === null) {
            return ['errore' => 'Certificazione non trovata.'];
        }

        return [
            'certificazione' => $certificazione,
            'chef' => $certificazione->getIdChef() !== null ? FPersistentManager::loadChef((int) $certificazione->getIdChef()) : null
        ];
    }

    public function approvaCertificazione(int $idCertificazione, string $noteAdmin = ''): array
    {
        return $this->aggiornaStatoCertificazione($idCertificazione, ECertificazione::STATO_APPROVATA, $noteAdmin, 'Certificazione approvata.');
    }

    public function rifiutaCertificazione(int $idCertificazione, string $noteAdmin = ''): array
    {
        return $this->aggiornaStatoCertificazione($idCertificazione, ECertificazione::STATO_RIFIUTATA, $noteAdmin, 'Certificazione rifiutata.');
    }

    private function aggiornaStatoCertificazione(int $idCertificazione, string $stato, string $noteAdmin, string $messaggio): array
    {
        $this->validaId($idCertificazione);
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

    private function validaId(int $idCertificazione): void
    {
        if ($idCertificazione <= 0) {
            throw new InvalidArgumentException('ID certificazione non valido.');
        }
    }
}


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
        if ($certificazione === false) {
            return ['errore' => 'Certificazione non aggiornata.'];
        }

        return [
            'certificazione' => $certificazione,
            'messaggio' => $messaggio
        ];
    }

    public function visualizzaCertificazioniInAttesaWeb(array $accesso): array
    {
        if (!$this->isAdmin($accesso)) {
            return [
                'accessoRichiesto' => true,
                'messaggioAccesso' => 'Non hai permessi per questa sezione.',
                'certificazioni' => [],
            ];
        }

        return $this->visualizzaCertificazioniInAttesa() + ['accesso' => $accesso];
    }

    public function visualizzaDettaglioCertificazioneWeb(int $idCertificazione, array $accesso): array
    {
        if (!$this->isAdmin($accesso)) {
            return [
                'accessoRichiesto' => true,
                'messaggioAccesso' => 'Non hai permessi per questa sezione.',
            ];
        }

        return $this->visualizzaDettaglioCertificazione($idCertificazione) + ['accesso' => $accesso];
    }

    public function aggiornaCertificazioneWeb(int $idCertificazione, string $azione, array $accesso, array $post): array
    {
        if (!$this->isAdmin($accesso)) {
            return $this->esito('Accesso non consentito', 'Non hai permessi per questa sezione.', false);
        }

        try {
            $azione = strtolower(trim($azione));
            $result = match ($azione) {
                'approva' => $this->approvaCertificazione($idCertificazione, (string) ($post['noteAdmin'] ?? '')),
                'rifiuta' => $this->rifiutaCertificazione($idCertificazione, (string) ($post['noteAdmin'] ?? '')),
                default => ['errore' => 'Azione certificazione non valida.'],
            };

            if (isset($result['errore'])) {
                return $this->esito('Certificazione non aggiornata', (string) $result['errore'], false);
            }

            return $this->esito('Certificazione aggiornata', (string) ($result['messaggio'] ?? 'Operazione completata.'), true);
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Certificazione non aggiornata', $exception->getMessage(), false);
        } catch (Throwable $exception) {
            error_log('[CValidazioneCertificazioni] ' . $exception->getMessage());
            return $this->esito('Certificazione non aggiornata', 'Errore interno durante la validazione. Riprova piu tardi.', false);
        }
    }

    private function esito(string $titolo, string $messaggio, bool $successo): array
    {
        return [
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'successo' => $successo,
            'ritorno' => '/certificazioni',
        ];
    }

    private function isAdmin(array $accesso): bool
    {
        $ruoli = $accesso['ruoli'] ?? [];
        return ($accesso['isLogged'] ?? false) === true && (in_array('admin', $ruoli, true) || in_array('amministratore', $ruoli, true));
    }

    private function validaId(int $idCertificazione): void
    {
        if ($idCertificazione <= 0) {
            throw new InvalidArgumentException('ID certificazione non valido.');
        }
    }
}


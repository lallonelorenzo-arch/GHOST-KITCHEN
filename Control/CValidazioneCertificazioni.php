<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CValidazioneCertificazioni
{
    public function visualizzaCertificazioni(): array
    {
        $certificazioni = FPersistentManager::loadTutteCertificazioni();
        $certificazioniInScadenza = FPersistentManager::loadCertificazioniInScadenza(90);

        return [
            'certificazioni' => $certificazioni,
            'certificazioniInScadenza' => $this->preparaCertificazioniInScadenza($certificazioniInScadenza),
            'ownerCertificazioni' => $this->mappaOwner($certificazioni),
            'riepilogoCertificazioni' => $this->riepilogo($certificazioni),
            'azioni' => [
                'dettaglio' => '/ValidazioneCertificazioni/visualizzaDettaglioCertificazione',
                'approva' => '/ValidazioneCertificazioni/approvaCertificazione',
                'rifiuta' => '/ValidazioneCertificazioni/rifiutaCertificazione',
                'rimettiInAttesa' => '/ValidazioneCertificazioni/rimettiInAttesaCertificazione',
            ]
        ];
    }

    public function visualizzaCertificazioniInAttesa(): array
    {
        return $this->visualizzaCertificazioni();
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
            'owner' => $this->loadOwner($certificazione),
            'ownerLabel' => $this->ownerLabel($certificazione),
            'scadenza' => $certificazione->getDataScadenza() !== '' ? $certificazione->getDataScadenza() : null,
        ];
    }

    public function approvaCertificazione(int $idCertificazione, string $noteAdmin = '', string $dataScadenza = ''): array
    {
        return $this->aggiornaStatoCertificazione($idCertificazione, ECertificazione::STATO_APPROVATA, $noteAdmin, 'Certificazione approvata.', $dataScadenza);
    }

    public function rifiutaCertificazione(int $idCertificazione, string $noteAdmin = ''): array
    {
        return $this->aggiornaStatoCertificazione($idCertificazione, ECertificazione::STATO_RIFIUTATA, $noteAdmin, 'Certificazione rifiutata.');
    }

    public function rimettiInAttesaCertificazione(int $idCertificazione, string $noteAdmin = ''): array
    {
        return $this->aggiornaStatoCertificazione($idCertificazione, ECertificazione::STATO_IN_ATTESA, $noteAdmin, 'Certificazione rimessa in attesa.');
    }

    private function aggiornaStatoCertificazione(int $idCertificazione, string $stato, string $noteAdmin, string $messaggio, string $dataScadenza = ''): array
    {
        $this->validaId($idCertificazione);
        $certificazione = FPersistentManager::loadCertificazione($idCertificazione);

        if ($certificazione === null) {
            return ['errore' => 'Certificazione non trovata.'];
        }

        if ($stato === ECertificazione::STATO_APPROVATA) {
            $dataScadenza = trim($dataScadenza);
            if ($dataScadenza === '') {
                return ['errore' => 'Per approvare una certificazione devi indicare la data di scadenza.'];
            }
            $timestampScadenza = strtotime($dataScadenza);
            if ($timestampScadenza === false || date('Y-m-d', $timestampScadenza) < date('Y-m-d')) {
                return ['errore' => 'La data di scadenza deve essere valida e non precedente a oggi.'];
            }
            $certificazione->setDataScadenza(date('Y-m-d', $timestampScadenza));
        } else {
            $certificazione->setDataScadenza('');
        }

        $certificazione->setStato($stato);
        $certificazione->setNoteAdmin($noteAdmin);
        $certificazione->setDataValidazione($stato === ECertificazione::STATO_IN_ATTESA ? '' : date('Y-m-d'));
        $certificazione = FPersistentManager::updateCertificazione($certificazione);
        if ($certificazione === false) {
            return ['errore' => 'Certificazione non aggiornata.'];
        }

        $this->sincronizzaStatoOwner($certificazione, $stato);

        return [
            'certificazione' => $certificazione,
            'messaggio' => $messaggio
        ];
    }

    private function sincronizzaStatoOwner(ECertificazione $certificazione, string $stato): void
    {
        if ($certificazione->getTipoOwner() !== ECertificazione::OWNER_CHEF || $certificazione->getIdOwner() === null) {
            return;
        }

        $chef = FPersistentManager::loadChef((int) $certificazione->getIdOwner());
        if ($chef === null) {
            return;
        }

        if ($stato === ECertificazione::STATO_APPROVATA) {
            $chef->approvaVerifica();
            FPersistentManager::updateChef($chef);
            return;
        }

        if (FPersistentManager::chefHaCertificazioniInRegola((int) $certificazione->getIdOwner())) {
            return;
        }

        if ($stato === ECertificazione::STATO_RIFIUTATA) {
            $chef->rifiutaVerifica();
        } else {
            $chef->richiediVerifica();
        }

        FPersistentManager::updateChef($chef);
    }

    public function visualizzaCertificazioniInAttesaWeb(array $accesso): array
    {
        if (!$this->isAdmin($accesso)) {
            return [
                'accessoRichiesto' => true,
                'messaggioAccesso' => 'Non hai permessi per questa sezione.',
                'certificazioni' => [],
                'certificazioniInScadenza' => [],
                'ownerCertificazioni' => [],
                'riepilogoCertificazioni' => [],
            ];
        }

        return $this->visualizzaCertificazioni() + ['accesso' => $accesso];
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
            $noteAdmin = (string) ($post['noteAdmin'] ?? '');
            $result = match ($azione) {
                'approva' => $this->approvaCertificazione($idCertificazione, $noteAdmin, (string) ($post['dataScadenza'] ?? '')),
                'rifiuta' => $this->rifiutaCertificazione($idCertificazione, $noteAdmin),
                'in-attesa' => $this->rimettiInAttesaCertificazione($idCertificazione, $noteAdmin),
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

    private function mappaOwner(array $certificazioni): array
    {
        $owners = [];
        foreach ($certificazioni as $certificazione) {
            if (!$certificazione instanceof ECertificazione || $certificazione->getIdOwner() === null) {
                continue;
            }

            $key = $this->ownerKey($certificazione);
            if (!array_key_exists($key, $owners)) {
                $owners[$key] = $this->loadOwner($certificazione);
            }
        }

        return $owners;
    }

    private function loadOwner(ECertificazione $certificazione): mixed
    {
        if ($certificazione->getIdOwner() === null) {
            return null;
        }

        return $certificazione->getTipoOwner() === ECertificazione::OWNER_GHOST_KITCHEN
            ? FPersistentManager::loadGhostKitchen((int) $certificazione->getIdOwner())
            : FPersistentManager::loadChef((int) $certificazione->getIdOwner());
    }

    private function ownerKey(ECertificazione $certificazione): string
    {
        return $certificazione->getTipoOwner() . ':' . (string) $certificazione->getIdOwner();
    }

    private function ownerLabel(ECertificazione $certificazione): string
    {
        $owner = $this->loadOwner($certificazione);
        if ($owner === null) {
            return $certificazione->getTipoOwner() . ' #' . (string) $certificazione->getIdOwner();
        }

        if ($owner instanceof EGhostKitchen) {
            return 'Ghost kitchen: ' . $owner->getNome();
        }

        if (method_exists($owner, 'getNome') && method_exists($owner, 'getCognome')) {
            return 'Chef: ' . trim((string) $owner->getNome() . ' ' . (string) $owner->getCognome());
        }

        return $certificazione->getTipoOwner() . ' #' . (string) $certificazione->getIdOwner();
    }

    private function riepilogo(array $certificazioni): array
    {
        $riepilogo = [
            'totale' => 0,
            ECertificazione::STATO_IN_ATTESA => 0,
            ECertificazione::STATO_APPROVATA => 0,
            ECertificazione::STATO_RIFIUTATA => 0,
        ];

        foreach ($certificazioni as $certificazione) {
            if (!$certificazione instanceof ECertificazione) {
                continue;
            }
            $riepilogo['totale']++;
            $riepilogo[$certificazione->getStato()] = ($riepilogo[$certificazione->getStato()] ?? 0) + 1;
        }

        return $riepilogo;
    }

    private function preparaCertificazioniInScadenza(array $certificazioni): array
    {
        $items = [];
        $owners = $this->mappaOwner($certificazioni);

        foreach ($certificazioni as $certificazione) {
            if (!$certificazione instanceof ECertificazione) {
                continue;
            }

            $items[] = [
                'certificazione' => $certificazione,
                'owner' => $owners[$this->ownerKey($certificazione)] ?? null,
                'ownerLabel' => $this->ownerLabel($certificazione),
                'scadenza' => $certificazione->getDataScadenza() !== '' ? $certificazione->getDataScadenza() : null,
            ];
        }

        return $items;
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

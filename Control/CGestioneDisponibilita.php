<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CGestioneDisponibilita
{
    public function visualizzaCalendario(string $tipoOwner, int $idOwner): array
    {
        if ($idOwner <= 0) {
            throw new InvalidArgumentException('ID owner non valido.');
        }

        $tipoOwner = $this->normalizzaTipoOwner($tipoOwner);

        if ($tipoOwner === 'chef') {
            return ['tipoOwner' => $tipoOwner, 'disponibilita' => FPersistentManager::loadDisponibilitaChef($idOwner)];
        }

        return ['tipoOwner' => $tipoOwner, 'disponibilita' => FPersistentManager::loadDisponibilitaGhostKitchen($idOwner)];
    }

    public function aggiungiDisponibilita(string $tipoOwner, int $idOwner, string $data, string $oraInizio, string $oraFine): array
    {
        if ($idOwner <= 0 || trim($data) === '' || trim($oraInizio) === '' || trim($oraFine) === '') {
            throw new InvalidArgumentException('Dati disponibilita non validi.');
        }

        $tipoOwner = $this->normalizzaTipoOwner($tipoOwner);

        if ($tipoOwner === 'chef') {
            $disponibilita = new EDisponibilitaChef(null, $idOwner, trim($data), trim($oraInizio), trim($oraFine), EDisponibilitaChef::STATO_LIBERA);
            $saved = FPersistentManager::storeDisponibilitaChef($disponibilita);
        } else {
            $disponibilita = new EDisponibilitaGhostKitchen(null, $idOwner, trim($data), trim($oraInizio), trim($oraFine), EDisponibilitaGhostKitchen::STATO_LIBERA);
            $saved = FPersistentManager::storeDisponibilitaGhostKitchen($disponibilita);
        }

        return [
            'messaggio' => 'Disponibilita creata',
            'disponibilita' => $saved,
            'calendario' => $this->visualizzaCalendario($tipoOwner, $idOwner)
        ];
    }

    public function bloccaDisponibilita(string $tipoOwner, int $idDisponibilita): array
    {
        if ($idDisponibilita <= 0) {
            throw new InvalidArgumentException('ID disponibilita non valido.');
        }

        $tipoOwner = $this->normalizzaTipoOwner($tipoOwner);

        if ($tipoOwner === 'chef') {
            $disponibilita = FPersistentManager::loadDisponibilitaChefById($idDisponibilita);
            if ($disponibilita === null) {
                return ['errore' => 'Disponibilita chef non trovata'];
            }
            if ($disponibilita->getStato() === EDisponibilitaChef::STATO_OCCUPATA) {
                return ['errore' => 'Disponibilita occupata, non bloccabile'];
            }
            $disponibilita->blocca();
            FPersistentManager::updateDisponibilitaChef($disponibilita);
        } else {
            $disponibilita = FPersistentManager::loadDisponibilitaGhostKitchenById($idDisponibilita);
            if ($disponibilita === null) {
                return ['errore' => 'Disponibilita ghost kitchen non trovata'];
            }
            if ($disponibilita->getStato() === EDisponibilitaGhostKitchen::STATO_OCCUPATA) {
                return ['errore' => 'Disponibilita occupata, non bloccabile'];
            }
            $disponibilita->blocca();
            FPersistentManager::updateDisponibilitaGhostKitchen($disponibilita);
        }

        return ['messaggio' => 'Disponibilita bloccata', 'disponibilita' => $disponibilita];
    }

    public function liberaDisponibilita(string $tipoOwner, int $idDisponibilita): array
    {
        if ($idDisponibilita <= 0) {
            throw new InvalidArgumentException('ID disponibilita non valido.');
        }

        $tipoOwner = $this->normalizzaTipoOwner($tipoOwner);

        if ($tipoOwner === 'chef') {
            $disponibilita = FPersistentManager::loadDisponibilitaChefById($idDisponibilita);
            if ($disponibilita === null) {
                return ['errore' => 'Disponibilita chef non trovata'];
            }
            $disponibilita->libera();
            FPersistentManager::updateDisponibilitaChef($disponibilita);
        } else {
            $disponibilita = FPersistentManager::loadDisponibilitaGhostKitchenById($idDisponibilita);
            if ($disponibilita === null) {
                return ['errore' => 'Disponibilita ghost kitchen non trovata'];
            }
            $disponibilita->libera();
            FPersistentManager::updateDisponibilitaGhostKitchen($disponibilita);
        }

        return ['messaggio' => 'Disponibilita liberata', 'disponibilita' => $disponibilita];
    }


    public function mostraDisponibilitaWeb(array $accesso, array $query = []): array
    {
        $data = [
            'accesso' => $accesso,
            'calendarioChef' => null,
            'calendarioGhostKitchen' => null,
            'idGhostKitchen' => (int) ($query['idGhostKitchen'] ?? 0),
            'ghostKitchenGestore' => [],
        ];

        if (($accesso['isLogged'] ?? false) !== true) {
            $data['messaggioAccesso'] = 'Accedi come chef o gestore per gestire le disponibilita.';
            return $data;
        }

        $ruoli = $accesso['ruoli'] ?? [];
        if (in_array('chef', $ruoli, true)) {
            $data['calendarioChef'] = $this->visualizzaCalendario('chef', (int) $accesso['idUtente']);
        }

        if (in_array('gestore', $ruoli, true)) {
            $data['ghostKitchenGestore'] = FPersistentManager::loadGhostKitchenByGestore((int) $accesso['idUtente']);
            if ($data['idGhostKitchen'] <= 0 && count($data['ghostKitchenGestore']) === 1) {
                $data['idGhostKitchen'] = (int) $data['ghostKitchenGestore'][0]->getId();
            }

            if ($data['idGhostKitchen'] > 0) {
                $data['calendarioGhostKitchen'] = $this->visualizzaCalendario('ghost_kitchen', $data['idGhostKitchen']);
            } elseif ($data['ghostKitchenGestore'] === []) {
                $data['messaggioGestore'] = 'Nessuna ghost kitchen risulta collegata al tuo profilo gestore.';
            } else {
                $data['messaggioGestore'] = 'Seleziona una ghost kitchen da gestire.';
            }
        }

        return $data;
    }

    public function aggiungiDisponibilitaChefWeb(array $accesso, array $post): array
    {
        if (($accesso['isLogged'] ?? false) !== true || !in_array('chef', $accesso['ruoli'] ?? [], true)) {
            return $this->esito('Accesso richiesto', 'Serve un utente con ruolo chef per aggiungere disponibilita.', false, '/disponibilita');
        }

        try {
            $result = $this->aggiungiDisponibilita(
                'chef',
                (int) $accesso['idUtente'],
                (string) ($post['data'] ?? ''),
                (string) ($post['oraInizio'] ?? ''),
                (string) ($post['oraFine'] ?? '')
            );

            return $this->esito('Disponibilita chef', (string) ($result['messaggio'] ?? 'Disponibilita aggiornata.'), true, '/disponibilita');
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Errore disponibilita chef', $exception->getMessage(), false, '/disponibilita');
        } catch (Throwable $exception) {
            error_log('[CGestioneDisponibilita] ' . $exception->getMessage());
            return $this->esito('Errore disponibilita chef', 'Non e stato possibile aggiornare la disponibilita. Riprova piu tardi.', false, '/disponibilita');
        }
    }

    public function aggiungiDisponibilitaGhostKitchenWeb(array $accesso, array $post): array
    {
        if (($accesso['isLogged'] ?? false) !== true || !in_array('gestore', $accesso['ruoli'] ?? [], true)) {
            return $this->esito('Accesso richiesto', 'Serve un utente con ruolo gestore per aggiungere disponibilita.', false, '/disponibilita');
        }

        try {
            $idGhostKitchen = (int) ($post['idGhostKitchen'] ?? 0);
            if (!$this->gestorePossiedeGhostKitchen((int) $accesso['idUtente'], $idGhostKitchen)) {
                return $this->esito('Accesso non consentito', 'Puoi aggiungere disponibilita solo alle ghost kitchen collegate al tuo profilo.', false, '/disponibilita');
            }

            $result = $this->aggiungiDisponibilita(
                'ghost_kitchen',
                $idGhostKitchen,
                (string) ($post['data'] ?? ''),
                (string) ($post['oraInizio'] ?? ''),
                (string) ($post['oraFine'] ?? '')
            );

            return $this->esito('Disponibilita ghost kitchen', (string) ($result['messaggio'] ?? 'Disponibilita aggiornata.'), true, '/disponibilita?idGhostKitchen=' . $idGhostKitchen);
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Errore disponibilita ghost kitchen', $exception->getMessage(), false, '/disponibilita');
        } catch (Throwable $exception) {
            error_log('[CGestioneDisponibilita] ' . $exception->getMessage());
            return $this->esito('Errore disponibilita ghost kitchen', 'Non e stato possibile aggiornare la disponibilita. Riprova piu tardi.', false, '/disponibilita');
        }
    }

    private function esito(string $titolo, string $messaggio, bool $successo, string $ritorno): array
    {
        return [
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'successo' => $successo,
            'ritorno' => $ritorno,
        ];
    }

    private function gestorePossiedeGhostKitchen(int $idGestore, int $idGhostKitchen): bool
    {
        foreach (FPersistentManager::loadGhostKitchenByGestore($idGestore) as $ghostKitchen) {
            if ((int) $ghostKitchen->getId() === $idGhostKitchen) {
                return true;
            }
        }

        return false;
    }

    private function normalizzaTipoOwner(string $tipoOwner): string
    {
        $tipoOwner = strtolower(trim($tipoOwner));
        if (!in_array($tipoOwner, ['chef', 'ghost_kitchen'], true)) {
            throw new InvalidArgumentException('tipoOwner non valido.');
        }

        return $tipoOwner;
    }
}


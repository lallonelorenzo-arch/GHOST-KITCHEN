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
        ];

        if (($accesso['isLogged'] ?? false) !== true) {
            $data['messaggioAccesso'] = 'Accedi come chef o gestore per gestire le disponibilita.';
            return $data;
        }

        $ruoli = $accesso['ruoli'] ?? [];
        if (in_array('chef', $ruoli, true)) {
            $data['calendarioChef'] = $this->visualizzaCalendario('chef', (int) $accesso['idUtente']);
        }

        if (in_array('gestore', $ruoli, true) && $data['idGhostKitchen'] > 0) {
            $data['calendarioGhostKitchen'] = $this->visualizzaCalendario('ghost_kitchen', $data['idGhostKitchen']);
        } elseif (in_array('gestore', $ruoli, true)) {
            $data['messaggioGestore'] = 'Inserisci l ID della ghost kitchen da gestire. Il collegamento automatico gestore -> ghost kitchen resta da completare.';
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
        } catch (Throwable $exception) {
            return $this->esito('Errore disponibilita chef', $exception->getMessage(), false, '/disponibilita');
        }
    }

    public function aggiungiDisponibilitaGhostKitchenWeb(array $accesso, array $post): array
    {
        if (($accesso['isLogged'] ?? false) !== true || !in_array('gestore', $accesso['ruoli'] ?? [], true)) {
            return $this->esito('Accesso richiesto', 'Serve un utente con ruolo gestore per aggiungere disponibilita.', false, '/disponibilita');
        }

        try {
            $idGhostKitchen = (int) ($post['idGhostKitchen'] ?? 0);
            $result = $this->aggiungiDisponibilita(
                'ghost_kitchen',
                $idGhostKitchen,
                (string) ($post['data'] ?? ''),
                (string) ($post['oraInizio'] ?? ''),
                (string) ($post['oraFine'] ?? '')
            );

            return $this->esito('Disponibilita ghost kitchen', (string) ($result['messaggio'] ?? 'Disponibilita aggiornata.'), true, '/disponibilita?idGhostKitchen=' . $idGhostKitchen);
        } catch (Throwable $exception) {
            return $this->esito('Errore disponibilita ghost kitchen', $exception->getMessage(), false, '/disponibilita');
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

    private function normalizzaTipoOwner(string $tipoOwner): string
    {
        $tipoOwner = strtolower(trim($tipoOwner));
        if (!in_array($tipoOwner, ['chef', 'ghost_kitchen'], true)) {
            throw new InvalidArgumentException('tipoOwner non valido.');
        }

        return $tipoOwner;
    }
}


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
            throw new InvalidArgumentException('Dati disponibilità non validi.');
        }

        $tipoOwner = $this->normalizzaTipoOwner($tipoOwner);
        $this->validaSlotTemporale($data, $oraInizio, $oraFine);

        if ($tipoOwner === 'chef') {
            if (FPersistentManager::loadDisponibilitaChefBySlot($idOwner, trim($data), trim($oraInizio), trim($oraFine)) !== null) {
                return ['errore' => 'Esiste gia una disponibilità chef per questo slot.'];
            }
            $disponibilita = new EDisponibilitaChef(null, $idOwner, trim($data), trim($oraInizio), trim($oraFine), EDisponibilitaChef::STATO_LIBERA);
            $saved = FPersistentManager::storeDisponibilitaChef($disponibilita);
        } else {
            if (FPersistentManager::loadDisponibilitaGhostKitchenBySlot($idOwner, trim($data), trim($oraInizio), trim($oraFine)) !== null) {
                return ['errore' => 'Esiste gia una disponibilità ghost kitchen per questo slot.'];
            }
            $disponibilita = new EDisponibilitaGhostKitchen(null, $idOwner, trim($data), trim($oraInizio), trim($oraFine), EDisponibilitaGhostKitchen::STATO_LIBERA);
            $saved = FPersistentManager::storeDisponibilitaGhostKitchen($disponibilita);
        }

        return [
            'messaggio' => 'Disponibilità creata',
            'disponibilita' => $saved,
            'calendario' => $this->visualizzaCalendario($tipoOwner, $idOwner)
        ];
    }

    public function bloccaDisponibilita(string $tipoOwner, int $idDisponibilita): array
    {
        if ($idDisponibilita <= 0) {
            throw new InvalidArgumentException('ID disponibilità non valido.');
        }

        $tipoOwner = $this->normalizzaTipoOwner($tipoOwner);

        if ($tipoOwner === 'chef') {
            $disponibilita = FPersistentManager::loadDisponibilitaChefById($idDisponibilita);
            if ($disponibilita === null) {
                return ['errore' => 'Disponibilità chef non trovata'];
            }
            if ($disponibilita->getStato() === EDisponibilitaChef::STATO_OCCUPATA) {
                return ['errore' => 'Disponibilità occupata, non bloccabile'];
            }
            $disponibilita->blocca();
            FPersistentManager::updateDisponibilitaChef($disponibilita);
        } else {
            $disponibilita = FPersistentManager::loadDisponibilitaGhostKitchenById($idDisponibilita);
            if ($disponibilita === null) {
                return ['errore' => 'Disponibilità ghost kitchen non trovata'];
            }
            if ($disponibilita->getStato() === EDisponibilitaGhostKitchen::STATO_OCCUPATA) {
                return ['errore' => 'Disponibilità occupata, non bloccabile'];
            }
            $disponibilita->blocca();
            FPersistentManager::updateDisponibilitaGhostKitchen($disponibilita);
        }

        return ['messaggio' => 'Disponibilità bloccata', 'disponibilita' => $disponibilita];
    }

    public function liberaDisponibilita(string $tipoOwner, int $idDisponibilita): array
    {
        if ($idDisponibilita <= 0) {
            throw new InvalidArgumentException('ID disponibilità non valido.');
        }

        $tipoOwner = $this->normalizzaTipoOwner($tipoOwner);

        if ($tipoOwner === 'chef') {
            $disponibilita = FPersistentManager::loadDisponibilitaChefById($idDisponibilita);
            if ($disponibilita === null) {
                return ['errore' => 'Disponibilità chef non trovata'];
            }
            $disponibilita->libera();
            FPersistentManager::updateDisponibilitaChef($disponibilita);
        } else {
            $disponibilita = FPersistentManager::loadDisponibilitaGhostKitchenById($idDisponibilita);
            if ($disponibilita === null) {
                return ['errore' => 'Disponibilità ghost kitchen non trovata'];
            }
            $disponibilita->libera();
            FPersistentManager::updateDisponibilitaGhostKitchen($disponibilita);
        }

        return ['messaggio' => 'Disponibilità liberata', 'disponibilita' => $disponibilita];
    }


    public function aggiungiDisponibilitaChefWeb(array $accesso, array $post): array
    {
        if (($accesso['isLogged'] ?? false) !== true || !in_array('chef', $accesso['ruoli'] ?? [], true)) {
            return $this->esito('Accesso richiesto', 'Serve un utente con ruolo chef per aggiungere disponibilità.', false, '/dashboard?ruolo=chef&tab=disponibilita');
        }

        try {
            $fasce = is_array($post['fasce'] ?? null) ? $post['fasce'] : [];
            if ($fasce === []) {
                $fasciaSingola = trim((string) ($post['fascia'] ?? ''));
                if ($fasciaSingola !== '') {
                    $fasce = [$fasciaSingola];
                }
            }

            if ($fasce === []) {
                $result = $this->aggiungiDisponibilita(
                    'chef',
                    (int) $accesso['idUtente'],
                    (string) ($post['data'] ?? ''),
                    (string) ($post['oraInizio'] ?? ''),
                    (string) ($post['oraFine'] ?? '')
                );
                if (isset($result['errore'])) {
                    return $this->esito('Errore disponibilità chef', (string) $result['errore'], false, '/dashboard?ruolo=chef&tab=disponibilita');
                }
                return $this->esito('Disponibilità chef', 'Disponibilità aggiornata.', true, '/dashboard?ruolo=chef&tab=disponibilita');
            }

            $create = 0;
            $errors = [];
            foreach (array_unique($fasce) as $fascia) {
                [$oraInizio, $oraFine] = EDisponibilitaChef::orariPerFascia((string) $fascia);
                $result = $this->aggiungiDisponibilita(
                    'chef',
                    (int) $accesso['idUtente'],
                    (string) ($post['data'] ?? ''),
                    $oraInizio,
                    $oraFine
                );
                if (isset($result['errore'])) {
                    $errors[] = ucfirst((string) $fascia) . ': ' . $result['errore'];
                } else {
                    $create++;
                }
            }

            if ($create === 0) {
                return $this->esito('Disponibilità chef', implode(' ', $errors), false, '/dashboard?ruolo=chef&tab=disponibilita');
            }

            $message = $create === 1 ? 'Fascia pubblicata.' : 'Fasce pranzo e cena pubblicate.';
            if ($errors !== []) {
                $message .= ' ' . implode(' ', $errors);
            }
            return $this->esito('Disponibilità chef', $message, true, '/dashboard?ruolo=chef&tab=disponibilita');
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Errore disponibilità chef', $exception->getMessage(), false, '/dashboard?ruolo=chef&tab=disponibilita');
        } catch (Throwable $exception) {
            error_log('[CGestioneDisponibilita] ' . $exception->getMessage());
            return $this->esito('Errore disponibilità chef', 'Non e stato possibile aggiornare la disponibilità. Riprova piu tardi.', false, '/dashboard?ruolo=chef&tab=disponibilita');
        }
    }

    public function aggiungiDisponibilitaGhostKitchenWeb(array $accesso, array $post): array
    {
        if (($accesso['isLogged'] ?? false) !== true || !in_array('gestore', $accesso['ruoli'] ?? [], true)) {
            return $this->esito('Accesso richiesto', 'Serve un utente con ruolo gestore per aggiungere disponibilità.', false, '/dashboard?ruolo=gestore&tab=disponibilita');
        }

        try {
            $idGhostKitchen = (int) ($post['idGhostKitchen'] ?? 0);
            if (!$this->gestorePossiedeGhostKitchen((int) $accesso['idUtente'], $idGhostKitchen)) {
                return $this->esito('Accesso non consentito', 'Puoi aggiungere disponibilità solo alle ghost kitchen collegate al tuo profilo.', false, '/dashboard?ruolo=gestore&tab=disponibilita');
            }

            $result = $this->aggiungiDisponibilita(
                'ghost_kitchen',
                $idGhostKitchen,
                (string) ($post['data'] ?? ''),
                (string) ($post['oraInizio'] ?? ''),
                (string) ($post['oraFine'] ?? '')
            );

            if (isset($result['errore'])) {
                return $this->esito('Errore disponibilità ghost kitchen', (string) $result['errore'], false, '/dashboard?ruolo=gestore&tab=disponibilita');
            }

            return $this->esito('Disponibilità ghost kitchen', (string) ($result['messaggio'] ?? 'Disponibilità aggiornata.'), true, '/dashboard?ruolo=gestore&tab=disponibilita');
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Errore disponibilità ghost kitchen', $exception->getMessage(), false, '/dashboard?ruolo=gestore&tab=disponibilita');
        } catch (Throwable $exception) {
            error_log('[CGestioneDisponibilita] ' . $exception->getMessage());
            return $this->esito('Errore disponibilità ghost kitchen', 'Non e stato possibile aggiornare la disponibilità. Riprova piu tardi.', false, '/dashboard?ruolo=gestore&tab=disponibilita');
        }
    }

    public function aggiornaStatoDisponibilitaWeb(string $tipoOwner, int $idDisponibilita, string $azione, array $accesso): array
    {
        $tipoOwner = $this->normalizzaTipoOwner($tipoOwner);
        $azione = strtolower(trim($azione));
        $isChef = $tipoOwner === 'chef';
        $ritorno = $isChef
            ? '/dashboard?ruolo=chef&tab=disponibilita'
            : '/dashboard?ruolo=gestore&tab=disponibilita';

        if (($accesso['isLogged'] ?? false) !== true) {
            return $this->esito('Accesso richiesto', 'Accedi per gestire la disponibilità.', false, $ritorno);
        }

        $slot = $isChef
            ? FPersistentManager::loadDisponibilitaChefById($idDisponibilita)
            : FPersistentManager::loadDisponibilitaGhostKitchenById($idDisponibilita);
        if ($slot === null) {
            return $this->esito('Disponibilità', 'Slot non trovato.', false, $ritorno);
        }

        if ($isChef) {
            if (!in_array('chef', $accesso['ruoli'] ?? [], true) || (int) $slot->getIdChef() !== (int) ($accesso['idUtente'] ?? 0)) {
                return $this->esito('Accesso non consentito', 'Lo slot non appartiene al tuo profilo chef.', false, $ritorno);
            }
        } elseif (!in_array('gestore', $accesso['ruoli'] ?? [], true)
            || !$this->gestorePossiedeGhostKitchen((int) ($accesso['idUtente'] ?? 0), (int) $slot->getIdGhostKitchen())
        ) {
            return $this->esito('Accesso non consentito', 'Lo slot non appartiene a una tua Ghost Kitchen.', false, $ritorno);
        }

        if ($slot->getStato() === 'occupata') {
            return $this->esito('Disponibilità', 'Uno slot occupato non puo essere modificato manualmente.', false, $ritorno);
        }

        try {
            $result = match ($azione) {
                'blocca' => $this->bloccaDisponibilita($tipoOwner, $idDisponibilita),
                'libera' => $this->liberaDisponibilita($tipoOwner, $idDisponibilita),
                default => ['errore' => 'Azione non valida.'],
            };

            return isset($result['errore'])
                ? $this->esito('Disponibilità', (string) $result['errore'], false, $ritorno)
                : $this->esito('Disponibilità', (string) ($result['messaggio'] ?? 'Slot aggiornato.'), true, $ritorno);
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Disponibilità', $exception->getMessage(), false, $ritorno);
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

    private function validaSlotTemporale(string $data, string $oraInizio, string $oraFine): void
    {
        $data = trim($data);
        $oraInizio = trim($oraInizio);
        $oraFine = trim($oraFine);

        $giorno = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
        if ($giorno === false || $giorno->format('Y-m-d') !== $data) {
            throw new InvalidArgumentException('Inserisci una data valida.');
        }

        if ($giorno < new DateTimeImmutable('today')) {
            throw new InvalidArgumentException('Non puoi pubblicare disponibilità nel passato.');
        }

        $inizio = DateTimeImmutable::createFromFormat('H:i', $oraInizio) ?: DateTimeImmutable::createFromFormat('H:i:s', $oraInizio);
        $fine = DateTimeImmutable::createFromFormat('H:i', $oraFine) ?: DateTimeImmutable::createFromFormat('H:i:s', $oraFine);
        if ($inizio === false || $fine === false || $fine <= $inizio) {
            throw new InvalidArgumentException('Ora fine deve essere successiva all ora inizio.');
        }
    }
}

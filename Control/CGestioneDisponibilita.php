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

    private function normalizzaTipoOwner(string $tipoOwner): string
    {
        $tipoOwner = strtolower(trim($tipoOwner));
        if (!in_array($tipoOwner, ['chef', 'ghost_kitchen'], true)) {
            throw new InvalidArgumentException('tipoOwner non valido.');
        }

        return $tipoOwner;
    }
}


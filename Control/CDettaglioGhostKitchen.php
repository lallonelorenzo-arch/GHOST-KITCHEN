<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CDettaglioGhostKitchen
{
    public function visualizzaDettaglioGhostKitchen(int $idGhostKitchen): array
    {
        if ($idGhostKitchen <= 0) {
            throw new InvalidArgumentException('ID ghost kitchen non valido.');
        }

        $ghostKitchen = FPersistentManager::loadGhostKitchen($idGhostKitchen);
        if ($ghostKitchen === null) {
            return ['errore' => 'Ghost kitchen non trovata'];
        }

        return [
            'ghostKitchen' => $ghostKitchen,
            'gestore' => $ghostKitchen->getIdGestore() !== null ? FPersistentManager::loadGestore((int) $ghostKitchen->getIdGestore()) : null,
            'attrezzature' => FPersistentManager::loadAttrezzatureByGhostKitchen($idGhostKitchen),
            'disponibilitaPubbliche' => FPersistentManager::loadDisponibilitaGhostKitchen($idGhostKitchen),
            'mediaPrincipale' => FPersistentManager::getMediaPrincipale('ghost_kitchen', $idGhostKitchen),
            'media' => FPersistentManager::getMediaByOwner('ghost_kitchen', $idGhostKitchen),
            'azioni' => [
                'prenotaGhostKitchen' => '/PrenotazioneGhostKitchen/avviaPrenotazioneGhostKitchen'
            ]
        ];
    }
}

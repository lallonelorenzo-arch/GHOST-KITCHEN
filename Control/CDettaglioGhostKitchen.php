<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';
require_once __DIR__ . '/../Foundation/FSession.php';

class CDettaglioGhostKitchen
{
    public function visualizzaDettaglioGhostKitchen(int $idGhostKitchen, array $accesso = []): array
    {
        if ($idGhostKitchen <= 0) {
            throw new InvalidArgumentException('ID ghost kitchen non valido.');
        }

        $ghostKitchen = FPersistentManager::loadGhostKitchen($idGhostKitchen);
        if ($ghostKitchen === null) {
            return ['errore' => 'Ghost kitchen non trovata'];
        }

        $gestore = $ghostKitchen->getIdGestore() !== null ? FPersistentManager::loadGestore((int) $ghostKitchen->getIdGestore()) : null;
        $recensioni = FPersistentManager::loadRecensioniByGhostKitchen($idGhostKitchen);
        $autoriRecensioni = [];
        foreach ($recensioni as $recensione) {
            if ($recensione instanceof ERecensioneGhostKitchen && $recensione->getIdAutore() !== null) {
                $autoriRecensioni[(int) $recensione->getIdAutore()] = FPersistentManager::loadUtente((int) $recensione->getIdAutore());
            }
        }

        $data = [
            'ghostKitchen' => $ghostKitchen,
            'gestore' => $gestore,
            'attrezzature' => FPersistentManager::loadAttrezzatureByGhostKitchen($idGhostKitchen),
            'recensioni' => $recensioni,
            'autoriRecensioni' => $autoriRecensioni,
            'disponibilitaPubbliche' => FPersistentManager::loadDisponibilitaGhostKitchen($idGhostKitchen),
            'availabilityPayload' => $this->availabilityPayload($idGhostKitchen),
            'tipoRichiedente' => $this->tipoRichiedenteDaAccesso($accesso),
            'ghostKitchenPrenotabile' => $ghostKitchen->getStato() === EGhostKitchen::STATO_ATTIVA
                && $gestore !== null
                && $gestore->isVerificato()
                && FPersistentManager::ghostKitchenHaCertificazioniInRegola($idGhostKitchen),
            'accesso' => $accesso,
            'mediaPrincipale' => FPersistentManager::getMediaPrincipale('ghost_kitchen', $idGhostKitchen),
            'media' => FPersistentManager::getMediaByOwner('ghost_kitchen', $idGhostKitchen),
            'canManageGallery' => ($accesso['isLogged'] ?? false) === true
                && in_array('gestore', $accesso['ruoli'] ?? [], true)
                && $gestore !== null
                && (int) ($accesso['idUtente'] ?? 0) === (int) $gestore->getIdGestore(),
        ];

        $flash = FSession::get('gk_booking_flash');
        if (is_array($flash) && (int) ($flash['idGhostKitchen'] ?? 0) === $idGhostKitchen) {
            $data['messaggioSuccesso'] = (string) ($flash['messaggioSuccesso'] ?? '');
            FSession::remove('gk_booking_flash');
        }

        return $data;
    }

    private function availabilityPayload(int $idGhostKitchen): array
    {
        $slots = array_filter(
            FPersistentManager::loadDisponibilitaGhostKitchen($idGhostKitchen),
            static fn (EDisponibilitaGhostKitchen $slot): bool => $slot->isLibera() && $slot->getData() >= date('Y-m-d')
        );

        return array_map(static fn (EDisponibilitaGhostKitchen $slot): array => [
            'date' => $slot->getData(),
            'start' => substr($slot->getOraInizio(), 0, 5),
            'end' => substr($slot->getOraFine(), 0, 5),
        ], array_values($slots));
    }

    private function tipoRichiedenteDaAccesso(array $accesso): ?string
    {
        if (($accesso['isLogged'] ?? false) !== true || (int) ($accesso['idUtente'] ?? 0) <= 0) {
            return null;
        }

        $ruoli = $accesso['ruoli'] ?? [];
        $ruoloAttivo = (string) ($accesso['ruoloAttivo'] ?? '');
        if ($ruoloAttivo === 'gestore') {
            return null;
        }
        if ($ruoloAttivo === 'chef' || ($ruoloAttivo === '' && in_array('chef', $ruoli, true))) {
            return EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CHEF;
        }

        return in_array('cliente', $ruoli, true) ? EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CLIENTE : null;
    }
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';
require_once __DIR__ . '/../Foundation/FSession.php';

class CDettaglioChef
{
    public function visualizzaDettaglioChef(int $idChef, array $accesso = []): array
    {
        if ($idChef <= 0) {
            throw new InvalidArgumentException('ID chef non valido.');
        }

        $chef = FPersistentManager::loadChef($idChef);

        if ($chef === null) {
            return [
                'errore' => 'Chef non trovato'
            ];
        }

        $fotoProfilo = FPersistentManager::getMediaPrincipale('chef', $idChef);
        $menuChef = array_values(array_filter(
            FPersistentManager::loadMenuByChef($idChef),
            static fn (EMenu $menu): bool => $menu->isAttivo()
        ));
        $menuDettagliati = [];

        foreach ($menuChef as $menu) {
            $piattiDettagliati = [];

            foreach (FPersistentManager::loadPiattiByMenu((int) $menu->getIdMenu()) as $piatto) {
                $piattiDettagliati[] = [
                    'piatto' => $piatto,
                    'media' => FPersistentManager::getMediaByOwner('piatto', (int) $piatto->getIdPiatto())
                ];
            }

            $menuDettagliati[] = [
                'menu' => $menu,
                'piatti' => $piattiDettagliati
            ];
        }

        $disponibilita = array_values(array_filter(
            FPersistentManager::loadDisponibilitaChef($idChef),
            static fn (EDisponibilitaChef $slot): bool =>
                $slot->isLibera() && $slot->getData() >= date('Y-m-d')
        ));
        $recensioni = FPersistentManager::loadRecensioniByChef($idChef);
        $autoriRecensioni = [];
        foreach ($recensioni as $recensione) {
            if ($recensione instanceof ERecensioneChef && $recensione->getIdAutore() !== null) {
                $autoriRecensioni[(int) $recensione->getIdAutore()] = FPersistentManager::loadUtente((int) $recensione->getIdAutore());
            }
        }
        $utente = ($accesso['isLogged'] ?? false) === true
            ? FPersistentManager::loadUtente((int) ($accesso['idUtente'] ?? 0))
            : null;
        $ruoli = $accesso['ruoli'] ?? [];
        $ruoloAttivo = (string) ($accesso['ruoloAttivo'] ?? '');
        $canBook = ($accesso['isLogged'] ?? false) === true
            && $ruoloAttivo !== 'chef'
            && (in_array('cliente', $ruoli, true) || in_array('gestore', $ruoli, true));

        return [
            'chef' => $chef,
            'fotoProfilo' => $fotoProfilo,
            'media' => FPersistentManager::getMediaByOwner('chef', $idChef),
            'canManageGallery' => ($accesso['isLogged'] ?? false) === true
                && in_array('chef', $accesso['ruoli'] ?? [], true)
                && (int) ($accesso['idUtente'] ?? 0) === $idChef,
            'menu' => $menuDettagliati,
            'certificazioni' => FPersistentManager::loadCertificazioniApprovateByChef($idChef),
            'disponibilitaChef' => $disponibilita,
            'recensioni' => $recensioni,
            'autoriRecensioni' => $autoriRecensioni,
            'accesso' => $accesso,
            'canBookChef' => $canBook,
            'chefPrenotabile' => FPersistentManager::chefHaCertificazioniInRegola($idChef),
            'indirizzoSalvato' => [
                'indirizzo' => $utente?->getIndirizzo() ?? '',
                'citta' => $utente?->getCitta() ?? '',
                'provincia' => $utente?->getProvincia() ?? '',
                'numeroCivico' => $utente?->getNumeroCivico() ?? '',
            ],
            'bookingCsrfToken' => $utente !== null
                ? FSession::csrfToken('chef_booking')
                : '',
            'azioni' => [
                'prenotaChef' => '/PrenotazioneChef/avviaPrenotazioneChef'
            ]
        ];
    }
}

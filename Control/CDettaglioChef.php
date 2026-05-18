<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CDettaglioChef
{
    public static function visualizzaDettaglioChef(int $idChef): array
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
        $menuChef = FPersistentManager::loadMenuByChef($idChef);
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

        return [
            'chef' => $chef,
            'fotoProfilo' => $fotoProfilo,
            'menu' => $menuDettagliati,
            'certificazioni' => FPersistentManager::loadCertificazioniApprovateByChef($idChef),
            'azioni' => [
                'prenotaChef' => '/PrenotazioneChef/avviaPrenotazioneChef'
            ]
        ];
    }
}
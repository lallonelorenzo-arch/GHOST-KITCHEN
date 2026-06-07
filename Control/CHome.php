<?php
declare(strict_types=1);

require_once __DIR__ . '/CRicerca.php';
require_once __DIR__ . '/../Foundation/FChef.php';

class CHome
{
    public function home(): array
    {
        $ricerca = new CRicerca();
        $risultati = $ricerca->cercaOfferte([
            'localita' => '',
            'tipologiaCucina' => '',
            'budgetMax' => 0,
            'valutazioneMin' => 0,
            'tipoRisultato' => 'tutti',
        ]);

        $chefInEvidenza = array_slice($risultati['chef'], 0, 4);
        if (count($chefInEvidenza) < 4) {
            $ids = array_map(static fn (EChef $chef): ?int => $chef->getIdChef(), $chefInEvidenza);
            foreach (FChef::loadAll() as $chef) {
                if (count($chefInEvidenza) >= 4) {
                    break;
                }
                if (!in_array($chef->getIdChef(), $ids, true)) {
                    $chefInEvidenza[] = $chef;
                    $ids[] = $chef->getIdChef();
                }
            }
        }

        return [
            'chefInEvidenza' => $chefInEvidenza,
            'ghostKitchenInEvidenza' => array_slice($risultati['ghostKitchen'], 0, 3),
            'cucine' => ['Italiana', 'Giapponese', 'Vegana', 'Mediterranea', 'Fusion', 'Pasticceria'],
        ];
    }

}

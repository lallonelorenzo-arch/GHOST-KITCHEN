<?php
declare(strict_types=1);

require_once __DIR__ . '/CRicerca.php';

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

        return [
            'chefInEvidenza' => array_slice($risultati['chef'], 0, 4),
            'ghostKitchenInEvidenza' => array_slice($risultati['ghostKitchen'], 0, 3),
            'cucine' => ['Italiana', 'Giapponese', 'Vegana', 'Mediterranea', 'Fusion', 'Pasticceria'],
        ];
    }

}

<?php
declare(strict_types=1);

/**
 * UC1 - Navigazione e Ricerca
 * Operazioni di sistema derivate da SSD:
 * 1) avviaRicerca()
 * 2) cercaChef(localita, tipologia, budget, valutazione)
 */
class CRicerca
{
    /**
     * Mostra il form iniziale di ricerca (Boundary: VRicerca).
     */
    public static function avviaRicerca(): void
    {
        if (class_exists('VRicerca') && method_exists('VRicerca', 'mostraFormRicerca')) {
            VRicerca::mostraFormRicerca();
            return;
        }

        // Fallback utile durante lo sviluppo iniziale senza View pronta.
        self::jsonResponse([
            'status' => 'ok',
            'message' => 'Form ricerca non ancora collegato alla View VRicerca.',
        ]);
    }

    /**
     * Applica i filtri e restituisce chef + ghost kitchens.
     */
    public static function cercaChef(
        ?string $localita,
        ?string $tipologia,
        ?float $budget,
        ?float $valutazione
    ): void {
        $localita = self::normalizeString($localita);
        $tipologia = self::normalizeString($tipologia);
        $budget = self::normalizeNonNegative($budget);
        $valutazione = self::normalizeRating($valutazione);

        $listaChef = [];
        $listaKitchen = [];

        if (class_exists('FChef') && method_exists('FChef', 'ricercaPerFiltri')) {
            $listaChef = FChef::ricercaPerFiltri($localita, $tipologia, $budget, $valutazione);
        }

        if (class_exists('FGhostKitchen') && method_exists('FGhostKitchen', 'ricercaPerFiltri')) {
            $listaKitchen = FGhostKitchen::ricercaPerFiltri($localita, $tipologia, $budget, $valutazione);
        }

        if (class_exists('VRicerca') && method_exists('VRicerca', 'mostraRisultati')) {
            VRicerca::mostraRisultati($listaChef, $listaKitchen);
            return;
        }

        // Fallback per test rapido del Controller anche senza View.
        self::jsonResponse([
            'status' => 'ok',
            'filters' => [
                'localita' => $localita,
                'tipologia' => $tipologia,
                'budget' => $budget,
                'valutazione' => $valutazione,
            ],
            'listaChef' => $listaChef,
            'listaKitchen' => $listaKitchen,
        ]);
    }

    private static function normalizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        return $value === '' ? null : $value;
    }

    private static function normalizeNonNegative(?float $value): ?float
    {
        if ($value === null) {
            return null;
        }

        return $value < 0 ? 0.0 : $value;
    }

    private static function normalizeRating(?float $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if ($value < 0) {
            return 0.0;
        }

        if ($value > 5) {
            return 5.0;
        }

        return $value;
    }

    private static function jsonResponse(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}


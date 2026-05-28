<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/FPrenotazioneGhostKitchen.php';
require_once __DIR__ . '/../Entity/ECancellazione.php';

class FCancellazione extends FAbstractTable
{
    protected static function tableName(): string { return 'cancellazioni'; }
    protected static function primaryKey(): string { return 'id_cancellazione'; }
    protected static function columns(): array { return ['id_cancellazione', 'id_prenotazione', 'id_richiedente', 'motivo', 'data_richiesta', 'penale_applicata', 'importo_rimborsato', 'stato']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdCancellazione(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return ['id_cancellazione' => $entity->getIdCancellazione(), 'id_prenotazione' => $entity->getIdPrenotazione(), 'id_richiedente' => $entity->getIdRichiedente(), 'motivo' => $entity->getMotivo() ?: null, 'data_richiesta' => $entity->getDataRichiesta(), 'penale_applicata' => $entity->getPenaleApplicata(), 'importo_rimborsato' => $entity->getImportoRimborsato(), 'stato' => $entity->getStato()];
    }
    protected static function hydrate(array $row): ECancellazione
    {
        return new ECancellazione((int) $row['id_cancellazione'], (int) $row['id_prenotazione'], self::tipoPrenotazione((int) $row['id_prenotazione']), (int) $row['id_richiedente'], (string) ($row['motivo'] ?? ''), (string) $row['data_richiesta'], (float) $row['penale_applicata'], (float) $row['importo_rimborsato'], (string) $row['stato']);
    }
    private static function tipoPrenotazione(int $idPrenotazione): string
    {
        if (FPrenotazioneGhostKitchen::exist($idPrenotazione)) { return ECancellazione::PRENOTAZIONE_GHOST_KITCHEN; }
        return ECancellazione::PRENOTAZIONE_CHEF;
    }

    public static function calcolaRimborsoStimato(string $tipoPrenotazione, int $idPrenotazione): array
    {
        $pagamento = FPagamento::loadByPrenotazione($tipoPrenotazione, $idPrenotazione);
        if ($pagamento === null) {
            return ['trovato' => false, 'messaggio' => 'Pagamento non trovato per la prenotazione indicata.'];
        }

        $importoPagato = $pagamento->getImporto();
        // TODO: sostituire questa policy base con regole di business complete quando saranno definite.
        $percentualeRimborso = 0.8;
        $importoRimborsabile = round($importoPagato * $percentualeRimborso, 2);
        $penale = round($importoPagato - $importoRimborsabile, 2);

        return ['trovato' => true, 'tipoPrenotazione' => $tipoPrenotazione, 'idPrenotazione' => $idPrenotazione, 'idPagamento' => $pagamento->getIdPagamento(), 'importoPagato' => $importoPagato, 'percentualeRimborso' => $percentualeRimborso, 'penale' => $penale, 'importoRimborsabile' => $importoRimborsabile, 'criterio' => 'policy_base_80_percento'];
    }
}

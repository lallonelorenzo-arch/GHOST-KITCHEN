<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/../Entity/ECertificazione.php';

class FCertificazione extends FAbstractTable
{
    protected static function tableName(): string { return 'certificazioni'; }
    protected static function primaryKey(): string { return 'id_certificazione'; }
    protected static function columns(): array { return ['id_certificazione', 'id_chef', 'tipo_owner', 'id_owner', 'tipo', 'nome_file', 'path_file', 'stato', 'data_caricamento', 'data_validazione', 'data_scadenza', 'note_admin']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdCertificazione(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return [
            'id_certificazione' => $entity->getIdCertificazione(),
            'id_chef' => $entity->getTipoOwner() === ECertificazione::OWNER_CHEF ? $entity->getIdOwner() : null,
            'tipo_owner' => $entity->getTipoOwner(),
            'id_owner' => $entity->getIdOwner(),
            'tipo' => $entity->getTipo(),
            'nome_file' => $entity->getNomeFile(),
            'path_file' => $entity->getPathFile(),
            'stato' => $entity->getStato(),
            'data_caricamento' => $entity->getDataCaricamento(),
            'data_validazione' => $entity->getDataValidazione() ?: null,
            'data_scadenza' => $entity->getDataScadenza() ?: null,
            'note_admin' => $entity->getNoteAdmin() ?: null
        ];
    }
    protected static function hydrate(array $row): ECertificazione
    {
        $tipoOwner = (string) ($row['tipo_owner'] ?? ECertificazione::OWNER_CHEF);
        $idOwner = isset($row['id_owner']) ? (int) $row['id_owner'] : (isset($row['id_chef']) ? (int) $row['id_chef'] : null);
        $idChef = $tipoOwner === ECertificazione::OWNER_CHEF ? (isset($row['id_chef']) ? (int) $row['id_chef'] : $idOwner) : null;

        return new ECertificazione((int) $row['id_certificazione'], $idChef, (string) $row['tipo'], (string) $row['nome_file'], (string) $row['path_file'], (string) $row['stato'], (string) $row['data_caricamento'], (string) ($row['data_validazione'] ?? ''), (string) ($row['note_admin'] ?? ''), (string) ($row['data_scadenza'] ?? ''), $tipoOwner, $idOwner);
    }
    public static function loadByChef(int $idChef): array
    {
        return static::fetchAllWhere('tipo_owner = :tipo_owner AND id_owner = :id ORDER BY data_caricamento DESC', ['tipo_owner' => ECertificazione::OWNER_CHEF, 'id' => $idChef]);
    }
    public static function loadByGhostKitchen(int $idGhostKitchen): array
    {
        return static::fetchAllWhere('tipo_owner = :tipo_owner AND id_owner = :id ORDER BY data_caricamento DESC', ['tipo_owner' => ECertificazione::OWNER_GHOST_KITCHEN, 'id' => $idGhostKitchen]);
    }
    public static function loadByStato(string $stato): array
    {
        return static::fetchAllWhere('stato = :stato ORDER BY data_caricamento DESC', ['stato' => $stato]);
    }

    public static function loadAllCertificazioni(): array
    {
        return static::run('caricamento tutte le certificazioni', static function (): array {
            $sql = 'SELECT * FROM certificazioni
                    ORDER BY FIELD(stato, :in_attesa, :approvata, :rifiutata), tipo_owner ASC, data_caricamento DESC';
            $statement = static::connection()->prepare($sql);
            $statement->execute([
                'in_attesa' => ECertificazione::STATO_IN_ATTESA,
                'approvata' => ECertificazione::STATO_APPROVATA,
                'rifiutata' => ECertificazione::STATO_RIFIUTATA,
            ]);

            return array_map(static fn (array $row): ECertificazione => static::hydrate($row), $statement->fetchAll());
        });
    }

    public static function loadCertificazioniInScadenza(int $giorni = 90): array
    {
        return static::run('caricamento certificazioni in scadenza', static function () use ($giorni): array {
            $giorni = max(1, $giorni);
            $sql = 'SELECT * FROM certificazioni
                    WHERE stato = :stato
                      AND data_scadenza IS NOT NULL
                      AND data_scadenza BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ' . $giorni . ' DAY)
                    ORDER BY data_scadenza ASC';
            $statement = static::connection()->prepare($sql);
            $statement->execute([
                'stato' => ECertificazione::STATO_APPROVATA,
            ]);

            return array_map(static fn (array $row): ECertificazione => static::hydrate($row), $statement->fetchAll());
        });
    }

    public static function loadHaccpInScadenza(int $giorni = 90): array
    {
        return self::loadCertificazioniInScadenza($giorni);
    }

    public static function chefHaCertificazioniInRegola(int $idChef): bool
    {
        return self::ownerHaCertificazioniInRegola(ECertificazione::OWNER_CHEF, $idChef);
    }

    public static function ghostKitchenHaCertificazioniInRegola(int $idGhostKitchen): bool
    {
        return self::ownerHaCertificazioniInRegola(ECertificazione::OWNER_GHOST_KITCHEN, $idGhostKitchen);
    }

    private static function ownerHaCertificazioniInRegola(string $tipoOwner, int $idOwner): bool
    {
        if ($idOwner <= 0) {
            return false;
        }

        return static::run('verifica certificazioni owner in regola', static function () use ($tipoOwner, $idOwner): bool {
            $sql = 'SELECT 1
                    FROM certificazioni
                    WHERE tipo_owner = :tipo_owner
                      AND id_owner = :id_owner
                      AND stato = :stato
                      AND data_scadenza IS NOT NULL
                      AND data_scadenza >= CURDATE()
                    LIMIT 1';
            $statement = static::connection()->prepare($sql);
            $statement->execute([
                'tipo_owner' => $tipoOwner,
                'id_owner' => $idOwner,
                'stato' => ECertificazione::STATO_APPROVATA,
            ]);

            return $statement->fetchColumn() !== false;
        });
    }
}

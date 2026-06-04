<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CSegnalazione
{
    public function avviaSegnalazione(int $idSegnalante, string $tipoTarget, int $idTarget): array
    {
        $this->validaId($idSegnalante, 'ID segnalante non valido.');
        $this->validaTipoTarget($tipoTarget);
        $this->validaId($idTarget, 'ID target non valido.');

        $segnalante = FPersistentManager::loadUtente($idSegnalante);
        $target = FPersistentManager::loadTargetSegnalazione($tipoTarget, $idTarget);

        if ($segnalante === null) {
            return ['errore' => 'Segnalante non trovato.'];
        }
        if ($target === null) {
            return ['errore' => 'Target segnalazione non trovato.'];
        }

        return [
            'segnalante' => $segnalante,
            'target' => $target,
            'campi' => [
                'idSegnalante' => $idSegnalante,
                'tipoTarget' => $tipoTarget,
                'idTarget' => $idTarget,
                'motivo' => '',
                'descrizione' => ''
            ],
            'azioni' => [
                'inviaSegnalazione' => '/Segnalazione/inviaSegnalazione'
            ]
        ];
    }

    public function inviaSegnalazione(array $datiSegnalazione): array
    {
        $idSegnalante = (int) ($datiSegnalazione['idSegnalante'] ?? 0);
        $tipoTarget = strtolower(trim((string) ($datiSegnalazione['tipoTarget'] ?? '')));
        $idTarget = (int) ($datiSegnalazione['idTarget'] ?? 0);
        $motivo = trim((string) ($datiSegnalazione['motivo'] ?? ''));
        $descrizione = trim((string) ($datiSegnalazione['descrizione'] ?? ''));

        $this->validaId($idSegnalante, 'ID segnalante non valido.');
        $this->validaTipoTarget($tipoTarget);
        $this->validaId($idTarget, 'ID target non valido.');
        if ($motivo === '') {
            throw new InvalidArgumentException('Motivo segnalazione obbligatorio.');
        }

        if (FPersistentManager::loadUtente($idSegnalante) === null) {
            return ['errore' => 'Segnalante non trovato.'];
        }
        if (FPersistentManager::loadTargetSegnalazione($tipoTarget, $idTarget) === null) {
            return ['errore' => 'Target segnalazione non trovato.'];
        }

        $segnalazione = new ESegnalazione(null, $idSegnalante, $tipoTarget, $idTarget, $motivo, $descrizione, ESegnalazione::STATO_APERTA, date('Y-m-d'));
        $segnalazione = FPersistentManager::storeSegnalazione($segnalazione);
        if ($segnalazione === false) {
            return ['errore' => 'Segnalazione non salvata. Riprova piu tardi.'];
        }

        return [
            'segnalazione' => $segnalazione,
            'messaggio' => 'Segnalazione registrata.'
        ];
    }

    public function mostraSegnalazioneWeb(string $tipoTarget, int $idTarget, array $accesso): array
    {
        if (!$this->isLogged($accesso)) {
            return [
                'accessoRichiesto' => true,
                'messaggioAccesso' => 'Accedi per inviare una segnalazione.',
                'tipoTarget' => $this->tipoDaSlug($tipoTarget),
                'idTarget' => $idTarget,
                'form' => [],
            ];
        }

        $tipoTarget = $this->tipoDaSlug($tipoTarget);
        $data = $this->avviaSegnalazione((int) $accesso['idUtente'], $tipoTarget, $idTarget);
        $data['accesso'] = $accesso;
        $data['form'] = $data['campi'] ?? [];
        $data['segnalazione'] = null;

        return $data;
    }

    public function inviaSegnalazioneWeb(string $tipoTarget, int $idTarget, array $accesso, array $post): array
    {
        $data = $this->mostraSegnalazioneWeb($tipoTarget, $idTarget, $accesso);
        $data['form'] = array_merge($data['form'] ?? [], $post);

        if (!empty($data['accessoRichiesto']) || isset($data['errore'])) {
            return $data;
        }

        try {
            $result = $this->inviaSegnalazione([
                'idSegnalante' => (int) $accesso['idUtente'],
                'tipoTarget' => $this->tipoDaSlug($tipoTarget),
                'idTarget' => $idTarget,
                'motivo' => (string) ($post['motivo'] ?? ''),
                'descrizione' => (string) ($post['descrizione'] ?? ''),
            ]);

            if (isset($result['errore'])) {
                $data['erroreForm'] = $result['errore'];
                return $data;
            }

            $data['segnalazione'] = $result['segnalazione'] ?? null;
            $data['messaggioSuccesso'] = $result['messaggio'] ?? 'Segnalazione registrata.';
            return $data;
        } catch (InvalidArgumentException $exception) {
            $data['erroreForm'] = $exception->getMessage();
            return $data;
        } catch (Throwable $exception) {
            error_log('[CSegnalazione] ' . $exception->getMessage());
            $data['erroreForm'] = 'Non e stato possibile inviare la segnalazione. Riprova piu tardi.';
            return $data;
        }
    }

    private function validaTipoTarget(string $tipoTarget): void
    {
        $ammessi = ['utente', 'chef', 'ghost_kitchen', 'recensione', 'menu'];
        if (!in_array($tipoTarget, $ammessi, true)) {
            throw new InvalidArgumentException('Tipo target segnalazione non valido.');
        }
    }

    private function tipoDaSlug(string $tipoTarget): string
    {
        $tipoTarget = strtolower(trim($tipoTarget));
        return $tipoTarget === 'ghost-kitchen' ? 'ghost_kitchen' : $tipoTarget;
    }

    private function isLogged(array $accesso): bool
    {
        return ($accesso['isLogged'] ?? false) === true && (int) ($accesso['idUtente'] ?? 0) > 0;
    }

    private function validaId(int $id, string $messaggio): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException($messaggio);
        }
    }
}


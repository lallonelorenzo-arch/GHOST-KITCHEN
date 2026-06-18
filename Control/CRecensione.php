<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CRecensione
{
    public function avviaRecensione(string $tipoTarget, int $idPrenotazione, int $idAutore): array
    {
        $this->validaTipoTarget($tipoTarget);
        $this->validaId($idPrenotazione, 'ID prenotazione non valido.');
        $this->validaId($idAutore, 'ID autore non valido.');

        $verifica = FPersistentManager::verificaPrenotazioneRecensibile($tipoTarget, $idPrenotazione, $idAutore);
        if (($verifica['recensibile'] ?? false) !== true) {
            return ['errore' => $verifica['motivo'] ?? 'Prenotazione non recensibile.'];
        }

        $prenotazione = $tipoTarget === 'chef'
            ? FPersistentManager::loadPrenotazioneChef($idPrenotazione)
            : FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);

        return [
            'tipoTarget' => $tipoTarget,
            'prenotazione' => $prenotazione,
            'targetRecensione' => $this->targetRecensione($tipoTarget, $prenotazione),
            'campi' => [
                'tipoTarget' => $tipoTarget,
                'idPrenotazione' => $idPrenotazione,
                'idAutore' => $idAutore,
                'punteggio' => 5,
                'commento' => ''
            ],
            'azioni' => [
                'pubblicaRecensione' => '/Recensione/pubblicaRecensione'
            ]
        ];
    }

    public function pubblicaRecensione(array $datiRecensione): array
    {
        $tipoTarget = strtolower(trim((string) ($datiRecensione['tipoTarget'] ?? '')));
        $idPrenotazione = (int) ($datiRecensione['idPrenotazione'] ?? 0);
        $idAutore = (int) ($datiRecensione['idAutore'] ?? 0);
        $punteggio = (int) ($datiRecensione['punteggio'] ?? 0);
        $commento = trim((string) ($datiRecensione['commento'] ?? ''));

        $this->validaTipoTarget($tipoTarget);
        $this->validaId($idPrenotazione, 'ID prenotazione non valido.');
        $this->validaId($idAutore, 'ID autore non valido.');
        if ($punteggio < 1 || $punteggio > 5) {
            throw new InvalidArgumentException('Punteggio recensione non valido.');
        }
        if ($commento === '') {
            throw new InvalidArgumentException('Commento recensione obbligatorio.');
        }

        $verifica = FPersistentManager::verificaPrenotazioneRecensibile($tipoTarget, $idPrenotazione, $idAutore);
        if (($verifica['recensibile'] ?? false) !== true) {
            return ['errore' => $verifica['motivo'] ?? 'Prenotazione non recensibile.'];
        }

        if ($tipoTarget === 'chef') {
            $prenotazione = FPersistentManager::loadPrenotazioneChef($idPrenotazione);
            $recensione = new ERecensioneChef(null, $idAutore, $punteggio, $commento, date('Y-m-d'), ERecensione::STATO_VISIBILE, $prenotazione !== null ? $prenotazione->getIdChef() : null, $idPrenotazione);
            $recensione = FPersistentManager::storeRecensioneChef($recensione);
            if ($recensione === false) {
                return ['errore' => 'Recensione non salvata. Riprova piu tardi.'];
            }
            $valutazione = FPersistentManager::aggiornaValutazioneChef((int) $recensione->getIdChef());
        } else {
            $prenotazione = FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);
            $recensione = new ERecensioneGhostKitchen(null, $idAutore, $punteggio, $commento, date('Y-m-d'), ERecensione::STATO_VISIBILE, $prenotazione !== null ? $prenotazione->getIdGhostKitchen() : null, $idPrenotazione);
            $recensione = FPersistentManager::storeRecensioneGhostKitchen($recensione);
            if ($recensione === false) {
                return ['errore' => 'Recensione non salvata. Riprova piu tardi.'];
            }
            $valutazione = FPersistentManager::aggiornaValutazioneGhostKitchen((int) $recensione->getIdGhostKitchen());
        }

        return [
            'recensione' => $recensione,
            'valutazione' => $valutazione,
            'messaggio' => 'Recensione pubblicata.'
        ];
    }

    public function mostraRecensioneWeb(string $tipoTarget, int $idPrenotazione, array $accesso): array
    {
        if (!$this->isLogged($accesso)) {
            return [
                'accessoRichiesto' => true,
                'messaggioAccesso' => 'Accedi per pubblicare una recensione.',
                'tipoTarget' => $this->tipoDaSlug($tipoTarget),
                'idPrenotazione' => $idPrenotazione,
                'form' => ['punteggio' => 5, 'commento' => ''],
            ];
        }

        $tipoTarget = $this->tipoDaSlug($tipoTarget);
        $data = $this->avviaRecensione($tipoTarget, $idPrenotazione, (int) $accesso['idUtente']);
        if (isset($data['errore'])) {
            $data['erroreForm'] = $data['errore'];
            unset($data['errore']);
            $data['tipoTarget'] = $tipoTarget;
        }
        $data['accesso'] = $accesso;
        $data['form'] = $data['campi'] ?? ['punteggio' => 5, 'commento' => ''];
        $data['recensione'] = null;

        return $data;
    }

    public function pubblicaRecensioneWeb(string $tipoTarget, int $idPrenotazione, array $accesso, array $post): array
    {
        $data = $this->mostraRecensioneWeb($tipoTarget, $idPrenotazione, $accesso);
        $data['form'] = array_merge($data['form'] ?? [], $post);

        if (!empty($data['accessoRichiesto']) || isset($data['errore'])) {
            return $data;
        }

        try {
            $result = $this->pubblicaRecensione([
                'tipoTarget' => $this->tipoDaSlug($tipoTarget),
                'idPrenotazione' => $idPrenotazione,
                'idAutore' => (int) $accesso['idUtente'],
                'punteggio' => (int) ($post['punteggio'] ?? 0),
                'commento' => (string) ($post['commento'] ?? ''),
            ]);

            if (isset($result['errore'])) {
                $data['erroreForm'] = $result['errore'];
                return $data;
            }

            $data['recensione'] = $result['recensione'] ?? null;
            $data['valutazione'] = $result['valutazione'] ?? null;
            $data['messaggioSuccesso'] = $result['messaggio'] ?? 'Recensione pubblicata.';
            return $data;
        } catch (InvalidArgumentException $exception) {
            $data['erroreForm'] = $exception->getMessage();
            return $data;
        } catch (Throwable $exception) {
            error_log('[CRecensione] ' . $exception->getMessage());
            $data['erroreForm'] = 'Non e stato possibile pubblicare la recensione. Riprova piu tardi.';
            return $data;
        }
    }

    private function validaTipoTarget(string $tipoTarget): void
    {
        if (!in_array($tipoTarget, ['chef', 'ghost_kitchen'], true)) {
            throw new InvalidArgumentException('Tipo target recensione non valido.');
        }
    }

    private function tipoDaSlug(string $tipoTarget): string
    {
        $tipoTarget = strtolower(trim($tipoTarget));
        return $tipoTarget === 'ghost-kitchen' ? 'ghost_kitchen' : $tipoTarget;
    }

    private function targetRecensione(string $tipoTarget, mixed $prenotazione): array
    {
        if ($tipoTarget === 'chef') {
            $chef = $prenotazione !== null && method_exists($prenotazione, 'getIdChef')
                ? FPersistentManager::loadChef((int) $prenotazione->getIdChef())
                : null;

            return [
                'label' => 'Chef',
                'nome' => $chef !== null ? trim($chef->getNome() . ' ' . $chef->getCognome()) : 'Chef non disponibile',
                'dettagli' => array_filter([
                    'Specializzazione' => $chef !== null ? $chef->getSpecializzazione() : '',
                    'Cucina' => $chef !== null ? $chef->getTipologiaCucina() : '',
                ], static fn (string $value): bool => trim($value) !== ''),
            ];
        }

        $ghostKitchen = $prenotazione !== null && method_exists($prenotazione, 'getIdGhostKitchen')
            ? FPersistentManager::loadGhostKitchen((int) $prenotazione->getIdGhostKitchen())
            : null;

        return [
            'label' => 'Ghost kitchen',
            'nome' => $ghostKitchen !== null ? $ghostKitchen->getNome() : 'Ghost kitchen non disponibile',
            'dettagli' => array_filter([
                'Citta' => $ghostKitchen !== null ? $ghostKitchen->getCitta() : '',
                'Indirizzo' => $ghostKitchen !== null ? $ghostKitchen->getIndirizzo() : '',
            ], static fn (string $value): bool => trim($value) !== ''),
        ];
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


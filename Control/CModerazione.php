<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CModerazione
{
    public function visualizzaContenutiDaModerare(): array
    {
        $segnalazioni = FPersistentManager::loadSegnalazioniDaModerare();

        return [
            'segnalazioni' => $segnalazioni,
            'segnalazioniModerazione' => $this->preparaSchedeSegnalazioni($segnalazioni),
            'riepilogoModerazione' => $this->preparaRiepilogo($segnalazioni),
            'azioni' => [
                'prendiInCarico' => '/Moderazione/prendiInCaricoSegnalazione',
                'moderaRecensione' => '/Moderazione/moderaRecensione',
                'moderaProfilo' => '/Moderazione/moderaProfilo',
                'chiudiSegnalazione' => '/Moderazione/chiudiSegnalazione'
            ]
        ];
    }

    public function prendiInCaricoSegnalazione(int $idSegnalazione): array
    {
        $this->validaId($idSegnalazione, 'ID segnalazione non valido.');
        $segnalazione = FPersistentManager::loadSegnalazione($idSegnalazione);

        if ($segnalazione === null) {
            return ['errore' => 'Segnalazione non trovata.'];
        }

        $segnalazione->prendiInCarico();
        $segnalazione = FPersistentManager::updateSegnalazione($segnalazione);
        if ($segnalazione === false) {
            return ['errore' => 'Segnalazione non aggiornata.'];
        }

        return [
            'segnalazione' => $segnalazione,
            'messaggio' => 'Segnalazione presa in carico.'
        ];
    }

    public function moderaRecensione(int $idRecensione, string $azione): array
    {
        $this->validaId($idRecensione, 'ID recensione non valido.');
        $azione = strtolower(trim($azione));
        if (!in_array($azione, ['nascondi', 'rimuovi', 'ripristina'], true)) {
            throw new InvalidArgumentException('Azione recensione non valida.');
        }

        $recensione = FPersistentManager::loadRecensione($idRecensione);
        if ($recensione === null) {
            return ['errore' => 'Recensione non trovata.'];
        }

        $recensione->{$azione}();
        $recensione = FPersistentManager::updateRecensione($recensione);
        if ($recensione === false) {
            return ['errore' => 'Recensione non aggiornata.'];
        }

        return [
            'recensione' => $recensione,
            'messaggio' => 'Recensione moderata.'
        ];
    }

    public function moderaProfilo(int $idUtente, string $azione): array
    {
        $this->validaId($idUtente, 'ID utente non valido.');
        $azione = strtolower(trim($azione));
        $mappaStati = [
            'sospendi' => EUtente::STATO_SOSPESO,
            'banna' => EUtente::STATO_BANNATO,
            'riattiva' => EUtente::STATO_ATTIVO
        ];
        if (!isset($mappaStati[$azione])) {
            throw new InvalidArgumentException('Azione profilo non valida.');
        }

        $utente = FPersistentManager::loadUtente($idUtente);
        if ($utente === null) {
            return ['errore' => 'Utente non trovato.'];
        }

        $utente->setStato($mappaStati[$azione]);
        $utente = FPersistentManager::updateUtente($utente);
        if ($utente === false) {
            return ['errore' => 'Profilo non aggiornato.'];
        }

        return [
            'utente' => $utente,
            'messaggio' => 'Profilo moderato.'
        ];
    }

    public function chiudiSegnalazione(int $idSegnalazione, string $esito, string $noteAdmin = ''): array
    {
        $this->validaId($idSegnalazione, 'ID segnalazione non valido.');
        $esito = strtolower(trim($esito));
        if (!in_array($esito, ['risolta', 'archiviata', 'respinta'], true)) {
            throw new InvalidArgumentException('Esito segnalazione non valido.');
        }

        $segnalazione = FPersistentManager::loadSegnalazione($idSegnalazione);
        if ($segnalazione === null) {
            return ['errore' => 'Segnalazione non trovata.'];
        }

        if ($segnalazione->getStato() === ESegnalazione::STATO_APERTA && $esito === 'risolta') {
            $segnalazione->prendiInCarico();
        }

        if ($esito === 'risolta') {
            $segnalazione->risolvi();
        } elseif ($esito === 'respinta') {
            $segnalazione->respingi();
        } else {
            try {
                $segnalazione->archivia();
            } catch (InvalidArgumentException $e) {
                $segnalazione->setStato(ESegnalazione::STATO_ARCHIVIATA);
            }
        }

        $segnalazione->setNoteAdmin($noteAdmin);
        $segnalazione->setDataGestione(date('Y-m-d'));
        $segnalazione = FPersistentManager::updateSegnalazione($segnalazione);
        if ($segnalazione === false) {
            return ['errore' => 'Segnalazione non chiusa.'];
        }

        return [
            'segnalazione' => $segnalazione,
            'messaggio' => 'Segnalazione chiusa.'
        ];
    }

    public function visualizzaContenutiDaModerareWeb(array $accesso): array
    {
        if (!$this->isAdmin($accesso)) {
            return [
                'accessoRichiesto' => true,
                'messaggioAccesso' => 'Non hai permessi per questa sezione.',
                'segnalazioni' => [],
                'segnalazioniModerazione' => [],
                'riepilogoModerazione' => [],
            ];
        }

        return $this->visualizzaContenutiDaModerare() + ['accesso' => $accesso];
    }

    public function prendiInCaricoSegnalazioneWeb(int $idSegnalazione, array $accesso): array
    {
        if (!$this->isAdmin($accesso)) {
            return $this->esito('Accesso non consentito', 'Non hai permessi per questa sezione.', false);
        }

        return $this->esitoDaOperazione(fn (): array => $this->prendiInCaricoSegnalazione($idSegnalazione));
    }

    public function moderaRecensioneWeb(int $idRecensione, string $azione, array $accesso, array $post = []): array
    {
        $ritorno = (string) ($post['ritorno'] ?? '/moderazione');
        if (!in_array($ritorno, ['/moderazione', '/recensioni'], true)) {
            $ritorno = '/moderazione';
        }

        if (!$this->isAdmin($accesso)) {
            return $this->esito('Accesso non consentito', 'Non hai permessi per questa sezione.', false, $ritorno);
        }

        return $this->esitoDaOperazione(fn (): array => $this->moderaRecensione($idRecensione, $azione), $ritorno);
    }

    public function moderaProfiloWeb(int $idUtente, string $azione, array $accesso): array
    {
        if (!$this->isAdmin($accesso)) {
            return $this->esito('Accesso non consentito', 'Non hai permessi per questa sezione.', false);
        }

        return $this->esitoDaOperazione(fn (): array => $this->moderaProfilo($idUtente, $azione));
    }

    public function chiudiSegnalazioneWeb(int $idSegnalazione, array $accesso, array $post): array
    {
        if (!$this->isAdmin($accesso)) {
            return $this->esito('Accesso non consentito', 'Non hai permessi per questa sezione.', false);
        }

        return $this->esitoDaOperazione(fn (): array => $this->chiudiSegnalazione(
            $idSegnalazione,
            (string) ($post['esito'] ?? ''),
            (string) ($post['noteAdmin'] ?? '')
        ));
    }

    private function esitoDaOperazione(callable $callback, string $ritorno = '/moderazione'): array
    {
        try {
            $result = $callback();
            if (isset($result['errore'])) {
                return $this->esito('Operazione non completata', (string) $result['errore'], false, $ritorno);
            }

            return $this->esito('Operazione completata', (string) ($result['messaggio'] ?? 'Aggiornamento eseguito.'), true, $ritorno);
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Operazione non completata', $exception->getMessage(), false, $ritorno);
        } catch (Throwable $exception) {
            error_log('[CModerazione] ' . $exception->getMessage());
            return $this->esito('Operazione non completata', 'Errore interno durante la moderazione. Riprova piu tardi.', false, $ritorno);
        }
    }

    private function esito(string $titolo, string $messaggio, bool $successo, string $ritorno = '/moderazione'): array
    {
        return [
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'successo' => $successo,
            'ritorno' => $ritorno,
        ];
    }

    private function isAdmin(array $accesso): bool
    {
        $ruoli = $accesso['ruoli'] ?? [];
        return ($accesso['isLogged'] ?? false) === true && (in_array('admin', $ruoli, true) || in_array('amministratore', $ruoli, true));
    }


    private function preparaSchedeSegnalazioni(array $segnalazioni): array
    {
        $schede = [];

        foreach ($segnalazioni as $segnalazione) {
            if (!$segnalazione instanceof ESegnalazione) {
                continue;
            }

            $tipoTarget = $segnalazione->getTipoTarget();
            $idTarget = (int) $segnalazione->getIdTarget();
            $target = null;

            if ($idTarget > 0) {
                try {
                    $target = FPersistentManager::loadTargetSegnalazione($tipoTarget, $idTarget);
                } catch (Throwable $exception) {
                    error_log('[CModerazione] target segnalazione non caricato: ' . $exception->getMessage());
                }
            }

            $schede[] = [
                'segnalazione' => $segnalazione,
                'targetLabel' => $this->targetLabel($tipoTarget),
                'targetSummary' => $this->targetSummary($target, $tipoTarget, $idTarget),
                'isRecensione' => $tipoTarget === ESegnalazione::TARGET_RECENSIONE,
                'isProfilo' => in_array($tipoTarget, [ESegnalazione::TARGET_UTENTE, ESegnalazione::TARGET_CHEF], true),
            ];
        }

        return $schede;
    }

    private function preparaRiepilogo(array $segnalazioni): array
    {
        $riepilogo = [
            'totale' => 0,
            'recensioni' => 0,
            'profili' => 0,
            'contenuti' => 0,
        ];

        foreach ($segnalazioni as $segnalazione) {
            if (!$segnalazione instanceof ESegnalazione) {
                continue;
            }

            $riepilogo['totale']++;
            $tipoTarget = $segnalazione->getTipoTarget();
            if ($tipoTarget === ESegnalazione::TARGET_RECENSIONE) {
                $riepilogo['recensioni']++;
            } elseif (in_array($tipoTarget, [ESegnalazione::TARGET_UTENTE, ESegnalazione::TARGET_CHEF], true)) {
                $riepilogo['profili']++;
            } else {
                $riepilogo['contenuti']++;
            }
        }

        return $riepilogo;
    }

    private function targetLabel(string $tipoTarget): string
    {
        return [
            ESegnalazione::TARGET_UTENTE => 'Profilo utente',
            ESegnalazione::TARGET_CHEF => 'Profilo chef',
            ESegnalazione::TARGET_GHOST_KITCHEN => 'Ghost kitchen',
            ESegnalazione::TARGET_RECENSIONE => 'Recensione',
            ESegnalazione::TARGET_MENU => 'Menu',
        ][$tipoTarget] ?? ucfirst(str_replace('_', ' ', $tipoTarget));
    }

    private function targetSummary(mixed $target, string $tipoTarget, int $idTarget): string
    {
        if ($target === null) {
            return $this->targetLabel($tipoTarget) . ' #' . $idTarget;
        }

        if (method_exists($target, 'getNome') && method_exists($target, 'getCognome')) {
            $nome = trim((string) $target->getNome() . ' ' . (string) $target->getCognome());
            return $nome !== '' ? $nome : $this->targetLabel($tipoTarget) . ' #' . $idTarget;
        }

        if (method_exists($target, 'getNome')) {
            return (string) $target->getNome();
        }

        if (method_exists($target, 'getCommento')) {
            $commento = (string) $target->getCommento();
            return $commento !== '' ? substr($commento, 0, 90) : 'Recensione #' . $idTarget;
        }

        return $this->targetLabel($tipoTarget) . ' #' . $idTarget;
    }

    private function validaId(int $id, string $messaggio): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException($messaggio);
        }
    }
}


<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CGestioneGhostKitchen
{
    public function gestisciGhostKitchenWeb(array $accesso, array $post): array
    {
        $idGestore = $this->requireGestore($accesso);
        $azione = strtolower(trim((string) ($post['azione'] ?? '')));
        $idGhostKitchen = (int) ($post['idGhostKitchen'] ?? 0);

        try {
            if ($azione === 'crea') {
                $ghostKitchen = new EGhostKitchen(
                    null,
                    $idGestore,
                    (string) ($post['nome'] ?? ''),
                    (string) ($post['descrizione'] ?? ''),
                    (string) ($post['indirizzo'] ?? ''),
                    (string) ($post['citta'] ?? ''),
                    (string) ($post['cap'] ?? ''),
                    (float) ($post['prezzoOrario'] ?? 0),
                    (int) ($post['capienza'] ?? 0),
                    (float) ($post['mq'] ?? 0),
                    EGhostKitchen::STATO_NON_DISPONIBILE
                );
                $this->validaGhostKitchen($ghostKitchen);

                return FPersistentManager::storeGhostKitchen($ghostKitchen) !== false
                    ? $this->esito('Gestione Ghost Kitchen', 'Ghost Kitchen creata. Puoi completarla e pubblicarla.', true)
                    : $this->esito('Gestione Ghost Kitchen', 'Non e stato possibile creare la Ghost Kitchen.', false);
            }

            $ghostKitchen = $this->ghostKitchenGestore($idGhostKitchen, $idGestore);
            if ($ghostKitchen === null) {
                return $this->esito('Gestione Ghost Kitchen', 'Ghost Kitchen non trovata o non appartenente al tuo profilo.', false);
            }

            if ($azione === 'aggiorna') {
                $ghostKitchen->setNome((string) ($post['nome'] ?? ''));
                $ghostKitchen->setDescrizione((string) ($post['descrizione'] ?? ''));
                $ghostKitchen->setIndirizzo((string) ($post['indirizzo'] ?? ''));
                $ghostKitchen->setCitta((string) ($post['citta'] ?? ''));
                $ghostKitchen->setCap((string) ($post['cap'] ?? ''));
                $ghostKitchen->setPrezzoOrario((float) ($post['prezzoOrario'] ?? 0));
                $ghostKitchen->setCapienza((int) ($post['capienza'] ?? 0));
                $ghostKitchen->setMq((float) ($post['mq'] ?? 0));
                $this->validaGhostKitchen($ghostKitchen);
            } elseif ($azione === 'pubblica') {
                $ghostKitchen->setStato(EGhostKitchen::STATO_ATTIVA);
            } elseif ($azione === 'rimuovi') {
                $ghostKitchen->setStato(EGhostKitchen::STATO_NON_DISPONIBILE);
            } else {
                return $this->esito('Gestione Ghost Kitchen', 'Azione non valida.', false);
            }

            return FPersistentManager::updateGhostKitchen($ghostKitchen) !== false
                ? $this->esito('Gestione Ghost Kitchen', 'Ghost Kitchen aggiornata.', true)
                : $this->esito('Gestione Ghost Kitchen', 'Non e stato possibile aggiornare la Ghost Kitchen.', false);
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Gestione Ghost Kitchen', $exception->getMessage(), false);
        } catch (Throwable $exception) {
            error_log('[CGestioneGhostKitchen] ' . $exception->getMessage());
            return $this->esito('Gestione Ghost Kitchen', 'Operazione non completata. Verifica i dati inseriti.', false);
        }
    }

    public function gestisciAttrezzaturaWeb(array $accesso, array $post): array
    {
        $idGestore = $this->requireGestore($accesso);
        $azione = strtolower(trim((string) ($post['azione'] ?? '')));
        $idGhostKitchen = (int) ($post['idGhostKitchen'] ?? 0);
        $ghostKitchen = $this->ghostKitchenGestore($idGhostKitchen, $idGestore);
        if ($ghostKitchen === null) {
            return $this->esito('Gestione attrezzature', 'Ghost Kitchen non valida.', false);
        }

        try {
            if ($azione === 'crea') {
                $attrezzatura = new EAttrezzatura(
                    null,
                    $idGhostKitchen,
                    (string) ($post['nome'] ?? ''),
                    (string) ($post['categoria'] ?? ''),
                    (string) ($post['descrizione'] ?? ''),
                    (int) ($post['quantita'] ?? 0)
                );
                $this->validaAttrezzatura($attrezzatura);

                return FPersistentManager::storeAttrezzatura($attrezzatura) !== false
                    ? $this->esito('Gestione attrezzature', 'Attrezzatura aggiunta.', true)
                    : $this->esito('Gestione attrezzature', 'Attrezzatura non aggiunta.', false);
            }

            $idAttrezzatura = (int) ($post['idAttrezzatura'] ?? 0);
            $attrezzatura = $idAttrezzatura > 0 ? FPersistentManager::loadAttrezzatura($idAttrezzatura) : null;
            if ($attrezzatura === null || (int) $attrezzatura->getIdGhostKitchen() !== $idGhostKitchen) {
                return $this->esito('Gestione attrezzature', 'Attrezzatura non trovata.', false);
            }

            if ($azione === 'rimuovi') {
                return FPersistentManager::deleteAttrezzatura($idAttrezzatura)
                    ? $this->esito('Gestione attrezzature', 'Attrezzatura rimossa.', true)
                    : $this->esito('Gestione attrezzature', 'Attrezzatura non rimossa.', false);
            }

            if ($azione !== 'aggiorna') {
                return $this->esito('Gestione attrezzature', 'Azione non valida.', false);
            }

            $attrezzatura->setNome((string) ($post['nome'] ?? ''));
            $attrezzatura->setCategoria((string) ($post['categoria'] ?? ''));
            $attrezzatura->setDescrizione((string) ($post['descrizione'] ?? ''));
            $attrezzatura->setQuantita((int) ($post['quantita'] ?? 0));
            $this->validaAttrezzatura($attrezzatura);

            return FPersistentManager::updateAttrezzatura($attrezzatura) !== false
                ? $this->esito('Gestione attrezzature', 'Attrezzatura aggiornata.', true)
                : $this->esito('Gestione attrezzature', 'Attrezzatura non aggiornata.', false);
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Gestione attrezzature', $exception->getMessage(), false);
        } catch (Throwable $exception) {
            error_log('[CGestioneGhostKitchen] ' . $exception->getMessage());
            return $this->esito('Gestione attrezzature', 'Operazione non completata. Verifica che l attrezzatura non sia gia presente.', false);
        }
    }

    private function requireGestore(array $accesso): int
    {
        if (($accesso['isLogged'] ?? false) !== true || !in_array('gestore', $accesso['ruoli'] ?? [], true)) {
            throw new InvalidArgumentException('Serve un account gestore per questa operazione.');
        }

        $idGestore = (int) ($accesso['idUtente'] ?? 0);
        if ($idGestore <= 0) {
            throw new InvalidArgumentException('Account gestore non valido.');
        }

        return $idGestore;
    }

    private function ghostKitchenGestore(int $idGhostKitchen, int $idGestore): ?EGhostKitchen
    {
        $ghostKitchen = $idGhostKitchen > 0 ? FPersistentManager::loadGhostKitchen($idGhostKitchen) : null;
        return $ghostKitchen !== null && (int) $ghostKitchen->getIdGestore() === $idGestore ? $ghostKitchen : null;
    }

    private function validaGhostKitchen(EGhostKitchen $ghostKitchen): void
    {
        if ($ghostKitchen->getNome() === '' || $ghostKitchen->getDescrizione() === '') {
            throw new InvalidArgumentException('Nome e descrizione sono obbligatori.');
        }
        if ($ghostKitchen->getIndirizzo() === '' || $ghostKitchen->getCitta() === '' || $ghostKitchen->getCap() === '') {
            throw new InvalidArgumentException('Inserisci indirizzo, citta e CAP.');
        }
        if ($ghostKitchen->getCapienza() <= 0 || $ghostKitchen->getMq() <= 0) {
            throw new InvalidArgumentException('Capienza e metri quadri devono essere maggiori di zero.');
        }
    }

    private function validaAttrezzatura(EAttrezzatura $attrezzatura): void
    {
        if ($attrezzatura->getNome() === '' || $attrezzatura->getCategoria() === '') {
            throw new InvalidArgumentException('Nome e categoria dell attrezzatura sono obbligatori.');
        }
    }

    private function esito(string $titolo, string $messaggio, bool $successo): array
    {
        return [
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'successo' => $successo,
            'ritorno' => '/dashboard?ruolo=gestore&tab=ghost_kitchen',
        ];
    }
}

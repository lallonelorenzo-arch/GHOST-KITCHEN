<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CDashboardGestore
{
    public function visualizzaDashboardWeb(array $accesso, array $query = []): array
    {
        if (($accesso['isLogged'] ?? false) !== true || !in_array('gestore', $accesso['ruoli'] ?? [], true)) {
            return ['messaggioAccesso' => 'Non hai permessi per questa sezione.'];
        }

        $idGestore = (int) ($accesso['idUtente'] ?? 0);
        $ghostKitchen = $idGestore > 0 ? FPersistentManager::loadGhostKitchenByGestore($idGestore) : [];
        $prenotazioni = $idGestore > 0 ? FPersistentManager::loadPrenotazioniRicevuteGhostKitchenByGestore($idGestore) : [];
        $inAttesa = array_values(array_filter($prenotazioni, static fn (EPrenotazioneGhostKitchen $p): bool => $p->getStato() === EPrenotazione::STATO_IN_ATTESA));
        $accettate = array_values(array_filter($prenotazioni, static fn (EPrenotazioneGhostKitchen $p): bool => in_array($p->getStato(), [EPrenotazione::STATO_ACCETTATA, EPrenotazione::STATO_PAGATA, EPrenotazione::STATO_COMPLETATA], true)));
        $fatturato = array_reduce($accettate, static fn (float $totale, EPrenotazioneGhostKitchen $p): float => $totale + $p->getImportoTotale(), 0.0);
        $fatturatoMese = array_reduce($accettate, static function (float $totale, EPrenotazioneGhostKitchen $p): float {
            return str_starts_with($p->getDataServizio(), date('Y-m')) ? $totale + $p->getImportoTotale() : $totale;
        }, 0.0);
        $ore = array_reduce($accettate, static function (float $totale, EPrenotazioneGhostKitchen $p): float {
            $inizio = strtotime($p->getOraInizio());
            $fine = strtotime($p->getOraFine());
            return $totale + ($inizio !== false && $fine !== false ? max(0, ($fine - $inizio) / 3600) : 0);
        }, 0.0);

        $tabAttiva = strtolower(trim((string) ($query['tab'] ?? 'panoramica')));
        if (!in_array($tabAttiva, ['panoramica', 'prenotazioni', 'richieste', 'calendario', 'statistiche', 'ghost_kitchen'], true)) {
            $tabAttiva = 'panoramica';
        }
        $filtroRichieste = strtolower(trim((string) ($query['filtro'] ?? 'tutte')));
        if (!in_array($filtroRichieste, ['tutte', 'in_attesa', 'accettate', 'rifiutate'], true)) {
            $filtroRichieste = 'tutte';
        }

        return [
            'accesso' => $accesso,
            'tabAttiva' => $tabAttiva,
            'filtroRichieste' => $filtroRichieste,
            'ghostKitchenGestore' => $ghostKitchen,
            'metriche' => [
                'ghostKitchenTotali' => count($ghostKitchen),
                'prenotazioniTotali' => count($prenotazioni),
                'richiesteInAttesa' => count($inAttesa),
                'fatturato' => $fatturato,
                'fatturatoMese' => $fatturatoMese,
                'oreOccupate' => $ore,
                'valutazioneMedia' => $this->valutazioneMediaGhostKitchen($ghostKitchen),
            ],
            'fatturatoMensile' => $this->fatturatoMensile($accettate),
            'prenotazioniSettimanali' => $this->prenotazioniSettimanali($prenotazioni),
            'prossimePrenotazioni' => $this->prossimePrenotazioni($prenotazioni),
            'prenotazioniTabella' => $this->prenotazioniTabella($prenotazioni),
            'richiestePrenotazione' => $this->richiestePrenotazione($prenotazioni),
            'statisticheGestore' => $this->statisticheGestore($prenotazioni, $accettate),
        ];
    }

    private function valutazioneMediaGhostKitchen(array $ghostKitchen): float
    {
        $valutate = array_values(array_filter($ghostKitchen, static fn (EGhostKitchen $gk): bool => $gk->getNumeroRecensioni() > 0));
        if ($valutate === []) {
            return 0.0;
        }

        $totale = array_reduce($valutate, static fn (float $sum, EGhostKitchen $gk): float => $sum + $gk->getValutazioneMedia(), 0.0);
        return round($totale / count($valutate), 2);
    }

    private function fatturatoMensile(array $prenotazioni): array
    {
        $mesi = [1 => 'Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
        $punti = [];
        for ($i = 6; $i >= 0; $i--) {
            $timestamp = strtotime('-' . $i . ' months');
            $key = date('Y-m', $timestamp);
            $punti[$key] = ['label' => $mesi[(int) date('n', $timestamp)], 'value' => 0.0];
        }

        foreach ($prenotazioni as $prenotazione) {
            if (!$prenotazione instanceof EPrenotazioneGhostKitchen) {
                continue;
            }
            $key = substr($prenotazione->getDataServizio(), 0, 7);
            if (isset($punti[$key])) {
                $punti[$key]['value'] += $prenotazione->getImportoTotale();
            }
        }

        return array_values($punti);
    }

    private function prenotazioniSettimanali(array $prenotazioni): array
    {
        $giorni = [
            1 => ['label' => 'Lun', 'value' => 0],
            2 => ['label' => 'Mar', 'value' => 0],
            3 => ['label' => 'Mer', 'value' => 0],
            4 => ['label' => 'Gio', 'value' => 0],
            5 => ['label' => 'Ven', 'value' => 0],
            6 => ['label' => 'Sab', 'value' => 0],
            7 => ['label' => 'Dom', 'value' => 0],
        ];

        foreach ($prenotazioni as $prenotazione) {
            if (!$prenotazione instanceof EPrenotazioneGhostKitchen) {
                continue;
            }
            $timestamp = strtotime($prenotazione->getDataServizio());
            if ($timestamp !== false) {
                $giorni[(int) date('N', $timestamp)]['value']++;
            }
        }

        return array_values($giorni);
    }

    private function prossimePrenotazioni(array $prenotazioni): array
    {
        $future = array_values(array_filter($prenotazioni, static function (EPrenotazioneGhostKitchen $p): bool {
            return strtotime($p->getDataServizio() . ' ' . $p->getOraInizio()) >= strtotime('today')
                && in_array($p->getStato(), [EPrenotazione::STATO_IN_ATTESA, EPrenotazione::STATO_ACCETTATA, EPrenotazione::STATO_PAGATA], true);
        }));

        usort($future, static fn (EPrenotazioneGhostKitchen $a, EPrenotazioneGhostKitchen $b): int => strcmp($a->getDataServizio() . $a->getOraInizio(), $b->getDataServizio() . $b->getOraInizio()));
        $future = array_slice($future, 0, 3);

        return array_map(static function (EPrenotazioneGhostKitchen $prenotazione): array {
            $utente = FPersistentManager::loadUtente((int) $prenotazione->getIdRichiedente());
            $ghostKitchen = FPersistentManager::loadGhostKitchen((int) $prenotazione->getIdGhostKitchen());
            $nome = $utente !== null ? trim($utente->getNome() . ' ' . $utente->getCognome()) : 'Richiedente #' . $prenotazione->getIdRichiedente();

            return [
                'prenotazione' => $prenotazione,
                'nome' => $nome,
                'descrizione' => $ghostKitchen !== null ? $ghostKitchen->getNome() : 'Ghost kitchen',
                'stato' => $prenotazione->getStato(),
            ];
        }, $future);
    }

    private function prenotazioniTabella(array $prenotazioni): array
    {
        return array_map(static function (EPrenotazioneGhostKitchen $prenotazione): array {
            $utente = FPersistentManager::loadUtente((int) $prenotazione->getIdRichiedente());
            $ghostKitchen = FPersistentManager::loadGhostKitchen((int) $prenotazione->getIdGhostKitchen());

            return [
                'prenotazione' => $prenotazione,
                'richiedenteId' => $prenotazione->getIdRichiedente(),
                'richiedenteNome' => $utente !== null ? trim($utente->getNome() . ' ' . $utente->getCognome()) : 'Richiedente #' . $prenotazione->getIdRichiedente(),
                'richiedenteEmail' => $utente !== null ? $utente->getEmail() : '',
                'richiedenteTelefono' => $utente !== null ? $utente->getTelefono() : '',
                'richiedenteLocalita' => $utente !== null ? $utente->getLocalita() : '',
                'ghostKitchen' => $ghostKitchen !== null ? $ghostKitchen->getNome() : 'Ghost kitchen #' . $prenotazione->getIdGhostKitchen(),
                'indirizzoGhostKitchen' => $ghostKitchen !== null ? trim($ghostKitchen->getIndirizzo() . ', ' . $ghostKitchen->getCitta()) : '',
                'dettagli' => $prenotazione->getTipoRichiedente() === EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CHEF ? 'Richiesta chef' : 'Richiesta cliente',
                'stato' => $prenotazione->getStato(),
            ];
        }, $prenotazioni);
    }

    private function richiestePrenotazione(array $prenotazioni): array
    {
        return array_map(static function (EPrenotazioneGhostKitchen $richiesta): array {
            $utente = FPersistentManager::loadUtente((int) $richiesta->getIdRichiedente());
            $ghostKitchen = FPersistentManager::loadGhostKitchen((int) $richiesta->getIdGhostKitchen());
            $nome = $utente !== null ? trim($utente->getNome() . ' ' . $utente->getCognome()) : 'Richiedente #' . $richiesta->getIdRichiedente();
            $iniziali = $utente !== null
                ? strtoupper(substr($utente->getNome(), 0, 1) . substr($utente->getCognome(), 0, 1))
                : 'GK';

            return [
                'prenotazione' => $richiesta,
                'nomeRichiedente' => $nome,
                'iniziali' => $iniziali !== '' ? $iniziali : 'GK',
                'stato' => $richiesta->getStato(),
                'servizio' => $ghostKitchen !== null ? $ghostKitchen->getNome() : 'Ghost kitchen',
                'descrizione' => 'Prenotazione spazio - ' . ($ghostKitchen !== null ? $ghostKitchen->getNome() : 'Ghost kitchen'),
                'indirizzo' => $ghostKitchen !== null ? trim($ghostKitchen->getIndirizzo() . ', ' . $ghostKitchen->getCitta()) : 'Indirizzo non disponibile',
                'messaggio' => $richiesta->getNote() !== '' ? $richiesta->getNote() : 'Nessun messaggio aggiuntivo.',
                'ricevuta' => self::tempoTrascorso($richiesta->getDataCreazione()),
            ];
        }, $prenotazioni);
    }

    private function statisticheGestore(array $prenotazioni, array $prenotazioniValide): array
    {
        $stati = [
            EPrenotazione::STATO_IN_ATTESA => 0,
            EPrenotazione::STATO_ACCETTATA => 0,
            EPrenotazione::STATO_PAGATA => 0,
            EPrenotazione::STATO_COMPLETATA => 0,
            EPrenotazione::STATO_RIFIUTATA => 0,
            EPrenotazione::STATO_CANCELLATA => 0,
        ];
        $durataTotale = 0.0;
        $importoMedio = 0.0;

        foreach ($prenotazioni as $prenotazione) {
            if (!$prenotazione instanceof EPrenotazioneGhostKitchen) {
                continue;
            }
            $stato = $prenotazione->getStato();
            if (array_key_exists($stato, $stati)) {
                $stati[$stato]++;
            }
        }

        foreach ($prenotazioniValide as $prenotazione) {
            if (!$prenotazione instanceof EPrenotazioneGhostKitchen) {
                continue;
            }
            $inizio = strtotime($prenotazione->getOraInizio());
            $fine = strtotime($prenotazione->getOraFine());
            $durataTotale += $inizio !== false && $fine !== false ? max(0, ($fine - $inizio) / 3600) : 0;
            $importoMedio += $prenotazione->getImportoTotale();
        }

        $numeroValide = count($prenotazioniValide);

        return [
            'stati' => $stati,
            'orePrenotate' => $durataTotale,
            'durataMedia' => $numeroValide > 0 ? $durataTotale / $numeroValide : 0.0,
            'importoMedio' => $numeroValide > 0 ? $importoMedio / $numeroValide : 0.0,
            'tassoConferma' => count($prenotazioni) > 0 ? ($numeroValide / count($prenotazioni)) * 100 : 0.0,
        ];
    }

    private static function tempoTrascorso(string $dataCreazione): string
    {
        $timestamp = strtotime($dataCreazione);
        if ($timestamp === false) {
            return 'Ricevuta di recente';
        }

        $diff = max(0, time() - $timestamp);
        if ($diff < 3600) {
            $minuti = max(1, (int) floor($diff / 60));
            return 'Ricevuta ' . $minuti . ' min fa';
        }
        if ($diff < 86400) {
            $ore = max(1, (int) floor($diff / 3600));
            return 'Ricevuta ' . $ore . ' ore fa';
        }

        $giorni = max(1, (int) floor($diff / 86400));
        return 'Ricevuta ' . $giorni . ' giorni fa';
    }
}

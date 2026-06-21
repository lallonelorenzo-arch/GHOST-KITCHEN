<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CDashboardChef
{
    public function visualizzaDashboardWeb(array $accesso, array $query = []): array
    {
        if (($accesso['isLogged'] ?? false) !== true || !in_array('chef', $accesso['ruoli'] ?? [], true)) {
            return ['messaggioAccesso' => 'Non hai permessi per questa sezione.'];
        }

        $idChef = (int) ($accesso['idUtente'] ?? 0);
        $prenotazioni = $idChef > 0 ? FPersistentManager::loadPrenotazioniRicevuteChef($idChef) : [];
        $inAttesa = array_values(array_filter($prenotazioni, static fn (EPrenotazioneChef $p): bool => $p->getStato() === EPrenotazione::STATO_IN_ATTESA));
        $accettate = array_values(array_filter($prenotazioni, static fn (EPrenotazioneChef $p): bool => in_array($p->getStato(), [EPrenotazione::STATO_ACCETTATA, EPrenotazione::STATO_PAGATA, EPrenotazione::STATO_COMPLETATA], true)));
        $fatturato = array_reduce($accettate, static fn (float $totale, EPrenotazioneChef $p): float => $totale + $p->getImportoTotale(), 0.0);
        $fatturatoMese = array_reduce($accettate, static function (float $totale, EPrenotazioneChef $p): float {
            return str_starts_with($p->getDataServizio(), date('Y-m')) ? $totale + $p->getImportoTotale() : $totale;
        }, 0.0);
        $ore = array_reduce($accettate, static function (float $totale, EPrenotazioneChef $p): float {
            $inizio = strtotime($p->getOraInizio());
            $fine = strtotime($p->getOraFine());
            return $totale + ($inizio !== false && $fine !== false ? max(0, ($fine - $inizio) / 3600) : 0);
        }, 0.0);

        $chef = FPersistentManager::loadChef($idChef);

        $tabAttiva = strtolower(trim((string) ($query['tab'] ?? 'panoramica')));
        if (!in_array($tabAttiva, ['panoramica', 'prenotazioni', 'richieste', 'disponibilita', 'profilo', 'recensioni'], true)) {
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
            'metriche' => [
                'prenotazioniTotali' => count($prenotazioni),
                'richiesteInAttesa' => count($inAttesa),
                'fatturato' => $fatturato,
                'fatturatoMese' => $fatturatoMese,
                'valutazioneMedia' => $chef !== null ? $chef->getValutazioneMedia() : 0,
                'oreLavorate' => $ore,
            ],
            'fatturatoMensile' => $this->fatturatoMensile($accettate),
            'prenotazioniSettimanali' => $this->prenotazioniSettimanali($prenotazioni),
            'prossimePrenotazioni' => $this->prossimePrenotazioni($prenotazioni),
            'prenotazioniTabella' => $this->prenotazioniTabella($prenotazioni),
            'richiestePrenotazione' => $this->richiestePrenotazione($prenotazioni),
            'profiloChef' => $chef,
            'mediaChef' => $idChef > 0 ? FPersistentManager::getMediaByOwner('chef', $idChef) : [],
            'menuChef' => FPersistentManager::loadMenuByChef($idChef),
            'piattiMenuChef' => $this->piattiMenuChef($idChef),
            'recensioniChef' => $this->recensioniChef($idChef),
            'certificazioniChef' => FPersistentManager::loadCertificazioniByChef($idChef),
            'disponibilitaChef' => FPersistentManager::loadDisponibilitaChef($idChef),
        ];
    }

    private function recensioniChef(int $idChef): array
    {
        return array_map(static function (ERecensioneChef $recensione): array {
            $autore = FPersistentManager::loadUtente((int) $recensione->getIdAutore());
            return [
                'recensione' => $recensione,
                'autore' => $autore !== null ? trim($autore->getNome() . ' ' . $autore->getCognome()) : 'Utente',
            ];
        }, FPersistentManager::loadRecensioniByChef($idChef));
    }

    private function piattiMenuChef(int $idChef): array
    {
        $result = [];
        foreach (FPersistentManager::loadMenuByChef($idChef) as $menu) {
            $result[(int) $menu->getIdMenu()] = FPersistentManager::loadPiattiByMenu((int) $menu->getIdMenu());
        }
        return $result;
    }

    private function fatturatoMensile(array $prenotazioni): array
    {
        $mesi = [1 => 'Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
        $punti = [];
        for ($i = 6; $i >= 0; $i--) {
            $timestamp = strtotime('-' . $i . ' months');
            $key = date('Y-m', $timestamp);
            $punti[$key] = [
                'label' => $mesi[(int) date('n', $timestamp)],
                'value' => 0.0,
            ];
        }

        foreach ($prenotazioni as $prenotazione) {
            if (!$prenotazione instanceof EPrenotazioneChef) {
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
            if (!$prenotazione instanceof EPrenotazioneChef) {
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
        $future = array_values(array_filter($prenotazioni, static function (EPrenotazioneChef $p): bool {
            return strtotime($p->getDataServizio() . ' ' . $p->getOraInizio()) >= strtotime('today')
                && in_array($p->getStato(), [EPrenotazione::STATO_IN_ATTESA, EPrenotazione::STATO_ACCETTATA, EPrenotazione::STATO_PAGATA], true);
        }));

        usort($future, static fn (EPrenotazioneChef $a, EPrenotazioneChef $b): int => strcmp($a->getDataServizio() . $a->getOraInizio(), $b->getDataServizio() . $b->getOraInizio()));
        $future = array_slice($future, 0, 3);

        return array_map(static function (EPrenotazioneChef $prenotazione): array {
            $utente = FPersistentManager::loadUtente((int) $prenotazione->getIdRichiedente());
            $nome = $utente !== null ? trim($utente->getNome() . ' ' . $utente->getCognome()) : 'Cliente #' . $prenotazione->getIdRichiedente();

            return [
                'prenotazione' => $prenotazione,
                'nome' => $nome,
                'descrizione' => $prenotazione->getRichiesteSpeciali() ?: 'Menu degustazione',
                'stato' => $prenotazione->getStato(),
            ];
        }, $future);
    }

    private function prenotazioniTabella(array $prenotazioni): array
    {
        return array_map(static function (EPrenotazioneChef $prenotazione): array {
            $utente = FPersistentManager::loadUtente((int) $prenotazione->getIdRichiedente());
            $menu = FPersistentManager::loadMenu((int) $prenotazione->getIdMenu());

            return [
                'prenotazione' => $prenotazione,
                'clienteId' => $prenotazione->getIdRichiedente(),
                'clienteNome' => $utente !== null ? trim($utente->getNome() . ' ' . $utente->getCognome()) : 'Cliente #' . $prenotazione->getIdRichiedente(),
                'clienteEmail' => $utente !== null ? $utente->getEmail() : '',
                'clienteTelefono' => $utente !== null ? $utente->getTelefono() : '',
                'clienteLocalita' => $utente !== null ? $utente->getLocalita() : '',
                'servizio' => $menu !== null ? $menu->getNome() : ($prenotazione->getRichiesteSpeciali() ?: 'Servizio chef'),
                'dettagli' => $prenotazione->getNumeroPersone() . ' ospiti',
                'stato' => $prenotazione->getStato(),
            ];
        }, $prenotazioni);
    }

    private function richiestePrenotazione(array $richieste): array
    {
        return array_map(static function (EPrenotazioneChef $richiesta): array {
            $utente = FPersistentManager::loadUtente((int) $richiesta->getIdRichiedente());
            $menu = FPersistentManager::loadMenu((int) $richiesta->getIdMenu());
            $nome = $utente !== null ? trim($utente->getNome() . ' ' . $utente->getCognome()) : 'Cliente #' . $richiesta->getIdRichiedente();
            $iniziali = $utente !== null
                ? strtoupper(substr($utente->getNome(), 0, 1) . substr($utente->getCognome(), 0, 1))
                : 'CL';
            $menuNome = $menu !== null ? $menu->getNome() : 'Menu personalizzato';

            return [
                'prenotazione' => $richiesta,
                'nomeRichiedente' => $nome,
                'iniziali' => $iniziali !== '' ? $iniziali : 'CL',
                'stato' => $richiesta->getStato(),
                'servizio' => $menuNome,
                'descrizione' => 'Cena privata - ' . $menuNome,
                'indirizzo' => $richiesta->getIndirizzoServizio(),
                'messaggio' => $richiesta->getNote() !== '' ? $richiesta->getNote() : ($richiesta->getRichiesteSpeciali() ?: 'Nessun messaggio aggiuntivo.'),
                'ricevuta' => self::tempoTrascorso($richiesta->getDataCreazione()),
            ];
        }, $richieste);
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

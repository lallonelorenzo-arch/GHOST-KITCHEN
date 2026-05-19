# UC13 - Moderazione Contenuti

## Attori
- Amministratore di sistema

## Scenario principale di successo
1. L'admin visualizza contenuti o segnalazioni da moderare.
2. Il sistema mostra elenco di segnalazioni aperte/in valutazione e contenuti segnalati.
3. L'admin prende in carico una segnalazione.
4. L'admin decide un'azione di moderazione.
5. Il sistema aggiorna gli stati.
6. Il sistema restituisce conferma.

## SSD testuale
- Admin -> Sistema: visualizzaContenutiDaModerare()
- Sistema -> Admin: elencoContenuti
- Admin -> Sistema: prendiInCaricoSegnalazione(idSegnalazione)
- Sistema -> Admin: segnalazioneInValutazione
- Admin -> Sistema: moderaRecensione(idRecensione, azione)
- Sistema -> Admin: recensioneModerata
- Admin -> Sistema: moderaProfilo(idUtente, azione)
- Sistema -> Admin: profiloModerato
- Admin -> Sistema: chiudiSegnalazione(idSegnalazione, esito, noteAdmin)
- Sistema -> Admin: segnalazioneChiusa

## Operazioni di sistema
- visualizzaContenutiDaModerare()
- prendiInCaricoSegnalazione(idSegnalazione)
- moderaRecensione(idRecensione, azione)
- moderaProfilo(idUtente, azione)
- chiudiSegnalazione(idSegnalazione, esito, noteAdmin)

## Classe Control
- CModerazione

## Metodi Control
- public static function visualizzaContenutiDaModerare(): array
- public static function prendiInCaricoSegnalazione(int $idSegnalazione): array
- public static function moderaRecensione(int $idRecensione, string $azione): array
- public static function moderaProfilo(int $idUtente, string $azione): array
- public static function chiudiSegnalazione(int $idSegnalazione, string $esito, string $noteAdmin = ''): array

## URL associate
- /Moderazione/visualizzaContenutiDaModerare
- /Moderazione/prendiInCaricoSegnalazione
- /Moderazione/moderaRecensione
- /Moderazione/moderaProfilo
- /Moderazione/chiudiSegnalazione

## Entity coinvolte
- ESegnalazione
- ERecensione
- ERecensioneChef
- ERecensioneGhostKitchen
- EUtente
- EChef
- EGhostKitchen
- EAmministratore

## Servizi richiesti a FPersistentManager fittizio
- loadSegnalazioniDaModerare()
- loadSegnalazione(idSegnalazione)
- updateSegnalazione(ESegnalazione $segnalazione)
- loadRecensione(idRecensione)
- updateRecensione(ERecensione $recensione)
- loadUtente(idUtente)
- updateUtente(EUtente $utente)

## Note progettuali
La moderazione non cancella fisicamente contenuti. Le recensioni usano nascondi(), rimuovi(), ripristina(); i profili usano setStato(); le segnalazioni usano prendiInCarico(), risolvi(), archivia(), respingi() dove compatibile con lo stato corrente.

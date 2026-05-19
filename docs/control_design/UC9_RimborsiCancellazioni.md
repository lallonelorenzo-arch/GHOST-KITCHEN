# UC9 - Gestione Rimborsi e Cancellazioni

## Attori
- Cliente registrato
- Chef professionista
- Gestore Ghost Kitchen
- Amministratore di sistema, per casi critici

## Scenario principale di successo
1. L'attore avvia la richiesta di cancellazione di una prenotazione.
2. Il sistema mostra prenotazione, pagamento associato e rimborso stimato.
3. L'attore inserisce il motivo.
4. Il sistema calcola penale e importo rimborsabile.
5. Il sistema registra la cancellazione.
6. Se previsto, il sistema crea una richiesta di rimborso.
7. Il sistema restituisce riepilogo cancellazione e rimborso.

## SSD testuale
- Attore -> Sistema: avviaCancellazione(tipoPrenotazione, idPrenotazione, idRichiedente)
- Sistema -> Attore: datiCancellazione
- Attore -> Sistema: calcolaRimborsoStimato(tipoPrenotazione, idPrenotazione)
- Sistema -> Attore: rimborsoStimato
- Attore -> Sistema: confermaCancellazione(datiCancellazione)
- Sistema -> Attore: cancellazioneRegistrata

## Operazioni di sistema
- avviaCancellazione(tipoPrenotazione, idPrenotazione, idRichiedente)
- calcolaRimborsoStimato(tipoPrenotazione, idPrenotazione)
- confermaCancellazione(datiCancellazione)

## Classe Control
- CCancellazioneRimborso

## Metodi Control
- public static function avviaCancellazione(string $tipoPrenotazione, int $idPrenotazione, int $idRichiedente): array
- public static function calcolaRimborsoStimato(string $tipoPrenotazione, int $idPrenotazione): array
- public static function confermaCancellazione(array $datiCancellazione): array

## URL associate
- /CancellazioneRimborso/avviaCancellazione
- /CancellazioneRimborso/calcolaRimborsoStimato
- /CancellazioneRimborso/confermaCancellazione

## Entity coinvolte
- ECancellazione
- ERimborso
- EPagamento
- EPrenotazioneChef
- EPrenotazioneGhostKitchen

## Servizi richiesti a FPersistentManager fittizio
- loadPrenotazioneChef(idPrenotazione)
- loadPrenotazioneGhostKitchen(idPrenotazione)
- loadPagamentoByPrenotazione(tipoPrenotazione, idPrenotazione)
- calcolaRimborsoStimato(tipoPrenotazione, idPrenotazione)
- storeCancellazione(ECancellazione $cancellazione)
- storeRimborso(ERimborso $rimborso)
- updatePrenotazioneChef(EPrenotazioneChef $prenotazione)
- updatePrenotazioneGhostKitchen(EPrenotazioneGhostKitchen $prenotazione)
- updatePagamento(EPagamento $pagamento)

## Note progettuali
ECancellazione ed ERimborso restano separati: la prima riguarda la prenotazione, il secondo il pagamento. La policy e' simulata in FPersistentManager con tre criteri fittizi: largo anticipo, cancellazione tardiva, danni ghost kitchen. Non viene introdotta EPolicyCancellazione.

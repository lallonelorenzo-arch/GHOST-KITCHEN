# UC5 - Prenotazione Slot Ghost Kitchen

## Attori
- Cliente registrato
- Chef professionista

## Scenario principale di successo
1. L'attore avvia la prenotazione della ghost kitchen.
2. Il sistema mostra dati ghost kitchen e disponibilita.
3. L'attore seleziona uno slot.
4. L'attore inserisce note/richieste.
5. Il sistema verifica che lo slot sia libero.
6. Il sistema crea prenotazione ghost kitchen in stato in_attesa.
7. Il sistema restituisce riepilogo e azione pagamento/attesa.

## SSD testuale
- Attore -> Sistema: avviaPrenotazioneGhostKitchen(idRichiedente, tipoRichiedente, idGhostKitchen)
- Sistema -> Attore: datiPrenotazioneGhostKitchen
- Attore -> Sistema: selezionaDisponibilitaGhostKitchen(idDisponibilitaGhostKitchen)
- Sistema -> Attore: disponibilitaSelezionata
- Attore -> Sistema: inserisciDatiPrenotazioneGhostKitchen(datiPrenotazione)
- Sistema -> Attore: riepilogoPrenotazioneGhostKitchen
- Attore -> Sistema: confermaPrenotazioneGhostKitchen(datiConferma)
- Sistema -> Attore: prenotazioneCreata

## Operazioni di sistema
- avviaPrenotazioneGhostKitchen(idRichiedente, tipoRichiedente, idGhostKitchen)
- selezionaDisponibilitaGhostKitchen(idDisponibilitaGhostKitchen)
- inserisciDatiPrenotazioneGhostKitchen(datiPrenotazione)
- confermaPrenotazioneGhostKitchen(datiConferma)

## Classe Control
- CPrenotazioneGhostKitchen

## Metodi Control
- public static function avviaPrenotazioneGhostKitchen(int $idRichiedente, string $tipoRichiedente, int $idGhostKitchen): array
- public static function selezionaDisponibilitaGhostKitchen(int $idDisponibilitaGhostKitchen): array
- public static function inserisciDatiPrenotazioneGhostKitchen(array $datiPrenotazione): array
- public static function confermaPrenotazioneGhostKitchen(array $datiConferma): array

## URL associate
- /PrenotazioneGhostKitchen/avviaPrenotazioneGhostKitchen
- /PrenotazioneGhostKitchen/selezionaDisponibilitaGhostKitchen
- /PrenotazioneGhostKitchen/inserisciDatiPrenotazioneGhostKitchen
- /PrenotazioneGhostKitchen/confermaPrenotazioneGhostKitchen

## Entity coinvolte
- ECliente
- EChef
- EGhostKitchen
- EDisponibilitaGhostKitchen
- EPrenotazioneGhostKitchen

## Servizi richiesti a FPersistentManager fittizio
- loadUtente(idUtente)
- loadGhostKitchen(idGhostKitchen)
- loadDisponibilitaGhostKitchen(idGhostKitchen)
- loadDisponibilitaGhostKitchenById(idDisponibilitaGhostKitchen)
- verificaDisponibilitaGhostKitchen(idGhostKitchen, data, oraInizio, oraFine)
- storePrenotazioneGhostKitchen(EPrenotazioneGhostKitchen $prenotazione)

## Note progettuali
- Uso di idRichiedente + tipoRichiedente coerente con Entity.
- Nessuna sessione/DB reale.

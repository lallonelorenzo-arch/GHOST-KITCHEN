# UC4 - Prenotazione Chef a Domicilio

## Attori
- Cliente registrato

## Scenario principale di successo
1. Il cliente avvia la prenotazione di uno chef.
2. Il sistema mostra chef, menu e disponibilita.
3. Il cliente seleziona un menu.
4. Il cliente inserisce data, ora inizio/fine, indirizzo, numero persone, richieste speciali.
5. Il sistema verifica disponibilita e coerenza dati.
6. Il sistema crea la prenotazione chef in stato in_attesa.
7. Il sistema restituisce riepilogo e azione di pagamento/attesa accettazione.

## SSD testuale
- Cliente -> Sistema: avviaPrenotazioneChef(idCliente, idChef)
- Sistema -> Cliente: datiPrenotazioneChef
- Cliente -> Sistema: selezionaMenu(idMenu)
- Sistema -> Cliente: menuSelezionato
- Cliente -> Sistema: inserisciDatiPrenotazioneChef(datiPrenotazione)
- Sistema -> Cliente: riepilogoPrenotazioneChef
- Cliente -> Sistema: confermaPrenotazioneChef(datiConferma)
- Sistema -> Cliente: prenotazioneCreata

## Operazioni di sistema
- avviaPrenotazioneChef(idCliente, idChef)
- selezionaMenu(idMenu)
- inserisciDatiPrenotazioneChef(datiPrenotazione)
- confermaPrenotazioneChef(datiConferma)

## Classe Control
- CPrenotazioneChef

## Metodi Control
- public static function avviaPrenotazioneChef(int $idCliente, int $idChef): array
- public static function selezionaMenu(int $idMenu): array
- public static function inserisciDatiPrenotazioneChef(array $datiPrenotazione): array
- public static function confermaPrenotazioneChef(array $datiConferma): array

## URL associate
- /PrenotazioneChef/avviaPrenotazioneChef
- /PrenotazioneChef/selezionaMenu
- /PrenotazioneChef/inserisciDatiPrenotazioneChef
- /PrenotazioneChef/confermaPrenotazioneChef

## Entity coinvolte
- ECliente
- EChef
- EMenu
- EPiatto
- EDisponibilitaChef
- EPrenotazioneChef

## Servizi richiesti a FPersistentManager fittizio
- loadCliente(idCliente)
- loadChef(idChef)
- loadMenuByChef(idChef)
- loadMenu(idMenu)
- loadPiattiByMenu(idMenu)
- loadDisponibilitaChef(idChef)
- verificaDisponibilitaChef(idChef, data, oraInizio, oraFine)
- storePrenotazioneChef(EPrenotazioneChef $prenotazione)

## Note progettuali
- idCliente passato come input Control (nessuna sessione reale).
- Pagamento solo avviabile, non completato in UC4.

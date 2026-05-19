# UC7 - Accettazione/Rifiuto Richiesta

## Attori
- Chef professionista
- Gestore Ghost Kitchen

## Scenario principale di successo
1. L'attore visualizza richieste pendenti.
2. Il sistema mostra richieste compatibili con il ruolo.
3. L'attore seleziona una richiesta.
4. L'attore accetta o rifiuta.
5. Il sistema aggiorna lo stato prenotazione.
6. Il sistema restituisce conferma e lista aggiornata.

## SSD testuale
- Attore -> Sistema: visualizzaRichieste(tipoOwner, idOwner)
- Sistema -> Attore: elencoRichieste
- Attore -> Sistema: accettaRichiesta(tipoPrenotazione, idPrenotazione)
- Sistema -> Attore: richiestaAccettata
- Attore -> Sistema: rifiutaRichiesta(tipoPrenotazione, idPrenotazione, motivo)
- Sistema -> Attore: richiestaRifiutata

## Operazioni di sistema
- visualizzaRichieste(tipoOwner, idOwner)
- accettaRichiesta(tipoPrenotazione, idPrenotazione)
- rifiutaRichiesta(tipoPrenotazione, idPrenotazione, motivo)

## Classe Control
- CGestioneRichieste

## Metodi Control
- public static function visualizzaRichieste(string $tipoOwner, int $idOwner): array
- public static function accettaRichiesta(string $tipoPrenotazione, int $idPrenotazione): array
- public static function rifiutaRichiesta(string $tipoPrenotazione, int $idPrenotazione, string $motivo = ''): array

## URL associate
- /GestioneRichieste/visualizzaRichieste
- /GestioneRichieste/accettaRichiesta
- /GestioneRichieste/rifiutaRichiesta

## Entity coinvolte
- EPrenotazioneChef
- EPrenotazioneGhostKitchen
- EChef
- EGestore
- EGhostKitchen

## Servizi richiesti a FPersistentManager fittizio
- loadRichiestePrenotazioneChef(idChef)
- loadRichiestePrenotazioneGhostKitchenByGestore(idGestore)
- loadPrenotazioneChef(idPrenotazione)
- loadPrenotazioneGhostKitchen(idPrenotazione)
- updatePrenotazioneChef(EPrenotazioneChef $prenotazione)
- updatePrenotazioneGhostKitchen(EPrenotazioneGhostKitchen $prenotazione)

## Note progettuali
- Unica classe Control per entrambi i ruoli.
- Stato prenotazione gestito da metodi di dominio `accetta()` e `rifiuta()`.

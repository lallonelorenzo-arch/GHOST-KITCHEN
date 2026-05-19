# UC10 - Rilascio Recensione

## Attori
- Cliente registrato
- Chef professionista, solo per recensione ghost kitchen se ha prenotato uno slot

## Scenario principale di successo
1. L'attore avvia il rilascio recensione per una prenotazione completata.
2. Il sistema verifica che la prenotazione sia recensibile.
3. Il sistema mostra i dati necessari alla recensione.
4. L'attore inserisce punteggio e commento.
5. Il sistema crea la recensione.
6. Il sistema aggiorna la valutazione media del target con logica fittizia.
7. Il sistema restituisce conferma.

## SSD testuale
- Attore -> Sistema: avviaRecensione(tipoTarget, idPrenotazione, idAutore)
- Sistema -> Attore: datiRecensione
- Attore -> Sistema: pubblicaRecensione(datiRecensione)
- Sistema -> Attore: recensionePubblicata

## Operazioni di sistema
- avviaRecensione(tipoTarget, idPrenotazione, idAutore)
- pubblicaRecensione(datiRecensione)

## Classe Control
- CRecensione

## Metodi Control
- public static function avviaRecensione(string $tipoTarget, int $idPrenotazione, int $idAutore): array
- public static function pubblicaRecensione(array $datiRecensione): array

## URL associate
- /Recensione/avviaRecensione
- /Recensione/pubblicaRecensione

## Entity coinvolte
- ERecensione
- ERecensioneChef
- ERecensioneGhostKitchen
- EPrenotazioneChef
- EPrenotazioneGhostKitchen
- EChef
- EGhostKitchen

## Servizi richiesti a FPersistentManager fittizio
- verificaPrenotazioneRecensibile(tipoTarget, idPrenotazione, idAutore)
- loadPrenotazioneChef(idPrenotazione)
- loadPrenotazioneGhostKitchen(idPrenotazione)
- storeRecensioneChef(ERecensioneChef $recensione)
- storeRecensioneGhostKitchen(ERecensioneGhostKitchen $recensione)
- aggiornaValutazioneChef(idChef)
- aggiornaValutazioneGhostKitchen(idGhostKitchen)

## Note progettuali
I valori ammessi per tipoTarget sono chef e ghost_kitchen. La verifica di prenotazione completata e associata all'autore e' fittizia e non usa DB.

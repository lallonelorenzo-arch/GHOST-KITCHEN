# UC2 — Visualizzazione Dettaglio Chef

## Attori

- Utente non registrato
- Cliente registrato
- Chef professionista

## Scenario principale di successo

1. L’utente seleziona uno chef dalla lista risultati.
2. Il sistema recupera le informazioni pubbliche dello chef.
3. Il sistema mostra nome, cognome, biografia, specializzazione, tipologia cucina, prezzo base, anni esperienza, valutazione media e foto profilo.
4. Il sistema mostra i menu associati allo chef.
5. Per ogni menu, il sistema mostra i piatti associati.
6. Per ogni piatto, il sistema mostra nome, categoria, descrizione, ingredienti, allergeni, prezzo supplemento ed eventuali foto.
7. Il sistema mostra le certificazioni approvate dello chef.
8. L’utente può scegliere di procedere alla prenotazione, che sarà UC4.

## SSD testuale

Attore → Sistema: visualizzaDettaglioChef(idChef)

Sistema → Attore: dettaglioChef

## Operazioni di sistema

- `visualizzaDettaglioChef(idChef)`

## Classe Control

- `CDettaglioChef`

## Metodi Control

- `public static function visualizzaDettaglioChef(int $idChef): array`

## URL associate

- `/DettaglioChef/visualizzaDettaglioChef`

## Entity coinvolte

- `EChef`
- `EMenu`
- `EPiatto`
- `EMedia`
- `ECertificazione`

## Servizi richiesti al PersistentManager fittizio

- `loadChef(idChef)`
- `loadMenuByChef(idChef)`
- `loadPiattiByMenu(idMenu)`
- `loadCertificazioniApprovateByChef(idChef)`
- `getMediaPrincipale(tipoOwner, idOwner)`
- `getMediaByOwner(tipoOwner, idOwner)`

## Note progettuali

- UC2 non effettua prenotazioni.
- UC2 non modifica dati.
- UC2 non mostra Foundation o DB nello SSD.
- La selezione del pulsante `prenota` sarà gestita da UC4.
- Le foto dei piatti vengono recuperate tramite `EMedia` e non tramite un attributo foto in `EPiatto`.
- Il controller restituisce una struttura dati pronta per essere consumata da una futura View senza stampare HTML.

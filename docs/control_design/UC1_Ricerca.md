# UC1 — Navigazione e Ricerca

## Attori

- Utente non registrato
- Cliente registrato
- Chef professionista

## Scenario principale di successo

1. L’utente accede alla pagina di ricerca.
2. Il sistema mostra una form con filtri per località, tipologia cucina, budget massimo, valutazione minima e tipo risultato.
3. L’utente inserisce i filtri e invia la ricerca.
4. Il sistema cerca chef e/o ghost kitchen compatibili.
5. Il sistema mostra una lista di risultati.
6. Per ogni chef mostra dati essenziali: nome, specializzazione/tipologia cucina, valutazione media, prezzo base.
7. Per ogni ghost kitchen mostra dati essenziali: nome, città/indirizzo sintetico, prezzo orario, capienza, valutazione media.
8. L’utente può selezionare un risultato; se è uno chef, si passa a UC2, se è una ghost kitchen, si passa a UC3.

## SSD testuale

Attore → Sistema: avviaRicerca()

Sistema → Attore: formRicerca

Attore → Sistema: cercaOfferte(localita, tipologiaCucina, budgetMax, valutazioneMin, tipoRisultato)

Sistema → Attore: listaRisultati

## Operazioni di sistema

- `avviaRicerca()`
- `cercaOfferte(localita, tipologiaCucina, budgetMax, valutazioneMin, tipoRisultato)`

## Classe Control

- `CRicerca`

## Metodi Control

- `public static function avviaRicerca(): array`
- `public static function cercaOfferte(array $filtri): array`

## URL associate

- `/Ricerca/avviaRicerca`
- `/Ricerca/cercaOfferte`

## Entity coinvolte

- `EChef`
- `EGhostKitchen`
- `EMedia`
- `EMenu`
- `EAttrezzatura`

## Servizi richiesti al PersistentManager fittizio

- `cercaChef(localita, tipologiaCucina, budgetMax, valutazioneMin)`
- `cercaGhostKitchen(localita, budgetMax, valutazioneMin)`
- `getMediaPrincipale(tipoOwner, idOwner)`

## Note progettuali

- UC1 non deve implementare il dettaglio completo di chef o ghost kitchen.
- La selezione di un risultato è solo un ponte verso UC2 e UC3.
- La tipologia cucina si applica principalmente agli chef.
- Nello SSD non compaiono Entity, Foundation o dettagli di database.
- `CRicerca` normalizza i filtri prima di delegare la ricerca a `FPersistentManager`.
- `FPersistentManager` è solo un supporto fittizio per testare il collegamento tra Control ed Entity in assenza di DB reale.
- In questa fase `CRicerca` restituisce array descrittivi e risultati, senza redirect e senza View definitive.

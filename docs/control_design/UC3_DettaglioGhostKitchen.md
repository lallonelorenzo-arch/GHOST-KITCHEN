# UC3 - Visualizzazione Dettaglio Ghost Kitchen

## Attori
- Utente non registrato
- Cliente registrato
- Chef professionista

## Scenario principale di successo
1. L'utente seleziona una ghost kitchen dalla lista risultati.
2. Il sistema recupera le informazioni pubbliche della ghost kitchen.
3. Il sistema mostra nome, descrizione, indirizzo/citta, CAP, prezzo orario, capienza, mq, valutazione media.
4. Il sistema mostra attrezzature disponibili.
5. Il sistema mostra foto ambiente e planimetria tramite EMedia.
6. Il sistema mostra eventuali slot/disponibilita pubbliche.
7. L'utente puo procedere con UC5.

## SSD testuale
- Attore -> Sistema: visualizzaDettaglioGhostKitchen(idGhostKitchen)
- Sistema -> Attore: dettaglioGhostKitchen

## Operazioni di sistema
- visualizzaDettaglioGhostKitchen(idGhostKitchen)

## Classe Control
- CDettaglioGhostKitchen

## Metodi Control
- public static function visualizzaDettaglioGhostKitchen(int $idGhostKitchen): array

## URL associate
- /DettaglioGhostKitchen/visualizzaDettaglioGhostKitchen

## Entity coinvolte
- EGhostKitchen
- EAttrezzatura
- EDisponibilitaGhostKitchen
- EMedia

## Servizi richiesti a FPersistentManager fittizio
- loadGhostKitchen(idGhostKitchen)
- loadAttrezzatureByGhostKitchen(idGhostKitchen)
- loadDisponibilitaGhostKitchen(idGhostKitchen)
- getMediaPrincipale(tipoOwner, idOwner)
- getMediaByOwner(tipoOwner, idOwner)

## Note progettuali
- Nessun uso di DB o sessione reale.
- Output Control in array per test e futura View.

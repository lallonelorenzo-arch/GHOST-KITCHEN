# UC6 - Gestione Calendario

## Attori
- Chef professionista
- Gestore Ghost Kitchen

## Scenario principale di successo
1. L'attore accede alla gestione calendario.
2. Il sistema mostra le disponibilita correnti.
3. L'attore aggiunge una disponibilita.
4. Il sistema valida data, orari e owner.
5. Il sistema salva la disponibilita.
6. L'attore puo bloccare/liberare disponibilita non occupate.
7. Il sistema restituisce calendario aggiornato.

## SSD testuale
- Attore -> Sistema: visualizzaCalendario(tipoOwner, idOwner)
- Sistema -> Attore: calendario
- Attore -> Sistema: aggiungiDisponibilita(tipoOwner, idOwner, data, oraInizio, oraFine)
- Sistema -> Attore: disponibilitaCreata
- Attore -> Sistema: bloccaDisponibilita(tipoOwner, idDisponibilita)
- Sistema -> Attore: disponibilitaBloccata
- Attore -> Sistema: liberaDisponibilita(tipoOwner, idDisponibilita)
- Sistema -> Attore: disponibilitaLiberata

## Operazioni di sistema
- visualizzaCalendario(tipoOwner, idOwner)
- aggiungiDisponibilita(tipoOwner, idOwner, data, oraInizio, oraFine)
- bloccaDisponibilita(tipoOwner, idDisponibilita)
- liberaDisponibilita(tipoOwner, idDisponibilita)

## Classe Control
- CGestioneDisponibilita

## Metodi Control
- public static function visualizzaCalendario(string $tipoOwner, int $idOwner): array
- public static function aggiungiDisponibilita(string $tipoOwner, int $idOwner, string $data, string $oraInizio, string $oraFine): array
- public static function bloccaDisponibilita(string $tipoOwner, int $idDisponibilita): array
- public static function liberaDisponibilita(string $tipoOwner, int $idDisponibilita): array

## URL associate
- /GestioneDisponibilita/visualizzaCalendario
- /GestioneDisponibilita/aggiungiDisponibilita
- /GestioneDisponibilita/bloccaDisponibilita
- /GestioneDisponibilita/liberaDisponibilita

## Entity coinvolte
- EDisponibilitaChef
- EDisponibilitaGhostKitchen
- EChef
- EGhostKitchen

## Servizi richiesti a FPersistentManager fittizio
- loadDisponibilitaChef(idChef)
- loadDisponibilitaGhostKitchen(idGhostKitchen)
- storeDisponibilitaChef(EDisponibilitaChef $disponibilita)
- storeDisponibilitaGhostKitchen(EDisponibilitaGhostKitchen $disponibilita)
- loadDisponibilitaChefById(idDisponibilitaChef)
- loadDisponibilitaGhostKitchenById(idDisponibilitaGhostKitchen)
- updateDisponibilitaChef(EDisponibilitaChef $disponibilita)
- updateDisponibilitaGhostKitchen(EDisponibilitaGhostKitchen $disponibilita)

## Note progettuali
- Unica Control per owner `chef` e `ghost_kitchen`.
- Validazioni basiche in Control con eccezioni.

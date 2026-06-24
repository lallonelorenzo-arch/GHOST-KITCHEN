# Report implementazione UC4-UC7

## UC coperti

- UC4: prenotazione chef.
- UC5: prenotazione ghost kitchen.
- UC6: gestione disponibilita/calendario.
- UC7: gestione richieste.

## Control coinvolti

- `CPrenotazioneChef`
- `CPrenotazioneGhostKitchen`
- `CGestioneDisponibilita`
- `CGestioneRichieste`
- `CDashboardChef`
- `CDashboardGestore`

## Route principali

- `GET /chef/{idChef}`: mostra dettaglio chef e form di prenotazione.
- `POST /prenotazione/chef/{idChef}`: conferma prenotazione chef.
- `GET /ghost-kitchen/{idGhostKitchen}`: mostra dettaglio ghost kitchen e form di prenotazione.
- `POST /prenotazione/ghost-kitchen/{idGhostKitchen}`: conferma prenotazione ghost kitchen.
- `GET /disponibilita`: redirect alla dashboard professionale con tab disponibilita.
- `GET /richieste`: redirect alla dashboard professionale con tab richieste.
- `POST /disponibilita/chef`: aggiunge disponibilita chef.
- `POST /disponibilita/ghost-kitchen`: aggiunge disponibilita ghost kitchen.
- `POST /richieste/chef/{idPrenotazione}/accetta`
- `POST /richieste/chef/{idPrenotazione}/rifiuta`
- `POST /richieste/ghost-kitchen/{idPrenotazione}/accetta`
- `POST /richieste/ghost-kitchen/{idPrenotazione}/rifiuta`

## View coinvolte

- `dettaglio_chef.php`
- `dettaglio_ghost_kitchen.php`
- `dashboard_chef.php`
- `dashboard_gestore.php`
- `richiesta_esito.php`
- partial `booking_calendar.php`
- partial `dashboard_availability.php`

## Persistenza

Attraverso `FPersistentManager` vengono usati caricamenti e persistenze per chef, ghost kitchen, menu, disponibilita, prenotazioni e richieste.

## Stato finale

- Le prenotazioni vengono create da dettaglio chef o dettaglio ghost kitchen.
- Le disponibilita vengono gestite dalle dashboard professionali.
- Le richieste vengono accettate o rifiutate dalle dashboard chef/gestore.
- Le route sono protette in base al ruolo.
- Le View non leggono direttamente `$_GET`, `$_POST` o `$_SESSION`.

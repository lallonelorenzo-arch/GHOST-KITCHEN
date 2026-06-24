# Integrazione View - stato finale

Questo documento riassume lo stato finale dell'integrazione View. L'applicazione non serve una SPA React separata: l'interfaccia utente e' integrata in template PHP coerenti con l'architettura ECFV/MVC custom.

## Scelte finali

- `index.php` resta il punto di ingresso unico.
- `CFrontController` centralizza routing, normalizzazione input, CSRF e controllo ruoli.
- Le View sono template PHP in `View/templates/`.
- Gli asset statici sono in `public/assets/`.
- Le View ricevono dati preparati dai Control e non eseguono query SQL.
- La sessione e' gestita tramite `FSession`.

## Template principali

- `layout.php`
- `home.php`
- `lista_chef.php`
- `lista_ghost_kitchen.php`
- `dettaglio_chef.php`
- `dettaglio_ghost_kitchen.php`
- `login.php`
- `registrazione.php`
- `profilo.php`
- `prenotazioni.php`
- `dashboard.php`
- `dashboard_chef.php`
- `dashboard_gestore.php`
- `moderazione.php`
- `utenti.php`
- `certificazioni.php`
- `pagamento.php`
- `recensione.php`
- `segnalazione.php`
- `richiesta_esito.php`

## Vincoli architetturali rispettati

- Nessun SQL nelle View.
- Nessun accesso diretto a `$_SESSION` nelle View.
- Nessun accesso diretto a `$_GET` o `$_POST` nelle View.
- Routing con whitelist nel Front Controller.
- Output dinamico escapato tramite helper.
- Form POST protetti da token CSRF.

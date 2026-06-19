# Report implementazione UC8-UC14

## Obiettivo

Collegare alla View PHP i Control gia presenti per gli ultimi UC, mantenendo l'architettura ECFV:

- Entity: regole e stato degli oggetti di dominio.
- Control: coordinamento dei casi d'uso e wrapper web.
- Foundation: query, persistenza, sessione.
- View: template PHP/HTML/CSS e raccolta input.

Non e stato introdotto React parallelo. La UI Ghost Kitchen/Figma resta un riferimento grafico convertito in template PHP.

## UC collegati

### UC8 - Pagamento

- Control: `CPagamento`
- Template: `View/templates/pagamento.php`
- Route:
  - `GET /pagamento/chef/{idPrenotazione}`
  - `POST /pagamento/chef/{idPrenotazione}`
  - `GET /pagamento/ghost-kitchen/{idPrenotazione}`
  - `POST /pagamento/ghost-kitchen/{idPrenotazione}`

Controlli aggiunti:

- utente loggato;
- prenotazione collegata al richiedente;
- simulazione positiva su dati di utente, prenotazione e importo;
- gestione fallimento salvataggio pagamento.

### UC10 - Recensioni

- Control: `CRecensione`
- Template: `View/templates/recensione.php`
- Route:
  - `GET /recensione/chef/{idPrenotazione}`
  - `POST /recensione/chef/{idPrenotazione}`
  - `GET /recensione/ghost-kitchen/{idPrenotazione}`
  - `POST /recensione/ghost-kitchen/{idPrenotazione}`

Controlli gia rispettati dal dominio/Control:

- recensione ammessa solo per prenotazione completata;
- autore collegato alla prenotazione;
- punteggio 1-5;
- commento obbligatorio.

### UC11 - Segnalazioni

- Control: `CSegnalazione`
- Template: `View/templates/segnalazione.php`
- Route:
  - `GET /segnalazione/{tipoTarget}/{idTarget}`
  - `POST /segnalazione/{tipoTarget}/{idTarget}`

Target ammessi:

- `utente`
- `chef`
- `ghost-kitchen`
- `recensione`
- `menu`

Controlli aggiunti:

- utente loggato;
- target esistente;
- motivo obbligatorio;
- gestione fallimento salvataggio.

### UC12 - Validazione certificazioni

- Control: `CValidazioneCertificazioni`
- Template:
  - `View/templates/certificazioni.php`
  - `View/templates/certificazione_dettaglio.php`
- Route:
  - `GET /certificazioni`
  - `GET /certificazioni/{idCertificazione}`
  - `POST /certificazioni/{idCertificazione}/approva`
  - `POST /certificazioni/{idCertificazione}/rifiuta`

Controlli aggiunti:

- accesso solo admin;
- note admin;
- gestione fallimento update certificazione.

### UC13 - Moderazione

- Control: `CModerazione`
- Template: `View/templates/moderazione.php`
- Route:
  - `GET /moderazione`
  - `POST /moderazione/segnalazione/{id}/prendi`
  - `POST /moderazione/segnalazione/{id}/chiudi`
  - `POST /moderazione/recensione/{id}/{azione}`
  - `POST /moderazione/profilo/{id}/{azione}`

Azioni recensione:

- `nascondi`
- `rimuovi`
- `ripristina`

Azioni profilo:

- `sospendi`
- `banna`
- `riattiva`

Controlli aggiunti:

- accesso solo admin;
- esiti puliti con `richiesta_esito.php`;
- errori tecnici loggati e messaggio generico in View.

### UC14 - Dashboard statistiche

- Control: `CDashboardStatistiche`
- Template: `View/templates/dashboard.php`
- Route:
  - `GET /dashboard`

Dati mostrati:

- prenotazioni totali/chef/ghost kitchen;
- volume pagamenti;
- recensioni;
- segnalazioni aperte;
- certificazioni in attesa;
- ghost kitchen piu prenotate.

## File principali modificati

- `Control/CFrontController.php`: aggiunte route whitelist UC8-UC14.
- `Control/CPagamento.php`: wrapper web e controlli proprieta.
- `Control/CRecensione.php`: wrapper web e gestione errori non recensibile.
- `Control/CSegnalazione.php`: wrapper web e controllo target.
- `Control/CModerazione.php`: wrapper web admin-only.
- `Control/CValidazioneCertificazioni.php`: wrapper web admin-only.
- `Control/CDashboardStatistiche.php`: wrapper web admin-only.
- `View/templates/*.php`: template UC8-UC14.
- `public/assets/css/app.css`: stili condivisi UC.
- `public/assets/js/app.js`: apertura filtri e form dinamici moderazione.

## Test eseguiti

- `php -l` su tutti i file PHP in `Control`, `Foundation`, `View`, `Entity`.
- Smoke test GET su UC1-UC14.
- POST non autenticati su route nuove per verificare messaggi/accesso senza scritture DB.
- Test diretti dei wrapper web con contesti simulati:
  - cliente;
  - admin;
  - guest.
- `git diff --check`.

## Note per test manuali futuri

I test visuali finali vanno fatti dopo il collegamento di tutta la View, su:

- desktop;
- tablet 768px;
- mobile 390px;
- utente guest;
- cliente;
- chef;
- gestore;
- admin.

I POST che scrivono su DB vanno testati su database di prova o dopo reset del seed, per evitare duplicati o stati modificati in modo permanente.

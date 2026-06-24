# Ghost Kitchen

Progetto universitario per l'esame di Programmazione Web.

Ghost Kitchen e' una piattaforma web per mettere in contatto clienti, chef a domicilio e gestori di ghost kitchen. Il sistema permette di cercare chef e cucine professionali, gestire prenotazioni, pagamenti simulati, disponibilita, certificazioni, recensioni, segnalazioni e dashboard operative.

Repository GitHub:

```text
https://github.com/lallonelorenzo-arch/GHOST-KITCHEN
```

Demo online:

```text
https://ghostkitchen.infinityfreeapp.com/
```

## Componenti del gruppo

- Federico Ciccarelli
- Lorenzo Lallone
- Lorenzo Di Felice

## Contributi individuali

| Studente | Contributo in fase di progetto | Contributo in fase di implementazione |

| **Federico Ciccarelli** | Entity, UC3-UC8, integrazione View e dati demo. | Entity, Control/View, popolamento DB, dashboard chef, gallerie e fix finali. |

| **Lorenzo Lallone** | Foundation/Control, UC1-UC2, UC9-UC14, sicurezza e test. | FPersistentManager, Foundation, routing, login, calendario e documentazione. |

| **Lorenzo Di Felice** | Schema DB, UC8-UC14, recensioni e certificazioni. | Schema SQL, route/View UC8-UC14, recensioni utente/admin e certificazioni. |

La struttura principale del progetto e' stata definita in modo condiviso dal gruppo, in particolare per l'analisi dei casi d'uso, la definizione e organizzazione della struttura ECFV.

## Stack

- PHP 8.x
- MySQL / MariaDB
- Apache con XAMPP in locale
- HTML, CSS e JavaScript vanilla
- PDO per l'accesso al database
- Architettura ECFV / MVC custom

## Attori

L'applicazione prevede piu' attori:

- visitatore non autenticato;
- cliente registrato;
- chef;
- gestore di ghost kitchen;
- amministratore;
- utente multi-ruolo chef/gestore.

Ogni ruolo accede a funzionalita diverse. Le aree sensibili sono protette dal `CFrontController`, che verifica sessione e ruoli prima di eseguire il controller richiesto.

## Funzionalita principali

- ricerca chef e ghost kitchen;
- dettaglio chef con menu, recensioni e disponibilita;
- dettaglio ghost kitchen con attrezzature, recensioni e disponibilita;
- registrazione, login e profilo utente;
- prenotazione chef;
- prenotazione ghost kitchen;
- pagamento simulato;
- gestione disponibilita per chef e gestori;
- gestione richieste di prenotazione;
- recensioni e segnalazioni;
- validazione certificazioni;
- moderazione contenuti;
- dashboard statistiche;
- gestione utenti e ghost kitchen lato amministratore.

## Struttura del progetto

```text
GHOST_KITCHEN/
|-- index.php
|-- .htaccess
|-- Control/
|-- Entity/
|-- Foundation/
|-- View/
|-- public/
|-- database/
|-- docs/
`-- test/
```

Ruolo delle cartelle principali:

- `index.php`: punto di ingresso unico dell'applicazione.
- `.htaccess`: riscrive le route verso `index.php` e impedisce il listing delle cartelle.
- `Control/`: controller e logica applicativa dei casi d'uso.
- `Entity/`: oggetti di dominio, validazioni e stato delle entita.
- `Foundation/`: persistenza, query SQL, sessione e accesso al database.
- `View/`: template PHP, layout, partial e helper HTML.
- `public/`: asset statici CSS, JavaScript e cartelle upload.
- `database/`: schema SQL e script di popolamento.
- `docs/`: documentazione tecnica e casi d'uso.
- `test/`: script di verifica Foundation e Control.

## Architettura

Il flusso principale e':

```text
Browser
  -> Apache / PHP
  -> index.php
  -> CFrontController
  -> Control
  -> Foundation / FPersistentManager
  -> Database MySQL
  -> Entity
  -> ViewRenderer
  -> View/templates
  -> HTML
```

Il routing e' centralizzato in `Control/CFrontController.php`.

Le View non accedono direttamente al database e non contengono query SQL. I dati vengono preparati dai Control e passati ai template tramite `ViewRenderer`.

La sessione PHP e' incapsulata in `Foundation/FSession.php`: il resto dell'applicazione non legge o scrive direttamente `$_SESSION`. Le altre superglobali principali (`$_GET`, `$_POST`, `$_SERVER`, `$_FILES`) sono lette e normalizzate nel `CFrontController`, poi passate ai controller specifici come array ordinari.

## Requisiti locali

- XAMPP installato.
- Apache attivo.
- MySQL attivo.
- Progetto copiato in una cartella servita da Apache.

Percorso consigliato su Windows:

```text
C:\xampp\htdocs\GHOST_KITCHEN
```

URL locale:

```text
http://localhost/GHOST_KITCHEN/
```

## Installazione database locale

1. Avviare Apache e MySQL da XAMPP.
2. Aprire phpMyAdmin oppure usare il terminale MySQL.
3. Importare lo schema:

```bash
mysql -uroot < database/GhostKitchen.sql
```

4. Importare i dati demo:

```bash
mysql -uroot GhostKitchen < database/popolazioneDB.sql
```

Lo script `popolazioneDB.sql` e' necessario per consegnare un'applicazione gia' testabile, con una quantita significativa di dati dimostrativi.

## Configurazione database

La connessione e' configurata in:

```text
Foundation/FConnectionDB.php
```

Configurazione locale predefinita:

```text
host: localhost
database: GhostKitchen
user: root
password: vuota
charset: utf8mb4
```

Per hosting online, sostituire questi valori con host, nome database, username e password forniti dal provider.

## Deploy su InfinityFree

Su InfinityFree il contenuto da caricare dentro `htdocs` e':

```text
.htaccess
index.php
Control/
Entity/
Foundation/
View/
public/
```

Non e' necessario caricare online `database/`, `docs/` e `test/` dentro `htdocs`. Gli script SQL vanno invece usati da phpMyAdmin per creare e popolare il database.

Ordine di importazione in phpMyAdmin:

1. `database/GhostKitchen.sql`
2. `database/popolazioneDB.sql`

Dopo il caricamento, verificare almeno:

```text
/
/login
/ricerca/chef
/ricerca/ghost-kitchen
/dashboard
```

Se la home funziona ma le route come `/login` danno errore 404, controllare che `.htaccess` sia presente in `htdocs`.

## URL principali

```text
/                         Home
/ricerca/chef             Ricerca chef
/ricerca/ghost-kitchen    Ricerca ghost kitchen
/login                    Login
/registrazione            Registrazione
/profilo                  Area profilo
/prenotazioni             Prenotazioni utente
/dashboard                Dashboard per admin, chef o gestore
/disponibilita            Gestione disponibilita
/richieste                Gestione richieste
/moderazione              Moderazione admin
/utenti                   Gestione utenti admin
/certificazioni           Validazione certificazioni admin
/mie-certificazioni       Certificazioni chef
```

## Account demo

Password comune:

```text
Password123!
```

| Ruolo | Email |
| --- | --- |
| Cliente | `marco.rinaldi@gk.it` |
| Cliente | `giulia.conti@gk.it` |
| Chef | `alessandro.bassi@gk.it` |
| Chef | `federica.greco@gk.it` |
| Chef/Gestore | `marta.deluca@gk.it` |
| Gestore | `paolo.galli@gk.it` |
| Admin | `irene.villa@gk.it` |

Per la demo e' consigliato usare:

- cliente: `marco.rinaldi@gk.it`
- chef: `alessandro.bassi@gk.it`
- gestore: `paolo.galli@gk.it`
- admin: `irene.villa@gk.it`
- multi-ruolo chef/gestore: `marta.deluca@gk.it`

## Dati demo

Il database di esempio contiene dati per tutti i flussi principali:

- utenti, clienti, chef, gestori e amministratori;
- ghost kitchen;
- attrezzature;
- menu e piatti;
- media;
- certificazioni;
- disponibilita chef e ghost kitchen;
- prenotazioni;
- pagamenti;
- recensioni;
- segnalazioni.

## Casi d'uso implementati

- UC1: ricerca chef e ghost kitchen
- UC2: dettaglio chef
- UC3: dettaglio ghost kitchen
- UC4: prenotazione chef
- UC5: prenotazione ghost kitchen
- UC6: gestione disponibilita/calendario
- UC7: gestione richieste
- UC8: pagamento
- UC10: recensione
- UC11: segnalazione
- UC12: validazione certificazioni
- UC13: moderazione contenuti
- UC14: dashboard statistiche

## Limiti

- Il caso d'uso relativo a rimborsi, annullamenti avanzati e gestione economica post-pagamento (UC9) non e' stato sviluppato nella versione finale. Il pagamento presente nell'applicazione e' una simulazione di pagamento completato.

La documentazione dei casi d'uso e' disponibile in:

```text
docs/control_design/
```

## Sicurezza

- query SQL tramite prepared statements PDO;
- password salvate con `password_hash`;
- login verificato con `password_verify`;
- output HTML escapato tramite `htmlspecialchars` in `ViewHelpers::e`;
- token CSRF sui form POST;
- rigenerazione dell'id di sessione dopo il login;
- routing centralizzato con whitelist;
- controllo dei ruoli nelle route sensibili;
- listing delle cartelle disabilitato tramite `.htaccess`.

## Testing e collaudo

La cartella `test/` contiene script di verifica per Foundation e Control. Non e' una suite PHPUnit completa, ma serve a controllare rapidamente persistenza e casi d'uso.

Controllo sintassi PHP:

```bash
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
```

Checklist demo:

```text
docs/DEMO_CHECKLIST.md
```

Guida testing:

```text
docs/TESTING.md
```
## Materiale di consegna

Per la consegna d'esame il materiale comprende:

- codice sorgente dell'applicazione;
- schema SQL: `database/GhostKitchen.sql`;
- script di popolamento: `database/popolazioneDB.sql`;
- documentazione tecnica in `docs/`;
- descrizione dei casi d'uso in `docs/control_design/`;
- checklist demo e note di testing;
- repository GitHub usato durante lo sviluppo.

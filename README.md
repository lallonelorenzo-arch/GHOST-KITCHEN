# Ghost Kitchen

Progetto universitario per l'esame di Programmazione Web.

Ghost Kitchen e una piattaforma web per mettere in contatto clienti, chef, gestori di ghost kitchen e amministratori. Il sistema permette di cercare chef e cucine, gestire prenotazioni, pagamenti, disponibilita, certificazioni, recensioni, segnalazioni e dashboard operative.

## Stack

- PHP 8.x
- MySQL / MariaDB
- Apache con XAMPP
- HTML, CSS e JavaScript vanilla
- Architettura ECFV/MVC custom

## Struttura del progetto

```text
GHOST-KITCHEN/
├── index.php
├── Control/
├── Entity/
├── Foundation/
├── View/
├── public/
├── database/
├── docs/
└── test/
```

Ruolo delle cartelle principali:

- `index.php`: punto di ingresso unico dell'applicazione.
- `Control/`: controller e casi d'uso applicativi.
- `Entity/`: oggetti di dominio, validazioni e stato delle entita.
- `Foundation/`: persistenza, query SQL, sessione e accesso al database.
- `View/`: template PHP, layout, partial e helper HTML.
- `public/`: asset statici CSS, JavaScript e upload.
- `database/`: schema SQL, seed e migrazioni.
- `docs/`: documentazione tecnica e report dei casi d'uso.
- `test/`: script di verifica Foundation e Control.

## Architettura

Il flusso principale e:

```text
Browser
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

Il routing e centralizzato in `Control/CFrontController.php`.

Le View non accedono direttamente al database e non contengono query SQL. I dati vengono preparati dai Control e passati ai template tramite `ViewRenderer`.

## Requisiti

- XAMPP installato
- Apache attivo
- MySQL attivo
- Progetto nella cartella:

```text
/Applications/XAMPP/xamppfiles/htdocs/GHOST-KITCHEN
```

Su Windows il percorso equivalente puo essere:

```text
C:\xampp\htdocs\GHOST-KITCHEN
```

## Installazione database

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


## Configurazione database

La connessione e configurata in:

```text
Foundation/FConnectionDB.php
```

Valori predefiniti:

```text
host: localhost
database: GhostKitchen
user: root
password: vuota
charset: utf8mb4
```

## Avvio applicazione

Con Apache e MySQL attivi, aprire:

```text
http://localhost/GHOST-KITCHEN/
```

URL principali:

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

Account disponibili:

| Ruolo | Email |
| --- | --- |
| Cliente | `marco.rinaldi@gk.it` |
| Cliente | `giulia.conti@gk.it` |
| Chef | `alessandro.bassi@gk.it` |
| Chef | `federica.greco@gk.it` |
| Chef/Gestore | `marta.deluca@gk.it` |
| Gestore | `paolo.galli@gk.it` |
| Admin | `irene.villa@gk.it` |

Per la demo è consigliato usare:

- cliente: `marco.rinaldi@gk.it`
- chef: `alessandro.bassi@gk.it`
- gestore: `paolo.galli@gk.it`
- admin: `irene.villa@gk.it`
- multi-ruolo chef/gestore: `marta.deluca@gk.it`

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


## test Control | Foundation
La cartella `test/` contiene script di verifica per Foundation e Control.


## Note di sicurezza

- query SQL tramite prepared statements;
- password hashate con `password_hash`;
- login verificato con `password_verify`;
- output HTML escapato con `htmlspecialchars` tramite `ViewHelpers::e`;
- token CSRF sui form POST;
- routing centralizzato con whitelist;
- controllo dei ruoli nelle route sensibili.
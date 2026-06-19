# Testing e verifiche

Questa pagina raccoglie i controlli consigliati prima della demo d'esame.

Il progetto non usa una suite PHPUnit completa. La cartella `test/` contiene script PHP dimostrativi per verificare Foundation e Control; alcuni stampano oggetti con `print_r` o `var_dump` e alcuni possono modificare il database. Per questo motivo le verifiche sono divise in livelli.

## 1. Controllo sintassi PHP

Da eseguire sempre dopo modifiche al codice:

```bash
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
```

Esito atteso:

```text
No syntax errors detected ...
```

Non devono comparire errori di parsing.

## 2. Controllo ambiente

Con Apache e MySQL attivi:

```bash
mysqladmin ping
mysql -uroot -e "SHOW DATABASES LIKE 'GhostKitchen';"
curl -I http://localhost/GHOST-KITCHEN/
```

Esiti attesi:

- MySQL risponde con `mysqld is alive`;
- il database `GhostKitchen` viene trovato;
- la home risponde via HTTP senza errori applicativi.

## 3. Script smoke test sicuri

Questi script sono adatti a una verifica rapida perche non dovrebbero modificare dati applicativi rilevanti:

```bash
php test/test_FConnection.php
php test/foundation/test_Connection_DB.php
php test/foundation/test_FUtente.php
php test/foundation/test_FChef.php
php test/foundation/test_FGhostKitchen.php
php test/control/test_CRicerca.php
php test/control/test_CDettaglioChef.php
php test/control/test_CDettaglioGhostKitchen.php
```

Esito atteso:

- nessun `Fatal error`;
- nessun `Warning` bloccante;
- output coerente con dati demo presenti nel seed.

## 4. Script che possono modificare il database

Questi script testano flussi reali ma possono creare, aggiornare o cambiare stato a record:

```text
test/control/test_CPrenotazioneChef.php
test/control/test_CPrenotazioneGhostKitchen.php
test/control/test_CGestioneDisponibilita.php
test/control/test_CGestioneRichieste.php
test/control/test_CPagamento.php
test/control/test_CRecensione.php
test/control/test_CSegnalazione.php
test/control/test_CModerazione.php
test/control/test_CValidazioneCertificazioni.php
test/foundation/test_FFoundationGenerale.php
```

Prima di eseguirli conviene usare un database di prova oppure essere pronti a reimportare:

```bash
mysql -uroot < database/GhostKitchen.sql
mysql -uroot GhostKitchen < database/popolazioneDB.sql
```

## 5. Collaudo manuale web

Il collaudo web resta il controllo piu importante per l'esame. Seguire:

```text
docs/DEMO_CHECKLIST.md
```

Flussi minimi da provare:

- visitatore: home, ricerca chef, ricerca ghost kitchen, dettaglio;
- cliente: login, profilo, prenotazioni, prenotazione;
- chef: dashboard, disponibilita, richieste, certificazioni;
- gestore: dashboard, ghost kitchen, richieste;
- admin: dashboard, moderazione, utenti, certificazioni.

## 6. Nota sui token CSRF

I form `POST` includono token CSRF generati lato server. Un invio POST fatto a mano senza prima aprire la pagina del form restituisce errore `403`, ed e il comportamento corretto.

Per testare un POST web:

1. aprire la pagina nel browser;
2. compilare il form;
3. inviare dal browser.

## 7. Cosa dichiarare all'esame

Formula consigliata:

```text
Abbiamo verifiche automatiche di sintassi, script dimostrativi per Foundation e Control, e una checklist di collaudo manuale per i flussi web principali. Gli script in test/ non sono una suite PHPUnit completa con asserzioni, ma servono a validare rapidamente i casi d'uso e l'integrazione con il database demo.
```

# Demo Checklist

Checklist operativa per provare Ghost Kitchen prima dell'esame.

## Preparazione ambiente

- [ ] Avviare XAMPP.
- [ ] Avviare Apache.
- [ ] Avviare MySQL.
- [ ] Aprire `http://localhost/GHOST-KITCHEN/`.
- [ ] Verificare che la home risponda correttamente.
- [ ] Verificare che il database `GhostKitchen` sia presente.
- [ ] Verificare che i dati demo siano caricati.

Comandi utili:

```bash
curl -I http://localhost/GHOST-KITCHEN/
mysqladmin ping
mysql -uroot -e "SHOW DATABASES LIKE 'GhostKitchen';"
```

## Account demo

Password comune:

```text
Password123!
```

| Ruolo | Email | Da usare per |
| --- | --- | --- |
| Cliente | `marco.rinaldi@gk.it` | prenotazioni, profilo, pagamenti |
| Chef | `alessandro.bassi@gk.it` | dashboard chef, disponibilita, richieste, certificazioni |
| Gestore | `paolo.galli@gk.it` | dashboard gestore, disponibilita ghost kitchen |
| Chef/Gestore | `marta.deluca@gk.it` | passaggio tra dashboard chef e gestore |
| Admin | `irene.villa@gk.it` | dashboard admin, moderazione, utenti, certificazioni |

## Demo visitatore

- [ ] Aprire la home.
- [ ] Verificare layout, navbar e call to action.
- [ ] Aprire `Trova Chef`.
- [ ] Cercare chef per localita o cucina.
- [ ] Aprire il dettaglio di uno chef.
- [ ] Verificare menu, prezzo, valutazione e disponibilita.
- [ ] Aprire `Ghost Kitchen`.
- [ ] Cercare una ghost kitchen.
- [ ] Aprire il dettaglio di una ghost kitchen.
- [ ] Verificare descrizione, attrezzature, prezzo e disponibilita.
- [ ] Provare una URL inesistente e verificare pagina 404 controllata.

URL consigliati:

```text
/
/ricerca/chef
/ricerca/ghost-kitchen
/chef/5
/ghost-kitchen/1
/route-inesistente
```

## Demo cliente

- [ ] Effettuare login con `marco.rinaldi@gk.it`.
- [ ] Verificare redirect dopo login.
- [ ] Aprire profilo.
- [ ] Verificare dati personali e metodi di pagamento.
- [ ] Aprire `Le mie prenotazioni`.
- [ ] Verificare elenco prenotazioni chef e ghost kitchen.
- [ ] Aprire prenotazione chef da dettaglio chef.
- [ ] Selezionare menu e slot disponibile.
- [ ] Inviare una richiesta di prenotazione chef.
- [ ] Aprire prenotazione ghost kitchen da dettaglio ghost kitchen.
- [ ] Selezionare slot disponibile.
- [ ] Inviare una richiesta di prenotazione ghost kitchen.
- [ ] Verificare che la nuova prenotazione compaia nelle prenotazioni.

URL consigliati:

```text
/login
/profilo
/prenotazioni
/prenotazione/chef/5
/prenotazione/ghost-kitchen/1
```

## Demo chef

- [ ] Effettuare logout dal cliente.
- [ ] Effettuare login con `alessandro.bassi@gk.it`.
- [ ] Verificare dashboard chef.
- [ ] Verificare tab panoramica.
- [ ] Verificare tab prenotazioni.
- [ ] Verificare tab richieste.
- [ ] Aprire `Disponibilita`.
- [ ] Aggiungere una disponibilita futura.
- [ ] Verificare che lo slot compaia nel calendario.
- [ ] Aprire `Richieste`.
- [ ] Accettare una richiesta demo, se disponibile.
- [ ] Rifiutare una richiesta demo, se disponibile e se il DB puo essere modificato.
- [ ] Aprire `Le mie certificazioni`.
- [ ] Verificare elenco certificazioni.
- [ ] Provare caricamento certificazione solo su database di prova.

URL consigliati:

```text
/dashboard
/dashboard?ruolo=chef&tab=prenotazioni
/dashboard?ruolo=chef&tab=richieste
/disponibilita
/richieste
/mie-certificazioni
```

## Demo gestore

- [ ] Effettuare logout dallo chef.
- [ ] Effettuare login con `paolo.galli@gk.it`.
- [ ] Verificare dashboard gestore.
- [ ] Verificare tab panoramica.
- [ ] Verificare tab prenotazioni.
- [ ] Verificare tab richieste.
- [ ] Aprire `Disponibilita`.
- [ ] Selezionare ghost kitchen gestita.
- [ ] Aggiungere una disponibilita futura.
- [ ] Verificare calendario ghost kitchen.
- [ ] Aprire `Richieste`.
- [ ] Accettare o rifiutare una richiesta demo, se disponibile.

URL consigliati:

```text
/dashboard?ruolo=gestore
/dashboard?ruolo=gestore&tab=prenotazioni
/dashboard?ruolo=gestore&tab=richieste
/disponibilita
/richieste
```

## Demo multi-ruolo

- [ ] Effettuare logout dal gestore.
- [ ] Effettuare login con `marta.deluca@gk.it`.
- [ ] Verificare dashboard chef.
- [ ] Usare toggle `Chef`.
- [ ] Usare toggle `Ghost`.
- [ ] Verificare dashboard gestore.
- [ ] Verificare che navbar e profilo mostrino il ruolo attivo.

URL consigliati:

```text
/dashboard?ruolo=chef
/dashboard?ruolo=gestore
```

## Demo admin

- [ ] Effettuare logout dal multi-ruolo.
- [ ] Effettuare login con `irene.villa@gk.it`.
- [ ] Verificare dashboard admin.
- [ ] Aprire `Moderazione`.
- [ ] Verificare segnalazioni aperte.
- [ ] Prendere in carico una segnalazione, se il DB puo essere modificato.
- [ ] Chiudere una segnalazione, se il DB puo essere modificato.
- [ ] Aprire `Utenti`.
- [ ] Verificare elenco utenti, gestori e ghost kitchen.
- [ ] Aprire `Certificazioni`.
- [ ] Verificare certificazioni in attesa e in scadenza.
- [ ] Aprire dettaglio certificazione.
- [ ] Approvare o rifiutare una certificazione solo su database di prova.

URL consigliati:

```text
/dashboard
/moderazione
/utenti
/certificazioni
/certificazioni/6001
```

## Flussi POST da provare con attenzione

Questi flussi modificano il database. Prima di provarli conviene fare un backup o essere pronti a reimportare il seed.

- [ ] Registrazione nuovo cliente.
- [ ] Registrazione nuovo chef.
- [ ] Registrazione nuovo gestore con ghost kitchen.
- [ ] Aggiornamento profilo.
- [ ] Aggiunta metodo di pagamento.
- [ ] Aggiunta disponibilita chef.
- [ ] Aggiunta disponibilita ghost kitchen.
- [ ] Creazione prenotazione chef.
- [ ] Creazione prenotazione ghost kitchen.
- [ ] Accettazione richiesta chef.
- [ ] Rifiuto richiesta chef.
- [ ] Accettazione richiesta ghost kitchen.
- [ ] Rifiuto richiesta ghost kitchen.
- [ ] Pagamento caparra o totale.
- [ ] Cancellazione prenotazione.
- [ ] Creazione recensione.
- [ ] Invio segnalazione.
- [ ] Moderazione recensione.
- [ ] Moderazione profilo.
- [ ] Validazione certificazione.
- [ ] Caricamento certificazione chef.

## Controlli tecnici

- [ ] Consultare `docs/TESTING.md` per distinguere smoke test e script che modificano il database.
- [ ] Eseguire `php -l` su tutti i file PHP.
- [ ] Verificare che la home risponda `200`.
- [ ] Verificare che login cliente risponda e rediriga correttamente.
- [ ] Verificare che login chef risponda e rediriga correttamente.
- [ ] Verificare che login gestore risponda e rediriga correttamente.
- [ ] Verificare che login admin risponda e rediriga correttamente.
- [ ] Verificare che non compaiano `Fatal error`, `Warning`, `Notice` o `Errore applicativo` nelle pagine principali.
- [ ] Verificare responsivita base su desktop e mobile.

Comando sintassi PHP:

```bash
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
```

Pagine principali da controllare:

```text
/
/ricerca/chef
/ricerca/ghost-kitchen
/chef/5
/ghost-kitchen/1
/login
/profilo
/prenotazioni
/dashboard
/disponibilita
/richieste
/moderazione
/utenti
/certificazioni
/mie-certificazioni
```

## Cose da saper spiegare all'esame

- [ ] Perche il progetto usa `index.php` come punto di ingresso unico.
- [ ] Come funziona `CFrontController`.
- [ ] Come vengono protette le route in base ai ruoli.
- [ ] Differenza tra `Control`, `Foundation`, `Entity` e `View`.
- [ ] Ruolo di `FPersistentManager`.
- [ ] Come viene gestita la sessione in `FSession`.
- [ ] Come viene gestito il login.
- [ ] Come vengono salvate e caricate le prenotazioni.
- [ ] Come vengono gestiti pagamenti, cancellazioni e rimborsi.
- [ ] Come funzionano certificazioni e moderazione.
- [ ] Come vengono evitati accessi diretti SQL nelle View.
- [ ] Come vengono escapati i dati in output.
- [ ] Quali sono i limiti noti del progetto.

## Note finali

Prima della demo finale conviene reimportare il database o usare una copia pulita, cosi lo stato dei dati e prevedibile.

Durante l'esame e meglio mostrare pochi flussi ma sicuri, invece di provare tutte le funzioni disponibili.

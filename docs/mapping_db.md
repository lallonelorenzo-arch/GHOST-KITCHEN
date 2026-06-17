# Mapping Object-Relational - Ghost Kitchen

## Scopo e perimetro
Questo documento descrive la trasformazione delle classi `Entity` in struttura relazionale, senza generare ancora lo schema SQL definitivo.

Regole applicate:
- fonte primaria: classi in `Entity/`;
- `Control/` usato solo per confermare i flussi CRUD;
- nessuna modifica a `Entity`, `Control`, `Foundation`, `View`;
- nessuna introduzione di framework o concetti di dominio non presenti;
- dove mancano dettagli nelle Entity, sono indicate note di coerenza progettuale.

Convenzioni proposte:
- nomi tabella in `snake_case` al plurale;
- PK intere auto-incrementali (`INT`);
- stringhe in `VARCHAR/TEXT` con lunghezze da definire in fase schema;
- date/ora modellate in tipi SQL semantici (`DATE`, `TIME`, `DATETIME`) dove opportuno.

---

## 1) Gerarchia utenti

Strategia proposta: **table-per-subclass (joined inheritance)**.
- tabella base: `utenti`
- tabelle specializzate: `clienti`, `chef`, `gestori`, `amministratori`
- PK delle specializzate = FK a `utenti.id_utente`

Motivazione:
- coerente con le Entity (`ECliente`, `EChef`, `EGestore`, `EAmministratore` estendono `EUtente`);
- evita molti campi nullable nella tabella base;
- mantiene chiara l'identità univoca dell'utente;
- consente multi-ruolo senza tabella ruoli dedicata.

### Entity: `EUtente`
- Tabella: `utenti`
- PK: `id_utente`
- Colonne:
  - `id_utente INT`
  - `nome VARCHAR(100)`
  - `cognome VARCHAR(100)`
  - `email VARCHAR(255)`
  - `password_hash VARCHAR(255)`
  - `telefono VARCHAR(30)`
  - `stato VARCHAR(20)` (`attivo|sospeso|bannato`)
- NULL/NOT NULL:
  - `id_utente` NOT NULL (PK)
  - altri campi NOT NULL
- FK: nessuna
- UNIQUE:
  - `email` UNIQUE
- Relazioni:
  - 1:N con `metodi_pagamento`
  - 1:N con `prenotazioni` tramite `id_richiedente`
  - 1:N con `recensioni` tramite `id_autore`
  - 1:N con `segnalazioni` tramite `id_segnalante`
- Note progettuali:
  - un utente può avere più ruoli contemporaneamente;
  - il ruolo è determinato dalla presenza di `id_utente` nelle tabelle `clienti`, `chef`, `gestori`, `amministratori`;
  - esempio: se `id_utente` è presente sia in `chef` sia in `gestori`, l'utente ha entrambi i ruoli;
  - non viene introdotta una tabella ruoli separata in questa fase.

### Entity: `ECliente`
- Tabella: `clienti`
- PK: `id_utente`
- Colonne:
  - `id_utente INT`
- NULL/NOT NULL:
  - NOT NULL
- FK:
  - `id_utente -> utenti.id_utente`
- UNIQUE:
  - implicito dalla PK
- Relazioni:
  - specializzazione 1:1 di `utenti`
- Note:
  - nessun attributo aggiuntivo nella Entity.

### Entity: `EChef`
- Tabella: `chef`
- PK: `id_utente`
- Colonne:
  - `id_utente INT`
  - `biografia TEXT`
  - `specializzazione VARCHAR(150)`
  - `tipologia_cucina VARCHAR(100)`
  - `prezzo_base DECIMAL(10,2)`
  - `anni_esperienza INT`
  - `stato_verifica VARCHAR(30)` (`non_verificato|in_attesa|verificato|rifiutato|sospeso`)
  - `valutazione_media DECIMAL(3,2)`
  - `numero_recensioni INT`
- NULL/NOT NULL:
  - `id_utente` NOT NULL
  - altri campi NOT NULL
- FK:
  - `id_utente -> utenti.id_utente`
- UNIQUE:
  - nessun vincolo aggiuntivo necessario
- Relazioni:
  - 1:N con `menu`
  - 1:N con `disponibilita_chef`
  - 1:N con `prenotazioni_chef`
  - 1:N con `certificazioni`
  - 1:N con `recensioni_chef`
- Nota progettuale:
  - `valutazione_media` e `numero_recensioni` sono campi derivati/denormalizzati;
  - devono essere aggiornati quando vengono create, modificate, nascoste o rimosse recensioni.

### Entity: `EGestore`
- Tabella: `gestori`
- PK: `id_utente`
- Colonne:
  - `id_utente INT`
- NULL/NOT NULL:
  - NOT NULL
- FK:
  - `id_utente -> utenti.id_utente`
- Relazioni:
  - 1:N con `ghost_kitchen`

### Entity: `EAmministratore`
- Tabella: `amministratori`
- PK: `id_utente`
- Colonne:
  - `id_utente INT`
- NULL/NOT NULL:
  - NOT NULL
- FK:
  - `id_utente -> utenti.id_utente`
- Note:
  - nessun attributo aggiuntivo nella Entity.

---

## 2) Catalogo e risorse operative

### Entity: `EGhostKitchen`
- Tabella: `ghost_kitchen`
- PK: `id_ghost_kitchen`
- Colonne:
  - `id_ghost_kitchen INT`
  - `id_gestore INT`
  - `nome VARCHAR(150)`
  - `descrizione TEXT`
  - `indirizzo VARCHAR(255)`
  - `citta VARCHAR(100)`
  - `cap VARCHAR(10)`
  - `prezzo_orario DECIMAL(10,2)`
  - `capienza INT`
  - `mq DECIMAL(8,2)`
  - `stato VARCHAR(30)` (`attiva|sospesa|non_disponibile`)
  - `valutazione_media DECIMAL(3,2)`
  - `numero_recensioni INT`
- NULL/NOT NULL:
  - tutti NOT NULL tranne PK auto-generata
- FK:
  - `id_gestore -> gestori.id_utente`
- UNIQUE utili:
  - `UNIQUE(nome, indirizzo, cap)` (valutare)
- Relazioni:
  - 1:N con `attrezzature`
  - 1:N con `disponibilita_ghost_kitchen`
  - 1:N con `prenotazioni_ghost_kitchen`
  - 1:N con `recensioni_ghost_kitchen`
  - 1:N polimorfica con `media` (`tipo_owner='ghost_kitchen'`)
- Nota progettuale:
  - `valutazione_media` e `numero_recensioni` sono campi derivati/denormalizzati;
  - devono essere aggiornati quando vengono create, modificate, nascoste o rimosse recensioni.

### Entity: `EAttrezzatura`
- Tabella: `attrezzature`
- PK: `id_attrezzatura`
- Colonne:
  - `id_attrezzatura INT`
  - `id_ghost_kitchen INT`
  - `nome VARCHAR(120)`
  - `categoria VARCHAR(80)`
  - `descrizione TEXT`
  - `quantita INT`
- NULL/NOT NULL:
  - `id_attrezzatura` NOT NULL
  - `id_ghost_kitchen`, `nome`, `categoria`, `quantita` NOT NULL
  - `descrizione` NULL ammesso
- FK:
  - `id_ghost_kitchen -> ghost_kitchen.id_ghost_kitchen`
- UNIQUE utili:
  - `UNIQUE(id_ghost_kitchen, nome, categoria)` (valutare)
- Relazioni:
  - N:1 verso `ghost_kitchen`
- Nota:
  - resta **entity separata** come richiesto.
  - le attrezzature sono considerate elementi specifici di una singola ghost kitchen, non un catalogo generale riutilizzabile.

### Entity: `EMenu`
- Tabella: `menu`
- PK: `id_menu`
- Colonne:
  - `id_menu INT`
  - `id_chef INT`
  - `nome VARCHAR(150)`
  - `descrizione TEXT`
  - `prezzo_persona DECIMAL(10,2)`
  - `attivo BOOLEAN`
- NULL/NOT NULL:
  - tutti NOT NULL tranne PK auto-generata
- FK:
  - `id_chef -> chef.id_utente`
- UNIQUE utili:
  - `UNIQUE(id_chef, nome)` (valutare)
- Relazioni:
  - 1:N con `piatti`
  - 1:N polimorfica con `media` (`tipo_owner='menu'`)

### Entity: `EPiatto`
- Tabella: `piatti`
- PK: `id_piatto`
- Colonne:
  - `id_piatto INT`
  - `id_menu INT`
  - `nome VARCHAR(150)`
  - `categoria VARCHAR(30)` (`antipasto|primo|secondo|contorno|dolce|bevanda|altro`)
  - `descrizione TEXT`
  - `ingredienti TEXT`
  - `allergeni TEXT`
  - `prezzo_supplemento DECIMAL(10,2)`
  - `ordine_visualizzazione INT`
- NULL/NOT NULL:
  - campi strutturali NOT NULL (`id_menu`, `nome`, `categoria`, `prezzo_supplemento`, `ordine_visualizzazione`)
  - descrittivi (`descrizione`, `ingredienti`, `allergeni`) valutabili NULL
- FK:
  - `id_menu -> menu.id_menu`
- UNIQUE utili:
  - `UNIQUE(id_menu, ordine_visualizzazione)`
- Relazioni:
  - N:1 verso `menu`
  - 1:N polimorfica con `media` (`tipo_owner='piatto'`)

### Entity: `EMedia`
- Tabella: `media`
- PK: `id_media`
- Colonne:
  - `id_media INT`
  - `tipo_owner VARCHAR(30)` (`chef|menu|ghost_kitchen|piatto`)
  - `id_owner INT`
  - `tipo_media VARCHAR(30)` (`foto_profilo|foto_menu|foto_piatto|foto_ambiente|planimetria|generica`)
  - `nome_file VARCHAR(255)`
  - `path_file VARCHAR(500)`
  - `mime_type VARCHAR(100)`
  - `descrizione TEXT`
  - `data_caricamento DATETIME`
  - `ordine INT`
  - `stato VARCHAR(20)` (`attivo|nascosto|rimosso`)
- NULL/NOT NULL:
  - `tipo_owner`, `id_owner`, `tipo_media`, `nome_file`, `path_file`, `mime_type`, `data_caricamento`, `ordine`, `stato` NOT NULL
  - `descrizione` NULL ammesso
- FK:
  - **nessuna FK fisica singola** per scelta polimorfica
- UNIQUE utili:
  - `UNIQUE(tipo_owner, id_owner, ordine)`
- Relazioni:
  - associazione polimorfica verso `chef`, `menu`, `ghost_kitchen`, `piatti`
- Nota progettuale:
  - `EMedia` resta **generica** con `tipo_owner + id_owner` come richiesto.
  - La coerenza referenziale polimorfica e gestita dal livello applicativo.

### Entity: `ECertificazione`
- Tabella: `certificazioni`
- PK: `id_certificazione`
- Colonne:
  - `id_certificazione INT`
  - `id_chef INT`
  - `tipo VARCHAR(120)`
  - `nome_file VARCHAR(255)`
  - `path_file VARCHAR(500)`
  - `stato VARCHAR(30)` (`in_attesa|approvata|rifiutata`)
  - `data_caricamento DATETIME`
  - `data_validazione DATETIME`
  - `note_admin TEXT`
- NULL/NOT NULL:
  - `id_chef`, `tipo`, `nome_file`, `path_file`, `stato`, `data_caricamento` NOT NULL
  - `data_validazione`, `note_admin` NULL ammessi
- FK:
  - `id_chef -> chef.id_utente`
- Relazioni:
  - N:1 verso `chef`

---

## 3) Disponibilità

### Entity: `EDisponibilitaChef`
- Tabella: `disponibilita_chef`
- PK: `id_disponibilita_chef`
- Colonne:
  - `id_disponibilita_chef INT`
  - `id_chef INT`
  - `data DATE`
  - `ora_inizio TIME`
  - `ora_fine TIME`
  - `stato VARCHAR(20)` (`libera|occupata|bloccata`)
- NULL/NOT NULL:
  - tutti NOT NULL tranne PK auto-generata
- FK:
  - `id_chef -> chef.id_utente`
- UNIQUE utili:
  - `UNIQUE(id_chef, data, ora_inizio, ora_fine)`
- Relazioni:
  - N:1 verso `chef`

### Entity: `EDisponibilitaGhostKitchen`
- Tabella: `disponibilita_ghost_kitchen`
- PK: `id_disponibilita_ghost_kitchen`
- Colonne:
  - `id_disponibilita_ghost_kitchen INT`
  - `id_ghost_kitchen INT`
  - `data DATE`
  - `ora_inizio TIME`
  - `ora_fine TIME`
  - `stato VARCHAR(20)` (`libera|occupata|bloccata`)
- NULL/NOT NULL:
  - tutti NOT NULL tranne PK auto-generata
- FK:
  - `id_ghost_kitchen -> ghost_kitchen.id_ghost_kitchen`
- UNIQUE utili:
  - `UNIQUE(id_ghost_kitchen, data, ora_inizio, ora_fine)`
- Relazioni:
  - N:1 verso `ghost_kitchen`

---

## 4) Gerarchia prenotazioni

Strategia proposta: **joined inheritance**.
- tabella base: `prenotazioni`
- tabelle figlie: `prenotazioni_chef`, `prenotazioni_ghost_kitchen`

Vincolo richiesto:
- `prenotazioni.id_richiedente` deve riferire `utenti.id_utente` (non `clienti`), perché la ghost kitchen può essere prenotata anche da chef.

### Entity: `EPrenotazione`
- Tabella: `prenotazioni`
- PK: `id_prenotazione`
- Colonne:
  - `id_prenotazione INT`
  - `id_richiedente INT`
  - `data_creazione DATETIME`
  - `data_servizio DATE`
  - `ora_inizio TIME`
  - `ora_fine TIME`
  - `stato VARCHAR(30)` (`in_attesa|accettata|rifiutata|pagata|completata|cancellata`)
  - `importo_totale DECIMAL(10,2)`
  - `note TEXT`
- NULL/NOT NULL:
  - `id_richiedente`, `data_creazione`, `data_servizio`, `ora_inizio`, `ora_fine`, `stato`, `importo_totale` NOT NULL
  - `note` NULL ammesso
- FK:
  - `id_richiedente -> utenti.id_utente`
- Relazioni:
  - 1:1 con `prenotazioni_chef` oppure 1:1 con `prenotazioni_ghost_kitchen`
  - 1:N con `pagamenti` (operativamente spesso 1:N per caparra/saldo)
  - 1:N logica con `cancellazioni` (valutare cardinalità reale)
- Nota:
  - la coerenza "una prenotazione base deve avere esattamente una specializzazione" sarà controllata a livello applicativo o tramite vincoli/trigger in fase SQL.

### Entity: `EPrenotazioneChef`
- Tabella: `prenotazioni_chef`
- PK: `id_prenotazione`
- Colonne:
  - `id_prenotazione INT`
  - `id_chef INT`
  - `id_menu INT`
  - `indirizzo_servizio VARCHAR(255)`
  - `numero_persone INT`
  - `richieste_speciali TEXT`
- NULL/NOT NULL:
  - `id_chef`, `id_menu`, `indirizzo_servizio`, `numero_persone` NOT NULL
  - `richieste_speciali` NULL ammesso
- FK:
  - `id_prenotazione -> prenotazioni.id_prenotazione`
  - `id_chef -> chef.id_utente`
  - `id_menu -> menu.id_menu`
- UNIQUE utili:
  - `id_prenotazione` unico (PK)
- Relazioni:
  - specializzazione 1:1 della prenotazione base
- Nota di coerenza:
  - garantire che `id_menu` appartenga allo stesso chef indicato da `id_chef`;
  - questa coerenza potrà essere controllata lato applicativo, tramite Foundation, oppure con vincoli/trigger in fase SQL.

### Entity: `EPrenotazioneGhostKitchen`
- Tabella: `prenotazioni_ghost_kitchen`
- PK: `id_prenotazione`
- Colonne:
  - `id_prenotazione INT`
  - `id_ghost_kitchen INT`
  - `tipo_richiedente VARCHAR(20)` (`cliente|chef`)
- NULL/NOT NULL:
  - tutti NOT NULL
- FK:
  - `id_prenotazione -> prenotazioni.id_prenotazione`
  - `id_ghost_kitchen -> ghost_kitchen.id_ghost_kitchen`
- Relazioni:
  - specializzazione 1:1 della prenotazione base
- Nota:
  - `tipo_richiedente` resta campo informativo esplicito (`cliente|chef`) presente nella Entity.

---

## 5) Pagamenti, cancellazioni, rimborsi

### Entity: `EMetodoPagamento`
- Tabella: `metodi_pagamento`
- PK: `id_metodo_pagamento`
- Colonne:
  - `id_metodo_pagamento INT`
  - `id_utente INT`
  - `tipo VARCHAR(20)` (`carta|paypal|bonifico|contanti`)
  - `intestatario VARCHAR(150)`
  - `circuito VARCHAR(80)`
  - `ultime_quattro_cifre CHAR(4)`
  - `scadenza_mese INT`
  - `scadenza_anno INT`
  - `attivo BOOLEAN`
- NULL/NOT NULL:
  - `id_utente`, `tipo`, `intestatario`, `attivo` NOT NULL
  - gli altri campi **dipendono dal tipo** (es. carta vs bonifico) -> `NULL` ammesso
- FK:
  - `id_utente -> utenti.id_utente`
- Relazioni:
  - N:1 verso `utenti`
  - 1:N con `pagamenti`
- Nota di coerenza:
  - i controlli condizionali per tipo metodo pagamento sono gestiti a livello applicativo.

### Entity: `EPagamento`
- Tabella: `pagamenti`
- PK: `id_pagamento`
- Colonne:
  - `id_pagamento INT`
  - `id_prenotazione INT`
  - `id_metodo_pagamento INT`
  - `importo DECIMAL(10,2)`
  - `tipo_pagamento VARCHAR(20)` (`caparra|saldo|totale|penale`)
  - `stato VARCHAR(30)` (`in_attesa|autorizzato|completato|fallito|rimborsato|parzialmente_rimborsato`)
  - `codice_transazione VARCHAR(120)`
  - `data_pagamento DATETIME`
- NULL/NOT NULL:
  - `id_prenotazione`, `importo`, `tipo_pagamento`, `stato` NOT NULL
  - `id_metodo_pagamento`, `codice_transazione`, `data_pagamento` possono essere NULL in stati iniziali
- FK:
  - `id_prenotazione -> prenotazioni.id_prenotazione`
  - `id_metodo_pagamento -> metodi_pagamento.id_metodo_pagamento`
- UNIQUE utili:
  - `codice_transazione` UNIQUE (se valorizzato)
- Relazioni:
  - N:1 verso `prenotazioni`
  - N:1 verso `metodi_pagamento`
  - 1:N con `rimborsi`
- Nota:
  - il tipo concreto della prenotazione si ricava da `prenotazioni_chef` o `prenotazioni_ghost_kitchen`.

### Entity: `ECancellazione`
- Tabella: `cancellazioni`
- PK: `id_cancellazione`
- Colonne:
  - `id_cancellazione INT`
  - `id_prenotazione INT`
  - `id_richiedente INT`
  - `motivo TEXT`
  - `data_richiesta DATETIME`
  - `penale_applicata DECIMAL(10,2)`
  - `importo_rimborsato DECIMAL(10,2)`
  - `stato VARCHAR(20)` (`richiesta|accettata|rifiutata|completata`)
- NULL/NOT NULL:
  - `id_prenotazione`, `id_richiedente`, `data_richiesta`, `penale_applicata`, `importo_rimborsato`, `stato` NOT NULL
  - `motivo` NULL ammesso
- FK:
  - `id_prenotazione -> prenotazioni.id_prenotazione`
  - `id_richiedente -> utenti.id_utente`
- Relazioni:
  - N:1 verso `prenotazioni`
  - N:1 verso `utenti`
  - 1:N con `rimborsi`
- Nota:
  - resta separata da `rimborsi` come richiesto.
  - il tipo concreto della prenotazione si ricava da `prenotazioni_chef` o `prenotazioni_ghost_kitchen`.

### Entity: `ERimborso`
- Tabella: `rimborsi`
- PK: `id_rimborso`
- Colonne:
  - `id_rimborso INT`
  - `id_pagamento INT`
  - `id_cancellazione INT`
  - `importo DECIMAL(10,2)`
  - `motivo TEXT`
  - `stato VARCHAR(20)` (`richiesto|approvato|rifiutato|eseguito|fallito`)
  - `data_richiesta DATETIME`
  - `data_esecuzione DATETIME`
- NULL/NOT NULL:
  - `id_pagamento`, `id_cancellazione`, `importo`, `stato`, `data_richiesta` NOT NULL
  - `motivo`, `data_esecuzione` NULL ammessi
- FK:
  - `id_pagamento -> pagamenti.id_pagamento`
  - `id_cancellazione -> cancellazioni.id_cancellazione`
- Relazioni:
  - N:1 verso `pagamenti`
  - N:1 verso `cancellazioni`
- Nota:
  - resta separata da `cancellazioni` come richiesto.

---

## 6) Gerarchia recensioni

Strategia proposta: **joined inheritance**.
- tabella base: `recensioni`
- tabelle specializzate: `recensioni_chef`, `recensioni_ghost_kitchen`

### Entity: `ERecensione`
- Tabella: `recensioni`
- PK: `id_recensione`
- Colonne:
  - `id_recensione INT`
  - `id_autore INT`
  - `punteggio INT`
  - `commento TEXT`
  - `data_recensione DATETIME`
  - `stato VARCHAR(20)` (`visibile|nascosta|rimossa`)
- NULL/NOT NULL:
  - `id_autore`, `punteggio`, `data_recensione`, `stato` NOT NULL
  - `commento` NULL ammesso
- FK:
  - `id_autore -> utenti.id_utente`
- Relazioni:
  - 1:1 con `recensioni_chef` oppure 1:1 con `recensioni_ghost_kitchen`

### Entity: `ERecensioneChef`
- Tabella: `recensioni_chef`
- PK: `id_recensione`
- Colonne:
  - `id_recensione INT`
  - `id_chef INT`
  - `id_prenotazione_chef INT`
- NULL/NOT NULL:
  - tutti NOT NULL
- FK:
  - `id_recensione -> recensioni.id_recensione`
  - `id_chef -> chef.id_utente`
  - `id_prenotazione_chef -> prenotazioni_chef.id_prenotazione`
- UNIQUE utili:
  - `UNIQUE(id_prenotazione_chef)`
- Relazioni:
  - specializzazione 1:1 di `recensioni`
- Nota di coerenza:
  - garantire che `id_chef` sia lo stesso chef associato alla prenotazione chef recensita.

### Entity: `ERecensioneGhostKitchen`
- Tabella: `recensioni_ghost_kitchen`
- PK: `id_recensione`
- Colonne:
  - `id_recensione INT`
  - `id_ghost_kitchen INT`
  - `id_prenotazione_ghost_kitchen INT`
- NULL/NOT NULL:
  - tutti NOT NULL
- FK:
  - `id_recensione -> recensioni.id_recensione`
  - `id_ghost_kitchen -> ghost_kitchen.id_ghost_kitchen`
  - `id_prenotazione_ghost_kitchen -> prenotazioni_ghost_kitchen.id_prenotazione`
- UNIQUE utili:
  - `UNIQUE(id_prenotazione_ghost_kitchen)`
- Relazioni:
  - specializzazione 1:1 di `recensioni`
- Nota di coerenza:
  - garantire che `id_ghost_kitchen` sia la stessa ghost kitchen associata alla prenotazione ghost kitchen recensita.

---

## 7) Moderazione e segnalazioni

### Entity: `ESegnalazione`
- Tabella: `segnalazioni`
- PK: `id_segnalazione`
- Colonne:
  - `id_segnalazione INT`
  - `id_segnalante INT`
  - `tipo_target VARCHAR(30)` (`utente|chef|ghost_kitchen|recensione|menu`)
  - `id_target INT`
  - `motivo TEXT`
  - `descrizione TEXT`
  - `stato VARCHAR(30)` (`aperta|in_valutazione|risolta|archiviata|respinta`)
  - `data_segnalazione DATETIME`
  - `data_gestione DATETIME`
  - `note_admin TEXT`
- NULL/NOT NULL:
  - `id_segnalante`, `tipo_target`, `id_target`, `stato`, `data_segnalazione` NOT NULL
  - `motivo`, `descrizione`, `data_gestione`, `note_admin` NULL ammessi
- FK:
  - `id_segnalante -> utenti.id_utente`
  - target polimorfico senza FK fisica singola
- Relazioni:
  - N:1 verso `utenti` (segnalante)
  - associazione polimorfica verso target applicativo
- Nota progettuale:
  - `ESegnalazione` resta **generica** con `tipo_target + id_target` come richiesto.
  - La coerenza del target viene validata dal livello applicativo.

---

## 8) Vincoli trasversali e note di progetto

## 8.1 Ereditarietà
- `EUtente` + (`ECliente`, `EChef`, `EGestore`, `EAmministratore`): joined inheritance.
- `EPrenotazione` + (`EPrenotazioneChef`, `EPrenotazioneGhostKitchen`): joined inheritance.
- `ERecensione` + (`ERecensioneChef`, `ERecensioneGhostKitchen`): joined inheritance.

## 8.2 Associazioni polimorfiche
- `media(tipo_owner, id_owner)` e `segnalazioni(tipo_target, id_target)` non hanno FK fisiche singole.
- Integrità garantita lato applicativo (o trigger dedicati in fase SQL).

## 8.3 Cardinalità e coerenza
- Prenotazione base deve avere **esattamente una** specializzazione figlia.
- Recensione base deve avere **esattamente una** specializzazione figlia.
- `id_richiedente` in prenotazioni punta sempre a `utenti`, come richiesto.
- La coerenza "una prenotazione base -> una sola specializzazione" sarà enforceata a livello applicativo o con vincoli/trigger nella fase SQL.

## 8.4 Note per evoluzioni schema SQL
- Definire lunghezze esatte `VARCHAR` dove non esplicitate dalle Entity.
- Definire `CHECK` formali per stati/tipi (enum applicativi).
- Definire indici secondari per query frequenti (date, stato, FK).
- Definire policy `ON DELETE/ON UPDATE` per ogni FK.
- Definire gestione timezone per campi data/ora (solo date locali o datetime UTC).
- Definire vincoli anti-overlap per disponibilità (chef e ghost kitchen), se richiesti.

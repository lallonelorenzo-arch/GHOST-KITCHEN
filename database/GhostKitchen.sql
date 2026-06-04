-- Ghost Kitchen - Schema SQL (MySQL 8+, InnoDB, utf8mb4)
-- NOTE IMPORTANTI:
-- 1) Una prenotazione base deve avere esattamente una specializzazione (prenotazioni_chef XOR prenotazioni_ghost_kitchen).
-- 2) Una recensione base deve avere esattamente una specializzazione (recensioni_chef XOR recensioni_ghost_kitchen).
-- 3) In prenotazioni_chef, id_menu deve appartenere allo stesso chef indicato da id_chef.
-- 4) In recensioni_chef, id_chef deve coincidere con lo chef della prenotazione chef recensita.
-- 5) In recensioni_ghost_kitchen, id_ghost_kitchen deve coincidere con la ghost kitchen della prenotazione recensita.
-- 6) media e segnalazioni hanno target polimorfico: no FK fisica verso target.
-- I punti sopra richiedono logica applicativa o trigger/vincoli avanzati.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS rimborsi;
DROP TABLE IF EXISTS recensioni_ghost_kitchen;
DROP TABLE IF EXISTS recensioni_chef;
DROP TABLE IF EXISTS segnalazioni;
DROP TABLE IF EXISTS recensioni;
DROP TABLE IF EXISTS cancellazioni;
DROP TABLE IF EXISTS pagamenti;
DROP TABLE IF EXISTS metodi_pagamento;
DROP TABLE IF EXISTS prenotazioni_ghost_kitchen;
DROP TABLE IF EXISTS prenotazioni_chef;
DROP TABLE IF EXISTS prenotazioni;
DROP TABLE IF EXISTS disponibilita_ghost_kitchen;
DROP TABLE IF EXISTS disponibilita_chef;
DROP TABLE IF EXISTS certificazioni;
DROP TABLE IF EXISTS media;
DROP TABLE IF EXISTS piatti;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS attrezzature;
DROP TABLE IF EXISTS ghost_kitchen;
DROP TABLE IF EXISTS amministratori;
DROP TABLE IF EXISTS gestori;
DROP TABLE IF EXISTS chef;
DROP TABLE IF EXISTS clienti;
DROP TABLE IF EXISTS utenti;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE utenti (
  id_utente INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  cognome VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  telefono VARCHAR(30) NOT NULL,
  foto_profilo VARCHAR(500) NULL,
  localita VARCHAR(150) NULL,
  biografia TEXT NULL,
  stato VARCHAR(20) NOT NULL DEFAULT 'attivo',
  CONSTRAINT uq_utenti_email UNIQUE (email),
  CONSTRAINT chk_utenti_stato CHECK (stato IN ('attivo', 'sospeso', 'bannato')),
  INDEX idx_utenti_stato (stato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE clienti (
  id_utente INT PRIMARY KEY,
  CONSTRAINT fk_clienti_utenti FOREIGN KEY (id_utente)
    REFERENCES utenti(id_utente)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE chef (
  id_utente INT PRIMARY KEY,
  biografia TEXT NULL,
  specializzazione VARCHAR(150) NULL,
  tipologia_cucina VARCHAR(100) NULL,
  prezzo_base DECIMAL(10,2) NULL,
  anni_esperienza INT NOT NULL DEFAULT 0,
  stato_verifica VARCHAR(30) NOT NULL DEFAULT 'non_verificato',
  valutazione_media DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  numero_recensioni INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_chef_utenti FOREIGN KEY (id_utente)
    REFERENCES utenti(id_utente)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT chk_chef_stato_verifica CHECK (stato_verifica IN ('non_verificato', 'in_attesa', 'verificato', 'rifiutato', 'sospeso')),
  CONSTRAINT chk_chef_prezzo_base CHECK (prezzo_base >= 0),
  CONSTRAINT chk_chef_anni_esperienza CHECK (anni_esperienza >= 0),
  CONSTRAINT chk_chef_valutazione_media CHECK (valutazione_media >= 0 AND valutazione_media <= 5),
  CONSTRAINT chk_chef_numero_recensioni CHECK (numero_recensioni >= 0),
  INDEX idx_chef_tipologia_cucina (tipologia_cucina),
  INDEX idx_chef_prezzo_base (prezzo_base),
  INDEX idx_chef_stato_verifica (stato_verifica)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE gestori (
  id_utente INT PRIMARY KEY,
  CONSTRAINT fk_gestori_utenti FOREIGN KEY (id_utente)
    REFERENCES utenti(id_utente)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE amministratori (
  id_utente INT PRIMARY KEY,
  CONSTRAINT fk_amministratori_utenti FOREIGN KEY (id_utente)
    REFERENCES utenti(id_utente)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ghost_kitchen (
  id_ghost_kitchen INT AUTO_INCREMENT PRIMARY KEY,
  id_gestore INT NOT NULL,
  nome VARCHAR(150) NOT NULL,
  descrizione TEXT NOT NULL,
  indirizzo VARCHAR(255) NOT NULL,
  citta VARCHAR(100) NOT NULL,
  cap VARCHAR(10) NOT NULL,
  prezzo_orario DECIMAL(10,2) NOT NULL,
  capienza INT NOT NULL,
  mq DECIMAL(8,2) NOT NULL,
  stato VARCHAR(30) NOT NULL DEFAULT 'attiva',
  valutazione_media DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  numero_recensioni INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_ghost_kitchen_gestori FOREIGN KEY (id_gestore)
    REFERENCES gestori(id_utente)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT uq_ghost_kitchen_nome_indirizzo_cap UNIQUE (nome, indirizzo, cap),
  CONSTRAINT chk_ghost_kitchen_stato CHECK (stato IN ('attiva', 'sospesa', 'non_disponibile')),
  CONSTRAINT chk_ghost_kitchen_prezzo_orario CHECK (prezzo_orario >= 0),
  CONSTRAINT chk_ghost_kitchen_capienza CHECK (capienza > 0),
  CONSTRAINT chk_ghost_kitchen_mq CHECK (mq > 0),
  CONSTRAINT chk_ghost_kitchen_valutazione_media CHECK (valutazione_media >= 0 AND valutazione_media <= 5),
  CONSTRAINT chk_ghost_kitchen_numero_recensioni CHECK (numero_recensioni >= 0),
  INDEX idx_ghost_kitchen_citta (citta),
  INDEX idx_ghost_kitchen_prezzo_orario (prezzo_orario),
  INDEX idx_ghost_kitchen_stato (stato),
  INDEX idx_ghost_kitchen_id_gestore (id_gestore)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE attrezzature (
  id_attrezzatura INT AUTO_INCREMENT PRIMARY KEY,
  id_ghost_kitchen INT NOT NULL,
  nome VARCHAR(120) NOT NULL,
  categoria VARCHAR(80) NOT NULL,
  descrizione TEXT NULL,
  quantita INT NOT NULL,
  CONSTRAINT fk_attrezzature_ghost_kitchen FOREIGN KEY (id_ghost_kitchen)
    REFERENCES ghost_kitchen(id_ghost_kitchen)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT uq_attrezzature_gk_nome_categoria UNIQUE (id_ghost_kitchen, nome, categoria),
  CONSTRAINT chk_attrezzature_quantita CHECK (quantita >= 0),
  INDEX idx_attrezzature_id_ghost_kitchen (id_ghost_kitchen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu (
  id_menu INT AUTO_INCREMENT PRIMARY KEY,
  id_chef INT NOT NULL,
  nome VARCHAR(150) NOT NULL,
  descrizione TEXT NOT NULL,
  prezzo_persona DECIMAL(10,2) NOT NULL,
  attivo BOOLEAN NOT NULL DEFAULT TRUE,
  CONSTRAINT fk_menu_chef FOREIGN KEY (id_chef)
    REFERENCES chef(id_utente)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT uq_menu_id_chef_nome UNIQUE (id_chef, nome),
  CONSTRAINT chk_menu_prezzo_persona CHECK (prezzo_persona >= 0),
  INDEX idx_menu_id_chef (id_chef)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE piatti (
  id_piatto INT AUTO_INCREMENT PRIMARY KEY,
  id_menu INT NOT NULL,
  nome VARCHAR(150) NOT NULL,
  categoria VARCHAR(30) NOT NULL,
  descrizione TEXT NULL,
  ingredienti TEXT NULL,
  allergeni TEXT NULL,
  prezzo_supplemento DECIMAL(10,2) NOT NULL,
  ordine_visualizzazione INT NOT NULL,
  CONSTRAINT fk_piatti_menu FOREIGN KEY (id_menu)
    REFERENCES menu(id_menu)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT uq_piatti_id_menu_ordine UNIQUE (id_menu, ordine_visualizzazione),
  CONSTRAINT chk_piatti_categoria CHECK (categoria IN ('antipasto', 'primo', 'secondo', 'contorno', 'dolce', 'bevanda', 'altro')),
  CONSTRAINT chk_piatti_prezzo_supplemento CHECK (prezzo_supplemento >= 0),
  CONSTRAINT chk_piatti_ordine_visualizzazione CHECK (ordine_visualizzazione >= 0),
  INDEX idx_piatti_id_menu (id_menu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE media (
  id_media INT AUTO_INCREMENT PRIMARY KEY,
  tipo_owner VARCHAR(30) NOT NULL,
  id_owner INT NOT NULL,
  tipo_media VARCHAR(30) NOT NULL,
  nome_file VARCHAR(255) NOT NULL,
  path_file VARCHAR(500) NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  descrizione TEXT NULL,
  data_caricamento DATETIME NOT NULL,
  ordine INT NOT NULL,
  stato VARCHAR(20) NOT NULL DEFAULT 'attivo',
  CONSTRAINT uq_media_owner_ordine UNIQUE (tipo_owner, id_owner, ordine),
  CONSTRAINT chk_media_tipo_owner CHECK (tipo_owner IN ('chef', 'menu', 'ghost_kitchen', 'piatto')),
  CONSTRAINT chk_media_tipo_media CHECK (tipo_media IN ('foto_profilo', 'foto_menu', 'foto_piatto', 'foto_ambiente', 'planimetria', 'generica')),
  CONSTRAINT chk_media_stato CHECK (stato IN ('attivo', 'nascosto', 'rimosso')),
  CONSTRAINT chk_media_ordine CHECK (ordine >= 0),
  INDEX idx_media_tipo_owner_id_owner (tipo_owner, id_owner),
  INDEX idx_media_stato (stato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE certificazioni (
  id_certificazione INT AUTO_INCREMENT PRIMARY KEY,
  id_chef INT NOT NULL,
  tipo VARCHAR(120) NOT NULL,
  nome_file VARCHAR(255) NOT NULL,
  path_file VARCHAR(500) NOT NULL,
  stato VARCHAR(30) NOT NULL DEFAULT 'in_attesa',
  data_caricamento DATETIME NOT NULL,
  data_validazione DATETIME NULL,
  note_admin TEXT NULL,
  CONSTRAINT fk_certificazioni_chef FOREIGN KEY (id_chef)
    REFERENCES chef(id_utente)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT chk_certificazioni_stato CHECK (stato IN ('in_attesa', 'approvata', 'rifiutata')),
  INDEX idx_certificazioni_id_chef (id_chef),
  INDEX idx_certificazioni_stato (stato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE disponibilita_chef (
  id_disponibilita_chef INT AUTO_INCREMENT PRIMARY KEY,
  id_chef INT NOT NULL,
  data DATE NOT NULL,
  ora_inizio TIME NOT NULL,
  ora_fine TIME NOT NULL,
  stato VARCHAR(20) NOT NULL,
  CONSTRAINT fk_disponibilita_chef_chef FOREIGN KEY (id_chef)
    REFERENCES chef(id_utente)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT uq_disponibilita_chef_slot UNIQUE (id_chef, data, ora_inizio, ora_fine),
  CONSTRAINT chk_disponibilita_chef_stato CHECK (stato IN ('libera', 'occupata', 'bloccata')),
  CONSTRAINT chk_disponibilita_chef_ore CHECK (ora_fine > ora_inizio),
  INDEX idx_disponibilita_chef_id_chef (id_chef),
  INDEX idx_disponibilita_chef_stato (stato),
  INDEX idx_disponibilita_chef_data (data)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE disponibilita_ghost_kitchen (
  id_disponibilita_ghost_kitchen INT AUTO_INCREMENT PRIMARY KEY,
  id_ghost_kitchen INT NOT NULL,
  data DATE NOT NULL,
  ora_inizio TIME NOT NULL,
  ora_fine TIME NOT NULL,
  stato VARCHAR(20) NOT NULL,
  CONSTRAINT fk_disponibilita_gk_ghost_kitchen FOREIGN KEY (id_ghost_kitchen)
    REFERENCES ghost_kitchen(id_ghost_kitchen)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT uq_disponibilita_gk_slot UNIQUE (id_ghost_kitchen, data, ora_inizio, ora_fine),
  CONSTRAINT chk_disponibilita_gk_stato CHECK (stato IN ('libera', 'occupata', 'bloccata')),
  CONSTRAINT chk_disponibilita_gk_ore CHECK (ora_fine > ora_inizio),
  INDEX idx_disponibilita_gk_id_ghost_kitchen (id_ghost_kitchen),
  INDEX idx_disponibilita_gk_stato (stato),
  INDEX idx_disponibilita_gk_data (data)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE prenotazioni (
  id_prenotazione INT AUTO_INCREMENT PRIMARY KEY,
  id_richiedente INT NOT NULL,
  data_creazione DATETIME NOT NULL,
  data_servizio DATE NOT NULL,
  ora_inizio TIME NOT NULL,
  ora_fine TIME NOT NULL,
  stato VARCHAR(30) NOT NULL DEFAULT 'in_attesa',
  importo_totale DECIMAL(10,2) NOT NULL,
  note TEXT NULL,
  CONSTRAINT fk_prenotazioni_utenti FOREIGN KEY (id_richiedente)
    REFERENCES utenti(id_utente)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT chk_prenotazioni_stato CHECK (stato IN ('in_attesa', 'accettata', 'rifiutata', 'pagata', 'completata', 'cancellata')),
  CONSTRAINT chk_prenotazioni_importo_totale CHECK (importo_totale >= 0),
  CONSTRAINT chk_prenotazioni_ore CHECK (ora_fine > ora_inizio),
  INDEX idx_prenotazioni_id_richiedente (id_richiedente),
  INDEX idx_prenotazioni_stato (stato),
  INDEX idx_prenotazioni_data_servizio (data_servizio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE prenotazioni_chef (
  id_prenotazione INT PRIMARY KEY,
  id_chef INT NOT NULL,
  id_menu INT NOT NULL,
  indirizzo_servizio VARCHAR(255) NOT NULL,
  numero_persone INT NOT NULL,
  richieste_speciali TEXT NULL,
  CONSTRAINT fk_prenotazioni_chef_prenotazioni FOREIGN KEY (id_prenotazione)
    REFERENCES prenotazioni(id_prenotazione)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_prenotazioni_chef_chef FOREIGN KEY (id_chef)
    REFERENCES chef(id_utente)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_prenotazioni_chef_menu FOREIGN KEY (id_menu)
    REFERENCES menu(id_menu)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT chk_prenotazioni_chef_numero_persone CHECK (numero_persone > 0),
  INDEX idx_prenotazioni_chef_id_chef (id_chef),
  INDEX idx_prenotazioni_chef_id_menu (id_menu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE prenotazioni_ghost_kitchen (
  id_prenotazione INT PRIMARY KEY,
  id_ghost_kitchen INT NOT NULL,
  tipo_richiedente VARCHAR(20) NOT NULL,
  CONSTRAINT fk_prenotazioni_gk_prenotazioni FOREIGN KEY (id_prenotazione)
    REFERENCES prenotazioni(id_prenotazione)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_prenotazioni_gk_ghost_kitchen FOREIGN KEY (id_ghost_kitchen)
    REFERENCES ghost_kitchen(id_ghost_kitchen)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT chk_prenotazioni_gk_tipo_richiedente CHECK (tipo_richiedente IN ('cliente', 'chef')),
  INDEX idx_prenotazioni_gk_id_ghost_kitchen (id_ghost_kitchen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE metodi_pagamento (
  id_metodo_pagamento INT AUTO_INCREMENT PRIMARY KEY,
  id_utente INT NOT NULL,
  tipo VARCHAR(20) NOT NULL,
  intestatario VARCHAR(150) NOT NULL,
  circuito VARCHAR(80) NULL,
  ultime_quattro_cifre CHAR(4) NULL,
  scadenza_mese INT NULL,
  scadenza_anno INT NULL,
  attivo BOOLEAN NOT NULL,
  CONSTRAINT fk_metodi_pagamento_utenti FOREIGN KEY (id_utente)
    REFERENCES utenti(id_utente)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT chk_metodi_pagamento_tipo CHECK (tipo IN ('carta', 'paypal', 'bonifico', 'contanti')),
  CONSTRAINT chk_metodi_pagamento_scadenza_mese CHECK (scadenza_mese IS NULL OR (scadenza_mese BETWEEN 1 AND 12)),
  CONSTRAINT chk_metodi_pagamento_scadenza_anno CHECK (scadenza_anno IS NULL OR scadenza_anno >= 2000),
  INDEX idx_metodi_pagamento_id_utente (id_utente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pagamenti (
  id_pagamento INT AUTO_INCREMENT PRIMARY KEY,
  id_prenotazione INT NOT NULL,
  id_metodo_pagamento INT NULL,
  importo DECIMAL(10,2) NOT NULL,
  tipo_pagamento VARCHAR(20) NOT NULL,
  stato VARCHAR(30) NOT NULL DEFAULT 'in_attesa',
  codice_transazione VARCHAR(120) NULL,
  data_pagamento DATETIME NULL,
  CONSTRAINT fk_pagamenti_prenotazioni FOREIGN KEY (id_prenotazione)
    REFERENCES prenotazioni(id_prenotazione)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_pagamenti_metodi_pagamento FOREIGN KEY (id_metodo_pagamento)
    REFERENCES metodi_pagamento(id_metodo_pagamento)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT uq_pagamenti_codice_transazione UNIQUE (codice_transazione),
  CONSTRAINT chk_pagamenti_tipo CHECK (tipo_pagamento IN ('caparra', 'saldo', 'totale', 'penale')),
  CONSTRAINT chk_pagamenti_stato CHECK (stato IN ('in_attesa', 'autorizzato', 'completato', 'fallito', 'rimborsato', 'parzialmente_rimborsato')),
  CONSTRAINT chk_pagamenti_importo CHECK (importo >= 0),
  INDEX idx_pagamenti_id_prenotazione (id_prenotazione),
  INDEX idx_pagamenti_id_metodo_pagamento (id_metodo_pagamento),
  INDEX idx_pagamenti_stato (stato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cancellazioni (
  id_cancellazione INT AUTO_INCREMENT PRIMARY KEY,
  id_prenotazione INT NOT NULL,
  id_richiedente INT NOT NULL,
  motivo TEXT NULL,
  data_richiesta DATETIME NOT NULL,
  penale_applicata DECIMAL(10,2) NOT NULL,
  importo_rimborsato DECIMAL(10,2) NOT NULL,
  stato VARCHAR(20) NOT NULL DEFAULT 'richiesta',
  CONSTRAINT fk_cancellazioni_prenotazioni FOREIGN KEY (id_prenotazione)
    REFERENCES prenotazioni(id_prenotazione)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_cancellazioni_utenti FOREIGN KEY (id_richiedente)
    REFERENCES utenti(id_utente)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT uq_cancellazioni_id_prenotazione UNIQUE (id_prenotazione),
  CONSTRAINT chk_cancellazioni_stato CHECK (stato IN ('richiesta', 'accettata', 'rifiutata', 'completata')),
  CONSTRAINT chk_cancellazioni_penale CHECK (penale_applicata >= 0),
  CONSTRAINT chk_cancellazioni_importo_rimborsato CHECK (importo_rimborsato >= 0),
  INDEX idx_cancellazioni_id_prenotazione (id_prenotazione),
  INDEX idx_cancellazioni_id_richiedente (id_richiedente),
  INDEX idx_cancellazioni_stato (stato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE recensioni (
  id_recensione INT AUTO_INCREMENT PRIMARY KEY,
  id_autore INT NOT NULL,
  punteggio INT NOT NULL,
  commento TEXT NULL,
  data_recensione DATETIME NOT NULL,
  stato VARCHAR(20) NOT NULL DEFAULT 'visibile',
  CONSTRAINT fk_recensioni_utenti FOREIGN KEY (id_autore)
    REFERENCES utenti(id_utente)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT chk_recensioni_punteggio CHECK (punteggio BETWEEN 1 AND 5),
  CONSTRAINT chk_recensioni_stato CHECK (stato IN ('visibile', 'nascosta', 'rimossa')),
  INDEX idx_recensioni_id_autore (id_autore),
  INDEX idx_recensioni_stato (stato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE recensioni_chef (
  id_recensione INT PRIMARY KEY,
  id_chef INT NOT NULL,
  id_prenotazione_chef INT NOT NULL,
  CONSTRAINT fk_recensioni_chef_recensioni FOREIGN KEY (id_recensione)
    REFERENCES recensioni(id_recensione)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_recensioni_chef_chef FOREIGN KEY (id_chef)
    REFERENCES chef(id_utente)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_recensioni_chef_prenotazioni_chef FOREIGN KEY (id_prenotazione_chef)
    REFERENCES prenotazioni_chef(id_prenotazione)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT uq_recensioni_chef_id_prenotazione_chef UNIQUE (id_prenotazione_chef),
  INDEX idx_recensioni_chef_id_chef (id_chef)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE recensioni_ghost_kitchen (
  id_recensione INT PRIMARY KEY,
  id_ghost_kitchen INT NOT NULL,
  id_prenotazione_ghost_kitchen INT NOT NULL,
  CONSTRAINT fk_recensioni_gk_recensioni FOREIGN KEY (id_recensione)
    REFERENCES recensioni(id_recensione)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_recensioni_gk_ghost_kitchen FOREIGN KEY (id_ghost_kitchen)
    REFERENCES ghost_kitchen(id_ghost_kitchen)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_recensioni_gk_prenotazioni_gk FOREIGN KEY (id_prenotazione_ghost_kitchen)
    REFERENCES prenotazioni_ghost_kitchen(id_prenotazione)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT uq_recensioni_gk_id_prenotazione_ghost_kitchen UNIQUE (id_prenotazione_ghost_kitchen),
  INDEX idx_recensioni_gk_id_ghost_kitchen (id_ghost_kitchen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE segnalazioni (
  id_segnalazione INT AUTO_INCREMENT PRIMARY KEY,
  id_segnalante INT NOT NULL,
  tipo_target VARCHAR(30) NOT NULL,
  id_target INT NOT NULL,
  motivo TEXT NULL,
  descrizione TEXT NULL,
  stato VARCHAR(30) NOT NULL DEFAULT 'aperta',
  data_segnalazione DATETIME NOT NULL,
  data_gestione DATETIME NULL,
  note_admin TEXT NULL,
  CONSTRAINT fk_segnalazioni_utenti FOREIGN KEY (id_segnalante)
    REFERENCES utenti(id_utente)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT chk_segnalazioni_tipo_target CHECK (tipo_target IN ('utente', 'chef', 'ghost_kitchen', 'recensione', 'menu')),
  CONSTRAINT chk_segnalazioni_stato CHECK (stato IN ('aperta', 'in_valutazione', 'risolta', 'archiviata', 'respinta')),
  INDEX idx_segnalazioni_id_segnalante (id_segnalante),
  INDEX idx_segnalazioni_stato (stato),
  INDEX idx_segnalazioni_target (tipo_target, id_target)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rimborsi (
  id_rimborso INT AUTO_INCREMENT PRIMARY KEY,
  id_pagamento INT NOT NULL,
  id_cancellazione INT NOT NULL,
  importo DECIMAL(10,2) NOT NULL,
  motivo TEXT NULL,
  stato VARCHAR(20) NOT NULL DEFAULT 'richiesto',
  data_richiesta DATETIME NOT NULL,
  data_esecuzione DATETIME NULL,
  CONSTRAINT fk_rimborsi_pagamenti FOREIGN KEY (id_pagamento)
    REFERENCES pagamenti(id_pagamento)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_rimborsi_cancellazioni FOREIGN KEY (id_cancellazione)
    REFERENCES cancellazioni(id_cancellazione)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT chk_rimborsi_stato CHECK (stato IN ('richiesto', 'approvato', 'rifiutato', 'eseguito', 'fallito')),
  CONSTRAINT chk_rimborsi_importo CHECK (importo >= 0),
  INDEX idx_rimborsi_id_pagamento (id_pagamento),
  INDEX idx_rimborsi_id_cancellazione (id_cancellazione),
  INDEX idx_rimborsi_stato (stato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

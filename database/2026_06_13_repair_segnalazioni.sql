-- Ripristino mirato per una tabella segnalazioni presente nel catalogo
-- MySQL ma non apribile da InnoDB (errore 1932).
-- Eseguire solo su un'istanza che presenta tale errore.

DROP TABLE IF EXISTS segnalazioni;

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

INSERT INTO segnalazioni (
  id_segnalazione,
  id_segnalante,
  tipo_target,
  id_target,
  motivo,
  descrizione,
  stato,
  data_segnalazione,
  data_gestione,
  note_admin
) VALUES
  (1, 2, 'recensione', 4, 'Linguaggio non professionale', 'La recensione contiene toni poco costruttivi.', 'in_valutazione', '2026-05-09 08:00:00', NULL, NULL),
  (2, 6, 'ghost_kitchen', 3, 'Slot cancellato senza preavviso', 'Richiesta verifica su disservizio del 12 maggio.', 'risolta', '2026-05-13 11:10:00', '2026-05-14 16:00:00', 'Contattato il gestore, applicata procedura interna.'),
  (3, 1, 'menu', 4, 'Descrizione menu fuorviante', 'Alcuni piatti indicati non disponibili in data evento.', 'aperta', '2026-05-16 13:45:00', NULL, NULL),
  (4, 11, 'utente', 10, 'Comportamento scorretto in chat', 'Messaggi non consoni durante trattativa prenotazione.', 'archiviata', '2026-05-21 10:30:00', '2026-05-22 09:00:00', 'Segnalazione non supportata da evidenze sufficienti.');

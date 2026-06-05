SET NAMES utf8mb4;

ALTER TABLE gestori
  ADD COLUMN stato_verifica VARCHAR(30) NOT NULL DEFAULT 'verificato' AFTER id_utente,
  ADD CONSTRAINT chk_gestori_stato_verifica CHECK (stato_verifica IN ('non_verificato', 'in_attesa', 'verificato', 'rifiutato', 'sospeso')),
  ADD INDEX idx_gestori_stato_verifica (stato_verifica);

UPDATE gestori
SET stato_verifica = 'verificato'
WHERE stato_verifica IS NULL OR stato_verifica = '';

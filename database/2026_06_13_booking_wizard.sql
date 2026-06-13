-- Campi additivi per il wizard di prenotazione chef.
-- Script non distruttivo per MySQL 8 e MariaDB recenti.

ALTER TABLE utenti
  ADD COLUMN IF NOT EXISTS via VARCHAR(180) NULL AFTER localita,
  ADD COLUMN IF NOT EXISTS citta VARCHAR(120) NULL AFTER via,
  ADD COLUMN IF NOT EXISTS numero_civico VARCHAR(20) NULL AFTER citta,
  ADD COLUMN IF NOT EXISTS indirizzo VARCHAR(180) NULL AFTER numero_civico,
  ADD COLUMN IF NOT EXISTS provincia VARCHAR(100) NULL AFTER indirizzo;

UPDATE utenti
SET indirizzo = via
WHERE (indirizzo IS NULL OR indirizzo = '')
  AND via IS NOT NULL
  AND via <> '';

UPDATE utenti
SET citta = localita
WHERE (citta IS NULL OR citta = '')
  AND localita IS NOT NULL
  AND localita <> '';

ALTER TABLE prenotazioni_chef
  ADD COLUMN IF NOT EXISTS abbinamento_vini TINYINT(1) NOT NULL DEFAULT 0 AFTER richieste_speciali;

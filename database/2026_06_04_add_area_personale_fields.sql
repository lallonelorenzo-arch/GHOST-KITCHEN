ALTER TABLE utenti
  ADD COLUMN localita VARCHAR(150) NULL AFTER foto_profilo,
  ADD COLUMN biografia TEXT NULL AFTER localita;

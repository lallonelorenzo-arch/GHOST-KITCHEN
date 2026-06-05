SET NAMES utf8mb4;

ALTER TABLE certificazioni
  ADD COLUMN data_scadenza DATE NULL AFTER data_validazione,
  ADD INDEX idx_certificazioni_data_scadenza (data_scadenza);

UPDATE certificazioni
SET data_scadenza = CASE
    WHEN stato = 'approvata' AND LOWER(tipo) LIKE '%haccp%' AND data_validazione IS NOT NULL THEN DATE_ADD(DATE(data_validazione), INTERVAL 3 YEAR)
    WHEN stato = 'approvata' AND data_validazione IS NOT NULL THEN DATE_ADD(DATE(data_validazione), INTERVAL 2 YEAR)
    ELSE NULL
END
WHERE data_scadenza IS NULL;

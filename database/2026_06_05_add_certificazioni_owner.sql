-- Estende certificazioni per supportare chef e ghost kitchen.
-- Eseguire da phpMyAdmin sul DB GhostKitchen gia esistente.

ALTER TABLE certificazioni
  MODIFY id_chef INT NULL,
  ADD COLUMN tipo_owner VARCHAR(30) NOT NULL DEFAULT 'chef' AFTER id_chef,
  ADD COLUMN id_owner INT NULL AFTER tipo_owner;

UPDATE certificazioni
SET tipo_owner = 'chef',
    id_owner = id_chef
WHERE id_owner IS NULL;

ALTER TABLE certificazioni
  MODIFY id_owner INT NOT NULL,
  ADD CONSTRAINT chk_certificazioni_owner CHECK (tipo_owner IN ('chef', 'ghost_kitchen')),
  ADD CONSTRAINT chk_certificazioni_owner_chef CHECK ((tipo_owner = 'chef' AND id_chef IS NOT NULL) OR tipo_owner = 'ghost_kitchen'),
  ADD INDEX idx_certificazioni_owner (tipo_owner, id_owner);

INSERT INTO certificazioni (id_chef, tipo_owner, id_owner, tipo, nome_file, path_file, stato, data_caricamento, data_validazione, note_admin) VALUES
(NULL, 'ghost_kitchen', 1, 'SCIA sanitaria', 'scia_milano_isola.pdf', '/uploads/certificazioni/scia_milano_isola.pdf', 'approvata', '2026-01-18 10:00:00', '2026-01-19 12:00:00', 'Documentazione cucina verificata.'),
(NULL, 'ghost_kitchen', 2, 'HACCP struttura', 'haccp_navigli.pdf', '/uploads/certificazioni/haccp_navigli.pdf', 'approvata', '2026-01-19 10:00:00', '2026-01-21 12:00:00', 'HACCP struttura valido.'),
(NULL, 'ghost_kitchen', 3, 'SCIA sanitaria', 'scia_trastevere.pdf', '/uploads/certificazioni/scia_trastevere.pdf', 'rifiutata', '2026-02-10 10:00:00', '2026-02-12 12:00:00', 'Documento non aggiornato.'),
(NULL, 'ghost_kitchen', 4, 'SCIA sanitaria', 'scia_torino.pdf', '/uploads/certificazioni/scia_torino.pdf', 'approvata', '2026-02-15 10:00:00', '2026-02-16 12:00:00', 'Documentazione approvata.');

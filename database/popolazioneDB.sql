SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Pulizia tabelle in ordine sicuro per FK
DELETE FROM rimborsi;
DELETE FROM recensioni_ghost_kitchen;
DELETE FROM recensioni_chef;
DELETE FROM segnalazioni;
DELETE FROM recensioni;
DELETE FROM cancellazioni;
DELETE FROM pagamenti;
DELETE FROM metodi_pagamento;
DELETE FROM prenotazioni_ghost_kitchen;
DELETE FROM prenotazioni_chef;
DELETE FROM prenotazioni;
DELETE FROM disponibilita_ghost_kitchen;
DELETE FROM disponibilita_chef;
DELETE FROM certificazioni;
DELETE FROM media;
DELETE FROM piatti;
DELETE FROM menu;
DELETE FROM attrezzature;
DELETE FROM ghost_kitchen;
DELETE FROM amministratori;
DELETE FROM gestori;
DELETE FROM chef;
DELETE FROM clienti;
DELETE FROM utenti;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- UTENTI
-- =========================================================
-- Credenziali demo: tutte le password sono Password123!
INSERT INTO utenti (id_utente, nome, cognome, email, password_hash, telefono, stato, localita) VALUES
(1, 'Marco', 'Rinaldi', 'marco.rinaldi@gk.it', '$2y$10$DPTPyYss2m27Fb1j6iLU8eTSaveO/QeQ/69DqO4iIVLpEdVTkVTKa', '+393331111001', 'attivo', 'Milano'),
(2, 'Giulia', 'Conti', 'giulia.conti@gk.it', '$2y$10$DPTPyYss2m27Fb1j6iLU8eTSaveO/QeQ/69DqO4iIVLpEdVTkVTKa', '+393331111002', 'attivo', 'Roma'),
(3, 'Luca', 'Ferri', 'luca.ferri@gk.it', '$2y$10$DPTPyYss2m27Fb1j6iLU8eTSaveO/QeQ/69DqO4iIVLpEdVTkVTKa', '+393331111003', 'attivo', 'Firenze'),
(4, 'Sara', 'Neri', 'sara.neri@gk.it', '$2y$10$DPTPyYss2m27Fb1j6iLU8eTSaveO/QeQ/69DqO4iIVLpEdVTkVTKa', '+393331111004', 'attivo', 'Torino'),
(5, 'Alessandro', 'Bassi', 'alessandro.bassi@gk.it', '$2y$10$DPTPyYss2m27Fb1j6iLU8eTSaveO/QeQ/69DqO4iIVLpEdVTkVTKa', '+393331111005', 'attivo', 'Milano'),
(6, 'Federica', 'Greco', 'federica.greco@gk.it', '$2y$10$DPTPyYss2m27Fb1j6iLU8eTSaveO/QeQ/69DqO4iIVLpEdVTkVTKa', '+393331111006', 'attivo', 'Roma'),
(7, 'Davide', 'Romano', 'davide.romano@gk.it', '$2y$10$DPTPyYss2m27Fb1j6iLU8eTSaveO/QeQ/69DqO4iIVLpEdVTkVTKa', '+393331111007', 'attivo', 'Firenze'),
(8, 'Marta', 'De Luca', 'marta.deluca@gk.it', '$2y$10$DPTPyYss2m27Fb1j6iLU8eTSaveO/QeQ/69DqO4iIVLpEdVTkVTKa', '+393331111008', 'attivo', 'Torino'),
(9, 'Paolo', 'Galli', 'paolo.galli@gk.it', '$2y$10$DPTPyYss2m27Fb1j6iLU8eTSaveO/QeQ/69DqO4iIVLpEdVTkVTKa', '+393331111009', 'attivo', 'Milano'),
(10, 'Elisa', 'Moretti', 'elisa.moretti@gk.it', '$2y$10$DPTPyYss2m27Fb1j6iLU8eTSaveO/QeQ/69DqO4iIVLpEdVTkVTKa', '+393331111010', 'sospeso', 'Roma'),
(11, 'Stefano', 'Costa', 'stefano.costa@gk.it', '$2y$10$DPTPyYss2m27Fb1j6iLU8eTSaveO/QeQ/69DqO4iIVLpEdVTkVTKa', '+393331111011', 'attivo', 'Bologna'),
(12, 'Irene', 'Villa', 'irene.villa@gk.it', '$2y$10$DPTPyYss2m27Fb1j6iLU8eTSaveO/QeQ/69DqO4iIVLpEdVTkVTKa', '+393331111012', 'attivo', 'Napoli');

-- =========================================================
-- RUOLI
-- =========================================================
INSERT INTO clienti (id_utente) VALUES
(1), (2), (3), (4), (11);

INSERT INTO chef (id_utente, biografia, specializzazione, tipologia_cucina, prezzo_base, anni_esperienza, stato_verifica, valutazione_media, numero_recensioni) VALUES
(5, 'Chef privato con focus su cucina mediterranea contemporanea.', 'Cene private e degustazione olio EVO', 'mediterranea', 180.00, 12, 'verificato', 4.80, 16),
(6, 'Specialista in sushi omakase e preparazioni a crudo.', 'Sushi premium e cucina giapponese', 'giapponese', 220.00, 9, 'verificato', 4.60, 11),
(7, 'Chef pastry e brunch creator per eventi corporate.', 'Pasticceria moderna e brunch', 'fusion', 150.00, 7, 'in_attesa', 4.20, 5),
(8, 'Chef tradizione romana e menu stagionali.', 'Cucina romana e comfort food', 'italiana', 140.00, 6, 'non_verificato', 4.10, 3);

INSERT INTO gestori (id_utente, stato_verifica) VALUES
(8, 'verificato'), (9, 'verificato'), (10, 'verificato');

INSERT INTO amministratori (id_utente) VALUES
(12);

-- =========================================================
-- GHOST KITCHEN
-- =========================================================
INSERT INTO ghost_kitchen (id_ghost_kitchen, id_gestore, nome, descrizione, indirizzo, citta, cap, prezzo_orario, capienza, mq, stato, valutazione_media, numero_recensioni) VALUES
(1, 9, 'Milano Isola Lab', 'Cucina professionale modulare per delivery e meal prep.', 'Via Borsieri 21', 'Milano', '20159', 38.00, 18, 145.00, 'attiva', 4.50, 12),
(2, 9, 'Navigli Prep Kitchen', 'Spazio dedicato a format street food e catering leggero.', 'Ripa di Porta Ticinese 77', 'Milano', '20143', 34.00, 14, 120.00, 'attiva', 4.30, 9),
(3, 10, 'Roma Trastevere Hub', 'Ghost kitchen con linea calda e fredda separata.', 'Via della Lungaretta 58', 'Roma', '00153', 31.00, 16, 132.00, 'sospesa', 4.00, 7),
(4, 8, 'Torino Centrale Kitchen', 'Laboratorio per chef privati e micro brand food.', 'Corso Vittorio Emanuele II 86', 'Torino', '10121', 29.00, 12, 110.00, 'attiva', 4.70, 10);

-- =========================================================
-- ATTREZZATURE
-- =========================================================
INSERT INTO attrezzature (id_attrezzatura, id_ghost_kitchen, nome, categoria, descrizione, quantita) VALUES
(1, 1, 'Forno professionale Rational', 'cottura', 'Forno combinato 10 teglie GN 1/1.', 2),
(2, 1, 'Abbattitore Irinox', 'freddo', 'Abbattitore rapido temperatura.', 1),
(3, 1, 'Friggitrice doppia vasca', 'cottura', 'Friggitrice 2x10 litri.', 2),
(4, 1, 'Planetaria 20L', 'preparazione', 'Impasti e montate professionali.', 1),
(5, 1, 'Piano induzione 6 fuochi', 'cottura', 'Postazione induzione ad alta potenza.', 2),
(6, 2, 'Forno statico professionale', 'cottura', 'Forno statico per panificati.', 1),
(7, 2, 'Frigorifero industriale', 'freddo', 'Frigo 1400 litri doppia anta.', 2),
(8, 2, 'Sottovuoto campana', 'preparazione', 'Macchina sottovuoto professionale.', 1),
(9, 2, 'Piastra liscia', 'cottura', 'Piastra acciaio alta resa.', 1),
(10, 2, 'Cutter da banco', 'preparazione', 'Tritatura e emulsioni.', 1),
(11, 3, 'Forno convezione', 'cottura', 'Forno convezione 6 teglie.', 2),
(12, 3, 'Abbattitore 5 teglie', 'freddo', 'Raffreddamento rapido.', 1),
(13, 3, 'Piano induzione 4 fuochi', 'cottura', 'Piano a induzione compatto.', 2),
(14, 3, 'Frigorifero verticale', 'freddo', 'Conservazione prodotti freschi.', 2),
(15, 3, 'Friggitrice singola', 'cottura', 'Friggitrice 8 litri.', 1),
(16, 4, 'Forno pizza elettrico', 'cottura', 'Forno pizza doppia camera.', 1),
(17, 4, 'Impastatrice a spirale', 'preparazione', 'Impastatrice 25 kg.', 1),
(18, 4, 'Banco refrigerato', 'freddo', 'Banco 3 porte con alzatina.', 2),
(19, 4, 'Piano induzione 2 fuochi', 'cottura', 'Postazione supporto.', 2),
(20, 4, 'Abbattitore compatto', 'freddo', 'Abbattitore per pasticceria.', 1);

-- =========================================================
-- MENU
-- =========================================================
INSERT INTO menu (id_menu, id_chef, nome, descrizione, prezzo_persona, attivo) VALUES
(1, 5, 'Mediterraneo Classico', 'Percorso in 4 portate con ingredienti stagionali.', 58.00, TRUE),
(2, 5, 'Mare e Orto', 'Menu leggero pesce e verdure del mercato.', 64.00, TRUE),
(3, 6, 'Sushi Omakase Base', 'Selezione nigiri, hosomaki e uramaki.', 72.00, TRUE),
(4, 6, 'Nikkei Experience', 'Contaminazione giappone-peru con 5 portate.', 79.00, TRUE),
(5, 7, 'Brunch Premium', 'Brunch dolce e salato per eventi mattutini.', 42.00, TRUE),
(6, 7, 'Dessert Tasting', 'Degustazione dessert moderni.', 38.00, FALSE),
(7, 8, 'Roma Tradizione', 'Classici romani in chiave curata.', 49.00, TRUE),
(8, 8, 'Comfort Italiano', 'Piatti iconici italiani per gruppi.', 45.00, TRUE);

-- =========================================================
-- PIATTI (3-4 per menu, ordine univoco per menu)
-- =========================================================
INSERT INTO piatti (id_piatto, id_menu, nome, categoria, descrizione, ingredienti, allergeni, prezzo_supplemento, ordine_visualizzazione) VALUES
(1, 1, 'Carpaccio di ricciola', 'antipasto', 'Ricciola, agrumi e finocchietto.', 'Ricciola, arancia, limone, olio EVO', 'pesce', 6.00, 1),
(2, 1, 'Risotto al limone', 'primo', 'Risotto mantecato al limone.', 'Riso carnaroli, limone, burro', 'latte', 0.00, 2),
(3, 1, 'Orata in crosta', 'secondo', 'Filetto di orata e erbe.', 'Orata, erbe aromatiche', 'pesce', 8.00, 3),
(4, 1, 'Tiramisu espresso', 'dolce', 'Tiramisu monoporzione.', 'Mascarpone, caffe, savoiardi', 'uova,glutine,latte', 3.00, 4),
(5, 2, 'Insalata di polpo', 'antipasto', 'Polpo, patate e prezzemolo.', 'Polpo, patate, prezzemolo', 'molluschi', 4.00, 1),
(6, 2, 'Spaghetto alle vongole', 'primo', 'Classico alle vongole veraci.', 'Spaghetti, vongole, aglio', 'glutine,molluschi', 0.00, 2),
(7, 2, 'Branzino al forno', 'secondo', 'Branzino con verdure.', 'Branzino, zucchine, pomodorini', 'pesce', 7.00, 3),
(8, 2, 'Sorbetto limone', 'dolce', 'Sorbetto artigianale.', 'Acqua, zucchero, limone', NULL, 2.00, 4),
(9, 3, 'Edamame al sale', 'antipasto', 'Baccelli di soia al sale.', 'Edamame, sale marino', 'soia', 0.00, 1),
(10, 3, 'Nigiri misti', 'secondo', '8 pezzi assortiti.', 'Riso sushi, salmone, tonno', 'pesce', 10.00, 2),
(11, 3, 'Uramaki avocado', 'secondo', '8 pezzi avocado e sesamo.', 'Riso sushi, avocado, sesamo', 'sesamo', 5.00, 3),
(12, 3, 'Mochi gelato', 'dolce', 'Mochi artigianali.', 'Riso glutinoso, latte', 'latte', 3.00, 4),
(13, 4, 'Tartare nikkei', 'antipasto', 'Tonno, leche de tigre, mais.', 'Tonno, lime, peperoncino', 'pesce', 9.00, 1),
(14, 4, 'Ramen secco fusion', 'primo', 'Noodles con salsa intensa.', 'Noodles, soia, zenzero', 'glutine,soia', 6.00, 2),
(15, 4, 'Pollo teriyaki', 'secondo', 'Pollo glassato teriyaki.', 'Pollo, salsa teriyaki', 'soia', 4.00, 3),
(16, 4, 'Cheesecake yuzu', 'dolce', 'Cheesecake al profumo di yuzu.', 'Formaggio, yuzu, biscotto', 'latte,glutine', 3.00, 4),
(17, 5, 'Toast avocado e uovo', 'altro', 'Toast integrale con avocado.', 'Pane, avocado, uova', 'glutine,uova', 2.00, 1),
(18, 5, 'Pancake ai frutti rossi', 'dolce', 'Pancake con coulis.', 'Farina, latte, frutti rossi', 'glutine,latte,uova', 3.00, 2),
(19, 5, 'Club sandwich gourmet', 'secondo', 'Sandwich a triplo strato.', 'Pane, pollo, lattuga', 'glutine', 4.00, 3),
(20, 5, 'Centrifuga detox', 'bevanda', 'Mela, sedano e zenzero.', 'Mela, sedano, zenzero', NULL, 1.50, 4),
(21, 6, 'Paris-brest moderno', 'dolce', 'Crema nocciola leggera.', 'Pasta choux, nocciola', 'glutine,frutta a guscio,latte', 3.00, 1),
(22, 6, 'Entremet cioccolato', 'dolce', 'Mousse e biscuit cacao.', 'Cioccolato, panna, uova', 'uova,latte', 4.00, 2),
(23, 6, 'Crostatina limone', 'dolce', 'Meringa italiana.', 'Farina, burro, limone', 'glutine,latte,uova', 2.50, 3),
(24, 7, 'Suppli classico', 'antipasto', 'Riso al sugo e mozzarella.', 'Riso, pomodoro, mozzarella', 'latte,glutine', 2.00, 1),
(25, 7, 'Carbonara tradizionale', 'primo', 'Guanciale, pecorino, uovo.', 'Pasta, guanciale, pecorino', 'glutine,uova,latte', 0.00, 2),
(26, 7, 'Saltimbocca alla romana', 'secondo', 'Vitello, prosciutto e salvia.', 'Vitello, prosciutto, salvia', NULL, 5.00, 3),
(27, 7, 'Cicoria ripassata', 'contorno', 'Cicoria aglio e peperoncino.', 'Cicoria, aglio, olio', NULL, 1.50, 4),
(28, 8, 'Bruschette miste', 'antipasto', 'Pane e condimenti stagionali.', 'Pane, pomodoro, olio', 'glutine', 1.50, 1),
(29, 8, 'Lasagna classica', 'primo', 'Ragu tradizionale.', 'Sfoglia, carne, besciamella', 'glutine,latte', 3.00, 2),
(30, 8, 'Polpette al sugo', 'secondo', 'Polpette morbide al pomodoro.', 'Carne mista, pomodoro', 'uova', 2.50, 3),
(31, 8, 'Acqua aromatizzata', 'bevanda', 'Acqua con agrumi e menta.', 'Acqua, limone, menta', NULL, 0.00, 4);

-- =========================================================
-- MEDIA
-- =========================================================
INSERT INTO media (id_media, tipo_owner, id_owner, tipo_media, nome_file, path_file, mime_type, descrizione, data_caricamento, ordine, stato) VALUES
(1, 'chef', 5, 'foto_profilo', 'chef5_profile.jpg', '/uploads/chef/chef5_profile.jpg', 'image/jpeg', 'Profilo chef Marco Bassi', '2026-02-01 10:00:00', 0, 'attivo'),
(2, 'chef', 6, 'foto_profilo', 'chef6_profile.jpg', '/uploads/chef/chef6_profile.jpg', 'image/jpeg', 'Profilo chef Federica Greco', '2026-02-01 10:10:00', 0, 'attivo'),
(3, 'chef', 7, 'foto_profilo', 'chef7_profile.jpg', '/uploads/chef/chef7_profile.jpg', 'image/jpeg', 'Profilo chef Davide Romano', '2026-02-01 10:20:00', 0, 'attivo'),
(4, 'chef', 8, 'foto_profilo', 'chef8_profile.jpg', '/uploads/chef/chef8_profile.jpg', 'image/jpeg', 'Profilo chef Marta De Luca', '2026-02-01 10:30:00', 0, 'attivo'),
(5, 'menu', 1, 'foto_menu', 'menu1_cover.jpg', '/uploads/menu/menu1_cover.jpg', 'image/jpeg', 'Copertina menu Mediterraneo Classico', '2026-02-02 11:00:00', 0, 'attivo'),
(6, 'menu', 3, 'foto_menu', 'menu3_cover.jpg', '/uploads/menu/menu3_cover.jpg', 'image/jpeg', 'Copertina menu Sushi Omakase Base', '2026-02-02 11:10:00', 0, 'attivo'),
(7, 'ghost_kitchen', 1, 'foto_ambiente', 'gk1_ambiente.jpg', '/uploads/ghost_kitchen/gk1_ambiente.jpg', 'image/jpeg', 'Ambiente principale Milano Isola Lab', '2026-01-15 09:00:00', 0, 'attivo'),
(8, 'ghost_kitchen', 1, 'planimetria', 'gk1_plan.pdf.jpg', '/uploads/ghost_kitchen/gk1_plan.jpg', 'image/jpeg', 'Planimetria semplificata GK1', '2026-01-15 09:05:00', 1, 'attivo'),
(9, 'ghost_kitchen', 4, 'foto_ambiente', 'gk4_ambiente.jpg', '/uploads/ghost_kitchen/gk4_ambiente.jpg', 'image/jpeg', 'Ambiente Torino Centrale Kitchen', '2026-01-16 10:00:00', 0, 'attivo'),
(10, 'piatto', 10, 'foto_piatto', 'piatto10_nigiri.jpg', '/uploads/piatti/piatto10_nigiri.jpg', 'image/jpeg', 'Nigiri misti', '2026-02-03 12:00:00', 0, 'attivo'),
(11, 'piatto', 25, 'foto_piatto', 'piatto25_carb.jpg', '/uploads/piatti/piatto25_carb.jpg', 'image/jpeg', 'Carbonara tradizionale', '2026-02-03 12:15:00', 0, 'attivo'),
(12, 'menu', 7, 'generica', 'menu7_extra.jpg', '/uploads/menu/menu7_extra.jpg', 'image/jpeg', 'Dettaglio impiattamento menu romano', '2026-02-03 12:30:00', 1, 'attivo');

-- =========================================================
-- CERTIFICAZIONI
-- =========================================================
INSERT INTO certificazioni (id_certificazione, id_chef, tipo_owner, id_owner, tipo, nome_file, path_file, stato, data_caricamento, data_validazione, data_scadenza, note_admin) VALUES
(1, 5, 'chef', 5, 'HACCP Livello 3', 'haccp_marco.pdf', '/uploads/certificazioni/haccp_marco.pdf', 'approvata', '2026-01-20 08:00:00', '2026-01-22 14:00:00', '2029-01-22', 'Documentazione completa e valida.'),
(2, 6, 'chef', 6, 'Food Safety Manager', 'fsm_federica.pdf', '/uploads/certificazioni/fsm_federica.pdf', 'approvata', '2026-01-21 09:30:00', '2026-01-23 16:10:00', '2028-01-23', 'Certificazione verificata con ente emittente.'),
(3, 7, 'chef', 7, 'Corso Pasticceria Avanzata', 'pastry_davide.pdf', '/uploads/certificazioni/pastry_davide.pdf', 'in_attesa', '2026-03-02 11:00:00', NULL, NULL, NULL),
(4, 8, 'chef', 8, 'Corso Cucina Regionale', 'regionale_marta.pdf', '/uploads/certificazioni/regionale_marta.pdf', 'rifiutata', '2026-02-11 12:00:00', '2026-02-13 10:00:00', NULL, 'Documento illeggibile, richiesto nuovo upload.'),
(5, NULL, 'ghost_kitchen', 1, 'SCIA sanitaria', 'scia_milano_isola.pdf', '/uploads/certificazioni/scia_milano_isola.pdf', 'approvata', '2026-01-18 10:00:00', '2026-01-19 12:00:00', '2028-01-19', 'Documentazione cucina verificata.'),
(6, NULL, 'ghost_kitchen', 2, 'HACCP struttura', 'haccp_navigli.pdf', '/uploads/certificazioni/haccp_navigli.pdf', 'approvata', '2026-01-19 10:00:00', '2026-01-21 12:00:00', '2029-01-21', 'HACCP struttura valido.'),
(7, NULL, 'ghost_kitchen', 3, 'SCIA sanitaria', 'scia_trastevere.pdf', '/uploads/certificazioni/scia_trastevere.pdf', 'rifiutata', '2026-02-10 10:00:00', '2026-02-12 12:00:00', NULL, 'Documento non aggiornato.'),
(8, NULL, 'ghost_kitchen', 4, 'SCIA sanitaria', 'scia_torino.pdf', '/uploads/certificazioni/scia_torino.pdf', 'approvata', '2026-02-15 10:00:00', '2026-02-16 12:00:00', '2028-02-16', 'Documentazione approvata.');

-- =========================================================
-- DISPONIBILITA CHEF
-- =========================================================
INSERT INTO disponibilita_chef (id_disponibilita_chef, id_chef, data, ora_inizio, ora_fine, stato) VALUES
(1, 5, '2026-05-05', '19:00:00', '23:00:00', 'occupata'),
(2, 5, '2026-06-10', '19:00:00', '23:00:00', 'occupata'),
(3, 5, '2026-06-20', '19:00:00', '23:00:00', 'libera'),
(4, 6, '2026-05-18', '20:00:00', '23:30:00', 'occupata'),
(5, 6, '2026-06-15', '20:00:00', '23:30:00', 'occupata'),
(6, 6, '2026-07-02', '20:00:00', '23:30:00', 'libera'),
(7, 7, '2026-05-12', '10:00:00', '14:00:00', 'occupata'),
(8, 7, '2026-06-22', '09:00:00', '13:00:00', 'libera'),
(9, 8, '2026-05-28', '19:30:00', '23:00:00', 'libera'),
(10, 8, '2026-06-05', '19:30:00', '23:00:00', 'occupata');

-- =========================================================
-- DISPONIBILITA GHOST KITCHEN
-- =========================================================
INSERT INTO disponibilita_ghost_kitchen (id_disponibilita_ghost_kitchen, id_ghost_kitchen, data, ora_inizio, ora_fine, stato) VALUES
(1, 1, '2026-05-06', '09:00:00', '13:00:00', 'occupata'),
(2, 1, '2026-06-08', '14:00:00', '19:00:00', 'occupata'),
(3, 1, '2026-06-20', '09:00:00', '13:00:00', 'libera'),
(4, 2, '2026-05-07', '10:00:00', '15:00:00', 'occupata'),
(5, 2, '2026-06-11', '10:00:00', '15:00:00', 'libera'),
(6, 3, '2026-05-12', '16:00:00', '21:00:00', 'bloccata'),
(7, 3, '2026-06-18', '16:00:00', '21:00:00', 'libera'),
(8, 4, '2026-05-25', '08:00:00', '12:00:00', 'occupata'),
(9, 4, '2026-06-25', '08:00:00', '12:00:00', 'libera'),
(10, 4, '2026-07-01', '14:00:00', '18:00:00', 'libera');

-- =========================================================
-- PRENOTAZIONI BASE
-- =========================================================
INSERT INTO prenotazioni (id_prenotazione, id_richiedente, data_creazione, data_servizio, ora_inizio, ora_fine, stato, importo_totale, note) VALUES
(1, 1, '2026-04-20 10:00:00', '2026-05-05', '19:00:00', '23:00:00', 'completata', 696.00, 'Cena anniversario 12 persone.'),
(2, 2, '2026-05-30 12:10:00', '2026-06-10', '19:00:00', '23:00:00', 'pagata', 522.00, 'Cena aziendale 9 persone.'),
(3, 3, '2026-04-28 15:00:00', '2026-05-18', '20:00:00', '23:30:00', 'completata', 648.00, 'Compleanno privato 8 persone.'),
(4, 4, '2026-05-14 09:40:00', '2026-06-15', '20:00:00', '23:30:00', 'accettata', 474.00, 'Cena in terrazza 6 persone.'),
(5, 11, '2026-05-22 11:50:00', '2026-06-05', '19:30:00', '23:00:00', 'cancellata', 392.00, 'Cena famiglia allargata.'),
(6, 5, '2026-04-30 08:30:00', '2026-05-06', '09:00:00', '13:00:00', 'completata', 152.00, 'Uso cucina per prep menu evento.'),
(7, 1, '2026-05-12 16:20:00', '2026-06-08', '14:00:00', '19:00:00', 'pagata', 190.00, 'Batch cooking settimanale.'),
(8, 6, '2026-05-01 13:10:00', '2026-05-07', '10:00:00', '15:00:00', 'completata', 170.00, 'Produzione sushi catering.'),
(9, 2, '2026-05-18 18:00:00', '2026-06-25', '08:00:00', '12:00:00', 'in_attesa', 116.00, 'Prep brunch aziendale.'),
(10, 7, '2026-05-08 17:45:00', '2026-06-18', '16:00:00', '21:00:00', 'rifiutata', 155.00, 'Richiesta orario serale non confermata.');

-- =========================================================
-- PRENOTAZIONI CHEF (5)
-- =========================================================
INSERT INTO prenotazioni_chef (id_prenotazione, id_chef, id_menu, indirizzo_servizio, numero_persone, richieste_speciali) VALUES
(1, 5, 1, 'Via Solferino 12, Milano', 12, 'No lattosio per due ospiti.'),
(2, 5, 2, 'Corso Sempione 55, Milano', 9, 'Prediligere opzioni pesce.'),
(3, 6, 3, 'Via Appia Nuova 310, Roma', 8, 'Tavolo interno climatizzato.'),
(4, 6, 4, 'Via Tuscolana 101, Roma', 6, 'Menu ridotto piccante moderato.'),
(5, 8, 7, 'Via Po 9, Torino', 8, 'Antipasti serviti al centro tavola.');

-- =========================================================
-- PRENOTAZIONI GHOST KITCHEN (5)
-- =========================================================
INSERT INTO prenotazioni_ghost_kitchen (id_prenotazione, id_ghost_kitchen, tipo_richiedente) VALUES
(6, 1, 'chef'),
(7, 1, 'cliente'),
(8, 2, 'chef'),
(9, 4, 'cliente'),
(10, 3, 'chef');

-- =========================================================
-- METODI PAGAMENTO
-- =========================================================
INSERT INTO metodi_pagamento (id_metodo_pagamento, id_utente, tipo, intestatario, circuito, ultime_quattro_cifre, scadenza_mese, scadenza_anno, attivo) VALUES
(1, 1, 'carta', 'Marco Rinaldi', 'Visa', '4242', 12, 2028, TRUE),
(2, 2, 'paypal', 'Giulia Conti', NULL, NULL, NULL, NULL, TRUE),
(3, 3, 'carta', 'Luca Ferri', 'Mastercard', '5511', 7, 2027, TRUE),
(4, 4, 'bonifico', 'Sara Neri', NULL, NULL, NULL, NULL, TRUE),
(5, 5, 'contanti', 'Alessandro Bassi', NULL, NULL, NULL, NULL, TRUE),
(6, 6, 'carta', 'Federica Greco', 'Amex', '3005', 3, 2029, TRUE),
(7, 11, 'carta', 'Stefano Costa', 'Visa', '9876', 9, 2027, TRUE);

-- =========================================================
-- PAGAMENTI
-- =========================================================
INSERT INTO pagamenti (id_pagamento, id_prenotazione, id_metodo_pagamento, importo, tipo_pagamento, stato, codice_transazione, data_pagamento) VALUES
(1, 1, 1, 200.00, 'caparra', 'completato', 'TXN-2026-0001', '2026-04-20 10:15:00'),
(2, 1, 1, 496.00, 'saldo', 'completato', 'TXN-2026-0002', '2026-05-05 23:10:00'),
(3, 2, 2, 522.00, 'totale', 'autorizzato', 'TXN-2026-0003', '2026-05-30 12:20:00'),
(4, 3, 3, 648.00, 'totale', 'completato', 'TXN-2026-0004', '2026-04-28 15:10:00'),
(5, 4, 4, 150.00, 'caparra', 'completato', 'TXN-2026-0005', '2026-05-14 09:50:00'),
(6, 5, 7, 392.00, 'totale', 'parzialmente_rimborsato', 'TXN-2026-0006', '2026-05-22 12:00:00'),
(7, 6, 6, 152.00, 'totale', 'completato', 'TXN-2026-0007', '2026-04-30 08:40:00'),
(8, 7, 1, 190.00, 'totale', 'in_attesa', 'TXN-2026-0008', NULL),
(9, 8, 6, 170.00, 'totale', 'completato', 'TXN-2026-0009', '2026-05-01 13:20:00'),
(10, 9, 2, 116.00, 'totale', 'fallito', 'TXN-2026-0010', '2026-05-18 18:10:00'),
(11, 5, 7, 20.00, 'penale', 'completato', 'TXN-2026-0011', '2026-05-24 10:00:00');

-- =========================================================
-- CANCELLAZIONI
-- =========================================================
INSERT INTO cancellazioni (id_cancellazione, id_prenotazione, id_richiedente, motivo, data_richiesta, penale_applicata, importo_rimborsato, stato) VALUES
(1, 5, 11, 'Imprevisto familiare, impossibile confermare la presenza.', '2026-05-24 09:30:00', 20.00, 372.00, 'completata'),
(2, 10, 7, 'Cambio programma evento, non piu necessario lo spazio.', '2026-05-20 14:10:00', 0.00, 0.00, 'rifiutata');

-- =========================================================
-- RIMBORSI
-- =========================================================
INSERT INTO rimborsi (id_rimborso, id_pagamento, id_cancellazione, importo, motivo, stato, data_richiesta, data_esecuzione) VALUES
(1, 6, 1, 372.00, 'Rimborso totale meno penale su prenotazione cancellata.', 'eseguito', '2026-05-24 10:15:00', '2026-05-25 11:30:00');

-- =========================================================
-- RECENSIONI (solo prenotazioni completate)
-- =========================================================
INSERT INTO recensioni (id_recensione, id_autore, punteggio, commento, data_recensione, stato) VALUES
(1, 1, 5, 'Esperienza eccellente, servizio preciso e menu curato.', '2026-05-06 10:00:00', 'visibile'),
(2, 3, 4, 'Sushi molto buono e ottima organizzazione generale.', '2026-05-19 09:20:00', 'visibile'),
(3, 5, 5, 'Spazio pulito e attrezzatura completa, molto soddisfatto.', '2026-05-07 18:40:00', 'visibile'),
(4, 6, 4, 'Kitchen funzionale, migliorabile la zona stoccaggio.', '2026-05-08 12:10:00', 'visibile');

INSERT INTO recensioni_chef (id_recensione, id_chef, id_prenotazione_chef) VALUES
(1, 5, 1),
(2, 6, 3);

INSERT INTO recensioni_ghost_kitchen (id_recensione, id_ghost_kitchen, id_prenotazione_ghost_kitchen) VALUES
(3, 1, 6),
(4, 2, 8);

-- =========================================================
-- SEGNALAZIONI
-- =========================================================
INSERT INTO segnalazioni (id_segnalazione, id_segnalante, tipo_target, id_target, motivo, descrizione, stato, data_segnalazione, data_gestione, note_admin) VALUES
(1, 2, 'recensione', 4, 'Linguaggio non professionale', 'La recensione contiene toni poco costruttivi.', 'in_valutazione', '2026-05-09 08:00:00', NULL, NULL),
(2, 6, 'ghost_kitchen', 3, 'Slot cancellato senza preavviso', 'Richiesta verifica su disservizio del 12 maggio.', 'risolta', '2026-05-13 11:10:00', '2026-05-14 16:00:00', 'Contattato il gestore, applicata procedura interna.'),
(3, 1, 'menu', 4, 'Descrizione menu fuorviante', 'Alcuni piatti indicati non disponibili in data evento.', 'aperta', '2026-05-16 13:45:00', NULL, NULL),
(4, 11, 'utente', 10, 'Comportamento scorretto in chat', 'Messaggi non consoni durante trattativa prenotazione.', 'archiviata', '2026-05-21 10:30:00', '2026-05-22 09:00:00', 'Segnalazione non supportata da evidenze sufficienti.');

-- =========================================================
-- SELECT DI CONTROLLO (facoltative)
-- =========================================================
-- SELECT COUNT(*) AS utenti_totali FROM utenti;
-- SELECT COUNT(*) AS chef_totali FROM chef;
-- SELECT COUNT(*) AS ghost_kitchen_totali FROM ghost_kitchen;
-- SELECT p.id_prenotazione, p.stato, pc.id_chef, pgk.id_ghost_kitchen
-- FROM prenotazioni p
-- LEFT JOIN prenotazioni_chef pc ON pc.id_prenotazione = p.id_prenotazione
-- LEFT JOIN prenotazioni_ghost_kitchen pgk ON pgk.id_prenotazione = p.id_prenotazione
-- ORDER BY p.id_prenotazione;
-- SELECT r.id_recensione, rc.id_chef, rgk.id_ghost_kitchen
-- FROM recensioni r
-- LEFT JOIN recensioni_chef rc ON rc.id_recensione = r.id_recensione
-- LEFT JOIN recensioni_ghost_kitchen rgk ON rgk.id_recensione = r.id_recensione
-- ORDER BY r.id_recensione;

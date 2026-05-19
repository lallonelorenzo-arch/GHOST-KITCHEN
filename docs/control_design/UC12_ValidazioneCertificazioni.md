# UC12 - Validazione Certificazioni

## Attori
- Amministratore di sistema

## Scenario principale di successo
1. L'admin visualizza certificazioni in attesa.
2. Il sistema mostra l'elenco.
3. L'admin seleziona una certificazione.
4. Il sistema mostra dettaglio certificazione.
5. L'admin approva o rifiuta con eventuali note.
6. Il sistema aggiorna lo stato.
7. Il sistema restituisce conferma.

## SSD testuale
- Admin -> Sistema: visualizzaCertificazioniInAttesa()
- Sistema -> Admin: elencoCertificazioni
- Admin -> Sistema: visualizzaDettaglioCertificazione(idCertificazione)
- Sistema -> Admin: dettaglioCertificazione
- Admin -> Sistema: approvaCertificazione(idCertificazione, noteAdmin)
- Sistema -> Admin: certificazioneApprovata
- Admin -> Sistema: rifiutaCertificazione(idCertificazione, noteAdmin)
- Sistema -> Admin: certificazioneRifiutata

## Operazioni di sistema
- visualizzaCertificazioniInAttesa()
- visualizzaDettaglioCertificazione(idCertificazione)
- approvaCertificazione(idCertificazione, noteAdmin)
- rifiutaCertificazione(idCertificazione, noteAdmin)

## Classe Control
- CValidazioneCertificazioni

## Metodi Control
- public static function visualizzaCertificazioniInAttesa(): array
- public static function visualizzaDettaglioCertificazione(int $idCertificazione): array
- public static function approvaCertificazione(int $idCertificazione, string $noteAdmin = ''): array
- public static function rifiutaCertificazione(int $idCertificazione, string $noteAdmin = ''): array

## URL associate
- /ValidazioneCertificazioni/visualizzaCertificazioniInAttesa
- /ValidazioneCertificazioni/visualizzaDettaglioCertificazione
- /ValidazioneCertificazioni/approvaCertificazione
- /ValidazioneCertificazioni/rifiutaCertificazione

## Entity coinvolte
- ECertificazione
- EChef
- EAmministratore

## Servizi richiesti a FPersistentManager fittizio
- loadCertificazioniInAttesa()
- loadCertificazione(idCertificazione)
- updateCertificazione(ECertificazione $certificazione)

## Note progettuali
Non viene implementato upload file: qui si valida solo una certificazione gia' caricata. ECertificazione non espone approva/rifiuta, quindi il Control usa setStato(), setNoteAdmin() e setDataValidazione().

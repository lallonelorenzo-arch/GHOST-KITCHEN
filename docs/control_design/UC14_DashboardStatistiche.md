# UC14 - Dashboard Statistiche

## Attori
- Amministratore di sistema

## Scenario principale di successo
1. L'admin accede alla dashboard.
2. Il sistema recupera dati aggregati.
3. Il sistema mostra volumi prenotazioni e performance piattaforma.
4. L'admin puo' applicare filtri temporali.
5. Il sistema aggiorna i dati aggregati.

## SSD testuale
- Admin -> Sistema: visualizzaDashboard(filtri)
- Sistema -> Admin: statisticheDashboard

## Operazioni di sistema
- visualizzaDashboard(filtri)

## Classe Control
- CDashboardStatistiche

## Metodi Control
- public static function visualizzaDashboard(array $filtri = []): array

## URL associate
- /DashboardStatistiche/visualizzaDashboard

## Entity coinvolte
- EPrenotazioneChef
- EPrenotazioneGhostKitchen
- EPagamento
- ERecensioneChef
- ERecensioneGhostKitchen
- ESegnalazione
- ECertificazione
- EChef
- EGhostKitchen

## Servizi richiesti a FPersistentManager fittizio
- getStatisticheDashboard(filtri)
- getStatistichePrenotazioni(filtri)
- getStatistichePagamenti(filtri)
- getStatisticheRecensioni(filtri)
- getStatisticheModerazione(filtri)

## Note progettuali
La dashboard e' solo lettura. Non vengono create Entity statistiche: FPersistentManager restituisce array aggregati hardcoded per prenotazioni, pagamenti, recensioni e moderazione.

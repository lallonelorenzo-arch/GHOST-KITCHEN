# UC8 - Elaborazione Pagamento

## Attori
- Cliente registrato
- Chef professionista (se prenota ghost kitchen)

## Scenario principale di successo
1. L'attore avvia pagamento della prenotazione.
2. Il sistema mostra prenotazione e importo totale.
3. L'attore conferma.
4. Il sistema invia idealmente i dati di utente e prenotazione al sistema interbancario simulato.
5. Il sistema assume risposta positiva e registra il pagamento come completato.
6. Il sistema restituisce esito e riepilogo.

## SSD testuale
- Attore -> Sistema: avviaPagamento(tipoPrenotazione, idPrenotazione)
- Sistema -> Attore: datiPagamento
- Attore -> Sistema: confermaPagamento(datiPagamento)
- Sistema -> Attore: esitoPagamento

## Operazioni di sistema
- avviaPagamento(tipoPrenotazione, idPrenotazione)
- confermaPagamento(datiPagamento)

## Classe Control
- CPagamento

## Metodi Control
- public function avviaPagamento(string $tipoPrenotazione, int $idPrenotazione): array
- public static function confermaPagamento(array $datiPagamento): array

## URL associate
- /Pagamento/avviaPagamento
- /Pagamento/confermaPagamento

## Entity coinvolte
- EPagamento
- EPrenotazioneChef
- EPrenotazioneGhostKitchen

## Servizi richiesti a FPersistentManager fittizio
- loadPrenotazioneChef(idPrenotazione)
- loadPrenotazioneGhostKitchen(idPrenotazione)
- calcolaImportoPagamento(tipoPrenotazione, idPrenotazione)
- storePagamento(EPagamento $pagamento)

## Note progettuali
- Pagamento simulato: l'ipotetico sistema interbancario restituisce sempre esito positivo.
- Il pagamento viene salvato direttamente come `completato`.
- Non vengono raccolti o salvati dati di carta; la simulazione usa utente, prenotazione e importo.

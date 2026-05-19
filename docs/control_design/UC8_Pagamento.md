# UC8 - Elaborazione Pagamento

## Attori
- Cliente registrato
- Chef professionista (se prenota ghost kitchen)

## Scenario principale di successo
1. L'attore avvia pagamento della prenotazione.
2. Il sistema mostra importo, tipo pagamento, metodi disponibili.
3. L'attore seleziona metodo pagamento.
4. L'attore conferma.
5. Il sistema simula autorizzazione/completamento.
6. Il sistema aggiorna stato pagamento.
7. Il sistema restituisce esito e riepilogo.

## SSD testuale
- Attore -> Sistema: avviaPagamento(tipoPrenotazione, idPrenotazione, tipoPagamento)
- Sistema -> Attore: datiPagamento
- Attore -> Sistema: selezionaMetodoPagamento(idMetodoPagamento)
- Sistema -> Attore: metodoPagamentoSelezionato
- Attore -> Sistema: confermaPagamento(datiPagamento)
- Sistema -> Attore: esitoPagamento

## Operazioni di sistema
- avviaPagamento(tipoPrenotazione, idPrenotazione, tipoPagamento)
- selezionaMetodoPagamento(idMetodoPagamento)
- confermaPagamento(datiPagamento)

## Classe Control
- CPagamento

## Metodi Control
- public static function avviaPagamento(string $tipoPrenotazione, int $idPrenotazione, string $tipoPagamento): array
- public static function selezionaMetodoPagamento(int $idMetodoPagamento): array
- public static function confermaPagamento(array $datiPagamento): array

## URL associate
- /Pagamento/avviaPagamento
- /Pagamento/selezionaMetodoPagamento
- /Pagamento/confermaPagamento

## Entity coinvolte
- EPagamento
- EMetodoPagamento
- EPrenotazioneChef
- EPrenotazioneGhostKitchen

## Servizi richiesti a FPersistentManager fittizio
- loadPrenotazioneChef(idPrenotazione)
- loadPrenotazioneGhostKitchen(idPrenotazione)
- calcolaImportoPagamento(tipoPrenotazione, idPrenotazione, tipoPagamento)
- loadMetodiPagamentoByUtente(idUtente)
- loadMetodoPagamento(idMetodoPagamento)
- storePagamento(EPagamento $pagamento)
- updatePagamento(EPagamento $pagamento)

## Note progettuali
- Pagamento simulato, nessun gateway esterno.
- Gestiti tipi pagamento: caparra, saldo, totale, penale.

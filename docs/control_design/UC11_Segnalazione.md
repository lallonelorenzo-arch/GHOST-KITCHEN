# UC11 - Invio Segnalazione

## Attori
- Cliente registrato
- Chef professionista
- Gestore Ghost Kitchen

## Scenario principale di successo
1. L'attore avvia una segnalazione.
2. Il sistema mostra i dati minimi del target segnalato.
3. L'attore inserisce motivo e descrizione.
4. Il sistema registra la segnalazione in stato aperta.
5. Il sistema restituisce conferma.

## SSD testuale
- Attore -> Sistema: avviaSegnalazione(idSegnalante, tipoTarget, idTarget)
- Sistema -> Attore: datiSegnalazione
- Attore -> Sistema: inviaSegnalazione(datiSegnalazione)
- Sistema -> Attore: segnalazioneRegistrata

## Operazioni di sistema
- avviaSegnalazione(idSegnalante, tipoTarget, idTarget)
- inviaSegnalazione(datiSegnalazione)

## Classe Control
- CSegnalazione

## Metodi Control
- public static function avviaSegnalazione(int $idSegnalante, string $tipoTarget, int $idTarget): array
- public static function inviaSegnalazione(array $datiSegnalazione): array

## URL associate
- /Segnalazione/avviaSegnalazione
- /Segnalazione/inviaSegnalazione

## Entity coinvolte
- ESegnalazione
- EUtente
- EChef
- EGhostKitchen
- ERecensione
- EMenu

## Servizi richiesti a FPersistentManager fittizio
- loadUtente(idUtente)
- loadTargetSegnalazione(tipoTarget, idTarget)
- storeSegnalazione(ESegnalazione $segnalazione)

## Note progettuali
ESegnalazione rimane generica e usa tipoTarget + idTarget. I valori ammessi sono utente, chef, ghost_kitchen, recensione e menu.

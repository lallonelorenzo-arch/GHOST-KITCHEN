<?php
declare(strict_types=1);

require_once __DIR__ . '/../Entity/EUtente.php';
require_once __DIR__ . '/../Entity/EChef.php';
require_once __DIR__ . '/../Entity/EGhostKitchen.php';
require_once __DIR__ . '/../Entity/EMedia.php';
require_once __DIR__ . '/../Entity/EMenu.php';
require_once __DIR__ . '/../Entity/EPiatto.php';
require_once __DIR__ . '/../Entity/EAttrezzatura.php';
require_once __DIR__ . '/../Entity/ECertificazione.php';

/**
 * PersistentManager fittizio per test di UC1.
 * Non usa DB, SQL o sessioni reali.
 */
class FPersistentManager
{
    /**
     * @return EChef[]
     */
    public static function cercaChef(
        string $localita,
        string $tipologiaCucina,
        float $budgetMax,
        int $valutazioneMin
    ): array {
        $chefDisponibili = [
            new EChef(
                1,
                'Marco',
                'Rossi',
                'marco.rossi@example.com',
                'hash-marco',
                '+39061234567',
                EUtente::STATO_ATTIVO,
                'Chef specializzato in cucina giapponese contemporanea.',
                'Sushi chef',
                'sushi',
                90.0,
                8,
                EChef::STATO_VERIFICA_VERIFICATO,
                4.7,
                52
            ),
            new EChef(
                2,
                'Laura',
                'Bianchi',
                'laura.bianchi@example.com',
                'hash-laura',
                '+39069876543',
                EUtente::STATO_ATTIVO,
                'Chef per eventi privati e degustazioni.',
                'Chef fusion',
                'fusion',
                110.0,
                10,
                EChef::STATO_VERIFICA_VERIFICATO,
                4.8,
                41
            ),
            new EChef(
                3,
                'Giulia',
                'Verdi',
                'giulia.verdi@example.com',
                'hash-giulia',
                '+39065551234',
                EUtente::STATO_ATTIVO,
                'Chef orientata a menu mediterranei e stagionali.',
                'Chef mediterranea',
                'mediterranea',
                75.0,
                6,
                EChef::STATO_VERIFICA_VERIFICATO,
                4.2,
                19
            )
        ];

        $mappaLocalitaChef = [
            1 => 'roma',
            2 => 'milano',
            3 => 'roma'
        ];

        $localita = strtolower(trim($localita));
        $tipologiaCucina = strtolower(trim($tipologiaCucina));

        $risultati = [];

        foreach ($chefDisponibili as $chef) {
            $idChef = $chef->getIdChef();
            $localitaChef = $idChef !== null && isset($mappaLocalitaChef[$idChef]) ? $mappaLocalitaChef[$idChef] : '';

            if ($localita !== '' && $localitaChef !== $localita) {
                continue;
            }

            if (
                $tipologiaCucina !== '' &&
                strtolower($chef->getTipologiaCucina()) !== $tipologiaCucina &&
                strtolower($chef->getSpecializzazione()) !== $tipologiaCucina
            ) {
                continue;
            }

            if ($budgetMax > 0 && $chef->getPrezzoBase() > $budgetMax) {
                continue;
            }

            if ($chef->getValutazioneMedia() < $valutazioneMin) {
                continue;
            }

            $risultati[] = $chef;
        }

        return $risultati;
    }

    /**
     * @return EGhostKitchen[]
     */
    public static function cercaGhostKitchen(
        string $localita,
        float $budgetMax,
        int $valutazioneMin
    ): array {
        $ghostKitchenDisponibili = [
            new EGhostKitchen(
                101,
                11,
                'Ghost Roma Centro',
                'Spazio attrezzato per chef e piccoli team.',
                'Via Nazionale 10',
                'Roma',
                '00184',
                35.0,
                20,
                80.0,
                EGhostKitchen::STATO_ATTIVA,
                4.5,
                34
            ),
            new EGhostKitchen(
                102,
                12,
                'Milano Lab Kitchen',
                'Cucina professionale per delivery e catering.',
                'Via Torino 20',
                'Milano',
                '20123',
                55.0,
                30,
                120.0,
                EGhostKitchen::STATO_ATTIVA,
                4.9,
                27
            ),
            new EGhostKitchen(
                103,
                13,
                'Trastevere Food Hub',
                'Laboratorio culinario condiviso per eventi.',
                'Viale Trastevere 50',
                'Roma',
                '00153',
                48.0,
                16,
                65.0,
                EGhostKitchen::STATO_ATTIVA,
                4.1,
                18
            )
        ];

        $localita = strtolower(trim($localita));
        $risultati = [];

        foreach ($ghostKitchenDisponibili as $ghostKitchen) {
            if ($localita !== '' && strtolower($ghostKitchen->getCitta()) !== $localita) {
                continue;
            }

            if ($budgetMax > 0 && $ghostKitchen->getPrezzoOrario() > $budgetMax) {
                continue;
            }

            if ($ghostKitchen->getValutazioneMedia() < $valutazioneMin) {
                continue;
            }

            $risultati[] = $ghostKitchen;
        }

        return $risultati;
    }

    public static function getMediaPrincipale(
        string $tipoOwner,
        int $idOwner
    ): ?EMedia {
        $tipoOwner = strtolower(trim($tipoOwner));

        $mediaDisponibili = [
            new EMedia(
                201,
                EMedia::OWNER_CHEF,
                1,
                EMedia::TIPO_MEDIA_FOTO_PROFILO,
                'chef-marco.jpg',
                '/media/chef/chef-marco.jpg',
                'image/jpeg',
                'Foto profilo chef Marco Rossi',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            ),
            new EMedia(
                202,
                EMedia::OWNER_GHOST_KITCHEN,
                101,
                EMedia::TIPO_MEDIA_FOTO_AMBIENTE,
                'ghost-roma-centro.jpg',
                '/media/ghost-kitchen/ghost-roma-centro.jpg',
                'image/jpeg',
                'Immagine principale Ghost Roma Centro',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            )
        ];

        foreach ($mediaDisponibili as $media) {
            if ($media->getTipoOwner() === $tipoOwner && $media->getIdOwner() === $idOwner) {
                return $media;
            }
        }

        return null;
    }

    public static function loadChef(int $idChef): ?EChef
    {
        foreach (self::getChefDataset() as $chef) {
            if ($chef->getIdChef() === $idChef) {
                return $chef;
            }
        }

        return null;
    }

    /**
     * @return EMenu[]
     */
    public static function loadMenuByChef(int $idChef): array
    {
        $menuDisponibili = [
            new EMenu(
                301,
                1,
                'Percorso Sushi Signature',
                'Menu degustazione con sushi, nigiri e uramaki.',
                55.0,
                true
            ),
            new EMenu(
                302,
                1,
                'Japanese Dinner Experience',
                'Menu completo per cene private con portate giapponesi.',
                75.0,
                true
            ),
            new EMenu(
                303,
                2,
                'Fusion Experience',
                'Menu fusion per eventi aziendali e privati.',
                68.0,
                true
            )
        ];

        $risultati = [];

        foreach ($menuDisponibili as $menu) {
            if ($menu->getIdChef() === $idChef) {
                $risultati[] = $menu;
            }
        }

        return $risultati;
    }

    /**
     * @return EPiatto[]
     */
    public static function loadPiattiByMenu(int $idMenu): array
    {
        $piattiDisponibili = [
            new EPiatto(
                401,
                301,
                'Nigiri Selection',
                EPiatto::CATEGORIA_SECONDO,
                'Selezione di nigiri con pesce fresco.',
                'Riso, salmone, tonno, ricciola',
                'Pesce, soia',
                0.0,
                1
            ),
            new EPiatto(
                402,
                301,
                'Uramaki Crunch',
                EPiatto::CATEGORIA_SECONDO,
                'Uramaki croccante con gambero e avocado.',
                'Riso, gambero, avocado, panko',
                'Crostacei, glutine',
                4.5,
                2
            ),
            new EPiatto(
                403,
                302,
                'Miso Soup',
                EPiatto::CATEGORIA_PRIMO,
                'Zuppa miso con tofu e wakame.',
                'Miso, tofu, alga wakame',
                'Soia',
                0.0,
                1
            ),
            new EPiatto(
                404,
                302,
                'Mochi Trio',
                EPiatto::CATEGORIA_DOLCE,
                'Tris di mochi artigianali.',
                'Farina di riso, matcha, mango, cioccolato',
                'Può contenere latte',
                3.0,
                2
            ),
            new EPiatto(
                405,
                303,
                'Tataki Fusion',
                EPiatto::CATEGORIA_SECONDO,
                'Tataki rivisitato in chiave fusion.',
                'Manzo, sesamo, salsa ponzu',
                'Soia, sesamo',
                6.0,
                1
            )
        ];

        $risultati = [];

        foreach ($piattiDisponibili as $piatto) {
            if ($piatto->getIdMenu() === $idMenu) {
                $risultati[] = $piatto;
            }
        }

        return $risultati;
    }

    /**
     * @return ECertificazione[]
     */
    public static function loadCertificazioniApprovateByChef(int $idChef): array
    {
        $certificazioni = [
            new ECertificazione(
                501,
                1,
                'HACCP',
                'haccp-marco-rossi.pdf',
                '/certificazioni/haccp-marco-rossi.pdf',
                ECertificazione::STATO_APPROVATA,
                '2026-04-10',
                '2026-04-15',
                'Certificazione verificata.'
            ),
            new ECertificazione(
                502,
                1,
                'Somministrazione alimenti',
                'sab-marco-rossi.pdf',
                '/certificazioni/sab-marco-rossi.pdf',
                ECertificazione::STATO_APPROVATA,
                '2026-04-12',
                '2026-04-18',
                'Documento approvato.'
            ),
            new ECertificazione(
                503,
                2,
                'HACCP',
                'haccp-laura-bianchi.pdf',
                '/certificazioni/haccp-laura-bianchi.pdf',
                ECertificazione::STATO_APPROVATA,
                '2026-03-02',
                '2026-03-08',
                'Documento approvato.'
            )
        ];

        $risultati = [];

        foreach ($certificazioni as $certificazione) {
            if (
                $certificazione->getIdChef() === $idChef &&
                $certificazione->getStato() === ECertificazione::STATO_APPROVATA
            ) {
                $risultati[] = $certificazione;
            }
        }

        return $risultati;
    }

    /**
     * @return EMedia[]
     */
    public static function getMediaByOwner(string $tipoOwner, int $idOwner): array
    {
        $tipoOwner = strtolower(trim($tipoOwner));
        $risultati = [];

        foreach (self::getMediaDataset() as $media) {
            if ($media->getTipoOwner() === $tipoOwner && $media->getIdOwner() === $idOwner) {
                $risultati[] = $media;
            }
        }

        return $risultati;
    }

    /**
     * @return EChef[]
     */
    private static function getChefDataset(): array
    {
        return [
            new EChef(
                1,
                'Marco',
                'Rossi',
                'marco.rossi@example.com',
                'hash-marco',
                '+39061234567',
                EUtente::STATO_ATTIVO,
                'Chef specializzato in cucina giapponese contemporanea.',
                'Sushi chef',
                'sushi',
                90.0,
                8,
                EChef::STATO_VERIFICA_VERIFICATO,
                4.7,
                52
            ),
            new EChef(
                2,
                'Laura',
                'Bianchi',
                'laura.bianchi@example.com',
                'hash-laura',
                '+39069876543',
                EUtente::STATO_ATTIVO,
                'Chef per eventi privati e degustazioni.',
                'Chef fusion',
                'fusion',
                110.0,
                10,
                EChef::STATO_VERIFICA_VERIFICATO,
                4.8,
                41
            ),
            new EChef(
                3,
                'Giulia',
                'Verdi',
                'giulia.verdi@example.com',
                'hash-giulia',
                '+39065551234',
                EUtente::STATO_ATTIVO,
                'Chef orientata a menu mediterranei e stagionali.',
                'Chef mediterranea',
                'mediterranea',
                75.0,
                6,
                EChef::STATO_VERIFICA_VERIFICATO,
                4.2,
                19
            )
        ];
    }

    /**
     * @return EMedia[]
     */
    private static function getMediaDataset(): array
    {
        return [
            new EMedia(
                201,
                EMedia::OWNER_CHEF,
                1,
                EMedia::TIPO_MEDIA_FOTO_PROFILO,
                'chef-marco.jpg',
                '/media/chef/chef-marco.jpg',
                'image/jpeg',
                'Foto profilo chef Marco Rossi',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            ),
            new EMedia(
                202,
                EMedia::OWNER_GHOST_KITCHEN,
                101,
                EMedia::TIPO_MEDIA_FOTO_AMBIENTE,
                'ghost-roma-centro.jpg',
                '/media/ghost-kitchen/ghost-roma-centro.jpg',
                'image/jpeg',
                'Immagine principale Ghost Roma Centro',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            ),
            new EMedia(
                203,
                EMedia::OWNER_PIATTO,
                401,
                EMedia::TIPO_MEDIA_FOTO_PIATTO,
                'nigiri-selection.jpg',
                '/media/piatti/nigiri-selection.jpg',
                'image/jpeg',
                'Foto del piatto Nigiri Selection',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            ),
            new EMedia(
                204,
                EMedia::OWNER_PIATTO,
                402,
                EMedia::TIPO_MEDIA_FOTO_PIATTO,
                'uramaki-crunch.jpg',
                '/media/piatti/uramaki-crunch.jpg',
                'image/jpeg',
                'Foto del piatto Uramaki Crunch',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            ),
            new EMedia(
                205,
                EMedia::OWNER_PIATTO,
                404,
                EMedia::TIPO_MEDIA_FOTO_PIATTO,
                'mochi-trio.jpg',
                '/media/piatti/mochi-trio.jpg',
                'image/jpeg',
                'Foto del piatto Mochi Trio',
                '2026-05-18',
                0,
                EMedia::STATO_ATTIVO
            )
        ];
    }
}

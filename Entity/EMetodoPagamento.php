<?php
declare(strict_types=1);

class EMetodoPagamento
{
    public const TIPO_CARTA = 'carta';
    public const TIPO_PAYPAL = 'paypal';
    public const TIPO_BONIFICO = 'bonifico';
    public const TIPO_CONTANTI = 'contanti';

    private ?int $idMetodoPagamento;
    private ?int $idUtente;
    private string $tipo;
    private string $intestatario;
    private string $circuito;
    private string $ultimeQuattroCifre;
    private int $scadenzaMese;
    private int $scadenzaAnno;
    private bool $attivo;

    public function __construct(
        ?int $idMetodoPagamento = null,
        ?int $idUtente = null,
        string $tipo = self::TIPO_CARTA,
        string $intestatario = '',
        string $circuito = '',
        string $ultimeQuattroCifre = '',
        int $scadenzaMese = 0,
        int $scadenzaAnno = 0,
        bool $attivo = true
    ) {
        $this->setIdMetodoPagamento($idMetodoPagamento);
        $this->setIdUtente($idUtente);
        $this->setTipo($tipo);
        $this->setIntestatario($intestatario);
        $this->setCircuito($circuito);
        $this->setUltimeQuattroCifre($ultimeQuattroCifre);
        $this->setScadenzaMese($scadenzaMese);
        $this->setScadenzaAnno($scadenzaAnno);
        $this->setAttivo($attivo);
    }

    public function getIdMetodoPagamento(): ?int { return $this->idMetodoPagamento; }
    public function setIdMetodoPagamento(?int $idMetodoPagamento): void
    {
        if ($idMetodoPagamento !== null && $idMetodoPagamento <= 0) {
            throw new InvalidArgumentException('ID metodo pagamento non valido.');
        }
        $this->idMetodoPagamento = $idMetodoPagamento;
    }

    public function getIdUtente(): ?int { return $this->idUtente; }
    public function setIdUtente(?int $idUtente): void
    {
        if ($idUtente !== null && $idUtente <= 0) {
            throw new InvalidArgumentException('ID utente non valido.');
        }
        $this->idUtente = $idUtente;
    }

    public function getTipo(): string { return $this->tipo; }
    public function setTipo(string $tipo): void
    {
        $tipo = strtolower(trim($tipo));
        $ammessi = [self::TIPO_CARTA, self::TIPO_PAYPAL, self::TIPO_BONIFICO, self::TIPO_CONTANTI];
        if (!in_array($tipo, $ammessi, true)) {
            throw new InvalidArgumentException('Tipo metodo pagamento non valido.');
        }
        $this->tipo = $tipo;
    }

    public function getIntestatario(): string { return $this->intestatario; }
    public function setIntestatario(string $intestatario): void { $this->intestatario = trim($intestatario); }

    public function getCircuito(): string { return $this->circuito; }
    public function setCircuito(string $circuito): void { $this->circuito = trim($circuito); }

    public function getUltimeQuattroCifre(): string { return $this->ultimeQuattroCifre; }
    public function setUltimeQuattroCifre(string $ultimeQuattroCifre): void
    {
        $ultimeQuattroCifre = trim($ultimeQuattroCifre);
        if ($ultimeQuattroCifre !== '' && !preg_match('/^\d{4}$/', $ultimeQuattroCifre)) {
            throw new InvalidArgumentException('Ultime quattro cifre non valide.');
        }
        $this->ultimeQuattroCifre = $ultimeQuattroCifre;
    }

    public function getScadenzaMese(): int { return $this->scadenzaMese; }
    public function setScadenzaMese(int $scadenzaMese): void
    {
        if ($scadenzaMese !== 0 && ($scadenzaMese < 1 || $scadenzaMese > 12)) {
            throw new InvalidArgumentException('Mese di scadenza non valido.');
        }
        $this->scadenzaMese = $scadenzaMese;
    }

    public function getScadenzaAnno(): int { return $this->scadenzaAnno; }
    public function setScadenzaAnno(int $scadenzaAnno): void
    {
        if ($scadenzaAnno < 0) {
            throw new InvalidArgumentException('Anno di scadenza non valido.');
        }
        $this->scadenzaAnno = $scadenzaAnno;
    }

    public function isAttivo(): bool { return $this->attivo; }
    public function setAttivo(bool $attivo): void { $this->attivo = $attivo; }

    public function toArray(): array
    {
        return [
            'idMetodoPagamento' => $this->idMetodoPagamento,
            'idUtente' => $this->idUtente,
            'tipo' => $this->tipo,
            'intestatario' => $this->intestatario,
            'circuito' => $this->circuito,
            'ultimeQuattroCifre' => $this->ultimeQuattroCifre,
            'scadenzaMese' => $this->scadenzaMese,
            'scadenzaAnno' => $this->scadenzaAnno,
            'attivo' => $this->attivo
        ];
    }

    public function __toString(): string
    {
        return 'MetodoPagamento #' . ($this->idMetodoPagamento ?? 'nuovo') . ' [' . $this->tipo . ']';
    }
}

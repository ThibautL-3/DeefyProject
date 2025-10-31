<?php
namespace iutnc\deefy\audio\lists;

use iutnc\deefy\exception\InvalidPropertyNameException;

class AudioList
{
    protected string $nom;
    protected array $pistes;
    protected int $nombre = 0;
    protected int $dureeTotale = 0;

    public ?int $id = null;

    public function __construct(string $nom, array $pistes = [])
    {
        $this->nom = $nom;
        $this->pistes = $pistes;
        $this->recalc();
    }

    protected function recalc(): void
    {
        $this->nombre = count($this->pistes);
        $total = 0;
        foreach ($this->pistes as $p) { $total += (int)$p->__get('duree'); }
        $this->dureeTotale = $total;
    }

    public function __get(string $name)
    {
        if (property_exists($this, $name)) return $this->$name;
        throw new InvalidPropertyNameException("invalid property : {$name}");
    }

    public function setId(int $id): void {
        $this->id = $id;
    }
}

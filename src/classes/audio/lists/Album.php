<?php
namespace iutnc\deefy\audio\lists;

class Album extends AudioList
{
    protected string $artiste;
    protected ?string $dateSortie = null;

    public function __construct(string $nom, array $pistes, string $artiste)
    {
        parent::__construct($nom, $pistes);
        $this->artiste = $artiste;
    }

    public function setDateSortie(string $date): void { $this->dateSortie = $date; }
}

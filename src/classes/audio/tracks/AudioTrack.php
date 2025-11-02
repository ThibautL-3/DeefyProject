<?php
namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\exception\InvalidPropertyValueException;

class AudioTrack
{
    protected string $titre;
    protected ?string $auteur = null;
    protected $annee = null;
    protected ?string $genre = null;
    protected int $duree = 0;
    protected string $fichier;
    public ?int $id = null;

    public function __construct(string $titre, string $fichier)
    {
        $this->titre = $titre;
        $this->fichier = $fichier;
    }

    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new InvalidPropertyNameException("invalid property : {$name}");
    }

    public function setAuteur(string $auteur): void { $this->auteur = $auteur; }
    public function setAnnee(int $annee): void { $this->annee = $annee; }
    public function setGenre(string $genre): void { $this->genre = $genre; }
    public function setDuree(int $duree): void {
        if ($duree < 0) throw new InvalidPropertyValueException("duree must be >= 0");
        $this->duree = $duree;
    }

    public function __toString(): string { return json_encode(get_object_vars($this)); }
}

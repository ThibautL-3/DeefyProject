<?php
namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyValueException;

class AlbumTrack extends AudioTrack
{
    protected string $album;
    protected int $numero;

    public function __construct(string $titre, string $fichier, string $album, int $numero)
    {
        parent::__construct($titre, $fichier);
        if ($numero <= 0) throw new InvalidPropertyValueException("numero must be >= 1");
        $this->album = $album;
        $this->numero = $numero;
    }

    public function setAlbum(string $album): void { $this->album = $album; }
}

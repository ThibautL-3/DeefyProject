<?php
namespace iutnc\deefy\audio\tracks;

class PodcastTrack extends AudioTrack
{
    public function __construct(string $titre, string $fichier)
    {
        parent::__construct($titre, $fichier);
    }
}

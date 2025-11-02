<?php
namespace iutnc\deefy\audio\tracks;

class PodcastTrack extends AudioTrack
{
    protected string $datePodcast = '';
    public function __construct(string $titre, string $fichier)
    {
        parent::__construct($titre, $fichier);
    }

    public function setDatePodcast(string $date): void {
        $this->datePodcast = $date;
    }

    public function getDatePodcast(): string {
        return $this->datePodcast;
    }
}

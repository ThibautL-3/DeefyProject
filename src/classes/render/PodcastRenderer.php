<?php
namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\PodcastTrack;

class PodcastRenderer extends AudioTrackRenderer
{
    public function __construct(PodcastTrack $track)
    {
        parent::__construct($track);
        $this->track = $track;
    }

    protected function renderCompact(): string
    {
        $titre = htmlspecialchars($this->track->__get('titre'));
        $auteur = htmlspecialchars($this->track->__get('auteur') ?? 'Unknown');
        $date = htmlspecialchars($this->track->getDatePodcast() ?? 'Date inconnue');
        $f = $this->audioTag();

        return "<div class=\"podcast compact\">
                <p><strong>{$titre}</strong> — {$auteur}</p>
                {$f}
            </div>";
    }

    protected function renderLong(): string
    {
        $titre = htmlspecialchars($this->track->__get('titre'));
        $f = $this->audioTag();
        $duree = (int)$this->track->__get('duree');
        return "<div class=\"podcast long\"><h2>{$titre}</h2><p>duree: {$duree}s</p>{$f}</div>";
    }
}

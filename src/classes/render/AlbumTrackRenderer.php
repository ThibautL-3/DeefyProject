<?php
namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\AlbumTrack;

class AlbumTrackRenderer extends AudioTrackRenderer
{
    public function __construct(AlbumTrack $track)
    {
        parent::__construct($track);
        $this->track = $track;
    }

    protected function renderCompact(): string
    {
        $titre = htmlspecialchars($this->track->__get('titre'));
        $artiste = htmlspecialchars($this->track->__get('auteur') ?? 'Unknown');
        $album = htmlspecialchars($this->track->__get('album'));
        $numero = (int)$this->track->__get('numero');
        $f = $this->audioTag();
        return "<div class=\"track compact\"><p><strong>{$titre}</strong> — {$artiste} ({$album} — #{$numero})</p>{$f}</div>";
    }

    protected function renderLong(): string
    {
        $titre = htmlspecialchars($this->track->__get('titre'));
        $f = $this->audioTag();
        $duree = (int)$this->track->__get('duree');
        return "<div class=\"track long\"><h2>{$titre}</h2><p>duree: {$duree}s</p>{$f}</div>";
    }
}

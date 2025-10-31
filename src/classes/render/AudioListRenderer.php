<?php
namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\audio\tracks\PodcastTrack;

class AudioListRenderer implements Renderer
{
    protected AudioList $list;
    public function __construct(AudioList $list) { $this->list = $list; }

    public function render(int $selector): string
    {
        $html = "<div class=\"audiolist\"><h3>" . htmlspecialchars($this->list->__get('nom')) . "</h3>";
        foreach ($this->list->__get('pistes') as $p) {
            if ($p instanceof AlbumTrack) $r = new AlbumTrackRenderer($p);
            elseif ($p instanceof PodcastTrack) $r = new PodcastRenderer($p);
            else { $html .= "<div>" . htmlspecialchars($p->__get('titre')) . "</div>"; continue; }
            $html .= $r->render(Renderer::COMPACT);
        }
        $html .= "<p>Nombre: " . (int)$this->list->__get('nombre') . "</p>";
        $html .= "<p>Duree totale: " . (int)$this->list->__get('dureeTotale') . "s</p></div>";
        return $html;
    }
}

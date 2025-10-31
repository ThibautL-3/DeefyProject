<?php
namespace iutnc\deefy\action;

use iutnc\deefy\render\AudioListRenderer;

class ShowCurrentPlaylistAction extends Action
{
    public function execute(): string {
        if (!isset($_SESSION['playlist'])) {
            return "<p>Aucune playlist sélectionnée.</p>";
        }

        $playlist = unserialize($_SESSION['playlist']);
        $renderer = new AudioListRenderer($playlist);

        return "<h2>Playlist courante : " . htmlspecialchars($playlist->__get('nom')) . "</h2>" .
            $renderer->render(0);
    }
}
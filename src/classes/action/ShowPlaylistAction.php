<?php
namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\exception\AuthzException;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\render\AudioListRenderer;

class ShowPlaylistAction extends Action
{
    public function execute(): string {
        $id_pl = (int)($_GET['id'] ?? 0);
        if ($id_pl <= 0) return "<p>Erreur : identifiant de playlist manquant.</p>";

        try {
            $user = AuthnProvider::getSignedInUser();
            Authz::checkPlaylistOwner($id_pl);
        } catch (AuthzException $e) {
            return "<p>⛔ " . htmlspecialchars($e->getMessage()) . " ⛔</p>";
        } catch (\Exception $e) {
            return "<p>Erreur d'authentification : " . htmlspecialchars($e->getMessage()) . "</p>";
        }

        $repo = DeefyRepository::getInstance();
        $playlist = $repo->findPlaylistById($id_pl);

        if (!$playlist) return "<p>Playlist introuvable.</p>";

        $_SESSION['playlist'] = serialize($playlist);

        $renderer = new AudioListRenderer($playlist);
        return "<h2>Playlist : " . htmlspecialchars($playlist->__get('nom')) . "</h2>" .
            $renderer->render(0) .
            "<p><a href='?action=add-track&id={$id_pl}' class='btn btn-gray'>Ajouter une piste podcast</a> | 
                  <a href='?action=add-album&id={$id_pl}' class='btn btn-gray'>Ajouter une piste album</a></p>";
    }
}
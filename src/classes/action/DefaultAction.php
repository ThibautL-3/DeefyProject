<?php
namespace iutnc\deefy\action;

use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\AuthnProvider;

class DefaultAction extends Action
{
    public function execute(): string {
        $out = "<h2>Bienvenue sur Deefy</h2>";

        try {
            $user = AuthnProvider::getSignedInUser();
            $out .= "<p>Connecté en tant que <b>" . htmlspecialchars($user['email']) . "</b></p>";

            $repo = DeefyRepository::getInstance();
            $pls = $repo->findUserPlaylists($user['id']);

            if (count($pls) === 0) {
                $out .= "<p>Vous n'avez encore aucune playlist.</p>";
            } else {
                $out .= "<h3>Vos playlists :</h3><ul>";
                foreach ($pls as $p) {
                    $out .= "<li><a href='?action=show-playlist&id={$p->id}'>" . htmlspecialchars($p->__get('nom')) . "</a></li>";
                }
                $out .= "</ul>";
            }

            $out .= "<p><a href='?action=logout' class='btn btn-red'>Se déconnecter</a></p>";

        } catch (AuthnException $ex) {
            $out .= "<p><i>Vous n'êtes pas connecté.</i></p>
                     <p><a href='?action=signin' class='btn btn-blue'>Se connecter</a></p>";
        }
        return $out;
    }
}

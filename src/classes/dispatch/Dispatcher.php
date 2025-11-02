<?php
namespace iutnc\deefy\dispatch;

use iutnc\deefy\action\{AddAlbumTrackAction,
    DefaultAction,
    AddPlaylistAction,
    AddPodcastTrackAction,
    AddUserAction,
    LogoutAction,
    ShowPlaylistAction,
    SignInAction};
use iutnc\deefy\repository\DeefyRepository;

class Dispatcher
{
    public function run(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        DeefyRepository::setConfig('/users/home/e10106u/config/db.config.ini');
        $repo = DeefyRepository::getInstance();

        $action = $_GET['action'] ?? 'default';
        switch ($action) {
            case 'add-playlist': $act = new AddPlaylistAction(); break;
            case 'add-track': $act = new AddPodcastTrackAction(); break;
            case 'add-album': $act = new AddAlbumTrackAction(); break;
            case 'add-user': $act = new AddUserAction(); break;
            case 'show-playlist': $act = new ShowPlaylistAction(); break;
            case 'signin': $act = new SignInAction(); break;
            default: $act = new DefaultAction(); break;
            case 'logout': $act = new LogoutAction(); break;
        }

        $content = $act->execute();
        $this->renderPage($content);
    }

    private function renderPage(string $content): void {
        $menu = <<<HTML
            <nav>
                <a href="?action=default" class="btn btn-blue">Accueil</a> |
                <a href="?action=add-user" class="btn btn-blue">Inscription</a> |
                <a href="?action=add-playlist" class="btn btn-blue">Créer playlist</a>

            </nav>
        HTML;

        echo <<<HTML
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <title>Deefy</title>
                <link rel="stylesheet" href="src/css/style.css">
            </head>
            <body>
                <h1>Deefy 🎶</h1>
                $menu
                <hr>
                $content
                <footer>
                <div class="footer-left">
                    <p>Deefy, votre musique partout</p>
                </div>
                <div class="footer-divider"></div>
                <div class="footer-right">
                    <p>Ronan FISSON<br>Thibaut LOUYOT</p>
                </div>
                </footer>
            </body>
            </html>
        HTML;
    }
}

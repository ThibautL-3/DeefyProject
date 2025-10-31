<?php
namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;
use PDOException;

class AddPlaylistAction extends Action
{
    public function execute(): string {
        if ($this->http_method === 'GET') {
            return <<<HTML
                <h2>Créer une nouvelle playlist</h2>
                <form method="post" action="?action=add-playlist">
                    <label for="nom">Nom de la playlist :</label>
                    <input type="text" id="nom" name="nom" required>
                    <button type="submit">Créer</button>
                </form>
            HTML;
        }
        try {
            $user = AuthnProvider::getSignedInUser();
            $nom = filter_var($_POST['nom'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            if ($nom === '') return "<p>Nom invalide.</p>";

            $repo = DeefyRepository::getInstance();
            $playlist = new Playlist($nom);
            $id = $repo->savePlaylist($playlist);

            $sql = "INSERT INTO user2playlist (id_user, id_pl) VALUES (:u, :p)";
            $stmt = $repo->getInstance()->pdo->prepare($sql);
            $stmt->execute([':u' => $user['id'], ':p' => $id]);

            return "<p>Playlist « $nom » créée avec succès (ID $id)</p>
                    <a href='?action=add-track&id=$id' class='btn btn-blue'>Ajouter une piste</a>";
        } catch (AuthnException $ex) {
            return "<p>❌ Vous devez être connecté pour créer une playlist. ❌</p>";
        } catch (PDOException $e) {
            return "<p>Erreur BD : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

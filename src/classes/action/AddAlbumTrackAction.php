<?php
namespace iutnc\deefy\action;

use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\exception\AuthzException;
use iutnc\deefy\exception\InvalidPropertyValueException;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\utils\AudioUtils;

class AddAlbumTrackAction extends Action
{
    public function execute(): string {
        $playlistId = (int)($_GET['id'] ?? 0);
        if ($playlistId === 0 && isset($_SESSION['playlist'])) {
            $playlist = unserialize($_SESSION['playlist']);
            $playlistId = $playlist->__get('id');
        }
        if ($playlistId <= 0) return "<p>Erreur : id playlist manquant.</p>";

        if ($this->http_method === 'GET') {
            try {
                Authz::checkPlaylistOwner($playlistId);
            } catch (AuthzException $e) {
                return "<p>⛔ " . htmlspecialchars($e->getMessage()) . "</p>";
            }

            return <<<HTML
                <h2>Ajouter une piste d'album à la playlist #$playlistId</h2>
                <form method="post" enctype="multipart/form-data" action="?action=add-album&id=$playlistId">
                    <label for="titre">Titre :</label>
                    <input type="text" id="titre" name="titre" required><br>

                    <label for="auteur">Artiste :</label>
                    <input type="text" id="auteur" name="auteur" required><br>

                    <label for="album">Titre de l'album :</label>
                    <input type="text" id="album" name="album" required><br>

                    <label for="annee">Année :</label>
                    <input type="number" id="annee" name="annee" min="1900" max="2100"><br>

                    <label for="numero">Numéro de piste :</label>
                    <input type="number" id="numero" name="numero" min="1" required><br>

                    <label for="genre">Genre :</label>
                    <input type="text" id="genre" name="genre"><br>


                    <label for="fichier">Fichier MP3 :</label>
                    <input type="file" id="fichier" name="fichier" accept=".mp3" required><br>

                    <button type="submit">Ajouter la piste</button>
                </form>
            HTML;
        }

        try {
            Authz::checkPlaylistOwner($playlistId);
        } catch (AuthzException $e) {
            return "<p>⛔ " . htmlspecialchars($e->getMessage()) . "</p>";
        }

        $titre = filter_var($_POST['titre'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $auteur = filter_var($_POST['auteur'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $album = filter_var($_POST['album'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $annee = (int)($_POST['annee'] ?? 0);
        $numero = (int)($_POST['numero'] ?? 1);
        $genre = filter_var($_POST['genre'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK)
            return "<p>Erreur : fichier manquant.</p>";

        if ($_FILES['fichier']['type'] !== 'audio/mpeg')
            return "<p>Fichier non valide (MP3 attendu).</p>";

        $nomFichier = uniqid('album_', true) . '.mp3';
        $destination = __DIR__ . '/../../../audio/' . $nomFichier;
        move_uploaded_file($_FILES['fichier']['tmp_name'], $destination);

        $audioInfo = AudioUtils::getAudioInfo($destination);
        try {
            $track = new AlbumTrack($titre, $nomFichier, $album, $numero);
        } catch (InvalidPropertyValueException $e) {
            return "<p>Erreur : " . $e->getMessage() . "</p>";
        }
        $duree = $audioInfo['duree'] ?? 0;

        $track->setAuteur($auteur);
        $track->setGenre($genre);
        $track->setAnnee($annee);
        $track->setDuree($duree ?? 0);

        $repo = DeefyRepository::getInstance();
        $trackId = $repo->saveTrack($track);

        $stmt = $repo->getInstance()->pdo->prepare(
            "SELECT COALESCE(MAX(no_piste_dans_liste),0)+1 AS next FROM playlist2track WHERE id_pl=:id"
        );
        $stmt->execute([':id' => $playlistId]);
        $pos = (int)$stmt->fetch()['next'];

        $repo->addTrackToPlaylist($playlistId, $trackId, $pos);

        $playlist = $repo->findPlaylistById($playlistId);
        $_SESSION['playlist'] = serialize($playlist);

        return "<p>Piste ajoutée à la playlist $playlistId.</p>
                <a href='?action=add-track&id=$playlistId' class='btn btn-blue'>Ajouter une autre piste</a> |
                <a href='?action=show-playlist&id=$playlistId' class='btn btn-blue'>Voir la playlist</a>";
    }
}

<?php
namespace iutnc\deefy\repository;

use PDO;
use PDOException;
use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\audio\tracks\PodcastTrack;

class DeefyRepository
{
    private static ?array $config = null;
    private static ?DeefyRepository $instance = null;
    public PDO $pdo;

    public static function setConfig(string $file): void {
        $cfg = parse_ini_file($file);
        if ($cfg === false) throw new \RuntimeException("Erreur lecture fichier config DB");
        self::$config = $cfg;
    }

    public static function getInstance(): DeefyRepository {
        if (self::$config === null) throw new \RuntimeException("Config DB non initialisée");
        if (self::$instance === null) self::$instance = new DeefyRepository(self::$config);
        return self::$instance;
    }

    private function __construct(array $cfg) {
        $dsn = sprintf(
            "mysql:host=%s;port=%s;dbname=%s;charset=%s",
            $cfg['host'], $cfg['port'], $cfg['dbname'], $cfg['charset'] ?? 'utf8'
        );
        $this->pdo = new PDO($dsn, $cfg['user'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    /** Retourne toutes les playlists sans pistes */
    public function findPlaylists(): array {
        $stmt = $this->pdo->query("SELECT id, nom FROM playlist");
        $res = [];
        while ($row = $stmt->fetch()) {
            $pl = new Playlist($row['nom']);
            $pl->id = (int)$row['id'];
            $res[] = $pl;
        }
        return $res;
    }

    /** Insère une nouvelle playlist et retourne son id */
    public function savePlaylist(Playlist $pl): int {
        $stmt = $this->pdo->prepare("INSERT INTO playlist(nom) VALUES (:nom)");
        $stmt->execute([':nom' => $pl->__get('nom')]);
        $id = (int)$this->pdo->lastInsertId();
        $pl->id = $id;
        return $id;
    }

    /** Insère une piste dans la table track et retourne son id */
    public function saveTrack(AudioTrack $track): int {
        $type = ($track instanceof AlbumTrack) ? 'A' : 'P';
        $sql = "INSERT INTO track (titre, genre, duree, filename, type, 
                                   artiste_album, titre_album, annee_album, numero_album, 
                                   auteur_podcast, date_posdcast)
                VALUES (:titre, :genre, :duree, :filename, :type,
                        :artiste_album, :titre_album, :annee_album, :numero_album,
                        :auteur_podcast, :date_posdcast)";
        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':titre' => $track->__get('titre'),
            ':genre' => $track->__get('genre'),
            ':duree' => (int)$track->__get('duree'),
            ':filename' => $track->__get('fichier'),
            ':type' => $type,
            ':artiste_album' => $track instanceof AlbumTrack ? $track->__get('auteur') : null,
            ':titre_album' => $track instanceof AlbumTrack ? $track->__get('album') : null,
            ':annee_album' => $track instanceof AlbumTrack ? $track->__get('annee') : null,
            ':numero_album' => $track instanceof AlbumTrack ? $track->__get('numero') : null,
            ':auteur_podcast' => $track instanceof PodcastTrack ? $track->__get('auteur') : null,
            ':date_posdcast' => $track instanceof PodcastTrack ? $track->__get('annee') : null,
        ];

        $stmt->execute($params);
        $id = (int)$this->pdo->lastInsertId();
        $track->id = $id;
        return $id;
    }

    /** Ajoute une piste à une playlist */
    public function addTrackToPlaylist(int $playlistId, int $trackId, int $position): void {
        $sql = "INSERT INTO playlist2track (id_pl, id_track, no_piste_dans_liste)
                VALUES (:pl, :tr, :no)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pl' => $playlistId, ':tr' => $trackId, ':no' => $position]);
    }

    /** Retourne une playlist complète avec ses pistes */
    public function findPlaylistById(int $id): ?Playlist {
        $stmt = $this->pdo->prepare("SELECT id, nom FROM playlist WHERE id=:id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $pl = new Playlist($row['nom']);
        $pl->id = (int)$row['id'];

        // Charger les pistes associées
        $sql = "SELECT t.* 
                FROM track t 
                JOIN playlist2track p2t ON t.id = p2t.id_track
                WHERE p2t.id_pl = :id
                ORDER BY p2t.no_piste_dans_liste ASC";
        $stmt2 = $this->pdo->prepare($sql);
        $stmt2->execute([':id' => $id]);

        while ($tr = $stmt2->fetch()) {
            if ($tr['type'] === 'A') {
                $t = new AlbumTrack(
                    $tr['titre'],
                    $tr['filename'],
                    $tr['titre_album'] ?? 'Inconnu',
                    (int)($tr['numero_album'] ?? 1)
                );
                $t->setAuteur($tr['artiste_album'] ?? '');
                $t->setAnnee((int)($tr['annee_album'] ?? 0));
            } else {
                $t = new PodcastTrack($tr['titre'], $tr['filename']);
                $t->setAuteur($tr['auteur_podcast'] ?? '');
            }
            $t->setGenre($tr['genre'] ?? '');
            $t->setDuree((int)$tr['duree']);
            $pl->addPiste($t);
        }

        return $pl;
    }

    /** Renvoie l'id du propriétaire d'une playlist */
    public function getPlaylistOwnerId(int $playlistId): ?int {
        $sql = "SELECT id_user FROM user2playlist WHERE id_pl = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $playlistId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id_user'] : null;
    }

    /** Trouver un utilisateur par email */
    public function findUserByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM User WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Insérer un nouvel utilisateur */
    public function saveUser(array $user): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO User (email, passwd, role) VALUES (:email, :passwd, :role)"
        );
        $stmt->execute([
            ':email' => $user['email'],
            ':passwd' => $user['passwd'],
            ':role' => $user['role'] ?? 1
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findUserPlaylists(int $id_user): array {
        $sql = "SELECT p.* FROM playlist p
            JOIN user2playlist up ON p.id = up.id_pl
            WHERE up.id_user = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_user]);

        $res = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $pl = new Playlist($row['nom']);
            $pl->setId((int)$row['id']);
            $res[] = $pl;
        }
        return $res;
    }

}
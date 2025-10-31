<?php
namespace iutnc\deefy\auth;

use iutnc\deefy\exception\AuthzException;
use iutnc\deefy\repository\DeefyRepository;

class Authz
{
    public static function checkRole(int $expectedRole): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['user'])) throw new AuthzException("Non connecté.");
        $u = unserialize($_SESSION['user']);
        if (($u['role'] ?? 0) < $expectedRole) throw new AuthzException("Rôle insuffisant.");
    }

    public static function checkPlaylistOwner(int $playlistId): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['user'])) throw new AuthzException("Non connecté.");

        $u = unserialize($_SESSION['user']);
        $uid = (int)$u['id'];
        $role = (int)$u['role'];

        $repo = DeefyRepository::getInstance();
        $owner = $repo->getPlaylistOwnerId($playlistId);
        if ($owner === null) throw new AuthzException("Playlist inconnue.");
        if ($owner !== $uid && $role !== 100)
            throw new AuthzException("Accès refusé : vous n'êtes pas propriétaire.");
    }
}

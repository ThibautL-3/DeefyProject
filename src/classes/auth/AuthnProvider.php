<?php
namespace iutnc\deefy\auth;

use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\repository\DeefyRepository;

class AuthnProvider
{
    public static function signin(string $email, string $passwd): array {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $repo = DeefyRepository::getInstance();

        $user = $repo->findUserByEmail($email);
        if (!$user) throw new AuthnException("Email inconnu.");
        if (!password_verify($passwd, $user['passwd'])) throw new AuthnException("Mot de passe invalide.");

        $infos = ['id' => $user['id'], 'email' => $user['email'], 'role' => $user['role']];
        $_SESSION['user'] = serialize($infos);
        return $infos;
    }

    public static function register(string $email, string $passwd): int {
        if (strlen($passwd) < 10) throw new AuthnException("Mot de passe trop court.");
        $repo = DeefyRepository::getInstance();
        if ($repo->findUserByEmail($email)) throw new AuthnException("Email déjà enregistré.");

        $hash = password_hash($passwd, PASSWORD_BCRYPT, ['cost' => 12]);
        if ($hash === false || $hash === null) throw new AuthnException("Problème lors de l'enregistrement du mot de passe.");
        return $repo->saveUser(['email' => $email, 'passwd' => $hash, 'role' => 1]);
    }

    public static function getSignedInUser(): array {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['user'])) throw new AuthnException("Aucun utilisateur connecté.");
        return unserialize($_SESSION['user']);
    }
}

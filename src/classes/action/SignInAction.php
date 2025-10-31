<?php
namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

class SignInAction extends Action
{
    public function execute(): string {
        if ($this->http_method === 'GET') {
            return <<<HTML
                <h2>Connexion</h2>
                <form method="post" action="?action=signin">
                    <label for="email">Email :</label>
                    <input type="email" id="email" name="email" required><br>

                    <label for="passwd">Mot de passe :</label>
                    <input type="password" id="passwd" name="passwd" required><br>

                    <button type="submit">Se connecter</button>
                </form>
            HTML;
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $passwd = $_POST['passwd'] ?? '';

        try {
            $user = AuthnProvider::signin($email, $passwd);
            return "<p>✅ Bienvenue 👋 Connecté avec : <b>" . htmlspecialchars($user['email']) . "</b> !</p>
                    <a href='?action=default' class='btn btn-blue'>Retour à l'accueil</a>";
        } catch (AuthnException $e) {
            return "<p>❌ " . htmlspecialchars($e->getMessage()) . " ❌</p>
                    <a href='?action=signin' class='btn btn-red'>Réessayer</a>";
        }
    }
}

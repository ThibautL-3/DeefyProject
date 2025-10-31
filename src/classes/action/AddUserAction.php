<?php
namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

class AddUserAction extends Action
{
    public function execute(): string {
        if ($this->http_method === 'GET') {
            return <<<HTML
                <h2>Créer un compte utilisateur</h2>
                <form method="post" action="?action=add-user">
                    <label>Email :</label>
                    <input type="email" name="email" required><br>

                    <label>Mot de passe :</label>
                    <input type="password" name="passwd" required><br>

                    <button type="submit">Inscription</button>
                </form>
            HTML;
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $passwd = $_POST['passwd'] ?? '';

        try {
            $id = AuthnProvider::register($email, $passwd);
            AuthnProvider::signin($email, $passwd);
            return "<p>Inscription réussie (ID $id). Vous êtes connecté.</p>
                    <a href='?action=default' class='btn btn-blue'>Retour à l'accueil</a>";
        } catch (AuthnException $e) {
            return "<p>❌ " . htmlspecialchars($e->getMessage()) . " ❌</p><a href='?action=add-user' class='btn btn-blue'>Réessayer</a>";
        }
    }
}

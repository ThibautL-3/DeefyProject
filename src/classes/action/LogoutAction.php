<?php
namespace iutnc\deefy\action;

class LogoutAction extends Action
{
    public function execute(): string {
        session_destroy();
        return "<p>Opération réussie<br>🚪🏃 Déconnecté</p><a href='?action=default' class='btn btn-blue'>Retour à l'accueil</a>";
    }
}

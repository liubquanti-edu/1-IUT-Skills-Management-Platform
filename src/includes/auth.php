<?php
// À inclure en haut de chaque page protégée
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}
// Pour restreindre par rôle :
function requireRole(string $role) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
        header('HTTP/1.1 403 Forbidden');
        echo '<h2>Accès refusé</h2><p>Vous n\'avez pas les droits pour accéder à cette page.</p>';
        exit;
    }
}

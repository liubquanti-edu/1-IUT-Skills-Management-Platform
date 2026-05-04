<?php
// À inclure en haut de chaque page protégée
session_start();

// Vérifie si l'utilisateur est connecté
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../pages/login.php');
        exit;
    }
}

// Vérifie si l'utilisateur a un rôle précis
function requireRole(string $role) {
    requireLogin();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
        header('HTTP/1.1 403 Forbidden');
        echo '<h2>Accès refusé</h2><p>Vous n\'avez pas les droits pour accéder à cette page.</p>';
        exit;
    }
}

// Vérifie si l'utilisateur a un des rôles donnés (tableau)
function requireAnyRole(array $roles) {
    requireLogin();
    if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $roles, true)) {
        header('HTTP/1.1 403 Forbidden');
        echo '<h2>Accès refusé</h2><p>Vous n\'avez pas les droits pour accéder à cette page.</p>';
        exit;
    }
}

// Raccourcis pour chaque rôle
function requireEtudiant() { requireRole('etudiant'); }
function requireFormateur() { requireRole('formateur'); }
function requireAdmin() { requireRole('admin'); }

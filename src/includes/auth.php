<?php

session_start();


function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../pages/login.php');
        exit;
    }
}


function requireRole(string $role) {
    requireLogin();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
        header('HTTP/1.1 403 Forbidden');
        echo '<div class="fail"><h2>Accès refusé</h2><p>Vous n\'avez pas les droits pour accéder à cette page.</p></div>';
        exit;
    }
}


function requireAnyRole(array $roles) {
    requireLogin();
    if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $roles, true)) {
        header('HTTP/1.1 403 Forbidden');
        echo '<div class="fail"><h2>Accès refusé</h2><p>Vous n\'avez pas les droits pour accéder à cette page.</p></div>';
        exit;
    }
}


function requireEtudiant() { requireRole('etudiant'); }
function requireFormateur() { requireRole('formateur'); }
function requireAdmin() { requireRole('admin'); }

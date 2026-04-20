<?php
require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
</head>
<body>
    <h2>Bienvenue, <?= htmlspecialchars($_SESSION['user_nom']) ?> !</h2>
    <div>
        <strong>Rôle :</strong> <?= htmlspecialchars($_SESSION['user_role']) ?><br>
        <strong>ID utilisateur :</strong> <?= (int)$_SESSION['user_id'] ?>
    </div>
    <a href="logout.php">Déconnexion</a>
</body>
</html>

<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $errors[] = 'Tous les champs sont obligatoires.';
    } else {
        try {
            $pdo = getDatabaseConnection();
            $stmt = $pdo->prepare('SELECT id_user, nom, role, statut, password_hash FROM utilisateur WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if (!$user) {
                $errors[] = 'Aucun compte trouvé avec cet email.';
            } elseif (!password_verify($password, $user['password_hash'])) {
                $errors[] = 'Mot de passe incorrect.';
            } elseif ($user['statut'] !== 'valide') {
                $errors[] = 'Votre compte n\'est pas encore validé par un administrateur.';
            } else {
                // Authentification réussie
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_role'] = $user['role'];
                header('Location: dashboard.php');
                exit;
            }
        } catch (Throwable $e) {
            $errors[] = 'Erreur lors de la connexion : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>

    <h2>Connexion</h2>
    <?php if ($errors): ?>
        <ul><?php foreach ($errors as $err) echo '<li>' . htmlspecialchars($err) . '</li>'; ?></ul>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <label>Email : <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></label><br>
        <label>Mot de passe : <input type="password" name="password" required></label><br>
        <button type="submit">Se connecter</button>
    </form>
    <div>Pas encore de compte ? <a href="register.php">Inscription</a></div>
</body>
</html>

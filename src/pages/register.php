<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($nom === '' || $prenom === '' || $email === '' || $password === '' || $role === '') {
        $errors[] = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    } elseif (!in_array($role, ['etudiant', 'formateur'])) {
        $errors[] = "Rôle invalide.";
    }

    if (empty($errors)) {
        try {
            $pdo = getDatabaseConnection();
            $stmt = $pdo->prepare('SELECT id_user FROM utilisateur WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Un compte existe déjà avec cet email.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO utilisateur (nom, email, password_hash, role, statut) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([
                    $nom . ' ' . $prenom,
                    $email,
                    $hash,
                    $role,
                    'en_attente'
                ]);
                $success = true;
            }
        } catch (Throwable $e) {
            $errors[] = 'Erreur lors de l\'inscription : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>
</head>
<body>
    <h2>Inscription</h2>
    <?php if ($success): ?>
        <div>Inscription réussie ! Votre compte doit être validé par un administrateur.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <ul><?php foreach ($errors as $err) echo '<li>' . htmlspecialchars($err) . '</li>'; ?></ul>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <label>Nom : <input type="text" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"></label><br>
        <label>Prénom : <input type="text" name="prenom" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"></label><br>
        <label>Email : <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></label><br>
        <label>Mot de passe : <input type="password" name="password" required></label><br>
        <label>Rôle demandé :
            <select name="role" required>
                <option value="">Choisir</option>
                <option value="etudiant" <?= (($_POST['role'] ?? '') === 'etudiant') ? 'selected' : '' ?>>Étudiant</option>
                <option value="formateur" <?= (($_POST['role'] ?? '') === 'formateur') ? 'selected' : '' ?>>Formateur</option>
            </select>
        </label><br>
        <button type="submit">S'inscrire</button>
    </form>
    <div>Déjà inscrit ? <a href="login.php">Connexion</a></div>
</body>
</html>

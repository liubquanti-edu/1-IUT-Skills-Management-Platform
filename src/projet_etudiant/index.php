<?php
require_once __DIR__ . '/../includes/auth.php';
requireEtudiant();
require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];
$competences = $pdo->query('SELECT id_competence, nom FROM competence ORDER BY nom')->fetchAll();

$selected = isset($_GET['id_competence']) ? (int)$_GET['id_competence'] : (isset($_POST['competence']) ? (int)$_POST['competence'] : null);
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $competence = (int)($_POST['competence'] ?? 0);
    $commentaire = trim($_POST['commentaire'] ?? '');
    if (!$competence) {
        $errors[] = 'Veuillez choisir une compétence.';
    }
    if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Aucun fichier envoyé.';
    }
    if (empty($errors)) {
        $allowed = ['pdf','zip','doc','docx','txt'];
        $file = $_FILES['fichier'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Extension de fichier non autorisée.';
        } else {
            $safeName = uniqid('projet_', true) . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $dest = $uploadDir . $safeName;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                
                $stmt = $pdo->prepare('INSERT INTO soumission_projet (id_projet, id_user, fichier, commentaire_formateur, statut) VALUES (?, ?, ?, ?, ?)');
                
                $stmt2 = $pdo->prepare('SELECT id_projet FROM projet WHERE id_competence = ? LIMIT 1');
                $stmt2->execute([$competence]);
                $id_projet = $stmt2->fetchColumn();
                $stmt->execute([$id_projet, $userId, $safeName, $commentaire, 'en_attente']);
                $message = 'Projet déposé avec succès. En attente de validation.';
            } else {
                $errors[] = 'Erreur lors de l\'upload du fichier.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déposer un projet</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h2>Déposer un projet</h2>
    <?php if ($message): ?><div class="success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($errors): ?><ul class="fail"><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Compétence :
            <select name="competence" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($competences as $c): ?>
                    <option value="<?= $c['id_competence'] ?>" <?= ($selected === (int)$c['id_competence']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <label>Fichier : <input type="file" name="fichier" required></label><br>
        <label>Commentaire (optionnel) :<br><textarea name="commentaire" rows="3" cols="40"></textarea></label><br>
        <button type="submit">Déposer</button>
    </form>
</body>
</html>

<?php
require_once __DIR__ . '/../includes/auth.php';
requireEtudiant();
require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection();
$competences = $pdo->query('SELECT id_competence, nom FROM competence ORDER BY nom')->fetchAll();

$selected = isset($_POST['competence']) ? (int)$_POST['competence'] : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Compétences</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h2>Choisir une compétence</h2>
    <form method="post">
        <label>Compétence :
            <select name="competence" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($competences as $c): ?>
                    <option value="<?= $c['id_competence'] ?>" <?= ($selected === (int)$c['id_competence']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Valider</button>
    </form>

    <?php if ($selected): ?>
        <form method="get" action="/qcm_etudiant/">
            <input type="hidden" name="id_competence" value="<?= $selected ?>">
            <button type="submit">Passer un QCM</button>
        </form>
        <form method="get" action="/projet_etudiant/">
            <input type="hidden" name="id_competence" value="<?= $selected ?>">
            <button type="submit">Déposer un projet</button>
        </form>
    <?php endif; ?>
</body>
</html>

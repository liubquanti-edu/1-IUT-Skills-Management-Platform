<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection();
$role = $_SESSION['user_role'];

if ($role === 'formateur' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $competenceId = (int)$_POST['competence_id'];
    $scoreMin = isset($_POST['score_min']) && $_POST['score_min'] !== '' ? (int)$_POST['score_min'] : null;
    $projetObligatoire = isset($_POST['projet_obligatoire']) ? 1 : 0;
    $description = trim($_POST['description']);
    
    $check = $pdo->prepare('SELECT id_critere FROM critere_validation WHERE id_competence = ?');
    $check->execute([$competenceId]);
    $exists = $check->fetch();
    
    if ($exists) {
        $pdo->prepare('UPDATE critere_validation SET score_min_qcm = ?, projet_obligatoire = ?, description = ? WHERE id_competence = ?')
            ->execute([$scoreMin, $projetObligatoire, $description, $competenceId]);
    } else {
        $pdo->prepare('INSERT INTO critere_validation (id_competence, score_min_qcm, projet_obligatoire, description) VALUES (?, ?, ?, ?)')
            ->execute([$competenceId, $scoreMin, $projetObligatoire, $description]);
    }
}

$competences = $pdo->query('
    SELECT c.id_competence, c.nom, cv.score_min_qcm, cv.projet_obligatoire, cv.description
    FROM competence c
    LEFT JOIN critere_validation cv ON c.id_competence = cv.id_competence
    ORDER BY c.nom
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Critères de Validation</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h2>Critères de Validation des Compétences</h2>

    <?php if ($role === 'formateur'): ?>
        <p>Spécifiez pour chaque compétence les conditions que les étudiants doivent remplir.</p>

        <?php foreach ($competences as $c): ?>
            <div class="critere">
                <h3><?= htmlspecialchars($c['nom']) ?></h3>
                <form method="post">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="competence_id" value="<?= $c['id_competence'] ?>">
                    
                    <label>Score minimum au QCM (%) :</label><br>
                    <input type="number" name="score_min" min="0" max="100" value="<?= $c['score_min_qcm'] !== null ? (int)$c['score_min_qcm'] : '' ?>" placeholder="Laisser vide pour aucun QCM"><br><br>
                    
                    <label>
                        <input type="checkbox" name="projet_obligatoire" <?= $c['projet_obligatoire'] ? 'checked' : '' ?>>
                        Projet obligatoire
                    </label><br><br>
                    
                    <label>Description/détails :</label><br>
                    <textarea name="description" rows="3" cols="50"><?= htmlspecialchars($c['description'] ?? '') ?></textarea><br><br>
                    
                    <button type="submit">Enregistrer</button>
                </form>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <p>Voici les critères que vous devez remplir pour valider chaque compétence.</p>

        <?php foreach ($competences as $c): ?>
            <div class="critere">
                <div class="nom-competence"><?= htmlspecialchars($c['nom']) ?></div>
                <?php if ($c['score_min_qcm'] !== null || $c['projet_obligatoire']): ?>
                    <div class="critere-detail">
                        <b>Conditions à remplir :</b>
                        <ul>
                            <?php if ($c['score_min_qcm'] !== null): ?>
                                <li>Score minimum au QCM : <b><?= (int)$c['score_min_qcm'] ?>%</b></li>
                            <?php endif; ?>
                            <?php if ($c['projet_obligatoire']): ?>
                                <li>Soumission et validation d'un projet <b>obligatoire</b></li>
                            <?php else: ?>
                                <li>Projet : <b>Optionnel</b></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php if ($c['description']): ?>
                        <div class="critere-detail">
                            <b>Détails :</b> <?= htmlspecialchars($c['description']) ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="pas-criteria">
                        Critères non encore définis par le formateur.
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a class="button-link" href="/dashboard/">Retour au dashboard</a></p>
</body>
</html>

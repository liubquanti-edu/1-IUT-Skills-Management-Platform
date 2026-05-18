<?php
require_once __DIR__ . '/../includes/auth.php';
requireEtudiant();
require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];


$validatedCompetences = $pdo->prepare('
    SELECT vc.id_validation, c.nom, vc.date_validation
    FROM validation_competence vc
    JOIN competence c ON vc.id_competence = c.id_competence
    WHERE vc.id_user = ?
    ORDER BY vc.date_validation DESC
');
$validatedCompetences->execute([$userId]);
$competencesValidees = $validatedCompetences->fetchAll();


$projets = $pdo->prepare('
    SELECT sp.id_soumission, p.titre, c.nom AS competence, sp.statut, sp.date_soumission, sp.commentaire_formateur
    FROM soumission_projet sp
    JOIN projet p ON sp.id_projet = p.id_projet
    LEFT JOIN competence c ON p.id_competence = c.id_competence
    WHERE sp.id_user = ?
    ORDER BY sp.date_soumission DESC
');
$projets->execute([$userId]);
$projetsData = $projets->fetchAll();


$qcms = $pdo->prepare('
    SELECT tq.id_tentative, q.titre, c.nom AS competence, tq.score, tq.date_passage
    FROM tentative_qcm tq
    JOIN qcm q ON tq.id_qcm = q.id_qcm
    LEFT JOIN qcm_competence qc ON q.id_qcm = qc.id_qcm
    LEFT JOIN competence c ON qc.id_competence = c.id_competence
    WHERE tq.id_user = ?
    ORDER BY tq.date_passage DESC
');
$qcms->execute([$userId]);
$qcmsData = $qcms->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Validations</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h2>Mes Validations et Progression</h2>

    <h3>✓ Compétences validées</h3>
    <?php if ($competencesValidees): ?>
        <ul>
        <?php foreach ($competencesValidees as $c): ?>
            <li>
                <b><?= htmlspecialchars($c['nom']) ?></b> - Validée le <?= htmlspecialchars($c['date_validation']) ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Vous n'avez pas encore validé de compétences.</p>
    <?php endif; ?>

    <h3>📝 Projets soumis</h3>
    <?php if ($projetsData): ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <tr><th>Titre</th><th>Compétence</th><th>Statut</th><th>Date</th><th>Commentaire</th></tr>
            <?php foreach ($projetsData as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['titre']) ?></td>
                    <td><?= htmlspecialchars($p['competence'] ?? 'N/A') ?></td>
                    <td class="<?= $p['statut'] ?>"><?= ucfirst($p['statut']) ?></td>
                    <td><?= htmlspecialchars($p['date_soumission']) ?></td>
                    <td><?= $p['commentaire_formateur'] ? htmlspecialchars($p['commentaire_formateur']) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Vous n'avez pas encore soumis de projets.</p>
    <?php endif; ?>

    <h3>📊 Résultats QCM</h3>
    <?php if ($qcmsData): ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <tr><th>QCM</th><th>Compétence</th><th>Score</th><th>Date</th></tr>
            <?php foreach ($qcmsData as $q): ?>
                <tr>
                    <td><?= htmlspecialchars($q['titre']) ?></td>
                    <td><?= htmlspecialchars($q['competence'] ?? 'N/A') ?></td>
                    <td><b><?= (int)$q['score'] ?>/100</b></td>
                    <td><?= htmlspecialchars($q['date_passage']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Vous n'avez pas encore passé de QCM.</p>
    <?php endif; ?>

    <p><a class="button-link" href="/dashboard/">Retour au dashboard</a></p>
</body>
</html>

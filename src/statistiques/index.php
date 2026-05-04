<?php
require_once __DIR__ . '/../includes/auth.php';
requireFormateur();
require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection();
$formateurId = $_SESSION['user_id'];

// Stats
$nbFormations = $pdo->prepare('SELECT COUNT(*) FROM formation WHERE id_formateur = ?');
$nbFormations->execute([$formateurId]);
$nbFormationsCount = $nbFormations->fetchColumn();

$nbQcm = $pdo->prepare('SELECT COUNT(*) FROM qcm WHERE id_formateur = ?');
$nbQcm->execute([$formateurId]);
$nbQcmCount = $nbQcm->fetchColumn();

// Résultats QCM
$statsQcm = $pdo->prepare('
    SELECT 
        COUNT(tq.id_tentative) AS nb_tentatives,
        AVG(tq.score) AS score_moyen,
        MAX(tq.score) AS score_max,
        MIN(tq.score) AS score_min
    FROM tentative_qcm tq
    JOIN qcm q ON tq.id_qcm = q.id_qcm
    WHERE q.id_formateur = ?
');
$statsQcm->execute([$formateurId]);
$statsQcmData = $statsQcm->fetch();

// Projets
$projetsPending = $pdo->query('SELECT COUNT(*) FROM soumission_projet WHERE statut = "en_attente"')->fetchColumn();

$projetsStats = $pdo->query('
    SELECT sp.statut, COUNT(*) AS count
    FROM soumission_projet sp
    GROUP BY sp.statut
')->fetchAll();

// Messages
$messagesStats = $pdo->prepare('
    SELECT 
        (SELECT COUNT(*) FROM message WHERE id_formateur = ? AND (reponse IS NULL OR reponse = "")) AS non_repondus,
        (SELECT COUNT(*) FROM message WHERE id_formateur = ? AND reponse IS NOT NULL AND reponse != "") AS repondus
');
$messagesStats->execute([$formateurId, $formateurId]);
$messagesStatsData = $messagesStats->fetch();

// Détail QCM
$qcmsList = $pdo->prepare('
    SELECT 
        q.id_qcm, 
        q.titre,
        COUNT(tq.id_tentative) AS nb_tentatives,
        AVG(tq.score) AS score_moyen
    FROM qcm q
    LEFT JOIN tentative_qcm tq ON q.id_qcm = tq.id_qcm
    WHERE q.id_formateur = ?
    GROUP BY q.id_qcm
    ORDER BY q.titre
');
$qcmsList->execute([$formateurId]);
$qcmsListData = $qcmsList->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h2>Statistiques</h2>

    <h3>Vue d'ensemble</h3>
    <div class="stat-box">
        <div class="stat-number"><?= (int)$nbFormationsCount ?></div>
        <div class="stat-label">Formations créées</div>
    </div>

    <div class="stat-box">
        <div class="stat-number"><?= (int)$nbQcmCount ?></div>
        <div class="stat-label">QCM créés</div>
    </div>

    <div class="stat-box">
        <div class="stat-number"><?= (int)$projetsPending ?></div>
        <div class="stat-label">Projets en attente de validation</div>
    </div>

    <div class="stat-box">
        <div class="stat-number"><?= (int)$messagesStatsData['non_repondus'] ?></div>
        <div class="stat-label">Messages non répondus</div>
    </div>

    <h3>Résultats des QCM</h3>
    <?php if ($statsQcmData['nb_tentatives'] > 0): ?>
        <div class="stat-box">
            <p><b>Nombre de tentatives :</b> <?= (int)$statsQcmData['nb_tentatives'] ?></p>
            <p><b>Score moyen :</b> <?= round($statsQcmData['score_moyen'], 2) ?>/100</p>
            <p><b>Score maximum :</b> <?= (int)$statsQcmData['score_max'] ?>/100</p>
            <p><b>Score minimum :</b> <?= (int)$statsQcmData['score_min'] ?>/100</p>
        </div>
    <?php else: ?>
        <p>Aucune tentative de QCM enregistrée.</p>
    <?php endif; ?>

    <h3>Statut des projets</h3>
    <?php if ($projetsStats): ?>
        <table border="1" cellpadding="10">
            <tr><th>Statut</th><th>Nombre</th></tr>
            <?php foreach ($projetsStats as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['statut']) ?></td>
                    <td><b><?= (int)$s['count'] ?></b></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Aucun projet soumis.</p>
    <?php endif; ?>

    <h3>Détail par QCM</h3>
    <?php if ($qcmsListData): ?>
        <table border="1" cellpadding="10">
            <tr><th>Titre du QCM</th><th>Tentatives</th><th>Score moyen</th></tr>
            <?php foreach ($qcmsListData as $q): ?>
                <tr>
                    <td><?= htmlspecialchars($q['titre']) ?></td>
                    <td><?= (int)$q['nb_tentatives'] ?></td>
                    <td><?= $q['nb_tentatives'] > 0 ? round($q['score_moyen'], 2) : '—' ?>/100</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Vous n'avez pas créé de QCM.</p>
    <?php endif; ?>

    <p><a href="/dashboard/">Retour au dashboard</a></p>
</body>
</html>

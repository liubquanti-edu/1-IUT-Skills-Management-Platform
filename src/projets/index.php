<?php
require_once __DIR__ . '/../includes/auth.php';
requireFormateur();
require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_soumission'], $_POST['action'])) {
    $id_soumission = (int)$_POST['id_soumission'];
    $action = $_POST['action'];
    $commentaire = trim($_POST['commentaire'] ?? '');
    if ($action === 'valider') {
        $pdo->prepare('UPDATE soumission_projet SET statut = ?, commentaire_formateur = ? WHERE id_soumission = ?')->execute(['valide', $commentaire, $id_soumission]);
        $stmt = $pdo->prepare('SELECT sp.id_user, p.id_competence FROM soumission_projet sp JOIN projet p ON sp.id_projet = p.id_projet WHERE sp.id_soumission = ?');
        $stmt->execute([$id_soumission]);
        $row = $stmt->fetch();
        if ($row) {
            $stmt2 = $pdo->prepare('SELECT 1 FROM validation_competence WHERE id_user = ? AND id_competence = ?');
            $stmt2->execute([$row['id_user'], $row['id_competence']]);
            if (!$stmt2->fetch()) {
                $pdo->prepare('INSERT INTO validation_competence (id_user, id_competence) VALUES (?, ?)')->execute([$row['id_user'], $row['id_competence']]);
            }
        }
    } elseif ($action === 'refuser') {
        $pdo->prepare('UPDATE soumission_projet SET statut = ?, commentaire_formateur = ? WHERE id_soumission = ?')->execute(['refuse', $commentaire, $id_soumission]);
    }
}

$projets = $pdo->query('SELECT sp.id_soumission, sp.fichier, sp.date_soumission, sp.statut, u.nom AS etudiant, c.nom AS competence FROM soumission_projet sp JOIN utilisateur u ON sp.id_user = u.id_user LEFT JOIN projet p ON sp.id_projet = p.id_projet LEFT JOIN competence c ON p.id_competence = c.id_competence WHERE sp.statut = "en_attente" ORDER BY sp.date_soumission DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Validation des projets</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h2>Validation des projets</h2>
    <?php if ($projets): ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>Étudiant</th>
                <th>Compétence</th>
                <th>Fichier</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
            <?php foreach ($projets as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['etudiant']) ?></td>
                    <td><?= htmlspecialchars($p['competence']) ?></td>
                    <td><a href="/uploads/<?= htmlspecialchars($p['fichier']) ?>">Télécharger</a></td>
                    <td><?= htmlspecialchars($p['date_soumission']) ?></td>
                    <td><?= htmlspecialchars($p['statut']) ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="id_soumission" value="<?= (int)$p['id_soumission'] ?>">
                            <input type="text" name="commentaire" placeholder="Commentaire (optionnel)">
                            <button type="submit" name="action" value="valider">Valider</button>
                            <button type="submit" name="action" value="refuser">Refuser</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Aucun projet en attente.</p>
    <?php endif; ?>

    <p><a href="/dashboard/">Retour au dashboard</a></p>
</body>
</html>

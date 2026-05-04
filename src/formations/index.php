<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];
$role = $_SESSION['user_role'];
$competences = $pdo->query('SELECT id_competence, nom FROM competence ORDER BY nom')->fetchAll();

// Traiter création (formateur)
if ($role === 'formateur' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $titre = trim($_POST['titre']);
    $competencesSelected = isset($_POST['competences']) ? $_POST['competences'] : [];
    
    if ($titre) {
        $fichier = null;
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $fichier = 'formation_' . time() . '_' . uniqid() . '.pdf';
                move_uploaded_file($_FILES['pdf']['tmp_name'], __DIR__ . '/../uploads/' . $fichier);
            }
        }
        
        $stmt = $pdo->prepare('INSERT INTO formation (titre, fichier_pdf, id_formateur) VALUES (?, ?, ?)');
        $stmt->execute([$titre, $fichier, $userId]);
        $formationId = $pdo->lastInsertId();
        
        foreach ($competencesSelected as $compId) {
            $pdo->prepare('INSERT INTO formation_competence (id_formation, id_competence) VALUES (?, ?)')
                ->execute([$formationId, (int)$compId]);
        }
        
        header('Location: /formations/');
        exit;
    }
}

// Traiter suppression (formateur)
if ($role === 'formateur' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    $pdo->prepare('DELETE FROM formation WHERE id_formation = ? AND id_formateur = ?')->execute([$id, $userId]);
    header('Location: /formations/');
    exit;
}

// Filtrer formations (étudiant)
$filterCompetence = isset($_GET['competence']) ? (int)$_GET['competence'] : null;

if ($filterCompetence) {
    $stmt = $pdo->prepare('
        SELECT f.id_formation, f.titre, f.fichier_pdf, u.nom AS formateur
        FROM formation f
        JOIN formation_competence fc ON f.id_formation = fc.id_formation
        JOIN utilisateur u ON f.id_formateur = u.id_user
        WHERE fc.id_competence = ?
        ORDER BY f.titre
    ');
    $stmt->execute([$filterCompetence]);
    $formations = $stmt->fetchAll();
} else {
    $stmt = $pdo->query('
        SELECT f.id_formation, f.titre, f.fichier_pdf, u.nom AS formateur
        FROM formation f
        JOIN utilisateur u ON f.id_formateur = u.id_user
        ORDER BY f.titre
    ');
    $formations = $stmt->fetchAll();
}

// Récupérer formations du formateur
$mesFomations = null;
if ($role === 'formateur') {
    $mesFomations = $pdo->prepare('
        SELECT id_formation, titre, fichier_pdf
        FROM formation
        WHERE id_formateur = ?
        ORDER BY id_formation DESC
    ');
    $mesFomations->execute([$userId]);
    $mesFomations = $mesFomations->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formations</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h2>Formations</h2>

    <?php if ($role === 'formateur'): ?>
        <h3>Créer une nouvelle formation</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">
            
            <label>Titre :</label><br>
            <input type="text" name="titre" required><br><br>
            
            <label>Fichier PDF :</label><br>
            <input type="file" name="pdf" accept=".pdf"><br><br>
            
            <label>Associer à des compétences :</label><br>
            <?php foreach ($competences as $c): ?>
                <input type="checkbox" name="competences[]" value="<?= $c['id_competence'] ?>">
                <?= htmlspecialchars($c['nom']) ?><br>
            <?php endforeach; ?>
            <br>
            
            <button type="submit">Créer</button>
        </form>

        <h3>Mes formations</h3>
        <?php if ($mesFomations): ?>
            <table border="1" cellpadding="10">
                <tr><th>Titre</th><th>Fichier</th><th>Actions</th></tr>
                <?php foreach ($mesFomations as $f): ?>
                    <tr>
                        <td><?= htmlspecialchars($f['titre']) ?></td>
                        <td><?php if ($f['fichier_pdf']): ?>
                            <a href="/uploads/<?= htmlspecialchars($f['fichier_pdf']) ?>" target="_blank">📄 Télécharger</a>
                        <?php else: ?>
                            —
                        <?php endif; ?></td>
                        <td>
                            <form method="post">
                                <button type="submit" name="delete" value="<?= $f['id_formation'] ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Vous n'avez pas encore créé de formations.</p>
        <?php endif; ?>

    <?php else: // Étudiant ?>
        <label><b>Filtrer par compétence :</b></label>
        <form method="get">
            <select name="competence" onchange="this.form.submit()">
                <option value="">-- Toutes les formations --</option>
                <?php foreach ($competences as $c): ?>
                    <option value="<?= $c['id_competence'] ?>" <?= ($filterCompetence === (int)$c['id_competence']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <h3>Résultats</h3>
        <?php if ($formations): ?>
            <table border="1" cellpadding="10">
                <tr><th>Titre</th><th>Formateur</th><th>Accès</th></tr>
                <?php foreach ($formations as $f): ?>
                    <tr>
                        <td><?= htmlspecialchars($f['titre']) ?></td>
                        <td><?= htmlspecialchars($f['formateur']) ?></td>
                        <td>
                            <?php if ($f['fichier_pdf']): ?>
                                <a href="/uploads/<?= htmlspecialchars($f['fichier_pdf']) ?>" target="_blank">📄 Télécharger PDF</a>
                            <?php else: ?>
                                Aucun fichier
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Aucune formation disponible.</p>
        <?php endif; ?>
    <?php endif; ?>

    <p><a href="/dashboard/">Retour au dashboard</a></p>
</body>
</html>

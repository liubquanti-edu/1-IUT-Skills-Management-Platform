<?php
require_once __DIR__ . '/../includes/auth.php';
requireFormateur();
require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection();
$formateurId = $_SESSION['user_id'];
$competences = $pdo->query('SELECT id_competence, nom FROM competence ORDER BY nom')->fetchAll();

// Traiter création QCM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_qcm') {
    $titre = trim($_POST['titre']);
    $competencesSelected = isset($_POST['competences']) ? $_POST['competences'] : [];
    
    if ($titre) {
        $stmt = $pdo->prepare('INSERT INTO qcm (titre, id_formateur) VALUES (?, ?)');
        $stmt->execute([$titre, $formateurId]);
        $qcmId = $pdo->lastInsertId();
        
        foreach ($competencesSelected as $compId) {
            $pdo->prepare('INSERT INTO qcm_competence (id_qcm, id_competence) VALUES (?, ?)')
                ->execute([$qcmId, (int)$compId]);
        }
        
        header('Location: /qcm/?edit=' . $qcmId);
        exit;
    }
}

// Traiter suppression QCM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    $pdo->prepare('DELETE FROM qcm WHERE id_qcm = ? AND id_formateur = ?')->execute([$id, $formateurId]);
    header('Location: /qcm/');
    exit;
}

// Mode édition
$editQcmId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
if ($editQcmId) {
    $check = $pdo->prepare('SELECT id_qcm FROM qcm WHERE id_qcm = ? AND id_formateur = ?');
    $check->execute([$editQcmId, $formateurId]);
    if (!$check->fetch()) {
        $editQcmId = null;
    }
}

// Traiter ajout question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_question') {
    $qcmId = (int)$_POST['qcm_id'];
    $contenu = trim($_POST['contenu_question']);
    $reponses = isset($_POST['reponse']) ? $_POST['reponse'] : [];
    $correctes = isset($_POST['correcte']) ? $_POST['correcte'] : [];
    
    if ($contenu && count($reponses) >= 2) {
        $stmt = $pdo->prepare('INSERT INTO question (contenu, id_qcm) VALUES (?, ?)');
        $stmt->execute([$contenu, $qcmId]);
        $questionId = $pdo->lastInsertId();
        
        foreach ($reponses as $idx => $rep) {
            $rep = trim($rep);
            if ($rep) {
                $estCorrecte = in_array($idx, $correctes) ? 1 : 0;
                $pdo->prepare('INSERT INTO reponse (contenu, est_correcte, id_question) VALUES (?, ?, ?)')
                    ->execute([$rep, $estCorrecte, $questionId]);
            }
        }
    }
}

// Traiter suppression question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_question'])) {
    $questionId = (int)$_POST['delete_question'];
    $pdo->prepare('DELETE FROM question WHERE id_question = ?')->execute([$questionId]);
}

// Récupérer QCMs
$qcms = $pdo->prepare('SELECT id_qcm, titre FROM qcm WHERE id_formateur = ? ORDER BY id_qcm DESC');
$qcms->execute([$formateurId]);
$qcmsData = $qcms->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes QCM</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h2>Mes QCM</h2>

    <?php if (!$editQcmId): ?>
        <h3>Créer un nouveau QCM</h3>
        <form method="post">
            <input type="hidden" name="action" value="create_qcm">
            
            <label>Titre du QCM :</label><br>
            <input type="text" name="titre" required><br><br>
            
            <label>Associer à des compétences :</label><br>
            <?php foreach ($competences as $c): ?>
                <input type="checkbox" name="competences[]" value="<?= $c['id_competence'] ?>">
                <?= htmlspecialchars($c['nom']) ?><br>
            <?php endforeach; ?>
            <br>
            
            <button type="submit">Créer QCM</button>
        </form>

        <h3>Vos QCM</h3>
        <?php if ($qcmsData): ?>
            <table border="1" cellpadding="10">
                <tr><th>Titre</th><th>Actions</th></tr>
                <?php foreach ($qcmsData as $q): ?>
                    <tr>
                        <td><?= htmlspecialchars($q['titre']) ?></td>
                        <td>
                            <a href="/qcm/?edit=<?= $q['id_qcm'] ?>">Éditer</a>
                            <form method="post">
                                <button type="submit" name="delete" value="<?= $q['id_qcm'] ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Vous n'avez pas encore créé de QCM.</p>
        <?php endif; ?>

    <?php else: // Mode édition ?>
        <?php
        $qcmInfo = $pdo->prepare('SELECT titre FROM qcm WHERE id_qcm = ?');
        $qcmInfo->execute([$editQcmId]);
        $qcm = $qcmInfo->fetch();
        
        $questions = $pdo->prepare('SELECT id_question, contenu FROM question WHERE id_qcm = ? ORDER BY id_question');
        $questions->execute([$editQcmId]);
        $questionsData = $questions->fetchAll();
        ?>
        
        <h3>Éditer : <?= htmlspecialchars($qcm['titre']) ?></h3>
        
        <h4>Ajouter une question</h4>
        <form method="post">
            <input type="hidden" name="action" value="add_question">
            <input type="hidden" name="qcm_id" value="<?= $editQcmId ?>">
            
            <label>Énoncé :</label><br>
            <textarea name="contenu_question" rows="3" cols="50" required></textarea><br><br>
            
            <label>Réponses possibles :</label><br>
            <?php for ($i = 0; $i < 4; $i++): ?>
                <input type="checkbox" name="correcte[]" value="<?= $i ?>"> Correcte
                <input type="text" name="reponse[]" placeholder="Réponse <?= $i+1 ?>"><br>
            <?php endfor; ?>
            <br>
            
            <button type="submit">Ajouter question</button>
        </form>

        <h4>Questions existantes</h4>
        <?php if ($questionsData): ?>
            <?php foreach ($questionsData as $q): ?>
                <div>
                    <b><?= htmlspecialchars($q['contenu']) ?></b><br>
                    <?php
                    $repStmt = $pdo->prepare('SELECT id_reponse, contenu, est_correcte FROM reponse WHERE id_question = ?');
                    $repStmt->execute([$q['id_question']]);
                    $reponses = $repStmt->fetchAll();
                    ?>
                    <ul>
                    <?php foreach ($reponses as $r): ?>
                        <li><?= htmlspecialchars($r['contenu']) ?> <?= $r['est_correcte'] ? '✓' : '' ?></li>
                    <?php endforeach; ?>
                    </ul>
                    <form method="post">
                        <button type="submit" name="delete_question" value="<?= $q['id_question'] ?>">Supprimer</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune question ajoutée.</p>
        <?php endif; ?>
        
        <p><a href="/qcm/">Retour à la liste</a></p>
    <?php endif; ?>

    <p><a href="/dashboard/">Retour au dashboard</a></p>
</body>
</html>

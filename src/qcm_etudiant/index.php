<?php
require_once __DIR__ . '/../includes/auth.php';
requireEtudiant();
require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];
$id_competence = isset($_GET['id_competence']) ? (int)$_GET['id_competence'] : 0;


$stmt = $pdo->prepare('SELECT qcm.id_qcm, qcm.titre FROM qcm JOIN qcm_competence qc ON qcm.id_qcm = qc.id_qcm WHERE qc.id_competence = ? LIMIT 1');
$stmt->execute([$id_competence]);
$qcm = $stmt->fetch();

if (!$qcm) {
    echo "<p class=\"fail\">Aucun QCM disponible pour cette compétence.</p>";
    exit;
}
$id_qcm = $qcm['id_qcm'];


$stmt = $pdo->prepare('SELECT date_passage FROM tentative_qcm WHERE id_user = ? AND id_qcm = ? ORDER BY date_passage DESC LIMIT 1');
$stmt->execute([$userId, $id_qcm]);
$last = $stmt->fetch();
$canPass = true;
if ($last) {
    $lastDate = strtotime($last['date_passage']);
    $nextAllowed = strtotime('+30 days', $lastDate);
    if (time() < $nextAllowed) {
        $canPass = false;
        $days = ceil(($nextAllowed - time()) / 86400);
    }
}

if (!$canPass) {
    echo "<p class=\"fail\">Vous avez déjà passé ce QCM récemment. Prochain passage possible dans $days jour(s).</p>";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $total = 0;
    $stmt = $pdo->prepare('SELECT id_question FROM question WHERE id_qcm = ?');
    $stmt->execute([$id_qcm]);
    $questions = $stmt->fetchAll();
    foreach ($questions as $q) {
        $qid = $q['id_question'];
        $total++;
        $rep = $_POST['q_' . $qid] ?? null;
        if ($rep) {
            $stmt2 = $pdo->prepare('SELECT est_correcte FROM reponse WHERE id_reponse = ? AND id_question = ?');
            $stmt2->execute([$rep, $qid]);
            if ($stmt2->fetchColumn()) {
                $score++;
            }
        }
    }
    $finalScore = $total ? round($score * 100 / $total) : 0;
    
    $stmt = $pdo->prepare('INSERT INTO tentative_qcm (id_user, id_qcm, score) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $id_qcm, $finalScore]);
    echo "<div class=\"success\"><h2>Résultat du QCM</h2><p>Score : $finalScore % ($score/$total bonnes réponses)</p></div>";
    exit;
}


$stmt = $pdo->prepare('SELECT id_question, contenu FROM question WHERE id_qcm = ?');
$stmt->execute([$id_qcm]);
$questions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($qcm['titre']) ?></title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h2><?= htmlspecialchars($qcm['titre']) ?></h2>
    <form method="post">
        <?php foreach ($questions as $q): ?>
            <fieldset>
                <legend><?= htmlspecialchars($q['contenu']) ?></legend>
                <?php
                $stmt2 = $pdo->prepare('SELECT id_reponse, contenu FROM reponse WHERE id_question = ?');
                $stmt2->execute([$q['id_question']]);
                $reponses = $stmt2->fetchAll();
                foreach ($reponses as $r): ?>
                    <label><input type="radio" name="q_<?= $q['id_question'] ?>" value="<?= $r['id_reponse'] ?>"> <?= htmlspecialchars($r['contenu']) ?></label><br>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
        <button type="submit">Valider</button>
    </form>
</body>
</html>

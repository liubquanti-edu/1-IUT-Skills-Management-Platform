<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

// Traiter réponse (formateur)
if ($role === 'formateur' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    $messageId = (int)$_POST['message_id'];
    $reponse = trim($_POST['reponse']);
    
    if ($reponse) {
        $pdo->prepare('UPDATE message SET reponse = ? WHERE id_message = ? AND id_formateur = ?')
            ->execute([$reponse, $messageId, $userId]);
    }
}

// Traiter envoi (étudiant)
if ($role === 'etudiant' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $contenu = trim($_POST['message']);
    $formateur = (int)$_POST['formateur'];
    if ($contenu && $formateur > 0) {
        $pdo->prepare('INSERT INTO message (id_etudiant, id_formateur, contenu) VALUES (?, ?, ?)')
            ->execute([$userId, $formateur, $contenu]);
        header('Location: /messages/');
        exit;
    }
}

$formateurs = $pdo->query('SELECT id_user, nom FROM utilisateur WHERE role = "formateur" ORDER BY nom')->fetchAll();

if ($role === 'etudiant') {
    // Afficher les messages de l'étudiant
    $messages = $pdo->prepare('
        SELECT m.id_message, m.contenu, m.reponse, m.date_message, u.nom AS formateur
        FROM message m
        JOIN utilisateur u ON m.id_formateur = u.id_user
        WHERE m.id_etudiant = ?
        ORDER BY m.date_message DESC
    ');
    $messages->execute([$userId]);
    $messagesData = $messages->fetchAll();
} else {
    // Formateur voit tous ses messages
    $messages = $pdo->prepare('
        SELECT m.id_message, m.contenu, m.reponse, m.date_message, u.nom AS etudiant
        FROM message m
        JOIN utilisateur u ON m.id_etudiant = u.id_user
        WHERE m.id_formateur = ?
        ORDER BY m.reponse IS NOT NULL, m.date_message DESC
    ');
    $messages->execute([$userId]);
    $messagesData = $messages->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messages</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h2>Messages</h2>

    <?php if ($role === 'etudiant'): ?>
        <h3>Poser une question</h3>
        <form method="post">
            <label>Formateur :</label>
            <select name="formateur" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($formateurs as $f): ?>
                    <option value="<?= $f['id_user'] ?>"><?= htmlspecialchars($f['nom']) ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Votre question :</label><br>
            <textarea name="message" rows="5" cols="50" required></textarea><br><br>

            <button type="submit">Envoyer</button>
        </form>

        <h3>Historique de vos questions</h3>
        <?php if ($messagesData): ?>
            <ul>
            <?php foreach ($messagesData as $m): ?>
                <li>
                    <strong><?= htmlspecialchars($m['formateur']) ?></strong> (<?= htmlspecialchars($m['date_message']) ?>)<br>
                    <b>Question :</b> <?= htmlspecialchars($m['contenu']) ?><br>
                    <b>Réponse :</b> <?= $m['reponse'] ? htmlspecialchars($m['reponse']) : '<em>En attente...</em>' ?><br><br>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Vous n'avez pas encore posé de questions.</p>
        <?php endif; ?>

    <?php else: // Formateur ?>
        <?php $nbNonRepondus = count(array_filter($messagesData, fn($m) => !$m['reponse'])); ?>
        <p><b><?= $nbNonRepondus ?></b> message(s) non répondu(s)</p>

        <?php if ($messagesData): ?>
            <?php foreach ($messagesData as $m): ?>
                <div class="message-box <?= $m['reponse'] ? 'repondu' : 'non-repondu' ?>">
                    <div class="etudiant"><?= htmlspecialchars($m['etudiant']) ?></div>
                    <div class="date"><?= htmlspecialchars($m['date_message']) ?></div>
                    
                    <p><b>Question :</b></p>
                    <p><?= htmlspecialchars($m['contenu']) ?></p>
                    
                    <?php if ($m['reponse']): ?>
                        <p><b>Votre réponse :</b></p>
                        <p><?= htmlspecialchars($m['reponse']) ?></p>
                    <?php else: ?>
                        <form method="post">
                            <input type="hidden" name="action" value="reply">
                            <input type="hidden" name="message_id" value="<?= $m['id_message'] ?>">
                            
                            <label><b>Votre réponse :</b></label><br>
                            <textarea name="reponse" rows="4" cols="50" required></textarea><br><br>
                            
                            <button type="submit">Envoyer la réponse</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Vous n'avez pas reçu de messages.</p>
        <?php endif; ?>
    <?php endif; ?>

    <p><a href="/dashboard/">Retour au dashboard</a></p>
</body>
</html>

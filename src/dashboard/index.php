<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/database.php';
$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];
$role = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h2>Tableau de bord</h2>

    <?php if ($role === 'etudiant'): ?>
        <h3>Actions disponibles</h3>
        <ul>
            <li><a class="button-link" href="/competences/">Choisir une compétence</a> - Sélectionner une compétence et passer un QCM ou soumettre un projet</li>
            <li><a class="button-link" href="/formations/">Consulter les formations</a> - Accéder aux ressources pédagogiques</li>
            <li><a class="button-link" href="/criteres/">Critères de validation</a> - Voir les conditions à remplir pour valider chaque compétence</li>
            <li><a class="button-link" href="/validations/">Mes validations</a> - Consulter mon statut de progression et résultats</li>
            <li><a class="button-link" href="/messages/">Mes messages</a> - Poser des questions aux formateurs et consulter les réponses</li>
        </ul>
        
        <h3>Résumé</h3>
        <?php
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM validation_competence WHERE id_user = ?');
        $stmt->execute([$userId]);
        $nbCompetences = $stmt->fetchColumn();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM soumission_projet WHERE id_user = ?');
        $stmt->execute([$userId]);
        $nbProjets = $stmt->fetchColumn();
        ?>
        <ul>
            <li><strong>Compétences validées :</strong> <?= (int)$nbCompetences ?></li>
            <li><strong>Projets soumis :</strong> <?= (int)$nbProjets ?></li>
        </ul>

    <?php elseif ($role === 'formateur'): ?>
        <h3>Actions disponibles</h3>
        <ul>
            <li><a class="button-link" href="/formations/">Gérer mes formations</a> - Créer et gérer mes ressources pédagogiques</li>
            <li><a class="button-link" href="/qcm/">Gérer mes QCM</a> - Créer et éditer des questionnaires</li>
            <li><a class="button-link" href="/criteres/">Définir les critères</a> - Spécifier les conditions de validation des compétences</li>
            <li><a class="button-link" href="/projets/">Valider les projets</a> - Évaluer les projets soumis par les étudiants</li>
            <li><a class="button-link" href="/messages/">Mes messages</a> - Consulter et répondre aux questions des étudiants</li>
            <li><a class="button-link" href="/statistiques/">Statistiques</a> - Voir mes statistiques d'utilisation et performances</li>
        </ul>
        
        <h3>Résumé</h3>
        <?php
        $nbProjetsAttente = $pdo->query('SELECT COUNT(*) FROM soumission_projet WHERE statut = "en_attente"')->fetchColumn();
        $nbMessages = $pdo->prepare('SELECT COUNT(*) FROM message WHERE id_formateur = ? AND (reponse IS NULL OR reponse = "")');
        $nbMessages->execute([$userId]);
        $nbMessagesCount = $nbMessages->fetchColumn();
        ?>
        <ul>
            <li><strong>Projets en attente :</strong> <?= (int)$nbProjetsAttente ?></li>
            <li><strong>Messages sans réponse :</strong> <?= (int)$nbMessagesCount ?></li>
        </ul>

    <?php elseif ($role === 'admin'): ?>
        <h3>Statistiques globales</h3>
        <?php
        $nbUsers = $pdo->query('SELECT COUNT(*) FROM utilisateur')->fetchColumn();
        $nbProjets = $pdo->query('SELECT COUNT(*) FROM projet')->fetchColumn();
        $nbQcm = $pdo->query('SELECT COUNT(*) FROM qcm')->fetchColumn();
        $nbTentatives = $pdo->query('SELECT COUNT(*) FROM tentative_qcm')->fetchColumn();
        ?>
        <ul>
            <li>Utilisateurs : <?= (int)$nbUsers ?></li>
            <li>Projets : <?= (int)$nbProjets ?></li>
            <li>QCM : <?= (int)$nbQcm ?></li>
            <li>Tentatives QCM : <?= (int)$nbTentatives ?></li>
        </ul>

        <h3>Inscriptions en attente</h3>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_user'])) {
            $id = (int)$_POST['valider_user'];
            $pdo->prepare('UPDATE utilisateur SET statut = "valide" WHERE id_user = ?')->execute([$id]);
        }
        $enAttente = $pdo->query('SELECT id_user, nom, email, role FROM utilisateur WHERE statut = "en_attente"')->fetchAll();
        ?>
        <?php if ($enAttente): ?>
            <table border="1" cellpadding="5">
                <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Validation</th></tr>
                <?php foreach ($enAttente as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['nom']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td class="button-cell">
                            <form method="post" class="only-button">
                                <button type="submit" name="valider_user" value="<?= (int)$u['id_user'] ?>">Valider</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="success">Aucune inscription en attente.</p>
        <?php endif; ?>

        <h3>Gestion des comptes</h3>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_user'])) {
            $id = (int)$_POST['toggle_user'];
            $statut = $_POST['statut'] === 'valide' ? 'desactive' : 'valide';
            $pdo->prepare('UPDATE utilisateur SET statut = ? WHERE id_user = ?')->execute([$statut, $id]);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_pass'])) {
            $id = (int)$_POST['reset_pass'];
            $newpass = bin2hex(random_bytes(4));
            $hash = password_hash($newpass, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE utilisateur SET password_hash = ? WHERE id_user = ?')->execute([$hash, $id]);
            echo '<div class="success">Mot de passe réinitialisé pour l\'utilisateur ID ' . $id . ': <b>' . $newpass . '</b></div>';
        }
        $users = $pdo->query('SELECT id_user, nom, email, role, statut FROM utilisateur')->fetchAll();
        ?>
        <input type="text" id="userSearch" placeholder="Recherche...">
        <table border="1" cellpadding="5" id="usersTable">
            <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Activation</th><th>Réinitialiser mot de passe</th></tr>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['nom']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td><?= htmlspecialchars($u['statut']) ?></td>
                    <td class="button-cell">
                        <form method="post" class="only-button">
                            <input type="hidden" name="statut" value="<?= htmlspecialchars($u['statut']) ?>">
                            <button type="submit" name="toggle_user" value="<?= (int)$u['id_user'] ?>">
                                <?= $u['statut'] === 'valide' ? 'Désactiver' : 'Activer' ?>
                            </button>
                        </form>
                    </td>
                    <td class="button-cell">
                        <form method="post" class="only-button">
                            <button type="submit" name="reset_pass" value="<?= (int)$u['id_user'] ?>">Réinitialiser</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <script>
        document.getElementById('userSearch').addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#usersTable tr');
            for (let i = 1; i < rows.length; i++) { // skip header
                const name = rows[i].children[0].textContent.toLowerCase();
                const email = rows[i].children[1].textContent.toLowerCase();
                if (name.includes(filter) || email.includes(filter)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
        </script>
    <?php else: ?>
        <p>Rôle inconnu.</p>
    <?php endif; ?>

    <p><a class="button-link" href="/logout/">Déconnexion</a></p>
</body>
</html>

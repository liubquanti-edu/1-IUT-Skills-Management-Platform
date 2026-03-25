<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

try {
	$pdo = getDatabaseConnection();
	$pdo->query('SELECT 1');
	$message = 'Connexion reussie a la base de donnees.';
	$isSuccess = true;
} catch (Throwable $e) {
	$message = 'Echec de la connexion : ' . $e->getMessage();
	$isSuccess = false;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Test Connexion PDO</title>
</head>
<body>
	<main class="card">
		<h1>Verification de la connexion MySQL/PDO</h1>
		<p class="status"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
	</main>
</body>
</html>

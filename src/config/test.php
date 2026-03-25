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
	<style>
		body {
			font-family: Verdana, sans-serif;
			margin: 0;
			min-height: 100vh;
			display: grid;
			place-items: center;
			background: #f4f7fb;
		}

		.card {
			background: #fff;
			border-radius: 10px;
			box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
			padding: 24px;
			max-width: 560px;
			width: calc(100% - 32px);
		}

		h1 {
			margin-top: 0;
			font-size: 1.2rem;
		}

		.status {
			padding: 12px 14px;
			border-radius: 8px;
			font-weight: 700;
			color: #fff;
			background: <?php echo $isSuccess ? '#2f9e44' : '#c92a2a'; ?>;
		}
	</style>
</head>
<body>
	<main class="card">
		<h1>Verification de la connexion MySQL/PDO</h1>
		<p class="status"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
	</main>
</body>
</html>

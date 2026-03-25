<?php
declare(strict_types=1);

function getDatabaseConnection(): PDO
{
	static $pdo = null;

	if ($pdo instanceof PDO) {
		return $pdo;
	}

	$host = 'localhost';
	$dbName = 'cybersecurite_platform';
	$username = 'cybersecurite';
	$password = 'cybersecurite1!';

	$dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";

	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
	];

	try {
		$pdo = new PDO($dsn, $username, $password, $options);
	} catch (PDOException $e) {
		throw new RuntimeException('Erreur de connexion a la base de donnees : ' . $e->getMessage(), 0, $e);
	}

	return $pdo;
}

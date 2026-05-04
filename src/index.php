<?php
session_start();
if (isset($_SESSION['user_id'], $_SESSION['user_role'])) {
	header('Location: /dashboard/');
	exit;
} else {
	header('Location: /login/');
	exit;
}

<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "chauffeur") {
    header("Location: ../connexion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace chauffeur</title>
</head>
<body>
    <h1>Bienvenue dans votre espace chauffeur !</h1>
    <p>Bonjour, <?= htmlspecialchars($_SESSION["full_name"]) ?></p>
</body>
</html>
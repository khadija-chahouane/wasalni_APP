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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Espace Chauffeur - Wasalnii</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/sprint3.css">
</head>

<body class="chauffeur-dashboard">

    <nav class="chauffeur-nav">
        <a href="../index.php" class="brand">
            <span>W</span> Wasalnii
        </a>

        <a href="../logout.php" class="logout-btn">
            Déconnexion
        </a>
    </nav>

    <main class="chauffeur-content">

        <div class="welcome-card">

            <div class="welcome-icon">🚗</div>

            <h1>Bienvenue dans votre espace chauffeur !</h1>

            <p>
                Bonjour,
                <strong><?= htmlspecialchars($_SESSION["full_name"]) ?></strong>
            </p>

            <p class="welcome-description">
                Gérez vos commandes et communiquez avec vos clients
                facilement avec Wasalnii.
            </p>

            <a href="Commandes.php" class="command-btn">
                📋 Nouvelles commandes
            </a>

        </div>

    </main>

</body>
</html>
<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "client") {
    header("Location: ../connexion.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Client - Wasalnii</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/sprint3.css">
</head>

<body>

<header class="topbar">
    <a href="../index.php" class="brand">W Wasalnii</a>

    <div class="notif" id="notif-box"
         data-api="../API/notifications.php">

        <button type="button" id="notif-btn">
            🔔 <span id="notif-count">0</span>
        </button>

        <ul id="notif-list" hidden></ul>
    </div>

    <a href="../connexion.php" class="logout">Déconnexion</a>
</header>

<main class="client-dashboard">

    <h1>Bienvenue dans votre espace client !</h1>

    <p>
        Bonjour,
        <?= htmlspecialchars($_SESSION["full_name"]) ?>
    </p>

    <div class="client-actions">
        <h2>Que souhaitez-vous faire ?</h2>

        <a href="recherchVehic.php" class="btn-client">
            🔍 Rechercher un voyage
        </a>

        <a href="reservation.php" class="btn-client">
            📋 Mes réservations
        </a>
    </div>

</main>

<script src="../js/notifications.js"></script>

</body>
</html>
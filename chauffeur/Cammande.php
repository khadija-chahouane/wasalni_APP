<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "chauffeur") {
    header("Location: ../connexion.php");
    exit;
}
if (empty($_SESSION["csrf"])) {
    $_SESSION["csrf"] = bin2hex(random_bytes(16));
}

$stmt = $pdo->prepare(
    "SELECT r.*, v.chauffeur_id, v.marque, v.modele, v.ville_depart, v.ville_arrivee, v.prix, u.full_name AS client_nom, u.email AS client_email
     FROM reservations r
     JOIN vehicles v ON v.id = r.vehicle_id
     JOIN users u ON u.id = r.user_id
     WHERE v.chauffeur_id = ?
     ORDER BY r.created_at DESC"
);
$stmt->execute([$_SESSION["user_id"]]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$enAttente = array_filter($commandes, fn($c) => $c["statut"] === "en_attente");
$traitees  = array_filter($commandes, fn($c) => $c["statut"] !== "en_attente");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelles commandes - Wasalni</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/sprint3.css">
</head>
<body>
<header class="topbar">
    <a href="dashboard.php">← Tableau de bord</a>
    <div class="notif" id="notif-box" data-api="../API/notifications.php">
        <button type="button" id="notif-btn">🔔 <span id="notif-count">0</span></button>
        <ul id="notif-list" hidden></ul>
    </div>
</header>

<main class="container">
    <h1>Nouvelles commandes</h1>

    <?php if (!$enAttente): ?>
        <p class="empty">Aucune nouvelle commande pour le moment.</p>
    <?php endif; ?>

    <?php foreach ($enAttente as $c): ?>
        <article class="card">
            <h2><?= htmlspecialchars($c["ville_depart"]) ?> → <?= htmlspecialchars($c["ville_arrivee"]) ?></h2>
            <p>🚗 <?= htmlspecialchars($c["marque"] . " " . $c["modele"]) ?>
               · 📅 <?= htmlspecialchars($c["date_voyage"]) ?>
               · 👥 <?= (int)$c["nombre_places"] ?> place(s)
               · 💰 <?= htmlspecialchars($c["prix"]) ?> MAD</p>

            <!-- Informations du client -->
            <div class="client-info">
                <strong>Client :</strong> <?= htmlspecialchars($c["client_nom"]) ?><br>
                <strong>E-mail :</strong> <?= htmlspecialchars($c["client_email"]) ?>
            </div>

            <form action="traiter_commande.php" method="POST" class="actions">
                <input type="hidden" name="csrf" value="<?= $_SESSION["csrf"] ?>">
                <input type="hidden" name="id" value="<?= (int)$c["id"] ?>">
                <button type="submit" name="action" value="accepter" class="btn-accept">✔ Accepter</button>
                <button type="submit" name="action" value="refuser" class="btn-refuse"
                        onclick="return confirm('Refuser cette commande ?')">✖ Refuser</button>
            </form>
        </article>
    <?php endforeach; ?>

    <?php if ($traitees): ?>
        <h2>Commandes traitées</h2>
        <?php foreach ($traitees as $c): ?>
            <article class="card card-done">
                <strong><?= htmlspecialchars($c["ville_depart"]) ?> → <?= htmlspecialchars($c["ville_arrivee"]) ?></strong> · <?= htmlspecialchars($c["date_voyage"]) ?>
                — <?= htmlspecialchars($c["client_nom"]) ?>
                <span class="badge badge-<?= $c["statut"] ?>"><?= $c["statut"] === "acceptee" ? "Acceptée" : "Refusée" ?></span>
                <?php if ($c["statut"] === "acceptee"): ?>
                    <a class="btn-chat" href="../messagerie.php?reservation=<?= (int)$c["id"] ?>">💬 Message</a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
<script src="../js/notifications.js"></script>
</body>
</html>
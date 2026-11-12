<?php
session_start();

require_once __DIR__ . "/../config/database.php";

// Vérifier la connexion
if (!isset($_SESSION["user_id"])) {
    header("Location: ../connexion.php");
    exit;
}

$vehicle_id = (int)($_GET["vehicle_id"] ?? 0);

if ($vehicle_id <= 0) {
    die("Véhicule invalide.");
}

// Récupérer le véhicule
$stmt = $pdo->prepare(
    "SELECT * FROM vehicles WHERE id = ? AND disponible = 1"
);
$stmt->execute([$vehicle_id]);

$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    die("Véhicule introuvable ou indisponible.");
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Réservation</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="container">

    <h1>Réserver votre voyage</h1>

    <div class="vehicle">

        <h2>
            <?= htmlspecialchars($vehicle["marque"]) ?>
            <?= htmlspecialchars($vehicle["modele"]) ?>
        </h2>

        <p>Départ : <?= htmlspecialchars($vehicle["ville_depart"]) ?></p>

        <p>Arrivée : <?= htmlspecialchars($vehicle["ville_arrivee"]) ?></p>

        <p>Prix : <?= number_format($vehicle["prix"], 2) ?> DH</p>

        <p>Places disponibles : <?= (int)$vehicle["places"] ?></p>

    </div>

    <form method="POST" action="../API/reservation.php">

        <input type="hidden" name="vehicle_id"
               value="<?= (int)$vehicle["id"] ?>">

        <label>Date du voyage :</label>

        <input type="datetime-local"
               name="date_voyage"
               min="<?= date('Y-m-d\TH:i') ?>"
               required>

        <label>Nombre de places :</label>

        <input type="number"
               name="nombre_places"
               min="1"
               max="<?= (int)$vehicle["places"] ?>"
               value="1"
               required>

        <button type="submit">Confirmer la réservation</button>

    </form>

    <br>

    <a href="recherchVehic.php">Retour à la recherche</a>

</div>

</body>
</html>
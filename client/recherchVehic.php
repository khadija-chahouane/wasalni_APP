<?php
require_once __DIR__ . "/../config/database.php";

$depart = $_GET['depart'] ?? '';
$arrivee = $_GET['arrivee'] ?? '';

$sql = "SELECT * FROM vehicles
        WHERE disponible = 1";

$params = [];

if ($depart !== '') {
    $sql .= " AND ville_depart = ?";
    $params[] = $depart;
}

if ($arrivee !== '') {
    $sql .= " AND ville_arrivee = ?";
    $params[] = $arrivee;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehicules = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recherche des voyages</title>
    <<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="container">
    <h1>Recherche des voyages</h1>

    <form method="GET" action="recherche.php">
        <label>Ville de départ</label>
        <input type="text" name="depart"
               value="<?= htmlspecialchars($depart) ?>"
               placeholder="Ex: Casablanca" required>

        <label>Ville d'arrivée</label>
        <input type="text" name="arrivee"
               value="<?= htmlspecialchars($arrivee) ?>"
               placeholder="Ex: Rabat" required>

        <button type="submit">Rechercher</button>
    </form>
<?php 
    session_start();
    require_once "database.php";

    if (isset($_SESSION['user_id']))
        ?>
    <h2>Véhicules disponibles</h2>

    <?php if (count($vehicules) > 0): ?>
        <?php foreach ($vehicules as $v): ?>
            <div class="vehicle">
                <h3>
                    <?= htmlspecialchars($v['marque']) ?>
                    <?= htmlspecialchars($v['modele']) ?>
                </h3>

                <p>Départ : <?= htmlspecialchars($v['ville_depart']) ?></p>
                <p>Arrivée : <?= htmlspecialchars($v['ville_arrivee']) ?></p>
                <p>Places : <?= (int)$v['places'] ?></p>
                <p>Prix : <?= number_format($v['prix'], 2) ?> DH</p>

                <a class="btn"
                   href="reservation.php?vehicle_id=<?= (int)$v['id'] ?>">
                    Réserver
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun véhicule disponible pour ce trajet.</p>
    <?php endif; ?>
</div>
    

</body>
</html>
<?php

session_start();

require_once __DIR__ . "/../config/database.php";

// Vérifier la connexion
if (!isset($_SESSION["user_id"])) {
    header("Location: ../connexion.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../client/recherchVehic.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$vehicle_id = (int)($_POST["vehicle_id"] ?? 0);
$nombre_places = (int)($_POST["nombre_places"] ?? 0);
$date_voyage = $_POST["date_voyage"] ?? "";

if ($vehicle_id <= 0 || $nombre_places <= 0 || empty($date_voyage)) {
    die("Veuillez remplir tous les champs.");
}

// Vérifier la date
$date = DateTime::createFromFormat('Y-m-d\TH:i', $date_voyage);

if (!$date || $date->format('Y-m-d\TH:i') !== $date_voyage
    || $date <= new DateTime()) {
    die("Date de voyage invalide.");
}

$date_voyage = $date->format('Y-m-d H:i:s');

try {

    $pdo->beginTransaction();

    // Vérifier le véhicule
    $stmt = $pdo->prepare(
        "SELECT places FROM vehicles
         WHERE id = ? AND disponible = 1"
    );

    $stmt->execute([$vehicle_id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicle) {
        throw new Exception("Véhicule indisponible.");
    }

    // Calculer les places déjà réservées
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(nombre_places), 0)
         FROM reservations
         WHERE vehicle_id = ?
         AND date_voyage = ?
         AND statut IN ('en_attente', 'confirmee')"
    );

    $stmt->execute([$vehicle_id, $date_voyage]);

    $places_reservees = (int)$stmt->fetchColumn();

    $places_restantes = (int)$vehicle["places"] - $places_reservees;

    if ($nombre_places > $places_restantes) {
        throw new Exception("Nombre de places insuffisant.");
    }

    // Ajouter la réservation
    $stmt = $pdo->prepare(
        "INSERT INTO reservations
        (user_id, vehicle_id, date_voyage, nombre_places, statut)
        VALUES (?, ?, ?, ?, 'en_attente')"
    );

    $stmt->execute([
        $user_id,
        $vehicle_id,
        $date_voyage,
        $nombre_places
    ]);

    $pdo->commit();

    echo "Réservation enregistrée avec succès !";

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "Erreur : " . htmlspecialchars($e->getMessage());

}
?>
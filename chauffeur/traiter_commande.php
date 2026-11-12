<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "chauffeur") {
    header("Location: ../connexion.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST"
    || !hash_equals($_SESSION["csrf"] ?? "", $_POST["csrf"] ?? "")) {
    http_response_code(403);
    exit("Requête invalide.");
}

$id     = (int)($_POST["id"] ?? 0);
$action = $_POST["action"] ?? "";
$statut = ["accepter" => "acceptee", "refuser" => "refusee"][$action] ?? null;

if ($id > 0 && $statut) {
    $stmt = $pdo->prepare(
        "UPDATE reservations r
         JOIN vehicles v ON v.id = r.vehicle_id
         SET r.statut = ?
         WHERE r.id = ? AND v.chauffeur_id = ? AND r.statut = 'en_attente'"
    );
    $stmt->execute([$statut, $id, $_SESSION["user_id"]]);

    if ($stmt->rowCount() === 1) {
        $client = $pdo->prepare("SELECT user_id FROM reservations WHERE id = ?");
        $client->execute([$id]);
        $clientId = $client->fetchColumn();

        $texte = $statut === "acceptee"
            ? "Votre commande #$id a été acceptée par le chauffeur."
            : "Votre commande #$id a été refusée par le chauffeur.";
        $lien = $statut === "acceptee" ? "messagerie.php?reservation=$id" : null;

        $pdo->prepare("INSERT INTO notifications (user_id, message, lien) VALUES (?, ?, ?)")
            ->execute([$clientId, $texte, $lien]);
    }
}
header("Location: commandes.php");
exit;
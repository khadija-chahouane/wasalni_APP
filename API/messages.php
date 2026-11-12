<?php
session_start();
require_once __DIR__ . "/../config/database.php";
header("Content-Type: application/json; charset=utf-8");

function fail($code, $msg) {
    http_response_code($code);
    echo json_encode(["success" => false, "message" => $msg]);
    exit;
}
if (!isset($_SESSION["user_id"])) fail(401, "Non connecté");
$uid = (int)$_SESSION["user_id"];

$rid = (int)($_REQUEST["reservation"] ?? 0);
$stmt = $pdo->prepare(
    "SELECT r.user_id AS client_id, v.chauffeur_id, r.statut
     FROM reservations r JOIN vehicles v ON v.id = r.vehicle_id WHERE r.id = ?"
);
$stmt->execute([$rid]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res || ($uid !== (int)$res["client_id"] && $uid !== (int)$res["chauffeur_id"])) fail(403, "Accès refusé");
if ($res["statut"] !== "acceptee") fail(403, "Messagerie disponible après acceptation");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $contenu = trim($_POST["contenu"] ?? "");
    if ($contenu === "" || mb_strlen($contenu) > 1000) fail(422, "Message invalide");

    $pdo->prepare("INSERT INTO messages (reservation_id, sender_id, contenu) VALUES (?, ?, ?)")
        ->execute([$rid, $uid, $contenu]);

    $dest = $uid === (int)$res["client_id"] ? $res["chauffeur_id"] : $res["client_id"];
    $pdo->prepare("INSERT INTO notifications (user_id, message, lien) VALUES (?, ?, ?)")
        ->execute([$dest, "Nouveau message (commande #$rid)", "messagerie.php?reservation=$rid"]);

    echo json_encode(["success" => true]);
    exit;
}

$after = (int)($_GET["after"] ?? 0);
$stmt = $pdo->prepare(
    "SELECT m.id, m.sender_id, m.contenu, m.created_at, u.full_name
     FROM messages m JOIN users u ON u.id = m.sender_id
     WHERE m.reservation_id = ? AND m.id > ? ORDER BY m.id ASC"
);
$stmt->execute([$rid, $after]);
echo json_encode(["success" => true, "me" => $uid, "messages" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
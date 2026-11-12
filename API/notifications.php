<?php
session_start();
require_once __DIR__ . "/../config/database.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Non connecté"]);
    exit;
}
$uid = (int)$_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$uid]);
    echo json_encode(["success" => true]);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, message, lien, is_read, created_at FROM notifications
     WHERE user_id = ? ORDER BY created_at DESC LIMIT 10"
);
$stmt->execute([$uid]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cnt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND user_id = ?");
$cnt->execute([$uid]);

echo json_encode(["success" => true, "unread" => (int)$cnt->fetchColumn(), "items" => $items]);
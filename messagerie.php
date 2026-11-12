<?php
session_start();
require_once __DIR__ . "/config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: connexion.php");
    exit;
}
$uid = (int)$_SESSION["user_id"];
$rid = (int)($_GET["reservation"] ?? 0);

$stmt = $pdo->prepare(
    "SELECT r.*, v.chauffeur_id, v.ville_depart, v.ville_arrivee,
            c.full_name AS client_nom, d.full_name AS chauffeur_nom
     FROM reservations r
     JOIN vehicles v ON v.id = r.vehicle_id
     JOIN users c ON c.id = r.user_id
     JOIN users d ON d.id = v.chauffeur_id
     WHERE r.id = ?"
);
$stmt->execute([$rid]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$r || ($uid !== (int)$r["user_id"] && $uid !== (int)$r["chauffeur_id"]) || $r["statut"] !== "acceptee") {
    http_response_code(403);
    exit("Conversation indisponible.");
}
$interlocuteur = $uid === (int)$r["user_id"] ? $r["chauffeur_nom"] : $r["client_nom"];
$retour = $_SESSION["role"] === "chauffeur" ? "chauffeur/commandes.php" : "client/dashboard.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - Wasalni</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/sprint3.css">
</head>
<body>
<header class="topbar"><a href="<?= $retour ?>">← Retour</a></header>
<main class="container chat">
    <h1>💬 <?= htmlspecialchars($interlocuteur) ?></h1>
    <p class="muted"><?= htmlspecialchars($r["ville_depart"]) ?> → <?= htmlspecialchars($r["ville_arrivee"]) ?> · <?= htmlspecialchars($r["date_voyage"]) ?></p>

    <div id="messages" class="messages"></div>

   <div class="send">
    <textarea id="contenu"
              maxlength="1000"
              placeholder="Votre message..."
              rows="2"></textarea>

    <button type="button" id="envoyer">Envoyer</button>
</div>
</main>

<script>
const RID = <?= $rid ?>;
let lastId = 0;
const box = document.getElementById("messages");
const input = document.getElementById("contenu");

async function charger() {
    const res = await fetch(`API/messages.php?reservation=${RID}&after=${lastId}`);
    const data = await res.json();
    if (!data.success) return;
    data.messages.forEach(m => {
        const div = document.createElement("div");
        div.className = "msg " + (Number(m.sender_id) === data.me ? "mine" : "theirs");
        div.textContent = m.contenu;
        const t = document.createElement("small");
        t.textContent = m.created_at;
        div.appendChild(t);
        box.appendChild(div);
        lastId = Number(m.id);
    });
    if (data.messages.length) box.scrollTop = box.scrollHeight;
}

async function envoyer() {
    const texte = input.value.trim();
    if (!texte) return;
    input.value = "";
    await fetch("API/messages.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: new URLSearchParams({reservation: RID, contenu: texte})
    });
    charger();
}

document.getElementById("envoyer").addEventListener("click", envoyer);
input.addEventListener("keydown", e => { if (e.key === "Enter") envoyer(); });
charger();
setInterval(charger, 3000);
</script>
</body>
</html>
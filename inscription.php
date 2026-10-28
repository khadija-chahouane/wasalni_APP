<?php
require_once __DIR__ . "/config/database.php";
$message = "";
$messageType = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";
    if ($nom === "" || $email === "" || $password === "") {
        $message = "Tous les champs sont obligatoires.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Veuillez saisir un e-mail valide.";
        $messageType = "error";
    } elseif (strlen($password) < 8) {
        $message = "Le mot de passe doit contenir au moins 8 caractères.";
        $messageType = "error";
    } elseif (!in_array($role, ["client", "chauffeur"], true)) {
        $message = "Type de compte invalide.";
        $messageType = "error";
    } else {
        try {
            $check = $pdo->prepare(
                "SELECT id FROM users WHERE email = ?"
            );
            $check->execute([$email]);

            if ($check->fetch()) {
                $message = "Cet e-mail est déjà utilisé.";
                $messageType = "error";
            } else {
                $hashedPassword = password_hash($password,PASSWORD_DEFAULT);
                $sql = "INSERT INTO users(full_name, email, password, role)VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom,$email,$hashedPassword,$role]);
                $message = "Inscription réussie ! Votre compte est créé.";
                $messageType = "success";
            }
        } catch (PDOException $e) {
            $message = "Une erreur est survenue. Réessayez.";
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Transport</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php if ($message !== ""): ?>
        <p style="color: <?= $messageType === 'success' ? 'green' : 'red' ?>;">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form action="" method="POST">
        <h1>Créer un compte</h1>
        <label for="nom">Nom complet :</label>
        <input type="text" id="nom" name="nom" maxlength="100" required >
        <br><br>
        <label for="email">Adresse e-mail :</label>
        <input type="email" id="email" name="email" maxlength="150" required >
        <br><br>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" minlength="8" required>
        <br><br>
        <label for="role">Type de compte :</label>
        <select id="role" name="role" required>
            <option value="client">Client</option>
            <option value="chauffeur">Chauffeur</option>
        </select>
        <br><br>
        <button type="submit">S'inscrire</button> <br>
    <p>
        Vous Avez deja un compte?
        <a href="connexion.php">connexion</a>
    </p>
    </form>

</body>
</html>
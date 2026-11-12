<?php
session_start();

require_once __DIR__ . "/config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {

        $message = "Veuillez vérifier vos informations.";

    } else {

        $stmt = $pdo->prepare(
            "SELECT id, full_name, email, password, role
             FROM users
             WHERE email = ?"
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["role"] = $user["role"];

            // Redirection selon le rôle
            if ($user["role"] === "client") {

                header("Location: client/recherchVehic.php");
                exit;

            } elseif ($user["role"] === "chauffeur") {

                header("Location: chauffeur/dashboard.php");
                exit;

            } elseif ($user["role"] === "admin") {

                header("Location: admin/dashboard.php");
                exit;

            }

        } else {

            $message = "E-mail ou mot de passe incorrect.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Connexion - Wasalnii</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <h1>Se connecter</h1>

        <?php if ($message !== ""): ?>
            <p style="color:red;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="">

            <label for="email">Adresse e-mail :</label>

            <input
                type="email"
                id="email"
                name="email"
                required
            >

            <br><br>

            <label for="password">Mot de passe :</label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <br><br>

            <button type="submit">Se connecter</button>

        </form>

        <p>
            Pas encore de compte ?
            <a href="inscription.php">Créer un compte</a>
        </p>

        <p>
            <a href="index.php">Retour à l'accueil</a>
        </p>

    </div>

</body>
</html>
```

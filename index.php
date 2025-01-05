<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'inscription</title>
    <link rel="stylesheet" href="css/pico.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .form-container h2 {
            margin-bottom: 20px;
            font-size: 1.5em;
            text-align: center;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1em;
        }
        .form-container button:hover {
            background-color: #4cae4c;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
        .login-link {
            text-align: center;
            margin-top: 10px;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>S'inscrire</h2>
        <?php
        $name = $email = $password = "";
        $nameErr = $emailErr = $passwordErr = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = htmlspecialchars($_POST["name"]);
            $email = htmlspecialchars($_POST["email"]);
            $password = htmlspecialchars($_POST["password"]);
             // Hachage du mot de passe pour la sécurité
       // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
       $hashed_password = md5($password);


            if (empty($name)) {
                $nameErr = "Le nom est requis";
            }
            if (empty($email)) {
                $emailErr = "L'email est requis";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailErr = "Format d'email invalide";
            }
            if (empty($password)) {
                $passwordErr = "Le mot de passe est requis";
            } elseif (strlen($password) < 6) {
                $passwordErr = "Le mot de passe doit contenir au moins 6 caractères";
            }

            if (!$nameErr && !$emailErr && !$passwordErr) {
                // Connexion à la base de données
                $conn = new mysqli("localhost", "root", "", "formulaire");


                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "INSERT INTO users (id, email, mdp, nom) VALUES (NULL, '$email', '$password', '$name')";
                if ($conn->query($sql) === TRUE) {
                    echo "<p style='color: green;'>Inscription réussie !</p>";
                } else {
                    echo "<p style='color: red;'>Erreur : " . $conn->error . "</p>";
                }

                $conn->close();
            }
        }
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="name">Nom :</label>
            <input type="text" id="name" name="name" value="<?php echo $name; ?>">
            <span class="error"><?php echo $nameErr; ?></span>

            <label for="email">Email :</label>
            <input type="email" id="email" name="email" value="<?php echo $email; ?>">
            <span class="error"><?php echo $emailErr; ?></span>

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password">
            <span class="error"><?php echo $passwordErr; ?></span>

            <button type="submit">S'inscrire</button>
        </form>
        <div class="login-link">
            <p>Déjà un compte ? <a href="connexion.html">Connectez-vous ici</a>.</p>
        </div>
    </div>
</body>
</html>

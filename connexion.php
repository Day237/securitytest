<?php
// Connexion à la base de données
$servername = "localhost"; // ou l'adresse de votre serveur
$username = "root"; // Nom d'utilisateur par défaut de PHPMyAdmin
$password = ""; // Mot de passe (laisser vide par défaut pour XAMPP/WAMP)
$dbname = "formulaire"; // Nom de la base de données

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = htmlspecialchars($_POST["email"]);
    $password = htmlspecialchars($_POST["password"]);

    // Validation des champs (déjà gérée plus haut)
    if (!$nameErr && !$emailErr && !$passwordErr) {
        // Hachage du mot de passe pour la sécurité
       // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
       $hashed_password = md5($password);

        // Requête d'insertion SQL
        $sql = "INSERT INTO users (id, email, mdp, nom) VALUES (NULL, '$email', '$hashed_password', '$name')";

        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>Registration successful!</p>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Fermer la connexion
$conn->close();
?>

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
    $email = htmlspecialchars($_POST["email"]);
    $password = htmlspecialchars($_POST["password"]);
    
    // Hashage du mot de passe pour la comparaison avec celui stocké dans la base de données
    $hashed_password = md5($password);  // Utilisez md5 seulement si vous hachez le mot de passe avec md5 dans l'inscription

    // Requête pour vérifier si l'utilisateur existe
    $sql = "SELECT * FROM users WHERE email = '$email' AND mdp = '$hashed_password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // L'utilisateur existe
        echo "<p style='color: green;'>Connexion réussie !</p>";
        // Vous pouvez démarrer une session ici ou rediriger l'utilisateur vers une autre page
    } else {
        // L'utilisateur n'existe pas ou les informations sont incorrectes
        echo "<p style='color: red;'>Email ou mot de passe incorrect.</p>";
    }
}

// Fermer la connexion
$conn->close();
?>


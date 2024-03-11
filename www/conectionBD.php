<?php
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Si le formulaire de lancement d'activité est soumis
if(isset($_POST['activity_submit'])){
    $activity_id = $_POST['activity'];
    $person_id = $_SESSION['person_id'];
    $date_debut = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO activites (id_personne, id_activite, date_debut) VALUES ('$person_id', '$activity_id', '$date_debut')";
    if ($conn->query($sql) === TRUE) {
        echo "Activité lancée avec succès.";
    } else {
        echo "Erreur: " . $sql . "<br>" . $conn->error;
    }
}

// Si le bouton stop est cliqué
if(isset($_POST['stop_activity'])){
    $activity_id = $_POST['activity_id'];
    $date_fin = date('Y-m-d H:i:s');
    
    $sql = "UPDATE activites SET date_fin='$date_fin' WHERE id='$activity_id'";
    if ($conn->query($sql) === TRUE) {
        echo "Activité arrêtée avec succès.";
    } else {
        echo "Erreur: " . $sql . "<br>" . $conn->error;
    }
}
?>

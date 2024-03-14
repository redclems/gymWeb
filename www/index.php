<?php
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "gymweb";
$password = "sere";
$dbname = "gymweb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Si le formulaire de lancement d'activité est soumis
if(isset($_POST['activity_submit'])){
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
	$date_debut = date('Y-m-d H:i:s');
    $activity_id = $_POST['activity'];
    

    // Vérifier si la personne existe déjà dans la table
    $query = "SELECT id FROM personne WHERE nom='$nom' AND prenom='$prenom'";
    $result = $conn->query($query);

    if($result->num_rows > 0) {
        // La personne existe déjà, récupérer son ID
        $row = $result->fetch_assoc();
        $person_id = $row['id'];
    } else {
        // La personne n'existe pas, la créer
        $insert_query = "INSERT INTO personne (nom, prenom, date) VALUES ('$nom', '$prenom', '$date_debut')";
        if ($conn->query($insert_query) === TRUE) {
            // Récupérer l'ID de la personne nouvellement créée
            $person_id = $conn->insert_id;
        } else {
            echo "Erreur lors de la création de la personne: " . $conn->error;
            // Terminer l'exécution ou gérer l'erreur selon vos besoins
            exit;
        }
    }
    //
    
    // Stocker l'ID de d dans la session
    $_SESSION['id_personne'] = $person_id;
    
    $_SESSION['activity_id'] = $activity_id;
    $_SESSION['date_debut'] = $date_debut;
    //

    // Insérer l'activité dans la table "activiter"
    $sql = "INSERT INTO activiter (id_personne, id_nom_action, date_debut) VALUES ('$person_id', '$activity_id', '$date_debut')";
    if ($conn->query($sql) === TRUE) {
        echo "Activité lancée avec succès.";
        
    } else {
        echo "Erreur lors de l'insertion de l'activité: " . $conn->error;
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



<!DOCTYPE html>

<html>
<head>
    <title>Gestion des activités</title>
</head>
<body>
    <h1>Gestion des activités</h1>
	
    <?php
    
    // Si la session person_id est définie, afficher les informations de la personne
    if(isset($_SESSION['person_id'])){
        $person_id = $_SESSION['person_id'];
        $sql = "SELECT nom, prenom FROM personne WHERE id='$person_id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<p>Personne: " . $row['prenom'] . " " . $row['nom'] . "</p>";
        }
    }
    

    ?>

    <!-- Formulaire pour sélectionner une activité -->
    <form method="post">
	<label> Nom </label>
	<input name = "nom" id = "nom" type = "text "/> 
	<label> Prenom </label>
	<input name = "prenom" id = "nom" type = "text" /> 
        <select name="activity">
            <?php
            $sql = "SELECT * FROM nom_action";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['nom'] . "</option>";
                }
            }
            ?>
        </select>
        <input type="submit" name="activity_submit" value="Lancer l'activité">
    </form>

    <!-- Bouton pour arrêter l'activité -->
    <form method="post">
        <input type="hidden" name="activity_id" value="<?php echo $activity_id; ?>">
        <input type="submit" name="stop_activity" value="Stop">
    </form>

    <!-- Liste des activités -->
    <h2>Liste des activités</h2>
    <table>
        <tr>
            <th>Personne</th>
            <th>Activité</th>
            <th>Date de début</th>
            <th>Date de fin</th>
        </tr>
        <?php
        $sql = "SELECT personne.nom AS nom_personne, personne.prenom AS prenom_personne, nom_action.nom AS nom_action, activiter.date_debut, activiter.date_fin 
                FROM activiter
                INNER JOIN personne ON activiter.id_personne = personne.id 
                INNER JOIN nom_action ON activiter.id_nom_action = nom_action.id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['prenom_personne'] . " " . $row['nom_personne'] . "</td>";
                echo "<td>" . $row['nom_action'] . "</td>";
                echo "<td>" . $row['date_debut'] . "</td>";
                echo "<td>" . $row['date_fin'] . "</td>";
                echo "</tr>";
            }
        }
        ?>
    </table>

</body>
</html>

<?php
// Fermer la connexion à la base de données
$conn->close();
?>

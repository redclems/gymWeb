<!DOCTYPE html>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des activités</title>
    <link rel="stylesheet" href="./style/styles.css">
</head>
<?php
session_start();

require './connect.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo "<div class='error-box'> Connection failed: " . $conn->connect_error . "</div>";
}else{
    // Si le formulaire de lancement d'activité est soumis
    if(isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['activiter'])){
        $nom         = $_POST['nom'];
        $prenom      = $_POST['prenom'];
    	$date_debut  = date('Y-m-d H:i:s');
        $activity_id = $_POST['activiter'];
        

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
                echo "<div class='error-box'> Erreur lors de la création de la personne: " . $conn->error . "</div>";
                // Terminer l'exécution ou gérer l'erreur selon vos besoins
                exit;
            }
        }
        
        // Stocker l'ID de dans la session
        $_SESSION['id_personne'] = $person_id;
        $_SESSION['activity_id'] = $activity_id;
        $_SESSION['date_debut']  = $date_debut;
        //

        // Insérer l'activité dans la table "activiter"
        $sql = "INSERT INTO activiter (id_personne, id_nom_action, date_debut) VALUES ('$person_id', '$activity_id', '$date_debut')";
        if ($conn->query($sql) === TRUE) {
            echo "<div class='success-box'> Activité lancée avec succès par l'utilisateur " . $person_id . " .</div>";
            
        } else {
            echo "<div class='error-box'>Erreur lors de l'insertion de l'activité: " . $conn->error . "</div>";
        }
        //echo "<p>" . $_SESSION['id_personne'] . " : " . $_SESSION['activity_id'] . " : " . $_SESSION['date_debut'];
    }

    // Si le bouton stop est cliqué
    if(isset($_POST['activity_id']) && isset($_POST['person_id']) && isset($_POST['date_debut'])){
        $activity_id = $_POST['activity_id'];
        $person_id = $_POST['person_id'];
        $date_debut = $_POST['date_debut'];
        $date_fin = date('Y-m-d H:i:s');
        
        echo "UPDATE activiter SET date_fin='$date_fin' 
                WHERE id_personne='$person_id' AND id_nom_action='$activity_id' AND date_debut='$date_debut' AND date_fin IS NULL";

        $sql = "UPDATE activiter SET date_fin='$date_fin' 
                WHERE id_personne='$person_id' AND id_nom_action='$activity_id' AND date_debut='$date_debut' AND date_fin IS NULL";

        if ($conn->query($sql) === TRUE) {
            if ($conn->affected_rows > 0) {
                echo "<div class='success-box'>Activité arrêtée avec succès.</div>";
            } else {
                echo "<div class='error-box'>Aucune activité n'a été mise à jour.</div>";
            }
        } else {
            echo "<div class='error-box'>Erreur: " . $sql . "<br>" . $conn->error . "</div>";
        }


        $_SESSION['activity_id'] = null;
        $_SESSION['date_debut'] = null;
    }
}
?>
<body>
    <h1>Gestion des activités</h1>
	
    <?php

    $nom    = null;
    $prenom = null;

    // Si la session person_id est définie
    if(isset($_SESSION['id_personne']) && $conn){
        $person_id = $_SESSION['id_personne'];
        $sql = "SELECT nom, prenom FROM personne WHERE id='$person_id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $nom    = $row['nom'];
            $prenom = $row['prenom'];
            //echo "<p>Personne: " . $row['prenom'] . " " . $row['nom'] . "</p>";
        }
    }
    ?>
    <div class="container">
      </form>
      <!-- Bouton pour arrêter l'activité -->
      <?php
      if($conn 
        && isset($_SESSION['id_personne']) && $_SESSION['id_personne'] !== null 
        && isset($_SESSION['activity_id']) && $_SESSION['activity_id'] !== null 
        && isset($_SESSION['date_debut']) && $_SESSION['date_debut'] !== null 
        ){ ?>

        <div class="chrono" id="chronometer">00:00:00</div>
        <form method="post">
            <input type="hidden" name="activity_id" value="<?= $activity_id ?>"/>
            <input type="hidden" name="person_id" value="<?= $person_id ?>"/>
            <input type="hidden" name="date_debut" value="<?= $date_debut ?>"/>
            <input class="button button_stop" type="submit" name="stop_activity" value="Stop"/>
        </form>
     <?php } else { ?>

    <!-- Formulaire pour sélectionner une activité -->
      <h2>Créer une activitée</h2>
      <form class="form" method="post">
        <div class="user-box">
          <label for="nom" class="form__label">Nom</label>
          <input type="text" name="nom" placeholder="Nom" class="form__input" id="nom" required
          <?php if (isset($nom) && $nom !== null) {
             echo "value='" . $nom . "'";
          } ?>
          />
        </div>
        <div class="user-box">
          <label for="prenom" class="form__label">Prénom</label>
          <input type="text" name="prenom" placeholder="Prénom" class="form__input" id="prenom" required
          <?php if (isset($prenom) && $prenom !== null) {
             echo "value='" . $prenom . "'";
          } ?>
          />

        </div>

        <div class="user-box custom-select">
          <select name="activiter" id="choose_activiter">
            <?php
                if($conn){
                    $sql = "SELECT * FROM nom_action";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['nom'] . "</option>";
                        }
                    }
                }
                
            ?>
          </select>
        </div>

        <input class="button button_start" type="submit" name="activity_submit" value="Lancer l'activité"/>
    </div>
<?php
    }
    require './liste.php';
?>

</body>
</html>

<?php
if($conn 
    && isset($_SESSION['id_personne']) && $_SESSION['id_personne'] !== null 
    && isset($_SESSION['activity_id']) && $_SESSION['activity_id'] !== null 
    && isset($_SESSION['date_debut']) && $_SESSION['date_debut'] !== null 
    ){ ?>
<script>
var startTime;
var elapsedTime = 0;
var timerInterval;

// Démarre le chronomètre lorsque la page se charge
window.onload = function() {
    startTime = Date.now() - elapsedTime;
    timerInterval = setInterval(updateTime, 1000);
};

// Met à jour le temps affiché
function updateTime() {
    var elapsedTime = Date.now() - startTime;
    var hours = Math.floor(elapsedTime / 3600000);
    var minutes = Math.floor((elapsedTime % 3600000) / 60000);
    var seconds = Math.floor((elapsedTime % 60000) / 1000);

    // Formatage du temps pour afficher avec des zéros en tête
    var formattedTime = 
        (hours < 10 ? "0" : "") + hours + ":" +
        (minutes < 10 ? "0" : "") + minutes + ":" +
        (seconds < 10 ? "0" : "") + seconds;

    document.getElementById("chronometer").textContent = formattedTime;
}

// Arrête le chronomètre lorsque la page se ferme
window.onbeforeunload = function() {
    clearInterval(timerInterval);
};

</script>

<?php
}
// Fermer la connexion à la base de données
if($conn){
    $conn->close();
}
?>
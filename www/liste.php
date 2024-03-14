<!-- Liste des activités -->
<div class="container">
  <h2>Liste des activités</h2>
      <ul class="responsive-table">
        <li class="table-header">
          <div class="col col-1">Personne</div>
          <div class="col col-2">Activité</div>
          <div class="col col-3">Date de début</div>
          <div class="col col-4">Date de fin</div>
          <div class="col col-4">Nombre de repetition</div>
        </li>

        <?php
        $sql = "SELECT personne.nom AS nom_personne, personne.prenom AS prenom_personne, nom_action.nom AS nom_action, activiter.date_debut, activiter.date_fin, activiter.compte AS compte
                FROM activiter
                INNER JOIN personne ON activiter.id_personne = personne.id 
                INNER JOIN nom_action ON activiter.id_nom_action = nom_action.id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                ?>
                    <li class='table-row'>
                        <div class='col col-1'><?=$row['prenom_personne'] ?> <?= $row['nom_personne'] ?></div>
                        <div class='col col-2'><?= $row['nom_action'] ?></div>
                        <div class='col col-3'><?= $row['date_debut'] ?></div>
                        <div class='col col-4'><?= $row['date_fin'] ?></div>
                        <div class='col col-5'><?= $row['compte'] ?></div>
                    </li>
                <?php
            }
        }
        ?>
    </ul>
</div>
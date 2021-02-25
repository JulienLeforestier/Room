<?php

require_once('inc/init.php');

$title = 'Accueil';

require_once('inc/header.php');

$categories = execRequete(" SELECT DISTINCT categorie FROM salle ORDER BY categorie");
$villes = execRequete(" SELECT DISTINCT ville FROM salle ORDER BY ville");

// corps de la page
?>

<div class="row mt-3 mb-5" id="ajax">
    <div class="col-md-3">
        <a class="list-group-item" href="">Réinitialiser</a>
        <p class="lead pt-3">Catégories</p>
        <div class="list-group">
            <!-- <a class="list-group-item categorie" href="?categorie=">Toutes</a> -->
            <?php while ($categorie = $categories->fetch()) : ?>
                <a class="list-group-item categorie <?php echo (isset($_GET['categorie']) && $_GET['categorie'] == $categorie['categorie']) ? 'active' : '' ?>" href="?categorie=<?php echo $categorie['categorie'] ?>">
                    <?php
                    switch ($categorie['categorie']) {
                        case 'reunion':
                            echo 'Réunion';
                            break;
                        case 'formation':
                            echo 'Formation';
                            break;
                        case 'bureau':
                            echo 'Bureau';
                            break;
                    }
                    ?>
                </a>
            <?php endwhile; ?>
        </div>
        <p class="lead pt-3">Villes</p>
        <div class="list-group">
            <select class="form-control">
                <?php while ($ville = $villes->fetch()) : ?>
                    <option class="list-group-item">
                        <?php echo ucfirst($ville['ville']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <p class="lead pt-3">Capacité</p>
        <div class="list-group">
            <input type="number" class="form-control" id="capacite" name="capacite" placeholder="20">
        </div>
        <p class="lead pt-3">Prix</p>
        <div class="list-group">
            <input type="range" class="range" id="prix" name="prix" min="100" max="10000" value="10000" step="100">
            <output></output>
        </div>
        <p class="lead pt-3">Période</p>
        <div class="list-group">
            <label for="date_arrivee">Date d'arrivée</label>
            <input type="text" class="form-control date" id="date_arrivee" name="date_arrivee" placeholder="00/00/0000 00:00">
        </div>
        <div class="list-group">
            <label for="date_depart">Date de départ</label>
            <input type="text" class="form-control date" id="date_depart" name="date_depart" placeholder="00/00/0000 00:00">
        </div>
    </div>
    <div class="col-md-9" id="resultat">
        <?php require_once('inc/ajax.php') ?>
    </div>
</div>

<?php
require_once('inc/footer.php');

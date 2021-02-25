<?php

require_once('inc/init.php');

$title = 'Fiche produit';

if (!empty($_GET['id_produit'])) {
    $produit = execRequete("SELECT *, s.id_salle AS id_salle FROM produit p
                            INNER JOIN salle s ON s.id_salle = p.id_salle
                            LEFT JOIN avis a ON a.id_salle = s.id_salle
                            WHERE id_produit=:id_produit", array('id_produit' => $_GET['id_produit']));
    if ($produit->rowCount() == 0) {
        $errors[] = 'Produit inexistant <a href="' . URL . '">Revenir à l\'accueil</a>';
    } else {
        $infos = $produit->fetch();
        $title .= ' : ' . $infos['titre'];
        $_SESSION["id_produit"] = $infos['id_produit'];
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['btnReserv'])) {
    unset($_POST["btnReserv"]);
    $_POST["id_membre"] = $_SESSION['membre']['id_membre'];
    $_POST["id_produit"] = $_SESSION['id_produit'];
    execRequete("INSERT INTO commande VALUES (NULL,:id_membre,:id_produit,NOW())", $_POST);
    unset($_POST["id_membre"]);
    $_POST["etat"] = 'reservation';
    execRequete("UPDATE produit SET etat=:etat WHERE id_produit=:id_produit", $_POST);
    header('location:' . URL . 'commandes.php');
    exit();
} else {
    header('location:' . URL);
    exit();
}

require_once('inc/header.php');

// corps de la page
?>

<div class="container-fluid mb-5">
    <div class="row">
        <?php if (!empty($infos)) : ?>
            <h1 class="mt-2 col-11"><?php echo $infos['titre'] ?>
                <?php if ($infos['note']) : ?>
                    <!-- calcul de la note moyenne de la salle actuelle -->
                    <?php $note = execRequete(" SELECT ROUND(AVG(note)) AS note_moyenne FROM salle s 
                                                INNER JOIN avis a ON a.id_salle = s.id_salle
                                                WHERE s.id_salle = :id_salle", array('id_salle' => $infos['id_salle']))->fetch()['note_moyenne']; ?>
                    <?php switch ($note) {
                        case '1':
                            echo '★';
                            break;
                        case '2':
                            echo '★★';
                            break;
                        case '3':
                            echo '★★★';
                            break;
                        case '4':
                            echo '★★★★';
                            break;
                        case '5':
                            echo '★★★★★';
                            break;
                    }
                    ?>
                <?php endif; ?>
            </h1>
            <?php if (isConnected()) : ?>
                <form action="fiche.php" method="post" class="col-1 my-auto"><button type="submit" class="btn btn-primary" name="btnReserv">Réserver</button></form>
            <?php else : ?><a href="connexion.php" class="col-1 my-auto">Connexion</a>
            <?php endif; ?>
    </div>
    <div class="row">
        <div class="col-md-7">
            <img src="<?php echo URL . 'photos/' . $infos['photo'] ?>" alt="<?php echo $infos['titre'] ?>" class="img-fluid">
        </div>
        <div class="col-md-5">
            <h2>Description</h2>
            <p><?php echo $infos['description'] ?></p>
            <h2>Localisation</h2>
            <!-- googleMap -->
            <iframe width="100%" height="450" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=fr&amp;q=<?php echo $infos['adresse']. ',' . $infos['cp'] . ',' . $infos['ville'] . ',' . $infos['pays'] ?>&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe>
        </div>
    </div>
    <br>
    <div class="row">
        <h4>Informations complémentaires</h4>
    </div>
    <div class="row">
        <div class="col-md-4">
            <p>Arrivée : <?php echo date('d/m/Y H:i', strtotime($infos['date_arrivee'])) ?></p>
            <p>Départ : <?php echo date('d/m/Y H:i', strtotime($infos['date_depart'])) ?></p>
        </div>
        <div class="col-md-4">
            <p>Capacité : <?php echo $infos['capacite'] ?></p>
            <p>Catégorie : <?php switch ($infos['categorie']) {
                                case 'reunion':
                                    echo 'Réunion';
                                    break;
                                case 'formation':
                                    echo 'Formation';
                                    break;
                                case 'bureau':
                                    echo 'Bureau';
                                    break;
                            } ?></p>
        </div>
        <div class="col-md-4">
            <p>Adresse : <?php echo $infos['adresse'] . ', ' . $infos['cp'] . ', ' . $infos['ville'] ?></p>
            <p>Tarif : <?php echo number_format($infos['prix'], 2, ',', '&nbsp;') ?>&euro;</p>
        </div>
        <br>
        <?php if (isConnected()) : ?><a <?php echo "href='avis.php?id_salle=" . $infos['id_salle'] . "'" ?>>Déposer un commentaire et une note</a>
        <?php else : ?><a href="connexion.php">Connectez-vous</a>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>
<?php
require_once('inc/footer.php');

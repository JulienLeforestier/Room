<?php

require_once('inc/init.php');

$title = 'Fiche produit';

if (!empty($_GET['id_produit'])) {
    $produit = execRequete("SELECT * FROM produit WHERE id_produit=:id_produit", array('id_produit' => $_GET['id_produit']));
    if ($produit->rowCount() == 0) {
        $errors[] = 'Référence inexistante <a href="' . URL . '">Revenir à l\'accueil</a>';
    } else {
        $infos = $produit->fetch();
        $title .= ' : ' . $infos['titre'];
    }
} else {
    header('location:' . URL);
    exit();
}

require_once('inc/header.php');

// corps de la page
if (!empty($errors)) : ?>
    <div class="alert alert-danger mt-3">
        <?php echo implode('<br>', $errors) ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col">
        <?php if (!empty($infos)) : ?>
            <h1 class="page-header text-center mt-5"><?php echo $infos['titre'] ?></h1>
            <div class="row">
                <div class="col-md-7">
                    <img src="<?php echo URL . 'photos/' . $infos['photo'] ?>" alt="<?php echo $infos['titre'] ?>" class="img-fluid">
                </div>
                <div class="col-md-5">
                    <h2>Description</h2>
                    <p><?php echo $infos['description'] ?></p>
                    <h2>Détails</h2>
                    <ul>
                        <li>Catégorie : <?php echo $infos['categorie'] ?></li>
                        <li>Taille : <?php echo $infos['taille'] ?></li>
                        <li>Couleur : <?php echo $infos['couleur'] ?></li>
                    </ul>
                    <p class="lead">Prix : <?php echo number_format($infos['prix'], 2, ',', '&nbsp;') ?>&euro;</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php if (isConnected()) : ?><a href="avis.php">Déposer un commentaire et une note</a>
<?php else : ?><a href="connexion.php">Connectez-vous</a>
<?php endif; ?>

<?php

require_once('inc/footer.php');
